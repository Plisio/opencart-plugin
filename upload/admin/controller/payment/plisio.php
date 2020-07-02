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
        $this->load->model('localisation/geo_zone');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('plisio', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_api_secret_key'] = $this->language->get('entry_api_secret_key');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_currency'] = $this->language->get('entry_currency');
        $data['entry_currency_hint'] = $this->language->get('entry_currency_hint');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_pending_status'] = $this->language->get('entry_pending_status');
        $data['entry_confirming_status'] = $this->language->get('entry_confirming_status');
        $data['entry_paid_status'] = $this->language->get('entry_paid_status');
        $data['entry_changeback_status'] = $this->language->get('entry_changeback_status');
        $data['entry_expired_status'] = $this->language->get('entry_expired_status');
        $data['entry_invalid_status'] = $this->language->get('entry_invalid_status');
        $data['entry_canceled_status'] = $this->language->get('entry_canceled_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_currency_receive_all'] = $this->language->get('entry_currency_receive_all');
        $data['entry_white_label'] = $this->language->get('entry_white_label');
        $data['white_label_key'] = $this->language->get('white_label_key');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['tab_settings'] = $this->language->get('tab_settings');
        $data['tab_order_status'] = $this->language->get('tab_order_status');

        $data['action'] = $this->url->link('payment/plisio', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $data['receive_currencies'] = [];
        $plisio = new PlisioClient('');
        $currencies = $plisio->getCurrencies();
        if (isset($currencies['status']) && $currencies['status'] == 'success') {
            $data['receive_currencies'] = $currencies['data'];
        }
        if (isset($this->error) && !empty($this->error)) {
            foreach ($this->error as $key => $message) {
                $data['error_' . $key] = $message;
            }
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/plisio', 'token=' . $this->session->data['token'], true)
        );

        $fields = array('plisio_status', 'plisio_api_secret_key', 'plisio_receive_currencies',
            'plisio_order_status_id', 'plisio_pending_status_id', 'plisio_confirming_status_id', 'plisio_paid_status_id',
            'plisio_invalid_status_id', 'plisio_expired_status_id', 'plisio_changeback_status_id',
            'plisio_canceled_status_id', 'plisio_white_label', 'plisio_sort_order'
        );

        $data['white_label_options'] = [
            'false' => 'Disabled',
            'true' => 'Enabled',
        ];

        foreach ($fields as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } else {
                $data[$field] = $this->config->get($field);
            }
        }


        // Currency sort:
        $this->receive_currencies = array_map(function ($item) {
            return $item['cid'];
        }, $data['receive_currencies']);

        // get active currencies CIDs:
        if (is_string($data['plisio_receive_currencies'])) {
            $this->plisio_receive_currencies[] = $data['plisio_receive_currencies'];
        } else {
            $this->plisio_receive_currencies = $data['plisio_receive_currencies'];
        }

        // sort:
        usort($data['receive_currencies'], function ($a, $b) {
            $idxA = array_search($a['cid'], $this->plisio_receive_currencies);
            $idxB = array_search($b['cid'], $this->plisio_receive_currencies);

            $idxA = $idxA === false ? -1 : $idxA;
            $idxB = $idxB === false ? -1 : $idxB;

            if ($idxA < 0 && $idxB < 0) return -1;
            if ($idxA < 0 && $idxB >= 0) return 1;
            if ($idxA >= 0 && $idxB < 0) return -1;
            return $idxA - $idxB;
        });


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/plisio.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/plisio')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!class_exists('PlisioClient')) {
            $this->error['warning'] = $this->language->get('error_composer');
        }

        if (!isset($this->request->post['plisio_receive_currencies']) || empty($this->request->post['plisio_receive_currencies'])) {
            $this->error['plisio_receive_currencies'] = $this->language->get('error_no_currencies');
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


    public function install()
    {
        $this->load->model('payment/plisio');

        $this->model_payment_plisio->install();
    }

    public function uninstall()
    {
        $this->load->model('payment/plisio');

        $this->model_payment_plisio->uninstall();
    }
}
