<?php

require_once(DIR_SYSTEM . '/library/plisio/PlisioClient.php');
require_once(DIR_SYSTEM . '/library/plisio/version.php');

class ControllerPaymentPlisio extends Controller
{
    /** @var PlisioClient */
    private $plisio;

    public function index()
    {
        $this->load->language('payment/plisio');
        $this->load->model('checkout/order');
        $this->setupPlisioClient();

        $currencies = $this->plisio->getCurrencies();
        $this->data['currencies'] = $currencies['data'];
        $this->data['currency'] = $this->config->get('plisio_receive_currencies');
        if (isset($this->data['currency']) && !empty($this->data['currency'])) {
            $selectedCurrency = array_filter($currencies['data'], function ($i) {
                return $i['cid'] == $this->data['currency'];
            });
            if (!empty($selectedCurrency)) {
                $selectedCurrency = array_values($selectedCurrency);
            }
            if (is_array($selectedCurrency) && !empty($selectedCurrency)) {
                $this->data['button_confirm'] = sprintf($this->language->get('button_currency_confirm'), $selectedCurrency[0]['name'] . ' (' . $selectedCurrency[0]['currency'] . ')');
            }
        }
        if (!isset($this->data['button_confirm'])) {
            $this->data['button_confirm'] = $this->language->get('button_confirm');
        }

        $this->data['action'] = $this->url->link('payment/plisio/checkout', '', 'SSL');
        $this->data['button_confirm'] = $this->language->get('button_confirm');

        $this->template = 'default/template/payment/plisio.tpl';

        $this->render();
    }

    public function checkout()
    {
        $this->setupPlisioClient();
        $this->load->model('checkout/order');

        $orderId = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($orderId);

        $description = [];

        foreach ($this->cart->getProducts() as $product) {
            $description[] = $product['quantity'] . ' × ' . $product['name'];
        }

        $amount = $order_info['total'] * $this->currency->getvalue($order_info['currency_code']);
        $request = array(
            'source_amount' => number_format($amount, 8, '.', ''),
            'source_currency' => $order_info['currency_code'],
            'currency' => $this->request->post['currency'],
            'order_name' => $this->config->get('config_meta_title') . ' Order #' . $order_info['order_id'],
            'order_number' => $order_info['order_id'],
            'description' => join($description, ', '),
            'cancel_url' => $this->url->link('payment/plisio/callback', '', 'SSL'),
            'callback_url' => $this->url->link('payment/plisio/callback', '', 'SSL'),
            'success_url' => $this->url->link('payment/plisio/success', '', 'SSL'),
            'email' => $order_info['email'],
            'language' => $this->language->get('code'),
            'plugin' => 'opencart',
            'version' => PLISIO_OPENCART_EXTENSION_VERSION
        );


        $response = $this->plisio->createTransaction($request);
        if ($response && $response['status'] !== 'error' && !empty($response['data'])) {
            $this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('plisio_order_status_id'));
            $this->cart->clear();
            $this->redirect($response['data']['invoice_url'], '', 'SSL');
        } else {
            $this->log->write("Order #" . $order_info['order_id'] . " is not valid. " . (isset($response['data']) && isset($response['data']['message'])) ? $response['data']['message'] : '');
            $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
    }

    public function cancel()
    {
        $this->redirect($this->url->link('checkout/cart', ''));
    }

    public function success()
    {
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');

            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        } else {
            $order = '';
        }

        if (empty($order)) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        } else {
            $this->redirect($this->url->link('checkout/success', '', 'SSL'));
        }
    }

    private function verifyCallbackData()
    {
        if (!isset($this->request->post['verify_hash'])) {
            $this->log->write('Callback data has no verify hash');
            return false;
        }

        $post = $this->request->post;
        $verifyHash = $post['verify_hash'];
        unset($post['verify_hash']);
        ksort($post);
        $postString = serialize($post);
        $checkKey = hash_hmac('sha1', $postString, $this->config->get('plisio_api_secret_key'));
        if ($checkKey != $verifyHash) {
            $errorMessage = 'Callback data looks compromised';
            $this->log->write($errorMessage);
            return false;
        }

        return true;
    }

    public function callback()
    {
        if ($this->verifyCallbackData()) {
            $this->load->model('checkout/order');
            $this->load->model('payment/plisio');

            $order_id = $this->request->post['order_number'];
            $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!empty($order_info)) {
            switch ($this->request->post['status']) {
                case 'completed':
                    $cg_order_status = 'plisio_paid_status_id';
                    break;
                case 'confirming':
                    $cg_order_status = 'plisio_confirming_status_id';
                    break;
                case 'error':
                    $cg_order_status = 'plisio_invalid_status_id';
                    break;
                case 'cancelled':
                    $cg_order_status = 'plisio_canceled_status_id';
                    break;
                case 'expired':
                    if ($this->request->post['source_amount'] > 0) {
                        $cg_order_status = 'plisio_invalid_status_id';
                    } else {
                        $cg_order_status = 'plisio_canceled_status_id';
                    }
                    break;
                case 'mismatch':
                    $cg_order_status = 'plisio_changeback_status_id';
                    break;
                default:
                    $cg_order_status = NULL;
            }

            if (!is_null($cg_order_status)) {
                $comment = '';
                if (isset($this->request->post['comment']) && !empty($this->request->post['comment'])) {
                    $comment = $this->request->post['comment'];
                }
                $this->model_checkout_order->update($order_id, $this->config->get($cg_order_status), $comment/*, true*/);
            }
        }
            $this->response->addHeader('HTTP/1.1 200 OK');
        } else {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
        }
    }

    private function setupPlisioClient()
    {
        $secretKey = $this->config->get('plisio_api_secret_key');
        $this->plisio = new PlisioClient($secretKey);
    }
}
