<?php

require_once(DIR_SYSTEM . 'library/plisio/PlisioClient.php');

class ControllerExtensionPaymentPlisio extends Controller {
  private $error = array();

  protected $receive_currencies = array();
  protected $payment_plisio_receive_currencies = array();

  public function index() {
    $this->load->language('extension/payment/plisio');
    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');
    $this->load->model('localisation/order_status');
    $this->load->model('localisation/geo_zone');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_plisio', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
    }

    $data['action']             = $this->url->link('extension/payment/plisio', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel']             = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
    $data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();
    $data['geo_zones']          = $this->model_localisation_geo_zone->getGeoZones();
    $data['receive_currencies'] = [];
    $plisio = new PlisioClient('');
    $currencies = $plisio->getCurrencies();
    if (isset($currencies['status']) && $currencies['status'] == 'success') {
      $data['receive_currencies'] = $currencies['data'];
    }
    if (isset($this->error['warning'])) {
        $data['error_warning'] = $this->error['warning'];
    } else {
        $data['error_warning'] = '';
    }

    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );
    $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_extension'),
        'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
    );
    $data['breadcrumbs'][] = array(
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('extension/payment/plisio', 'user_token=' . $this->session->data['user_token'], true)
    );

    $fields = array('payment_plisio_status', 'payment_plisio_api_secret_key', 'payment_plisio_receive_currencies',
      'payment_plisio_order_status_id', 'payment_plisio_pending_status_id', 'payment_plisio_confirming_status_id', 'payment_plisio_paid_status_id',
      'payment_plisio_invalid_status_id', 'payment_plisio_expired_status_id', 'payment_plisio_changeback_status_id', 'payment_plisio_canceled_status_id', 'payment_plisio_white_label'
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
    foreach($data['receive_currencies'] as $currency) {
      $this->receive_currencies[] = $currency['cid'];
    }
    if (is_string($data['payment_plisio_receive_currencies'])) {
      $this->payment_plisio_receive_currencies[] = $data['payment_plisio_receive_currencies'];
    } else {
      $this->payment_plisio_receive_currencies = $data['payment_plisio_receive_currencies'];
    }

    usort($data['receive_currencies'], function($a, $b) {
      $includesA = in_array($a['cid'], $this->payment_plisio_receive_currencies);
      $includesB = in_array($b['cid'], $this->payment_plisio_receive_currencies);

      if ($includesA && !$includesB) {
        return -1;
      } else if (!$includesA && $includesB) {
        return 1;
      } else {
        $a = array_search($a['cid'], $this->receive_currencies);
        $b = array_search($b['cid'], $this->receive_currencies);

        return $b - $a;
      }
    });

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/payment/plisio', $data));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/plisio')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!class_exists('PlisioClient')) {
      $this->error['warning'] = $this->language->get('error_composer');
    }

      if (!isset($this->request->post['payment_plisio_receive_currencies']) || empty($this->request->post['payment_plisio_receive_currencies'])) {
          $this->error['warning'] = $this->language->get('error_no_currencies');
      }

    if (!$this->error) {
        $plisio = new PlisioClient($this->request->post['payment_plisio_api_secret_key']);
        $testConnection = $plisio->getCurrencies();

      if (!isset($testConnection['status']) || $testConnection['status'] !== 'success') {
        $this->error['warning'] = $testConnection['data']['message'];
      }
    }

    return !$this->error;
  }


	public function install() {
        $this->load->model('extension/payment/plisio');

		$this->model_extension_payment_plisio->install();
	}

	public function uninstall() {
		$this->load->model('extension/payment/plisio');

		$this->model_extension_payment_plisio->uninstall();
	}
}
