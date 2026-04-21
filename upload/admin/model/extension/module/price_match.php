<?php
class ModelExtensionModulePriceMatch extends Model {

    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "price_match` (
                `price_match_id`   INT(11)          NOT NULL AUTO_INCREMENT,
                `product_id`       INT(11)          NOT NULL DEFAULT '0',
                `customer_id`      INT(11)          NOT NULL DEFAULT '0',
                `firstname`        VARCHAR(32)      NOT NULL DEFAULT '',
                `lastname`         VARCHAR(32)      NOT NULL DEFAULT '',
                `email`            VARCHAR(96)      NOT NULL DEFAULT '',
                `telephone`        VARCHAR(32)      NOT NULL DEFAULT '',
                `competitor_name`  VARCHAR(255)     NOT NULL DEFAULT '',
                `competitor_url`   VARCHAR(512)     NOT NULL DEFAULT '',
                `competitor_price` DECIMAL(15,4)    NOT NULL DEFAULT '0.0000',
                `comment`          TEXT             NOT NULL,
                `status`           TINYINT(1)       NOT NULL DEFAULT '0',
                `admin_comment`    TEXT             NOT NULL,
                `date_added`       DATETIME         NOT NULL,
                PRIMARY KEY (`price_match_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "price_match`");
    }

    public function getRequests($data = array()) {
        $sql = "
            SELECT pm.*, pd.name AS product_name
            FROM `" . DB_PREFIX . "price_match` pm
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON (pm.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
            WHERE 1=1
        ";

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND pm.`status` = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_email'])) {
            $sql .= " AND pm.`email` LIKE '%" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (!empty($data['filter_product'])) {
            $sql .= " AND pd.`name` LIKE '%" . $this->db->escape($data['filter_product']) . "%'";
        }

        $sort_data = array(
            'pm.price_match_id',
            'pd.name',
            'pm.email',
            'pm.competitor_price',
            'pm.date_added',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pm.date_added";
        }

        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalRequests($data = array()) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM `" . DB_PREFIX . "price_match` pm
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON (pm.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
            WHERE 1=1
        ";

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND pm.`status` = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_email'])) {
            $sql .= " AND pm.`email` LIKE '%" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (!empty($data['filter_product'])) {
            $sql .= " AND pd.`name` LIKE '%" . $this->db->escape($data['filter_product']) . "%'";
        }

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    public function getRequest($price_match_id) {
        $query = $this->db->query("
            SELECT pm.*, pd.name AS product_name
            FROM `" . DB_PREFIX . "price_match` pm
            LEFT JOIN `" . DB_PREFIX . "product_description` pd
                ON (pm.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
            WHERE pm.price_match_id = '" . (int)$price_match_id . "'
        ");

        return $query->row;
    }

    public function editRequest($price_match_id, $data) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "price_match`
            SET
                `status`        = '" . (int)$data['status'] . "',
                `admin_comment` = '" . $this->db->escape($data['admin_comment']) . "'
            WHERE `price_match_id` = '" . (int)$price_match_id . "'
        ");
    }

    public function deleteRequest($price_match_id) {
        $this->db->query("
            DELETE FROM `" . DB_PREFIX . "price_match`
            WHERE `price_match_id` = '" . (int)$price_match_id . "'
        ");
    }
}
