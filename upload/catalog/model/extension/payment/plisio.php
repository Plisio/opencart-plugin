<?php

class ModelExtensionPaymentPlisio extends Model
{
    protected function validateRequiredData($data, $extra = [])
    {
        $required = array_merge(['order_id', 'plisio_invoice_id'], $extra);
        $invalid = [];
        foreach ($required as $item) {
            if (!isset($data[$item]) || empty($data[$item])) {
                $invalid[] = $item;
            }
        }
        return $invalid;
    }

    public function addOrder($data)
    {
        $invalid = $this->validateRequiredData($data);
        if (count($invalid) === 0) {
            $query = "INSERT INTO `" . DB_PREFIX . "plisio_order` SET `order_id` = '" . (int)$data['order_id'] . "', `plisio_invoice_id` = '" . $this->db->escape($data['plisio_invoice_id']) . "'";

                try {
                    if (isset($data['wallet_hash']) && !empty($data['wallet_hash'])) {
                        $keys = ['amount', 'pending_amount', 'wallet_hash', 'psys_cid', 'currency', 'status', 'expire_utc', 'qr_code', 'source_currency', 'source_rate', 'expected_confirmations'];
                        $queryArr = [];
                        foreach ($keys as $key) {
                            if (isset($data[$key])) {
                                $queryArr[] = "`$key`='" . $this->db->escape($data[$key]) . "'";
                            }
                        }
                        if (!empty($queryArr)) {
                            $query .= ', ' . implode(', ', $queryArr);
                        }
                    }

                    return $this->db->query($query);
                } catch (Exception $e) {
                    $this->log->write('Plisio::addOrder exception: ' . $e->getMessage());
                }
        } else {
            $this->log->write('Plisio::addOrder ' . implode(', ', $invalid) . ' fields are missing');
        }
        return false;
    }

    public function updateOrder($data)
    {
        $invalid = $this->validateRequiredData($data, ['wallet_hash']);
        if (count($invalid) === 0) {
            try {
                $keys = ['pending_amount', 'status', 'qr_code', 'confirmations', 'tx_urls'];
                $queryArr = [];
                foreach ($keys as $key) {
                    if (isset($data[$key])) {
                        $queryArr[] = "`$key`='" . $this->db->escape($data[$key]) . "'";
                    }
                }
                if (!empty($queryArr)) {
                    $query = "UPDATE `" . DB_PREFIX . "plisio_order` SET ";
                    $query .= implode(', ', $queryArr);
                    $query .= " WHERE `order_id` = '" . (int)$data['order_id'] . "' AND `plisio_invoice_id` = '" . $this->db->escape($data['plisio_invoice_id']) . "'";
                    return $this->db->query($query);
                }

            } catch (Exception $e) {
                $this->log->write('Plisio::updateOrder exception: ' . $e->getMessage());
            }
        } else {
            $this->log->write('Plisio::updateOrder ' . implode(', ', $invalid) . ' fields are missing');
        }
        return false;
    }

    public function setNewCurrency($data) {
        $invalid = $this->validateRequiredData($data, ['wallet_hash']);
        if (count($invalid) === 0) {
            try {
                $keys = ['amount', 'pending_amount', 'wallet_hash', 'status', 'psys_cid', 'currency','qr_code', 'expire_utc', 'qr_code', 'source_currency', 'source_rate', 'expected_confirmations', 'tx_urls'];
                $queryArr = [];
                foreach ($keys as $key) {
                    if (isset($data[$key])) {
                        $queryArr[] = "`$key`='" . $this->db->escape($data[$key]) . "'";
                    }
                }
                $queryArr[] = "plisio_invoice_id='" . $this->db->escape($data['txn_id']) . "'";
                if (!empty($queryArr)) {
                    $query = "UPDATE `" . DB_PREFIX . "plisio_order` SET ";
                    $query .= implode(', ', $queryArr);
                    $query .= " WHERE `order_id` = '" . (int)$data['order_id'] . "' AND `plisio_invoice_id` = '" . $this->db->escape($data['plisio_invoice_id']) . "'";
                    return $this->db->query($query);
                }

            } catch (Exception $e) {
                $this->log->write('Plisio::setNewCurrency exception: ' . $e->getMessage());
            }
        } else {
            $this->log->write('Plisio::setNewCurrency ' . implode(', ', $invalid) . ' fields are missing');
        }
        return false;

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
