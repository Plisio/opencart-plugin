<?php

class ModelExtensionPaymentPlisio extends Model
{
    public function install()
    {
        $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "plisio_order` (
        `plisio_order_id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `plisio_invoice_id` VARCHAR(120),
        PRIMARY KEY (`plisio_order_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
    ");

        $this->load->model('setting/setting');

        $defaults = array();

        $defaults['payment_plisio_receive_currencies'] = '';
        $defaults['payment_plisio_order_status_id'] = 1;
        $defaults['payment_plisio_pending_status_id'] = 1;
        $defaults['payment_plisio_confirming_status_id'] = 1;
        $defaults['payment_plisio_paid_status_id'] = 2;
        $defaults['payment_plisio_invalid_status_id'] = 10;
        $defaults['payment_plisio_changeback_status_id'] = 13;
        $defaults['payment_plisio_expired_status_id'] = 14;

        $this->model_setting_setting->editSetting('payment_plisio', $defaults);
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "plisio_order`;");
    }
}
