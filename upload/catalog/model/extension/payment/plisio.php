<?php

class ModelExtensionPaymentPlisio extends Model
{
    public function addOrder($data)
    {
        $query = "INSERT INTO `" . DB_PREFIX . "plisio_order` SET `order_id` = '" . (int)$data['order_id'] . "', `plisio_invoice_id` = '" . $this->db->escape($data['plisio_invoice_id']) . "'";
        if (isset($data['amount']) && !empty($data['amount'])){
            $keys = ['amount', 'hash', 'psys_cid', 'currency', 'expire_utc', 'qr_code'];
            $queryArr = [];
            foreach ($keys as $key){
                if (isset($data[$key]) && !empty($data[$key])) {
                    $queryArr[] = "`$key`='$data[$key]'";
                }
            }
            if (!empty($queryArr)){
                $query .= ', ' . implode(', ', $queryArr);
            }
        }
        $this->db->query($query);
    }

    public function getOrder($order_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "plisio_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

        return $query->row;
    }

    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/plisio');


        $method_data = array(
            'code' => 'plisio',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_plisio_sort_order')
        );

        return $method_data;
    }
}
