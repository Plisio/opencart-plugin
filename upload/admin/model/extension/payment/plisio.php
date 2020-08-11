<?php

class ModelExtensionPaymentPlisio extends Model
{
    public function install()
    {
        try {
            $result = $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "plisio_order` (
        `plisio_order_id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `plisio_invoice_id` VARCHAR(40),
        `amount` VARCHAR(40) DEFAULT '',
        `pending_amount` VARCHAR(40) DEFAULT '',
        `wallet_hash` VARCHAR(120) DEFAULT '',
        `psys_cid` VARCHAR(10) DEFAULT '',
        `currency` VARCHAR(10) DEFAULT '',
        `status` VARCHAR(10) DEFAULT 'new',
        `source_currency` VARCHAR(10) DEFAULT '',
        `source_rate` VARCHAR(40) DEFAULT '',
        `expire_utc` DATETIME DEFAULT NULL,
        `qr_code` BLOB DEFAULT NULL,
        `confirmations` TINYINT(2) DEFAULT 0,
        `expected_confirmations` TINYINT(2) DEFAULT 0,
        `tx_urls` TEXT DEFAULT NULL,
        PRIMARY KEY (`plisio_order_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
    ");
        } catch (Exception $exception) {
            $this->log->write('Plisio plugin install table failed: ' . $exception->getMessage());
        }

        $this->load->model('setting/setting');

        $defaults = array();

        $defaults['payment_plisio_receive_currencies'] = '';
        $defaults['payment_plisio_white_label'] = false;
        $defaults['payment_plisio_order_status_id'] = 1;
        $defaults['payment_plisio_pending_status_id'] = 1;
        $defaults['payment_plisio_confirming_status_id'] = 1;
        $defaults['payment_plisio_paid_status_id'] = 5;
        $defaults['payment_plisio_invalid_status_id'] = 10;
        $defaults['payment_plisio_changeback_status_id'] = 13;
        $defaults['payment_plisio_expired_status_id'] = 14;
        $defaults['payment_plisio_canceled_status_id'] = 7;
        $defaults['payment_plisio_sort_order'] = 1;

        $this->model_setting_setting->editSetting('payment_plisio', $defaults);
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "plisio_order`;");
    }
}
