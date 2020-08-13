<?php

require_once(DIR_SYSTEM . '/library/plisio/PlisioClient.php');
require_once(DIR_SYSTEM . '/library/plisio/version.php');

class ControllerPaymentPlisio extends Controller
{
    /** @var PlisioClient */
    private $plisio;
    private $plisio_receive_currencies = array();

    public function index()
    {
        $this->load->language('payment/plisio');
        $this->load->model('checkout/order');
        $this->setupPlisioClient();

        $currencies = $this->plisio->getCurrencies();
        $this->data['currencies'] = $currencies['data'];
        $selectedCurrencies =  $this->config->get('plisio_receive_currencies');
//        $selectedCurrencies = str_replace(['"', '[', ']'], '', $selectedCurrencies);
//        $selectedCurrencies = explode(',', $selectedCurrencies);
        if (!is_array($selectedCurrencies)) $selectedCurrencies = [$selectedCurrencies];

        if (count($selectedCurrencies) > 0) {
            $this->data['currencies'] = array_filter($currencies['data'], function ($i) use ($selectedCurrencies) {
                return in_array($i['cid'], $selectedCurrencies);
            });

            if (!empty($this->data['currencies'])) {
                $this->data['currencies'] = array_values($this->data['currencies']);
            }


            $this->plisio_receive_currencies = $selectedCurrencies;
            usort($this->data['currencies'], function($a, $b) {
                $idxA = array_search($a['cid'], $this->plisio_receive_currencies);
                $idxB = array_search($b['cid'], $this->plisio_receive_currencies);

                $idxA = $idxA === false ? -1 : $idxA;
                $idxB = $idxB === false ? -1 : $idxB;

                if ($idxA < 0 && $idxB < 0) return -1;
                if ($idxA < 0 && $idxB >= 0) return 1;
                if ($idxA >= 0 && $idxB < 0) return -1;
                return $idxA - $idxB;
            });


            $data['pay_with_text'] = $this->language->get('pay_with_text');
            if (is_array($data['currencies']) && count($data['currencies']) == 1) {
                $buttonCaption = sprintf($this->language->get('button_currency_confirm'), $data['currencies'][0]['name'] . ' (' . $data['currencies'][0]['currency'] . ')');
                $data['pay_with_text'] = $buttonCaption;
                $data['button_confirm'] = $buttonCaption;
            }
        }
        if (!isset($this->data['button_confirm'])) {
            $this->data['button_confirm'] = $this->language->get('button_confirm');
        }

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
            'description' => implode(', ', $description),
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
            $orderData = array(
                'order_id' => $order_info['order_id'],
                'plisio_invoice_id' => $response['data']['txn_id']
            );
            if (isset($response['data']) && isset($response['data']['wallet_hash']) && $this->verifyCallbackData($response['data'])) {
                $response['data']['expire_utc'] = date('Y-m-d H:i:s', $response['data']['expire_utc']);
                $orderData = array_merge($orderData, $response['data']);
            }

            $this->model_payment_plisio->addOrder($orderData);

            $this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('plisio_order_status_id'));
            $this->cart->clear();
            if (!isset($orderData['wallet_hash']) || empty($orderData['wallet_hash'])) {
                $this->redirect($response['data']['invoice_url'], '', 'SSL');
            } else {
                $this->redirect($this->url->link('payment/plisio/invoice', '', 'SSL'));
            }
        } else {
            $this->log->write("Order #" . $order_info['order_id'] . " is not valid. " . (isset($response['data']) && isset($response['data']['message'])) ? $response['data']['message'] : '');
            $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        }
    }

    public function invoice()
    {
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

        $data = $plisioOrder;

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($data));
            return;
        } else {
            if (!empty($data['tx_urls'])) {
                try {
                    $txUrl = json_decode($data['tx_urls']);
                    if (!empty($txUrl)) {
                        $txUrl = gettype($txUrl) === 'string' ? $txUrl : $txUrl[count($txUrl) - 1];
                        $data['txUrl'] = $txUrl;
                    }
                } catch (Exception $e) {
                    $this->log->write('Plisio error: ' . $e->getMessage());
                    return;
                }
            }
        }

        $invoiceId = $plisioOrder['plisio_invoice_id'];
        $plisioParsedUrl = parse_url($this->plisio->apiEndPoint);
        $plisioInvoiceUrl = $plisioParsedUrl['scheme'] . '://' . $plisioParsedUrl['host'] . '/invoice/' . $invoiceId;

        if (!isset($plisioOrder['wallet_hash'])) {
            $this->redirect($plisioInvoiceUrl);
        }

        if (isset($data['expire_utc'])) {
            $data['expire_utc'] = (new DateTime($data['expire_utc']))->getTimestamp()*1000;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data = $data;

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );
        $this->template = 'default/template/payment/plisio_invoice.tpl';
//        $this->response->setOutput($this->load->view('default/template/payment/plisio_invoice.tpl', $data));
//        $this->response->setOutput($this->render());
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

    private function verifyCallbackData($data)
    {
        if (!isset($data['verify_hash'])) {
            return false;
        }

        $post = $data;
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
        if ($this->verifyCallbackData($this->request->post)) {
            $this->load->model('checkout/order');
            $this->load->model('payment/plisio');

            $order_id = $this->request->post['order_number'];
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $ext_order = $this->model_payment_plisio->getOrder($order_id);
            $data = $this->request->post;

            if (!empty($order_info) && !empty($ext_order)) {
                if (isset($ext_order['amount']) && !empty($ext_order['amount'])) {
                    $data['plisio_invoice_id'] = $data['txn_id'];
                    $data['order_id'] = $order_id;
                    if (isset($data['tx_urls'])) {
                        $data['tx_urls'] = html_entity_decode($data['tx_urls']);
                    }
                    $this->model_payment_plisio->updateOrder($data);
                }

                if ($ext_order) {
                    switch ($data['status']) {
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
                            if ($data['source_amount'] > 0) {
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
                        if (isset($data['comment']) && !empty($data['comment'])) {
                            $comment = $data['comment'];
                        }
                        $this->model_checkout_order->update($order_id, $this->config->get($cg_order_status), $comment/*, true*/);
                    }
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