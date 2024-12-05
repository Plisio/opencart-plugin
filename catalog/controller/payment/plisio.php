<?php
namespace Opencart\Catalog\Controller\Extension\Plisio\Payment;

use Opencart\System\Engine\Controller;

class Plisio extends Controller
{
    /** @var PlisioClient */
    private $plisio;
    private $secretKey;

    const PLISIO_OPENCART_EXTENSION_VERSION = '4.0.2';

    public function index()
    {
        $this->load->language('extension/plisio/payment/plisio');
        $this->load->model('checkout/order');
        $this->setupPlisioClient();

        $shop = $this->plisio->getShopInfo();
        $data = [];
        $data['white_label'] = $shop['data']['white_label'] ?? false;

        if (!isset($data['button_confirm'])) {
            $data['button_confirm'] = $this->language->get('button_confirm');
        }
		$data['fail'] = $this->session->data['fail'] ?? false;
        $data['action'] = $this->url->link('extension/plisio/payment/plisio.confirm', '');

        return $this->load->view('extension/plisio/payment/plisio', $data);
    }

    public function confirm(): void
    {
        $this->setupPlisioClient();
        $this->load->model('checkout/order');
        $this->load->model('extension/plisio/payment/plisio');

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
            'cancel_url' => $this->url->link('extension/plisio/payment/plisio.callback', ''),
            'callback_url' => $this->url->link('extension/plisio/payment/plisio.callback', ''),
            'success_url' => $this->url->link('extension/plisio/payment/plisio.success', ''),
            'email' => $order_info['email'],
            'plugin' => 'opencart',
            'version' => self::PLISIO_OPENCART_EXTENSION_VERSION,
            'return_existing' => true
        );

        $response = $this->plisio->createTransaction($request);

        if ($response && $response['status'] !== 'error' && !empty($response['data'])) {
            $orderData = array(
                'order_id' => $order_info['order_id'],
                'plisio_invoice_id' => $response['data']['txn_id']
            );
            $orderData = array_merge($orderData, $response['data']);
            $this->model_extension_plisio_payment_plisio->addOrder($orderData);
            $this->model_checkout_order->addHistory($order_info['order_id'], $this->config->get('payment_plisio_order_status_id'), '', true);
            $this->session->data['fail'] = false;
            if (isset($shop['data']['white_label']) && $shop['data']['white_label']) {
                $this->response->redirect($this->url->link('extension/plisio/payment/plisio.invoice', ''));
            } else {
				unset($this->session->data['order_id']);
                $this->response->redirect($response['data']['invoice_url']);
            }
        } else {
            $this->log->write("Order #" . $order_info['order_id'] . " is not valid. " . (isset($response['data']) && isset($response['data']['message'])) ? $response['data']['message'] : '');
			$this->session->data['fail'] = implode(',', json_decode($response['data']['message'], true));
            $this->response->redirect($this->url->link('checkout/checkout', ''));
        }
    }

    public function invoice()
    {
        $this->load->model('checkout/order');
        $this->load->language('extension/plisio/payment/plisio');
        $this->load->model('extension/plisio/payment/plisio');
        $this->setupPlisioClient();

        $orderId = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : null;

        if (!$orderId){
            $this->response->redirect($this->url->link('common/home', ''));
        }

        $plisioOrder = $this->model_extension_plisio_payment_plisio->getOrder($orderId);
        if (!$plisioOrder){
            $this->response->redirect($this->url->link('common/home', ''));
        }

        $data = [];
        $data['plisio_invoice_id'] = $plisioOrder['plisio_invoice_id'];

        $order_info = $this->model_checkout_order->getOrder($orderId);
        if (empty($order_info)) {
            $this->response->redirect($this->url->link('common/home', ''));
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '')
        ];

        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/plisio/payment/plisio_invoice', $data));
    }

    public function cancel()
    {
        $this->response->redirect($this->url->link('checkout/cart', ''));
    }

    public function success()
    {
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $this->load->model('extension/plisio/payment/plisio');

            $order = $this->model_extension_plisio_payment_plisio->getOrder($this->session->data['order_id']);
        } else {
            $order = '';
        }

        if (empty($order)) {
            $this->response->redirect($this->url->link('common/home', ''));
        } else {
            $this->response->redirect($this->url->link('checkout/success', ''));
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
        if (isset($post['expire_utc'])){
            $post['expire_utc'] = (string)$post['expire_utc'];
        }
        if (isset($post['tx_urls'])){
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
            $this->load->model('extension/plisio/payment/plisio');

            $order_id = $this->request->post['order_number'];
            $order_info = $this->model_checkout_order->getOrder($order_id);

            $data = $this->request->post;

            if (!empty($order_info)) {
                $ext_order = $this->model_extension_plisio_payment_plisio->getOrder($order_id);
                if (!empty($ext_order) && isset($ext_order['wallet_hash']) && !empty($ext_order['wallet_hash'])) {
                    $data['plisio_invoice_id'] = $data['txn_id'];
                    $data['order_id'] = $order_id;
                    if (isset($data['tx_urls'])){
                        $data['tx_urls'] = html_entity_decode($data['tx_urls']);
                    }
                    $this->model_extension_plisio_payment_plisio->updateOrder($data);
                }

                switch ($data['status']) {
                    case 'completed':
                    case 'mismatch':
                        $cg_order_status = 'payment_plisio_paid_status_id';
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
                    default:
                        $cg_order_status = NULL;
                }

                if (!is_null($cg_order_status)) {
                    $comment = '';
                    if (isset($data['comment']) && !empty($data['comment'])) {
                        $comment = $data['comment'];
                    }
                    $this->model_checkout_order->addHistory($order_id, $this->config->get($cg_order_status), $comment/*, true*/);
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
        $this->secretKey = $this->config->get('payment_plisio_api_secret_key');
        $this->plisio = new \Opencart\System\Library\Extension\Plisio\Plisioclient($this->secretKey);
    }
}
