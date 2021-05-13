<?php

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

/**
 * Storage
 * add to storage
 * remove from storage
 */
class Storage {

    /**
     * ID заказа
     * @var integer
     */
    private $id = 0;

    /**
     * Новый "статус отправки"
     * @var string
     */
    private $status_sending = NULL;

    /**
     * Разница если менялось к-во
     * @var integer
     */
    private $sum = 0;

    /**
     * Предыдущий "статус отправки"
     * @var string
     */
    private $old_status_sending = NULL;

    /**
     * Новый "статус доставки"
     * @var string
     */
    private $status_delivery = NULL;

    /**
     * Предыдущий "статус доставки"
     * @var string
     */
    private $old_status_delivery = NULL;

    /**
     * Доступные свойства для склада
     * @var array
     */
    private $allowed_attributes = array('color', 'size', 'type', 'vendor');

    /**
     * Список офферов для обработки
     * @var array
     */
    private $offers = array();

    /**
     * Init storage
     * @param int $id Order ID
     * @param string $status_sending New order sending status
     * @param string $old_status_sending Old order sending status
     * @param string $status_delivery Deliverung status
     * @param string $old_status_delivery Old deliverung status
     * @param int $sum ???
     * @example status_sending - 'Груз отправлен','На контроль','На отправку','Оплачен','Отказ','Отправлен'
     * @example status_delivery - 'Автоответчик','Возврат денег','Груз в дороге','Груз вручен','Заберет','На доставку','На контроль','Нет товара','Обработка','Обратная доставка отправлена','Отложенная доставка','Перезвонить','Получен','Проблемный','Проверен','Располовинен','Свежий','Сделать замену','Упакован','Упакован добавочный','Упакован на почте','Упакован принят','Хранение','Частичный возврат'
     */
    public function Storage($id = 0, $status_sending = NULL, $old_status_sending = NULL, $status_delivery = NULL, $old_status_delivery = NULL, $sum = 0) {
        if (empty($id) || empty($status_sending)) {
            return false;
        }

        $this->id = (int) $id;
        $this->status_sending = $status_sending;
        $this->old_status_sending = $old_status_sending;
        $this->status_delivery = $status_delivery;
        $this->old_status_delivery = $old_status_delivery;

        ApiLogger::addLogJson("---------------------------------------------------------------------");
        ApiLogger::addLogJson($this->id . " / " . $this->status_sending . " / " . $this->old_status_sending . " / " . $this->status_delivery . " / " . $this->old_status_delivery);

        $sql = mysql_query("
            SELECT
                `offer`,
                `other_data` AS `attributes`,
                `package`,
                `dop_tovar`,
                `country`,
                `kz_delivery`
            FROM
                `staff_order`
            WHERE
                `id` = " . $this->id . "
            LIMIT 1
        ");

        $order = mysql_fetch_assoc($sql);

        if (!is_array($order)) {
            ApiLogger::addLogJson("Not found order #" . $this->id);
            return false;
        }

        // main offer
        $offer_attributes = "";

        $attributes = json_decode($order['attributes'], true);

        if (json_last_error() == JSON_ERROR_NONE && is_array($attributes)) {
            foreach ($attributes AS $key => $val) {
                if (!in_array($key, $this->allowed_attributes)) {
                    unset($attributes[$key]);
                }
            }

            if (count($attributes) > 0) {
                ksort($attributes); // сортировка свойств по ключу (по возростанию)
                $offer_attributes = "[" . implode("][", $attributes) . "]";
            }
        }

        $order['kz_delivery'] = ($order['kz_delivery'] == "Почта" ? "Астана Курьер" : $order['kz_delivery']);

        $storage_hash = md5($order['offer'] . $offer_attributes . $order['kz_delivery']);

        $this->offers[$storage_hash] = array(
            'hash' => $storage_hash,
            'package' => (int) $order['package'],
            'name' => $order['offer'],
            'kz_delivery' => $order['kz_delivery'],
                // 'test' => $order['offer'] . $offer_attributes . $order['kz_delivery']
        );

        // additional offers
        $dop_tovar = json_decode($order['dop_tovar'], true);

        if (json_last_error() == JSON_ERROR_NONE && is_array($dop_tovar)) {
            if (isset($dop_tovar['dop_tovar'])) {
                $count_dop_tovar = count($dop_tovar['dop_tovar']);

                for ($i = 0; $i < $count_dop_tovar; $i++) {
                    $attributes = array();

                    foreach ($dop_tovar AS $property_key => $property_val) {
                        if (in_array($property_key, $this->allowed_attributes)) {
                            if (!empty($property_val[$i])) {
                                $attributes[$property_key] = $property_val[$i];
                            }
                        }
                    }

                    $offer_attributes = "";

                    if (count($attributes) > 0) {
                        ksort($attributes); // сортировка свойств по ключу (по возростанию)
                        $offer_attributes = "[" . implode("][", $attributes) . "]";
                    }

                    $storage_hash = md5($dop_tovar['dop_tovar'][$i] . $offer_attributes . $order['kz_delivery']);

                    if (isset($this->offers[$storage_hash])) {
                        $this->offers[$storage_hash]['package'] += (int) $dop_tovar['dop_tovar_count'][$i];
                    } else {
                        $this->offers[$storage_hash] = array(
                            'hash' => $storage_hash,
                            'package' => (int) $dop_tovar['dop_tovar_count'][$i],
                            'name' => $dop_tovar['dop_tovar'][$i],
                            'kz_delivery' => $order['kz_delivery'],
                                // 'test' => $dop_tovar['dop_tovar'][$i] . $offer_attributes . $order['kz_delivery']
                        );
                    }
                }
            }
        }

        foreach ($this->offers as $offer) {
            if ($this->status_delivery != $this->old_status_delivery) {
                if ($this->status_delivery == "На доставку") {
                    $this->removeStorage($offer, $sum);
                } elseif (in_array($this->status_delivery, array("Обработка", "Отложенная доставка")) && $this->old_status_delivery == "На доставку") {
                    $this->addStorage($offer, $sum);
                } elseif (in_array($this->status_delivery, array("Обратная доставка отправлена")) && in_array($this->old_status_delivery, array("Хранение", "Заберет", "Груз вручен", "Получен", "Проверен", "Перезвонить", "Груз вручен", "Отложенная доставка"))) {
                    $this->addStorage($offer, $sum);
                }
            }

            if ((int) $sum != 0) {
                if ($this->old_status_delivery == "На доставку") {
                    if ((int) $sum > 0) {
                        $this->addStorage($offer, $sum);
                    } else {
                        $this->removeStorage($offer, $sum);
                    }
                }
            }
        }
    }

    /**
     * Add to storage
     * @param array $offer Offer data
     * @param int $sum ???
     * @return boolean
     */
    private function addStorage($offer = array(), $sum = 0) {
        $query_storage = mysql_query("SELECT `id` FROM `storage` WHERE `hash` = '" . $offer['hash'] . "' LIMIT 1");
        $storage = mysql_fetch_assoc($query_storage);

        if ($storage === false || !is_array($storage)) {
            return false;
        }

        if ($sum > 0) {
            $offer['package'] = $sum;
        }

        $sql_storage_action = "
            INSERT INTO
                `storage_action`
            SET
                `storage_id` = '" . $storage['id'] . "',
                `action_type` = 'return',
                `action_value` = '" . $offer['package'] . "',
                `staff_id` = '" . $_SESSION['Logged_StaffId'] . "',
                `staff_offer_id` = '" . $this->id . "',
                `delivery` = '" . $offer['kz_delivery'] . "'
        ";

        mysql_query($sql_storage_action);

        $this->updateStorage($offer['hash'], $offer['package'], "add");

        ApiLogger::addLogJson("addStorage storage_id " . $storage['id']);

        return true;
    }

    /**
     * Remove from storage
     * @param array $offer Offer data
     * @param int $sum ???
     * @return boolean
     */
    private function removeStorage($offer = array(), $sum = 0) {
        $query_storage = mysql_query("SELECT `id` FROM `storage` WHERE `hash` = '" . $offer['hash'] . "' LIMIT 1");
        $storage = mysql_fetch_assoc($query_storage);

        if ($storage === false || !is_array($storage)) {
            return false;
        }

        if ($sum < 0) {
            $offer['package'] = $sum * -1;
        }

        $sql_storage_action = "
            INSERT INTO
                `storage_action`
            SET
                `storage_id` = '" . $storage['id'] . "',
                `action_type` = 'send',
                `action_value` = '" . $offer['package'] . "',
                `staff_id` = '" . $_SESSION['Logged_StaffId'] . "',
                `staff_offer_id` = '" . $this->id . "',
                `delivery` = '" . $offer['kz_delivery'] . "'
        ";

        mysql_query($sql_storage_action);

        $this->updateStorage($offer['hash'], $offer['package'], "remove");

        ApiLogger::addLogJson("removeStorage storage_id " . $storage['id']);

        return true;
    }

    /**
     * Update storage goods count
     * @param string $hash Storage hash
     * @param int $count Storage goods count
     * @param string $action Action
     */
    public function updateStorage($hash = NULL, $count = NULL, $action = NULL) {
        if (!$hash) {
            return false;
        }

        if (!is_int($count)) {
            return false;
        }

        if (!$action) {
            return false;
        }

        switch ($action) {
            /**
             * Add goods to storage
             */
            case "add":
                $sql_storage = "
                    UPDATE
                        `storage`
                    SET
                        `quantity` = `quantity` + " . $count . "
                    WHERE
                        `hash` = '" . $hash . "'
                    LIMIT 1
                ";

                mysql_query($sql_storage);
                break;

            /**
             * Remove goods from storage
             */
            case "remove":
                $sql_storage = "
                    UPDATE
                        `storage`
                    SET
                        `quantity` = `quantity` - " . $count . "
                    WHERE
                        `hash` = '" . $hash . "'
                    LIMIT 1
                ";

                mysql_query($sql_storage);
                break;
        }

        // update redis data
        $this->updateRedisStorage($hash);
    }

    /**
     * Set storage to redis
     * @param string $hash Storage hash
     * @param int $quantity Storage goods count
     */
    public function updateRedisStorage($hash = NULL, $quantity = NULL) {
        if (!$hash) {
            return false;
        }

        if (!is_int($quantity)) {
            $query_storage = mysql_query("SELECT `quantity` FROM `storage` WHERE `hash` = '" . mysql_real_escape_string($hash) . "' LIMIT 1");
            $row_storage = mysql_fetch_array($query_storage, MYSQL_ASSOC);

            if (is_array($row_storage) && isset($row_storage['quantity'])) {
                $quantity = $row_storage['quantity'];
            } else {
                return false;
            }
        }

        $redis = RedisManager::getInstance()->getRedis();

        $redis->hMset(
                'storage', array(
            $hash => $quantity
                )
        );
    }

    /**
     * Get storage goods count
     * @param  string $hash Storage hash
     * @return int Goods count
     */
    public function getRedisStorageCount($hash = NULL) {
        if (!$hash) {
            return false;
        }

        $redis = RedisManager::getInstance()->getRedis();

        $storage = $redis->hGetAll('storage');

        if (isset($storage[$hash])) {
            return $storage[$hash];
        }

        return false;
    }

    /**
     * Create storage hash
     * @param  string $offer      Offer name
     * @param  array  $attributes Offer attributes
     * @param  string $delivery   Order dilivery
     * @return string             md5 hash string
     */
    public function createHash($offer = NULL, $attributes = array(), $delivery = NULL) {
        $offer_attributes = "";

        if (
                isset($attributes) &&
                is_array($attributes) &&
                count($attributes) > 0
        ) {
            ksort($attributes); // сортировка свойств по ключу (по возростанию)
            $offer_attributes = "[" . implode("][", $attributes) . "]";
        }

        return md5($offer . $offer_attributes . $delivery);
    }

    /**
     * Resync data from DB to Redis
     * @return array Processing result
     */
    public function resync() {
        $sql_storage = "
            SELECT
                `hash`,
                `quantity`
            FROM
                `storage`
        ";

        $query_storage = mysql_query($sql_storage);

        $result = array();

        while ($row_storage = mysql_fetch_array($query_storage, MYSQL_ASSOC)) {
            $result[$row_storage['hash']] = $row_storage['quantity'];
        }

        $redis = RedisManager::getInstance()->getRedis();

        $redis->hMset('storage', $result);

        return array(
            "success" => TRUE,
            "msg" => "Updated"
        );
    }

    /**
     * Log
     * @param  string $txt Log text
     */
    public function l($txt = "") {
        error_log(
                $txt . "\n", 3, dirname(__FILE__) . "/../logs/storage_" . date('Ymd') . ".log"
        );
    }

}
