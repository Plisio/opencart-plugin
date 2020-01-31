<?php

require_once(DIR_SYSTEM . 'library/plisio/PlisioClient.php');
require_once(DIR_SYSTEM . 'library/plisio/version.php');

class ControllerExtensionPaymentPlisio extends Controller
{
    /** @var PlisioClient */
    private $plisio;

    public function index()
    {
        $this->load->language('extension/payment/plisio');
        $this->load->model('checkout/order');
        $this->setupPlisioClient();

        $currencies = $this->plisio->getCurrencies();
        $data['currencies'] = $currencies['data'];
        $data['currency'] = $this->model_setting_setting->getSettingValue('payment_plisio_receive_currencies');
        if (isset($data['currency']) && !empty($data['currency'])){
            $selectedCurrency = array_filter($currencies['data'], function($i) use ($data){ return $i['cid'] == $data['currency'];});
            if (!empty($selectedCurrency)){
                $selectedCurrency = array_values($selectedCurrency);
            }
            if (is_array($selectedCurrency) && !empty($selectedCurrency)) {
                $data['button_confirm'] = sprintf($this->language->get('button_currency_confirm'), $selectedCurrency[0]['name'] . ' (' . $selectedCurrency[0]['currency'] . ')');
            }
        }
        if (!isset($data['button_confirm'])){
            $data['button_confirm'] =$this->language->get('button_confirm');
        }

        $data['action'] = $this->url->link('extension/payment/plisio/checkout', '', true);

        return $this->load->view('extension/payment/plisio', $data);
    }

    public function checkout()
    {
        $this->setupPlisioClient();
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/plisio');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

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
            'cancel_url' => $this->url->link('extension/payment/plisio/callback', '', true),
            'callback_url' => $this->url->link('extension/payment/plisio/callback', '', true),
            'success_url' => $this->url->link('extension/payment/plisio/success', '', true),
        );

        $response = $this->plisio->createTransaction($request);
        if ($response && $response['status'] !== 'error' && !empty($response['data'])) {
            $this->model_extension_payment_plisio->addOrder(array(
                'order_id' => $order_info['order_id'],
                'plisio_invoice_id' => $response['data']['txn_id']
            ));

            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_plisio_order_status_id'));

            $this->response->redirect($response['data']['invoice_url']);
        } else {
            $this->log->write("Order #" . $order_info['order_id'] . " is not valid. " . (isset($response['data']) && isset($response['data']['message'])) ? $response['data']['message'] : '');
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
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

    public function callback()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/plisio');

        $order_id = $this->request->post['order_number'];
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $ext_order = $this->model_extension_payment_plisio->getOrder($order_id);


        if (!empty($order_info) && !empty($ext_order)) {

            if ($ext_order) {
                switch ($this->request->post['status']) {
                    case 'completed':
                        $cg_order_status = 'payment_plisio_paid_status_id';
                        break;
                    case 'confirming':
                        $cg_order_status = 'payment_plisio_confirming_status_id';
                        break;
                    case 'mismatch':
                        $cg_order_status = 'payment_plisio_invalid_status_id';
                        break;
                    case 'expired':
                        $cg_order_status = 'payment_plisio_expired_status_id';
                        break;
                    case 'canceled':
                        $cg_order_status = 'payment_plisio_canceled_status_id';
                        break;
                    case 'refunded':
                        $cg_order_status = 'payment_plisio_refunded_status_id';
                        break;
                    default:
                        $cg_order_status = NULL;
                }

                if (!is_null($cg_order_status)) {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get($cg_order_status));
                }
            }
        }

        $this->response->addHeader('HTTP/1.1 200 OK');
    }

    private function setupPlisioClient()
    {
        $this->load->model('setting/setting');
        $secretKey = $this->model_setting_setting->getSettingValue('payment_plisio_api_secret_key');
        $this->plisio = new PlisioClient($secretKey);
    }
}
