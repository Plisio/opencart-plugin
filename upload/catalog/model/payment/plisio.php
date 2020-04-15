<?php

class ModelPaymentPlisio extends Model
{
    public function addOrder($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "plisio_order` SET `order_id` = '" . (int)$data['order_id'] . "', `plisio_invoice_id` = '" . $this->db->escape($data['plisio_invoice_id']) . "'");
    }

    public function getOrder($order_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "plisio_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        return $query->row;
    }

    public function getMethod($address, $total)
    {
        $this->load->language('payment/plisio');


        $method_data = array(
            'code' => 'plisio',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('plisio_sort_order')
        );

        return $method_data;
    }
}
