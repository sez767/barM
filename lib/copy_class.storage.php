<?php
/**
 * Storage
 * add to storage
 * remove from storage
 */
class Storage {

    private $id = 0;
    private $status_sending = NULL;
    private $old_status_sending = NULL;
    private $status_delivery = NULL;
    private $old_status_delivery = NULL;

    /**
     * Init storage
     * @param int $id Order ID
     * @param string $status_sending New order sending status
     * @param string $old_status_sending Old order sending status
     * @param string $status_delivery Deliverung status
     * @param string $old_status_delivery Old deliverung status
     * @example status_sending - 'Груз отправлен','На контроль','На отправку','Оплачен','Отказ','Отправлен'
     * @example status_delivery - 'Автоответчик','Возврат денег','Груз в дороге','Груз вручен','Заберет','На доставку','На контроль','Нет товара','Обработка','Обратная доставка отправлена','Отложенная доставка','Перезвонить','Получен','Проблемный','Проверен','Располовинен','Свежий','Сделать замену','Упакован','Упакован добавочный','Упакован на почте','Упакован принят','Хранение','Частичный возврат'
     */
    public function Storage($id = 0, $status_sending = NULL, $old_status_sending = NULL, $status_delivery = NULL, $old_status_delivery = NULL) {
        if (
            empty($id) ||
            empty($status_sending)
        ) {
            return;
        }

        $this->id = (int) $id;
        $this->status_sending = $status_sending;
        $this->old_status_sending = $old_status_sending;
        $this->status_delivery = $status_delivery;
        $this->old_status_delivery = $old_status_delivery;

        $properties = [];
        $offers = [];
        $products = [];

        $sql = mysql_query("SELECT `property_id`, `property_name` FROM `offer_property` WHERE `property_name` IN ('color', 'size', 'type', 'vendor')");
        
        while ($row = mysql_fetch_assoc($sql)) {
            $properties[$row['property_id']] = $row['property_name'];
        }

        $sql = mysql_query("SELECT `offer_name`, `offer_id` FROM `offers`");

        while ($row = mysql_fetch_assoc($sql)) {
            $products[$row['offer_name']] = $row['offer_id'];
        }

        $sql = mysql_query("SELECT `offer`, `other_data` AS `attributes`, `package`, `dop_tovar`, `country`, `kz_delivery` FROM `staff_order` WHERE `id` = " . $this->id . " LIMIT 1");
        $order = mysql_fetch_assoc($sql);

        $attributes = json_decode($order['attributes'], true);
        
        if (
            json_last_error() == JSON_ERROR_NONE &&
            is_array($attributes)
        ) {
            foreach ($attributes AS $key => $val) {
                if (!in_array($key, array('color', 'size', 'type', 'vendor'))) {
                    unset($attributes[$key]);
                }
            }
            
            $offer_attributes = "";
            
            if (count($attributes) > 0) {
                ksort($attributes); // сортировка свойств по ключу (по возростанию)
                $offer_attributes = "[" . implode("][", $attributes) . "]";
            }

            $storage_hash = md5($order['offer'] . $offer_attributes . $order['kz_delivery']);
            
            $offers[$storage_hash] = array(
                'package' => $order['package'],
                'color' => 0,
                'size' => 0,
                'name' => $order['offer'],
                'id' => 0,
                'kz_delivery' => $order['kz_delivery']
            );
        }

        /*
        die;

        // bug #1
        if (isset($attributes['type'])) {
            $attributes['size'] = $attributes['type'];
            unset($attributes['type']);
        }

        // bug #2
        if (isset($attributes['vendor'])) {
            $attributes['size'] = $attributes['vendor'];
            unset($attributes['vendor']);
        }

        if (is_array($attributes)) {
            if (count($attributes) == 1) {
                $type = key($attributes);

                if ($type == 'color') {
                    $offers[$order['offer'] . '_' . $attributes['color'] . '_0']['package'] = $order['package'];
                    $offers[$order['offer'] . '_' . $attributes['color'] . '_0']['color'] = $attributes['color'];
                    $offers[$order['offer'] . '_' . $attributes['color'] . '_0']['size'] = 0;
                    $offers[$order['offer'] . '_' . $attributes['color'] . '_0']['name'] = $order['offer'];
                    $offers[$order['offer'] . '_' . $attributes['color'] . '_0']['id'] = $products[$order['offer']];
                    $offers[$order['offer'] . '_' . $attributes['color'] . '_0']['kz_delivery'] = $order['kz_delivery'];
                } elseif ($type == 'size') {
                    $offers[$order['offer'] . '_' . $attributes['size'] . '_0']['package'] = $order['package'];
                    $offers[$order['offer'] . '_' . $attributes['size'] . '_0']['color'] = 0;
                    $offers[$order['offer'] . '_' . $attributes['size'] . '_0']['size'] = $attributes['size'];
                    $offers[$order['offer'] . '_' . $attributes['size'] . '_0']['name'] = $order['offer'];
                    $offers[$order['offer'] . '_' . $attributes['size'] . '_0']['id'] = $products[$order['offer']];
                    $offers[$order['offer'] . '_' . $attributes['size'] . '_0']['kz_delivery'] = $order['kz_delivery'];
                } else {
                    $offers[$order['offer'] . '_0_0']['package'] = $order['package'];
                    $offers[$order['offer'] . '_0_0']['color'] = 0;
                    $offers[$order['offer'] . '_0_0']['size'] = 0;
                    $offers[$order['offer'] . '_0_0']['name'] = $order['offer'];
                    $offers[$order['offer'] . '_0_0']['id'] = $products[$order['offer']];
                    $offers[$order['offer'] . '_0_0']['kz_delivery'] = $order['kz_delivery'];
                }
            }

            if (count($attributes) == 2) {
                $offers[$order['offer'].'_'.$attributes['color'].'_'.$attributes['size']]['package'] = $order['package'];
                $offers[$order['offer'].'_'.$attributes['color'].'_'.$attributes['size']]['color'] = $attributes[0];
                $offers[$order['offer'].'_'.$attributes['color'].'_'.$attributes['size']]['size'] = $attributes[1];
                $offers[$order['offer'].'_'.$attributes['color'].'_'.$attributes['size']]['name'] = $order['offer'];
                $offers[$order['offer'].'_'.$attributes['color'].'_'.$attributes['size']]['id'] = $products[$order['offer']];
                $offers[$order['offer'].'_'.$attributes['color'].'_'.$attributes['size']]['kz_delivery'] = $order['kz_delivery'];
            }
        } else {
            $offers[$order['offer'].'_0_0']['package'] = $order['package'];
            $offers[$order['offer'].'_0_0']['color'] = 0;
            $offers[$order['offer'].'_0_0']['size'] = 0;
            $offers[$order['offer'].'_0_0']['name'] = $order['offer'];
            $offers[$order['offer'].'_0_0']['id'] = $products[$order['offer']];
            $offers[$order['offer'].'_0_0']['kz_delivery'] = $order['kz_delivery'];
        }
        */

        /*
        
        // $additional = json_decode($order['dop_tovar'], true);

        $dop_tovar = json_decode($order['dop_tovar'], true);
        $additional = array();

        // преобразование формата доп товаров
        if (
            json_last_error() == JSON_ERROR_NONE &&
            is_array($dop_tovar)
        ) {
            if (isset($dop_tovar['dop_tovar'])) {
                for ($i = 0; $i < count($dop_tovar['dop_tovar']); $i++) {
                    $offer_attributes = array();

                    if (isset($dop_tovar['color'][$i])) {
                        $offer_attributes['color'] = $dop_tovar['color'][$i];
                    }

                    if (isset($dop_tovar['size'][$i]) && $dop_tovar['size'][$i] != "") {
                        $offer_attributes['size'] = $dop_tovar['size'][$i];
                    }

                    // bug 3
                    if (isset($dop_tovar['type'][$i]) && $dop_tovar['type'][$i] != "") {
                        $offer_attributes['color'] = $dop_tovar['type'][$i];
                    }

                    // bug 4
                    if (isset($dop_tovar['vendor'][$i]) && $dop_tovar['vendor'][$i] != "") {
                        $offer_attributes['size'] = $dop_tovar['vendor'][$i];
                    }

                    $additional[] = array(
                        "offer" => $dop_tovar['dop_tovar'][$i],
                        "count" => $dop_tovar['dop_tovar_count'][$i],
                        "attributes" => $offer_attributes
                    );
                }
            }
        }

        if (is_array($additional)) {
            foreach ($additional as $key => $item) {
                if (count($item['attributes']) == 1) {
                    //$type = isset($properties[$item['attributes'][0]]) ? $properties[$item['attributes'][0]] : '';
                    $type = key($item['attributes']);

                    if ($type == 'color') {
                        if (isset($offers[$item['offer'].'_'.$item['attributes']['color'].'_0'])) {
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['package'] += $item['count'];
                        } else {
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['package'] = $item['count'];
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['color'] = $item['attributes']['color'];
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['size'] = 0;
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['name'] = $item['offer'];
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['id'] = $products[$item['offer']];
                            $offers[$item['offer'].'_'.$item['attributes']['color'].'_0']['kz_delivery'] = $order['kz_delivery'];
                        }
                    } elseif ($type == 'size') {
                        if (isset($offers[$item['offer'].'_0'.'_'.$item['attributes']['size']])) {
                            $offers[$item['offer'].'_0'.'_'.$item['attributes']['size']]['package'] += $item['count'];
                        } else {
                            $offers[$item['offer'].'_0_'.$item['attributes']['size']]['package'] = $item['count'];
                            $offers[$item['offer'].'_0_'.$item['attributes']['size']]['color'] = 0;
                            $offers[$item['offer'].'_0_'.$item['attributes']['size']]['size'] = $item['attributes']['size'];
                            $offers[$item['offer'].'_0_'.$item['attributes']['size']]['name'] = $item['offer'];
                            $offers[$item['offer'].'_0_'.$item['attributes']['size']]['id'] = $products[$item['offer']];
                            $offers[$item['offer'].'_0_'.$item['attributes']['size']]['kz_delivery'] = $order['kz_delivery'];
                        }
                    } else {
                        if (isset($offers[$item['offer'].'_0_0'])) {
                            $offers[$item['offer'].'_0_0']['package'] += $item['count'];
                        } else {
                            $offers[$item['offer'].'_0_0']['package'] = $item['count'];
                            $offers[$item['offer'].'_0_0']['color'] = 0;
                            $offers[$item['offer'].'_0_0']['size'] = 0;
                            $offers[$item['offer'].'_0_0']['name'] = $item['offer'];
                            $offers[$item['offer'].'_0_0']['id'] = $products[$item['offer']];
                            $offers[$item['offer'].'_0_0']['kz_delivery'] = $order['kz_delivery'];
                        }
                    }
                }

                if (count($item['attributes']) == 2) {
                    $offers[$item['offer'] . '_' . ($item['attributes']['color'] ? $item['attributes']['color'] : 0) . '_' . ($item['attributes']['size'] ? $item['attributes']['size'] : 0)]['package'] = $item['count'];
                    $offers[$item['offer'] . '_' . ($item['attributes']['color'] ? $item['attributes']['color'] : 0) . '_' . ($item['attributes']['size'] ? $item['attributes']['size'] : 0)]['color'] = ($item['attributes']['color'] ? $item['attributes']['color'] : 0);
                    $offers[$item['offer'] . '_' . ($item['attributes']['color'] ? $item['attributes']['color'] : 0) . '_' . ($item['attributes']['size'] ? $item['attributes']['size'] : 0)]['size'] = ($item['attributes']['size'] ? $item['attributes']['size'] : 0);
                    $offers[$item['offer'] . '_' . ($item['attributes']['color'] ? $item['attributes']['color'] : 0) . '_' . ($item['attributes']['size'] ? $item['attributes']['size'] : 0)]['name'] = $item['offer'];
                    $offers[$item['offer'] . '_' . ($item['attributes']['color'] ? $item['attributes']['color'] : 0) . '_' . ($item['attributes']['size'] ? $item['attributes']['size'] : 0)]['id'] = $products[$item['offer']];
                    $offers[$item['offer'] . '_' . ($item['attributes']['color'] ? $item['attributes']['color'] : 0) . '_' . ($item['attributes']['size'] ? $item['attributes']['size'] : 0)]['kz_delivery'] = $order['kz_delivery'];
                }
            }
        }
        */
        
        print "<pre>";
        print_r($offers);
        die;

        foreach ($offers as $offer_hash => $offer) {
            $sql = mysql_query("SELECT `id` FROM `storage` WHERE `color` = '" . $offer['color'] . "' AND `size` = '" . $offer['size'] . "' AND `offer_id` = '" . $offer['id'] . "' LIMIT 1");
            $storage = mysql_fetch_assoc($sql);

            if ($this->status_sending != $this->old_status_sending) {
                if ($this->status_sending == "Отправлен") {
                    if (!in_array($this->status_delivery, array("Обработка", "Отложенная доставка"))) {
                        $this->addStorage($storage, $offer);
                    } else {
                        $this->removeStorage($storage, $offer);
                    }
                } elseif ($this->status_sending == "Отказ") {
                    $this->addStorage($storage, $offer);
                }
            } else {
                if ($this->status_sending == "Отправлен") {
                    if (in_array($this->status_delivery, array("Обработка", "Отложенная доставка"))) {
                        $this->addStorage($storage, $offer);
                    }

                    if (in_array($this->old_status_delivery, array("Обработка", "Отложенная доставка")) && !in_array($this->status_delivery, array("Обработка", "Отложенная доставка"))) {
                        $this->removeStorage($storage, $offer);
                    }
                }
            }
        }
    }

    // вносим на склад
    private function addStorage($storage = array(), $offer = array()) {
        $query = "SELECT `id`, `action_value` FROM `storage_action` WHERE `action_type` = 'return' AND `staff_offer_id` = '" . $this->id . "' AND `storage_id` = '" . $storage['id'] . "' AND `delivery` = '" . $offer['kz_delivery'] . "' ORDER BY `id` DESC LIMIT 1";

        print "/* " . print_r($query, true) . " */";

        $sql = mysql_query($query);
        $storageData = mysql_fetch_assoc($sql);
        if ($storageData !== false && is_array($storageData)) {
            $query = '
                UPDATE
                    `storage_action`
                SET
                    `action_value` = ' . $offer['package'] . '
                WHERE `id` = ' . $storageData['id'];

            print "/* " . print_r($query, true) . " */";

            mysql_query($query);
        } else {
            if ($storage !== false && is_array($storage)) {
                $query = "
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

                print "/* " . print_r($query, true) . " */";

                mysql_query($query);
            }
        }
    }

    // списываем со склада
    private function removeStorage($storage = array(), $offer = array()) {
        $query = "SELECT `id`, `action_value` FROM `storage_action` WHERE `action_type` = 'send' AND `staff_offer_id` = " . $this->id . " AND `storage_id` = '" . $storage['id'] . "' AND `delivery` = '" . $offer['kz_delivery'] . "' ORDER BY `id` DESC LIMIT 1";
        
        print "/* " . print_r($query, true) . " */";

        $sql = mysql_query($query);
        $storageData = mysql_fetch_assoc($sql);

        if ($storageData !== false && is_array($storageData)) {
            $query = '
                UPDATE
                    `storage_action`
                SET
                    `action_value` = ' . $offer['package'] . '
                WHERE
                    `id` = '.$storageData['id'];

            print "/* " . print_r($query, true) . " */";

            mysql_query($query);
        } else {
            if ($storage !== false && is_array($storage)) {
                $query = "
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

                print "/* " . print_r($query, true) . " */";

                mysql_query($query);
            }
        }
    }
}