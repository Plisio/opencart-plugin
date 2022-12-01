<?php

class ModelPaymentPlisio extends Model
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

        $defaults['plisio_order_status_id'] = 1;
        $defaults['plisio_pending_status_id'] = 1;
        $defaults['plisio_confirming_status_id'] = 1;
        $defaults['plisio_paid_status_id'] = 5;
        $defaults['plisio_invalid_status_id'] = 10;
        $defaults['plisio_changeback_status_id'] = 13;
        $defaults['plisio_expired_status_id'] = 14;
        $defaults['plisio_canceled_status_id'] = 7;
        $defaults['plisio_canceled_sort_order'] = 1;
        $defaults['plisio_sort_order'] = 1;

        $this->model_setting_setting->editSetting('plisio', $defaults);
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "plisio_order`;");
    }
}
