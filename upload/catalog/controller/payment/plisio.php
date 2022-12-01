<?php

require_once(DIR_SYSTEM . '/library/plisio/PlisioClient.php');
require_once(DIR_SYSTEM . '/library/plisio/version.php');

class ControllerPaymentPlisio extends Controller
{
    /** @var PlisioClient */
    private $plisio;
    private $secretKey;

    public function index()
    {
        $this->load->language('payment/plisio');
        $this->load->model('checkout/order');
        $this->setupPlisioClient();

        $shop = $this->plisio->getShopInfo();
        $this->data['white_label'] = isset($shop['data']['white_label']) ? $shop['data']['white_label'] : false;
        $this->data['button_confirm_white_label'] = $this->language->get('button_confirm_white_label');
        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['pay_with_text'] = $this->language->get('pay_with_text');

        $this->data['fail'] = isset($this->session->data['fail']) ? $this->session->data['fail'] : false;
        $this->data['action'] = $this->url->link('payment/plisio/checkout', '', 'SSL');

        $this->template = 'default/template/payment/plisio.tpl';

        $this->render();
    }

    public function checkout()
    {
        $this->setupPlisioClient();
        $this->load->model('checkout/order');
        $this->load->model('payment/plisio');

        $orderId = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($orderId);
        $shop = $this->plisio->getShopInfo();

        $description = [];

        foreach ($this->cart->getProducts() as $product) {
            $description[] = $product['quantity'] . ' × ' . $product['name'];
        }

        $amount = $order_info['total'] * $this->currency->getvalue($order_info['currency_code']);

        $siteTitle = is_array($this->config->get('config_meta_title')) ? implode(',', $this->config->get('config_meta_title')) : $this->config->get('config_meta_title');
        $orderName = $siteTitle . ' Order #' . $order_info['order_id'];
        $request = array(
            'source_amount' => number_format($amount, 8, '.', ''),
            'source_currency' => $order_info['currency_code'],
            'order_name' => $orderName,
            'order_number' => $order_info['order_id'],
            'description' => implode(',', $description),
            'cancel_url' => $this->url->link('payment/plisio/callback', '', true),
            'callback_url' => $this->url->link('payment/plisio/callback', '', true),
            'success_url' => $this->url->link('payment/plisio/success', '', true),
            'email' => $order_info['email'],
            'plugin' => 'opencart',
            'version' => PLISIO_OPENCART_EXTENSION_VERSION
        );

        $response = $this->plisio->createTransaction($request);

        if ($response && $response['status'] !== 'error' && !empty($response['data'])) {
            $orderData = array(
                'order_id' => $order_info['order_id'],
                'plisio_invoice_id' => $response['data']['txn_id']
            );
            $orderData = array_merge($orderData, $response['data']);
            $this->model_payment_plisio->addOrder($orderData);
            $this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('plisio_order_status_id'));
            $this->cart->clear();
            $this->session->data['fail'] = false;

            if (isset($shop['data']['white_label']) && $shop['data']['white_label']) {
                $this->redirect($this->url->link('payment/plisio/invoice', '', 'SSL'));
            } else {
                $this->response->redirect($response['data']['invoice_url']);
            }
        } else {
            $this->log->write("Order #" . $order_info['order_id'] . " is not valid. " . (isset($response['data']) && isset($response['data']['message'])) ? $response['data']['message'] : '');
            $this->session->data['fail'] = implode(',', json_decode($response['data']['message'], true));
            $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
    }

    public function invoice()
    {
        $this->load->model('checkout/order');
        $this->load->model('payment/plisio');
        $this->load->language('payment/plisio');
        $this->setupPlisioClient();

        $orderId = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : null;

        if (!$orderId) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        $plisioOrder = $this->model_payment_plisio->getOrder($orderId);
        if (!$plisioOrder) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        }

        $this->data = $plisioOrder;
        $data['plisio_invoice_id'] = $plisioOrder['plisio_invoice_id'];

        $order_info = $this->model_checkout_order->getOrder($orderId);
        if (empty($order_info)) {
            $this->redirect($this->url->link('common/home', '', true));
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );
        $this->template = 'default/template/payment/plisio_invoice.tpl';
        $this->response->setOutput($this->render());
    }

    public function cancel()
    {
        $this->redirect($this->url->link('checkout/cart', ''));
    }

    public function success()
    {
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $this->load->model('payment/plisio');

            $order = $this->model_payment_plisio->getOrder($this->session->data['order_id']);
        } else {
            $order = '';
        }

        if (empty($order)) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
        } else {
            $this->redirect($this->url->link('checkout/success', '', 'SSL'));
        }
    }

    function verifyCallbackData($post)
    {
        $secretKey = $this->secretKey;
        if (!isset($post['verify_hash'])) {
            return false;
        }

        $verifyHash = $post['verify_hash'];
        unset($post['verify_hash']);
        ksort($post);
        if (isset($post['expire_utc'])) {
            $post['expire_utc'] = (string)$post['expire_utc'];
        }
        if (isset($post['tx_urls'])) {
            $post['tx_urls'] = html_entity_decode($post['tx_urls']);
        }
        $postString = serialize($post);
        $checkKey = hash_hmac('sha1', $postString, $secretKey);
        if ($checkKey != $verifyHash) {
            return false;
        }

        return true;
    }

    public function callback()
    {
        $this->setupPlisioClient();
        if ($this->verifyCallbackData($this->request->post)) {
            $this->load->model('checkout/order');
            $this->load->model('payment/plisio');

            $order_id = $this->request->post['order_number'];
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $ext_order = $this->model_payment_plisio->getOrder($order_id);

            $data = $this->request->post;

            if (!empty($order_info) && !empty($ext_order)) {
                if (isset($ext_order['wallet_hash']) && !empty($ext_order['wallet_hash'])) {
                    $data['plisio_invoice_id'] = $data['txn_id'];
                    $data['order_id'] = $order_id;
                    if (isset($data['tx_urls'])) {
                        $data['tx_urls'] = html_entity_decode($data['tx_urls']);
                    }
                    $this->model_payment_plisio->updateOrder($data);
                }

                switch ($data['status']) {
                    case 'completed':
                    case 'mismatch':
                        $cg_order_status = 'plisio_paid_status_id';
                        break;
                    case 'cancelled':
                        $cg_order_status = 'plisio_canceled_status_id';
                        break;
                    case 'expired':
                        if ($data['source_amount'] > 0) {
                            $cg_order_status = 'plisio_invalid_status_id';
                        } else {
                            $cg_order_status = 'plisio_canceled_status_id';
                        }
                        break;
                    default:
                        $cg_order_status = NULL;
                }

                if (!is_null($cg_order_status)) {
                    $comment = '';
                    if (isset($data['comment']) && !empty($data['comment'])) {
                        $comment = $data['comment'];
                    }
                    $this->model_checkout_order->update($order_id, $this->config->get($cg_order_status), $comment/*, true*/);
                }
            } else {
                $this->log->write('Plisio order with id ' . $order_id . ' not found');
            }
            $this->response->addHeader('HTTP/1.1 200 OK');
        } else {
            $this->log->write('Plisio response looks suspicious. Skip updating order');
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
        }
    }

    private function setupPlisioClient()
    {
        $this->secretKey = $this->config->get('plisio_api_secret_key');
        $this->plisio = new PlisioClient($this->secretKey);
    }
}
