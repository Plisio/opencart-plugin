<?php

require_once(DIR_SYSTEM . 'library/plisio/PlisioClient.php');
require_once(DIR_SYSTEM . 'library/plisio/version.php');

class ControllerExtensionPaymentPlisio extends Controller
{
    /** @var PlisioClient */
    private $plisio;

    private function get_plisio_receive_currencies ($source_currency) {
        $currencies = $this->plisio->getCurrencies($source_currency);
        return array_reduce($currencies, function ($acc, $curr) {
            $acc[$curr['cid']] = $curr;
            return $acc;
        }, []);
    }

    public function index()
    {
        $this->load->language('extension/payment/plisio');
        $this->load->model('checkout/order');
        $this->setupPlisioClient();

        $shop = $this->plisio->getShopInfo();
        $data = [];
        $data['white_label'] = isset($shop['data']['white_label']) ? $shop['data']['white_label'] : false;

        if (!isset($data['button_confirm'])) {
            $data['button_confirm'] = $this->language->get('button_confirm');
        }

        $data['action'] = $this->url->link('extension/payment/plisio/checkout', '', true);

        return $this->load->view('extension/payment/plisio', $data);
    }

    public function checkout()
    {
        $this->setupPlisioClient();
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/plisio');

        $orderId = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($orderId);

        $this->session->data['invoice_currency_set'] = false;
        $plisio_receive_currencies = $this->get_plisio_receive_currencies($order_info['currency_code']);
        $plisio_receive_cids = array_keys($plisio_receive_currencies);
        $description = [];

        foreach ($this->cart->getProducts() as $product) {
            $description[] = $product['quantity'] . ' × ' . $product['name'];
        }

        $amount = $order_info['total'] * $this->currency->getvalue($order_info['currency_code']);
        // min_sum check:
        $defaultSelectedCurrency = reset($plisio_receive_currencies);
        if (!empty($plisio_receive_currencies)
            && $defaultSelectedCurrency['min_sum_in'] > $amount * $defaultSelectedCurrency['fiat_rate']
        ) {
            $this->load->language('extension/payment/plisio');
            $data = [
                'allowed_currencies' => [$defaultSelectedCurrency['currency'] => $defaultSelectedCurrency],
                'amount' => $amount * $defaultSelectedCurrency['fiat_rate'],
                'currency' =>  $defaultSelectedCurrency['currency'],
                'source_rate' =>  $defaultSelectedCurrency['fiat_rate'],
                'source_currency' =>  $defaultSelectedCurrency['fiat'],
                'order_id' =>  $order_info['order_id'],
            ];
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $this->response->setOutput($this->load->view('extension/payment/plisio_invoice', $data));
            return;
        }

        $siteTitle = is_array($this->config->get('config_meta_title')) ? implode(',', $this->config->get('config_meta_title')) : $this->config->get('config_meta_title');
        $orderName = $siteTitle . ' Order #' . $order_info['order_id'];
        $request = array(
            'source_amount' => number_format($amount, 8, '.', ''),
            'source_currency' => $order_info['currency_code'],
            'currency' => $plisio_receive_cids[0],
            'order_name' => $orderName,
            'order_number' => $order_info['order_id'],
            'description' => implode(',', $description),
            'cancel_url' => $this->url->link('extension/payment/plisio/callback', '', true),
            'callback_url' => $this->url->link('extension/payment/plisio/callback', '', true),
            'success_url' => $this->url->link('extension/payment/plisio/success', '', true),
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

            if (isset($response['data']) && isset($response['data']['wallet_hash'])){
                if ($this->verifyCallbackData($response['data'])) {
                    $response['data']['expire_utc'] = date('Y-m-d H:i:s', $response['data']['expire_utc']);
                    $orderData = array_merge($orderData, $response['data']);
                    $this->model_extension_payment_plisio->addOrder($orderData);
                } else {
                    $this->log->write($response['data']);
                    $this->log->write('Plisio response looks suspicious. Skip adding order');
                }
            }

            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_plisio_order_status_id'));
            $this->cart->clear();
            if (!isset($orderData['wallet_hash']) || empty($orderData['wallet_hash'])) {
                $this->response->redirect($response['data']['invoice_url']);
            } else {
                $this->response->redirect($this->url->link('extension/payment/plisio/invoice', '', true));
            }
        } else {
            $this->log->write("Order #" . $order_info['order_id'] . " is not valid. " . (isset($response['data']) && isset($response['data']['message'])) ? $response['data']['message'] : '');
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
    }

    public function chooseCurrency ()   // white-label only
    {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->response->redirect($this->url->link('common/home', '', true));
        }
        $this->response->addHeader('Content-Type: application/json');

        $this->load->model('checkout/order');
        $this->load->language('extension/payment/plisio');
        $this->load->model('extension/payment/plisio');
        $this->setupPlisioClient();

        $plisio_order = $this->model_extension_payment_plisio->getOrder($this->request->post['order_id']);
        $order_info = $this->model_checkout_order->getOrder($this->request->post['order_id']);

        if ($plisio_order['currency'] != $this->request->post['currency']) {

            $siteTitle = is_array($this->config->get('config_meta_title'))
                ? implode(',', $this->config->get('config_meta_title'))
                : $this->config->get('config_meta_title');
            $orderName = $siteTitle . ' Order #' . $plisio_order['order_id'];

            $request = array(
                'invoice' => $this->request->post['invoice'],
                'source_amount' => floatval($order_info['total'] * $order_info['currency_value']),
                'source_currency' => $plisio_order['source_currency'],
                'currency' => $this->request->post['currency'],
                'allowed_psys_cids' => $this->request->post['currency'],
                'order_number' => $this->request->post['order_id'],
                'order_name' => $orderName,
                'cancel_url' => $this->url->link('extension/payment/plisio/callback', '', true),
                'callback_url' => $this->url->link('extension/payment/plisio/callback', '', true),
                'success_url' => $this->url->link('extension/payment/plisio/success', '', true),
                'email' => $order_info['email'],
                'language' => $this->language->get('code'),
                'plugin' => 'opencart',
                'version' => PLISIO_OPENCART_EXTENSION_VERSION
            );

            $response = $this->plisio->createTransaction($request);
            if ($response && $response['status'] !== 'error' && !empty($response['data'])) {
                if (isset($response['data']['wallet_hash'])) {
                    if ($this->verifyCallbackData($response['data'])) {
                        $response['data']['expire_utc'] = date('Y-m-d H:i:s', $response['data']['expire_utc']);
                        $orderData = array_merge([
                            'order_id' => $plisio_order['order_id'],
                            'plisio_invoice_id' => $this->request->post['invoice']
                        ], $response['data']);
                        if ($this->model_extension_payment_plisio->setNewCurrency($orderData)) {
                            $this->session->data['invoice_currency_set'] = true;
                            $this->response->setOutput(json_encode([
                                'redirect' => $this->url->link('extension/payment/plisio/invoice')
                            ]));
                        } else {
                            return false;
                        }
                    }
                } else {
                    $this->log->write('Plisio response looks suspicious. Skip adding order');
                }
            }
        } else {
            $this->session->data['invoice_currency_set'] = true;
            $this->response->setOutput(json_encode([
                'redirect' => $this->url->link('extension/payment/plisio/invoice')
            ]));
        }
    }

    public function invoice()
    {
        $this->load->model('checkout/order');
        $this->load->language('extension/payment/plisio');
        $this->load->model('extension/payment/plisio');
        $this->setupPlisioClient();

        $orderId = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : null;

        if (!$orderId){
            $this->response->redirect($this->url->link('common/home', '', true));
        }

        $plisioOrder = $this->model_extension_payment_plisio->getOrder($orderId);
        if (!$plisioOrder){
            $this->response->redirect($this->url->link('common/home', '', true));
        }

        $data = $plisioOrder;
        $data['allowed_currencies'] = [];
        if (!isset($this->session->data['invoice_currency_set']) || $this->session->data['invoice_currency_set'] !== true) {
            $data['allowed_currencies'] = $this->get_plisio_receive_currencies($plisioOrder['source_currency']);
        }
        $order_info = $this->model_checkout_order->getOrder($orderId);
        if (empty($order_info)) {
            $this->response->redirect($this->url->link('common/home', '', true));
        }
        $data['checkout_total_fiat'] = floatval($order_info['total'] * $order_info['currency_value']);

        $shopInfo = $this->plisio->getShopInfo();
        if (empty($shopInfo) || empty($shopInfo['data']) || !isset($shopInfo['data']['extra_commission']) || !isset($shopInfo['data']['commission_payment'])) {
            $this->response->redirect($this->url->link('common/home', '', true));
        }
        $data['extra_commission'] = $shopInfo['data']['extra_commission'];
        $data['commission_payment'] = $shopInfo['data']['commission_payment'];

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($data));
            return;
        }   else {
            if (!empty($data['tx_urls'])) {
                try {
                    $txUrl = json_decode($data['tx_urls']);
                    if (!empty($txUrl)) {
                        $txUrl = gettype($txUrl) === 'string' ? $txUrl : $txUrl[count($txUrl) - 1];
                        $data['txUrl'] = $txUrl;
                    }
                } catch (Exception $e) {
                    $this->log->write('Plisio error: '. $e->getMessage());
                    return;
                }
            }
        }

        $invoiceId = $plisioOrder['plisio_invoice_id'];
        $plisioParsedUrl = parse_url($this->plisio->apiEndPoint);
        $plisioInvoiceUrl = $plisioParsedUrl['scheme'] . '://' . $plisioParsedUrl['host'] . '/invoice/' . $invoiceId;

        if (!isset($plisioOrder['wallet_hash'])){
            $this->response->redirect($plisioInvoiceUrl);
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', true)
        );

        if (isset($data['expire_utc'])){
            $data['expire_utc'] = (new DateTime($data['expire_utc']))->getTimestamp()*1000;
        }

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('extension/payment/plisio_invoice', $data));
    }

    public function cancel()
    {
        $this->response->redirect($this->url->link('checkout/cart', ''));
    }

    public function success()
    {
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/plisio');

            $order = $this->model_extension_payment_plisio->getOrder($this->session->data['order_id']);
        } else {
            $order = '';
        }

        if (empty($order)) {
            $this->response->redirect($this->url->link('common/home', '', true));
        } else {
            $this->response->redirect($this->url->link('checkout/success', '', true));
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
        if (isset($post['expire_utc'])){
            $post['expire_utc'] = (string)$post['expire_utc'];
        }
        if (isset($post['tx_urls'])){
            $post['tx_urls'] = html_entity_decode($post['tx_urls']);
        }
        $postString = serialize($post);
        $this->load->model('setting/setting');
        $checkKey = hash_hmac('sha1', $postString, $this->model_setting_setting->getSettingValue('payment_plisio_api_secret_key'));

        if ($checkKey != $verifyHash) {
            return false;
        }

        return true;
    }

    public function callback()
    {
        if ($this->verifyCallbackData($this->request->post)) {
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/plisio');

            $order_id = $this->request->post['order_number'];
            $order_info = $this->model_checkout_order->getOrder($order_id);

            $data = $this->request->post;

            if (!empty($order_info)) {
                $ext_order = $this->model_extension_payment_plisio->getOrder($order_id);
                if (!empty($ext_order) && isset($ext_order['wallet_hash']) && !empty($ext_order['wallet_hash'])) {
                    $data['plisio_invoice_id'] = $data['txn_id'];
                    $data['order_id'] = $order_id;
                    if (isset($data['tx_urls'])){
                        $data['tx_urls'] = html_entity_decode($data['tx_urls']);
                    }
                    $this->model_extension_payment_plisio->updateOrder($data);
                }

                switch ($data['status']) {
                    case 'completed':
                        $cg_order_status = 'payment_plisio_paid_status_id';
                        break;
                    case 'confirming':
                        $cg_order_status = 'payment_plisio_confirming_status_id';
                        break;
                    case 'error':
                        $cg_order_status = 'payment_plisio_invalid_status_id';
                        break;
                    case 'cancelled':
                        $cg_order_status = 'payment_plisio_canceled_status_id';
                        break;
                    case 'expired':
                        if ($data['source_amount'] > 0) {
                            $cg_order_status = 'payment_plisio_invalid_status_id';
                        } else {
                            $cg_order_status = 'payment_plisio_canceled_status_id';
                        }
                        break;
                    case 'mismatch':
                        $cg_order_status = 'payment_plisio_changeback_status_id';
                        break;
                    default:
                        $cg_order_status = NULL;
                }

                if (!is_null($cg_order_status)) {
                    $comment = '';
                    if (isset($data['comment']) && !empty($data['comment'])) {
                        $comment = $data['comment'];
                    }
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get($cg_order_status), $comment/*, true*/);
                }
            } else {
                $this->log->write('Plisio order with id '. $order_id . ' not found');
            }
            $this->response->addHeader('HTTP/1.1 200 OK');
        } else {
            $this->log->write('Plisio response looks suspicious. Skip updating order');
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
        }
    }

    private function setupPlisioClient()
    {
        $this->load->model('setting/setting');
        $secretKey = $this->model_setting_setting->getSettingValue('payment_plisio_api_secret_key');
        $this->plisio = new PlisioClient($secretKey);
    }
}
