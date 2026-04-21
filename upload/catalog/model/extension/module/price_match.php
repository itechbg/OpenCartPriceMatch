<?php
class ModelExtensionModulePriceMatch extends Model {

    public function addRequest($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "price_match`
            SET
                `product_id`       = '" . (int)$data['product_id'] . "',
                `customer_id`      = '" . (int)$data['customer_id'] . "',
                `firstname`        = '" . $this->db->escape($data['firstname']) . "',
                `lastname`         = '" . $this->db->escape($data['lastname']) . "',
                `email`            = '" . $this->db->escape($data['email']) . "',
                `telephone`        = '" . $this->db->escape($data['telephone']) . "',
                `competitor_name`  = '" . $this->db->escape($data['competitor_name']) . "',
                `competitor_url`   = '" . $this->db->escape($data['competitor_url']) . "',
                `competitor_price` = '" . (float)$data['competitor_price'] . "',
                `comment`          = '" . $this->db->escape($data['comment']) . "',
                `status`           = '" . (int)$data['status'] . "',
                `admin_comment`    = '',
                `date_added`       = NOW()
        ");

        return $this->db->getLastId();
    }
}
