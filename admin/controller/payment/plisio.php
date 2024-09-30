<?php

namespace Opencart\Admin\Controller\Extension\Plisio\Payment;

use Opencart\Extension\Plisio\System\Library\Plisio\PlisioClient;
use Opencart\System\Engine\Controller;

class Plisio extends Controller
{
    private $error = [];

    public function index(): void
    {
        $this->load->language('extension/plisio/payment/plisio');
        $this->load->model('extension/plisio/payment/plisio');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['save'] = $this->url->link('extension/plisio/payment/plisio.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/plisio/payment/plisio', 'user_token=' . $this->session->data['user_token'])
        ];

        $fields = ['payment_plisio_status', 'payment_plisio_api_secret_key', 'payment_plisio_order_status_id', 'payment_plisio_pending_status_id', 'payment_plisio_confirming_status_id',
            'payment_plisio_paid_status_id', 'payment_plisio_invalid_status_id', 'payment_plisio_expired_status_id',
            'payment_plisio_changeback_status_id', 'payment_plisio_canceled_status_id',
            'payment_plisio_sort_order'
        ];

        foreach ($fields as $field) {
            $data[$field] = $this->config->get($field);
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/plisio/payment/plisio', $data));
    }

    public function save(): void
    {
        $this->load->language('extension/plisio/payment/plisio');

        $this->load->model('extension/plisio/payment/plisio');

        if (!$this->user->hasPermission('modify', 'extension/plisio/payment/plisio')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('payment_plisio', $this->request->post);

            $data['success'] = $this->language->get('text_success');
        }

        $data['error'] = $this->error;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function install(): void
    {
        $this->load->model('extension/plisio/payment/plisio');
		$this->model_extension_plisio_payment_plisio->install();
    }

    public function uninstall(): void
    {
        $this->load->model('extension/plisio/payment/plisio');
		$this->model_extension_plisio_payment_plisio->uninstall();
    }
}
