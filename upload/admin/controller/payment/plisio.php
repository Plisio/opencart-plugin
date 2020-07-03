<?php

require_once(DIR_SYSTEM . 'library/plisio/PlisioClient.php');

class ControllerPaymentPlisio extends Controller
{
    private $error = array();

    protected $receive_currencies = array();
    protected $plisio_receive_currencies = array();

    public function index()
    {
        $this->load->language('payment/plisio');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
//


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('plisio', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
//			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_api_secret_key'] = $this->language->get('entry_api_secret_key');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $this->data['entry_currency'] = $this->language->get('entry_currency');
        $this->data['entry_currency_hint'] = $this->language->get('entry_currency_hint');
        $this->data['entry_order_status'] = $this->language->get('entry_order_status');
        $this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
        $this->data['entry_confirming_status'] = $this->language->get('entry_confirming_status');
        $this->data['entry_paid_status'] = $this->language->get('entry_paid_status');
        $this->data['entry_changeback_status'] = $this->language->get('entry_changeback_status');
        $this->data['entry_expired_status'] = $this->language->get('entry_expired_status');
        $this->data['entry_invalid_status'] = $this->language->get('entry_invalid_status');
        $this->data['entry_canceled_status'] = $this->language->get('entry_canceled_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['entry_currency_receive_all'] = $this->language->get('entry_currency_receive_all');
        $this->data['entry_white_label'] = $this->language->get('entry_white_label');
        $this->data['white_label_key'] = $this->language->get('white_label_key');

        $this->data['action'] = $this->url->link('payment/plisio', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['receive_currencies'] = [];
        $plisio = new PlisioClient('');
        $currencies = $plisio->getCurrencies();
        if (isset($currencies['status']) && $currencies['status'] == 'success') {
            $this->data['receive_currencies'] = $currencies['data'];
        }

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/plisio', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $fields = array(
            'plisio_status', 'plisio_api_secret_key', 'plisio_receive_currencies',
            'plisio_order_status_id', 'plisio_pending_status_id', 'plisio_confirming_status_id',
            'plisio_paid_status_id', 'plisio_invalid_status_id', 'plisio_expired_status_id',
            'plisio_changeback_status_id', 'plisio_canceled_status_id', 'plisio_white_label',
            'plisio_sort_order'
        );

        $this->data['white_label_options'] = [
            'false' => 'Disabled',
            'true' => 'Enabled',
        ];

        $defaults = [
            'plisio_order_status_id' => 1,
            'plisio_pending_status_id' => 1,
            'plisio_confirming_status_id' => 1,
            'plisio_paid_status_id' => 5,
            'plisio_changeback_status_id' => 13,
            'plisio_expired_status_id' => 14,
            'plisio_invalid_status_id' => 10,
            'plisio_canceled_status_id' => 7,
            'plisio_sort_order' => 1
        ];
        foreach ($fields as $field) {
            if (isset($this->request->post[$field])) {
                $this->data[$field] = $this->request->post[$field];
            } else {
                $value = $this->config->get($field);
                if (!$value && isset($defaults[$field])) $value = $defaults[$field];
                $this->data[$field] = $value;
            }
        }

        // Currency sort:
        $this->receive_currencies = array_map(function ($item) {
            return $item['cid'];
        }, $this->data['receive_currencies']);

        // get active currencies CIDs:
        if (!is_array($this->data['plisio_receive_currencies'])) {
            $this->plisio_receive_currencies[] = $this->data['plisio_receive_currencies'];
        } else {
            $this->plisio_receive_currencies = $this->data['plisio_receive_currencies'];
        }
        $this->data['plisio_receive_currencies'] = $this->plisio_receive_currencies;

        // sort:
        usort($this->data['receive_currencies'], function ($a, $b) {
            $idxA = array_search($a['cid'], $this->plisio_receive_currencies);
            $idxB = array_search($b['cid'], $this->plisio_receive_currencies);

            $idxA = $idxA === false ? -1 : $idxA;
            $idxB = $idxB === false ? -1 : $idxB;

            if ($idxA < 0 && $idxB < 0) return -1;
            if ($idxA < 0 && $idxB >= 0) return 1;
            if ($idxA >= 0 && $idxB < 0) return -1;
            return $idxA - $idxB;
        });

        $this->template = 'payment/plisio.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/plisio')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['plisio_receive_currencies']) || empty($this->request->post['plisio_receive_currencies'])) {
            $this->error['warning'] = $this->language->get('error_no_currencies');
        }

        if (!class_exists('PlisioClient')) {
            $this->error['warning'] = $this->language->get('error_composer');
        }

        if (!$this->error) {
            $plisio = new PlisioClient($this->request->post['plisio_api_secret_key']);
            $testConnection = $plisio->getCurrencies();

            if (!isset($testConnection['status']) || $testConnection['status'] !== 'success') {
                $this->error['warning'] = $testConnection['data']['message'];
            }
        }

        return !$this->error;
    }

//
//    public function install()
//    {
//        $this->load->model('extension/payment/plisio');
//
//        $this->model_extension_plisio->install();
//    }
//
//    public function uninstall()
//    {
//        $this->load->model('extension/payment/plisio');
//
//        $this->model_extension_plisio->uninstall();
//    }
}
