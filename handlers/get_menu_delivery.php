<?php

require_once dirname(__FILE__) . "/../lib/db.php";
ini_set('memory_limit', '1024M');

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        'success' => false,
        'msg' => 'Permission denied'
    )));
}

/**
 * Доставка
 */
header('Content-Type: application/json; charset=utf-8', true);

require_once dirname(__FILE__) . "/../lib/class.staff.php";

$redis = RedisManager::getInstance()->getRedis();

$time_1 = microtime(true);

if (!empty($_REQUEST['id'])) {
    // get ordersep_pay2
    $sql = 'SELECT * FROM `staff_order` WHERE `id` = %i';
    ApiLogger::addLogVarExport('START BY ID');
    ApiLogger::addLogVarExport("sql: $sql");

    if (($obj = DB::queryOneRow($sql, $_REQUEST['id']))) {
        $obj['phone_orig'] = preg_replace('/\D/', '', $obj['phone']);

        $obj['history'] = "Первый заказ";
        $obj['payed_curr_year_count'] = 0;

        if (($biletCount = getKonkusBiletCount(array($obj)))) {
            $obj['konkus_bilet_count'] = reset($biletCount);
        }
        if (($clientData = DB::queryFirstRow('SELECT order_payed_2020_count, order_payed_2020_total, client_worries FROM Clients WHERE uuid = %s', $obj['uuid']))) {
            $clientData['order_payed_2020_count'] = (int) $clientData['order_payed_2020_count'];
            $clientData['order_payed_2020_total'] = (int) $clientData['order_payed_2020_total'];
            $obj = array_merge($obj, $clientData);
        }

        $in_str = array('новая', '', 'Недозвон', 'Перезвонить', 'недозвон_ночь');
        ///////////////////////////////////////////////////////
        $sqlHistory = " SELECT
                            `id`,
                            IF(`return_date` > '2019-01-01 00:00:00' AND `status` = 'Подтвержден' AND `send_status` = 'Оплачен', 1, 0) AS payed_curr_year_count,
                            `last_edit`,
                            `fill_date`,
                            `status`,
                            `description`,
                            `send_status`,
                            `package`,
                            `offer`,
                            `fio`,
                            `status_kz`,
                            `addr`,
                            `staff_id`
                        FROM `staff_order`
                        WHERE `uuid` != '' AND `uuid` = %s";

        ///////////////////////////////////////////////////////
        if (($historyData = DB::query($sqlHistory, $obj['uuid']))) {
            $obj['history'] = '<table cellspacing="1" cellpadding="3" border="1">
            <tr>
                <td width="40">id</td>
                <td width="40">Оператор обзвона</td>
                <td width="80">Дата</td>
                <td width="120">Статус заказа</td>
                <td width="120">Статус доставки</td>
                <td width="120">Статус посылки</td>
                <td width="120">ФИО</td>
                <td width="120">Адрес</td>
                <td width="120">Источник</td>
                <td width="120">Название товара</td>
                <td width="120">Количество в заказе</td>
                <td width="120">Подстатус (коммент)</td>
            </tr>';

            foreach ($historyData as $historyItem) {
                $obj['payed_curr_year_count'] += $historyItem['payed_curr_year_count'];

                $obj['history'] .= sprintf(
                        '<tr' . ($historyItem['staff_id'] == 11112222 ? ' style="color: blue; font-weight: bold; font-size: 18px;"' : '') . ">
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td style='color:green;'>%s</td>
                            <td style='color:%s'>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                        </tr>", (empty($_SESSION['operatorcold']) ? '<a href="#" onclick="javascript:CreateMenuDelivery({order_id: ' . $historyItem['id'] . '}, \'kz\');">' : '') . $historyItem['id'] . (empty($_SESSION['operatorcold']) ? '</a>' : ''), $GLOBAL_STAFF_FIO[$historyItem['last_edit']], $historyItem['fill_date'], $historyItem['status'], $historyItem['send_status'] == 'Оплачен' ? 'green' : 'red', $historyItem['send_status'], $historyItem['status_kz'], $historyItem['fio'], $historyItem['addr'], $GLOBAL_PARTNERS[$historyItem['staff_id']], $historyItem['offer'], $historyItem['package'], $historyItem['description']
                );
            }

            $obj['history'] .= '</table>';
        }

        $obj['rings'] = "<span id='calls_" . $obj['id'] . "'><a href='javascript:void(0)' onclick='showRingsAnket(\"" . $obj['id'] . "\",\"" . $obj['country'] . "\"); return false;'>" . $obj['call_count'] . "</a></span>";

        if ($obj['country'] == 'kz') {
            $obj['phone'] = hide_phone($obj['phone']);
            $obj['phone_sms'] = hide_phone($obj['phone_sms']);
            $obj['kz_check'] = "<span id='kz_check_" . $obj['id'] . "'><a href='https://post.kz/services/postal/" . $obj['kz_code'] . "' target='_blank'>Проверить</a></span>";
        } elseif ($obj['country'] == 'kzg') {
            $obj['phone'] = '9' . hide_phone(substr($obj['phone'], 1));
            $obj['phone_sms'] = '9' . hide_phone(substr($obj['phone_sms'], 1));
        }
        if ($obj['phone_sms'] == '9000000') {
            $obj['phone_sms'] = '';
        }

        $obj['kz_admin'] = (int) $obj['kz_admin'];

        //
        // Дополнительный товар
        //
    $dopTovarArr = json_decode($obj['dop_tovar'], true);
        $obj['dop_tovar'] = $dop_tovar_tmp = array();
        if (json_last_error() == JSON_ERROR_NONE && !empty($dopTovarArr['dop_tovar'])) {
            $labels = array(
                "attribute" => "Атрибут",
                "color" => "Цвет",
                "size" => "Размер",
                "type" => "Тип",
                "vendor" => "Модель",
                "name" => "Название",
                "description" => "Описание",
                "dop_tovar" => "",
                "dop_tovar_count" => "Количество",
                "dop_tovar_price" => "Цена",
            );

            $description = array();

            foreach ($dopTovarArr['dop_tovar'] AS $key => $value) {
                foreach ($dopTovarArr AS $attr => $a) {
                    if ($attr == "dop_tovar") {
                        $dop_tovar_tmp["offer"] = $dopTovarArr["dop_tovar"][$key];
                    } elseif ($attr == "dop_tovar_count") {
                        $dop_tovar_tmp["count"] = $dopTovarArr["dop_tovar_count"][$key];
                    } elseif ($attr == "dop_tovar_price") {
                        $dop_tovar_tmp["price"] = $dopTovarArr["dop_tovar_price"][$key];
                    } else {
                        $dop_tovar_tmp[$attr] = $dopTovarArr[$attr][$key];
                    }
                }

                $obj['dop_tovar'][] = $dop_tovar_tmp;
            }
        }

        //
        // Формируем список свойств продукта
        //
    $obj['offer_properties'] = OfferPropertiesManager::getOfferProperties($obj['offer']);

        //
        // Выбираем атрибут товара
        //
    $other_data = json_decode($obj['other_data'], true);

        if (json_last_error() != JSON_ERROR_NONE) {
            $other_data = array();
        }

        $obj['offer_property'] = is_array($other_data) ? $other_data : array();

        //
        // Выборка всех офферов и их свойств
        // Для формирования списка дополнительных товаров и их свойств в анкете
        //
    $offersWithProperies = array();
        foreach ($GLOBAL_ACTIVE_OFFERS_ASS as $offerId => $offerItem) {
            if ($offerItem['offer_name']) {

                $offersWithProperies[$offerItem['offer_name']] = array(
                    'name' => $offerItem['offer_name'],
                    'title' => $offerItem['offer_desc'],
                    'properties' => array()
                );

                foreach ($GLOBAL_OFFER_PROPERTIES[$obj['country']][$offerId] as $propertyItem) {
                    if ($propertyItem['property_value']) {
                        $offersWithProperies[$offerItem['offer_name']]['properties'][$propertyItem['property_name']][] = $propertyItem['property_value'];
                    }
                }
            }
        }
        $obj['offers_with_properies'] = $offersWithProperies;


        if (!empty($_REQUEST['closed_level']) && in_array($_REQUEST['closed_level'], array(13, 15))) {
            $qs = 'SELECT COUNT(id) FROM staff_order WHERE status = "Подтвержден" AND send_status IN ("Отправлен", "Предоплата") AND uuid = %s';
            $obj['have_orders'] = DB::queryFirstField($qs, $obj['uuid']);
        }
        DB::update('staff_order', array('is_get' => 1, 'time_get' => DB::sqlEval('NOW()'), 'who_get' => $_SESSION['Logged_StaffId']), 'id = %i', $_REQUEST['id']);
        $actionHistoryObj = new ActionHistoryObj();
//        $actionHistoryObj->save('StaffOrderObj', $obj['id'], 'update', 'is_get', $obj['is_get'], 1);
        $actionHistoryObj->save('StaffOrderObj', $obj['id'], 'update', 'who_get', $obj['who_get'], $_SESSION['Logged_StaffId']);

        // GET HISTORY
        $historyData = array();
        $orderQueryHistory = "
        SELECT `id`,`date`,`from`,`to`,`type`,`property`,`was`,`set` FROM ActionHistory WHERE property IN ('status_cur','status_kz', 'send_status', 'status_cur', 'total_price') AND `to` = {$_REQUEST['id']}
        UNION
        SELECT `id`,`date`,`worker`,`object_id`,`type`,`property`,`was`,`set` FROM ActionHistoryOld WHERE property IN ('status_cur','status_kz', 'send_status', 'status_cur', 'total_price') AND `object_name` = 'StaffOrderObj' AND `object_id` = {$_REQUEST['id']}
        UNION
        SELECT `id`,`date`,`worker`,`object_id`,`type`,`property`,`was`,`set` FROM ActionHistoryNew WHERE property IN ('status_cur','status_kz', 'send_status', 'status_cur', 'total_price') AND `object_name` = 'StaffOrderObj' AND `object_id` = {$_REQUEST['id']}";
        //var_dump($orderQueryHistory); die;
        if (false && ($historyRowData = DB::query($orderQueryHistory))) {
            foreach ($historyRowData as $historyItem) {
                $historyData[substr($historyItem[date], 0, 16)][$historyItem['from']][$historyItem['property']]['was'] = $historyItem['was'];
                $historyData[substr($historyItem[date], 0, 16)][$historyItem['from']][$historyItem['property']]['set'] = $historyItem['set'];
            }
        }
        $historyHtml = '<table border="1" class="htable">';
        foreach ($historyData as $date => $valu) {
            $historyHtml .= '<tr><td rowspan="3">' . $date . '</td>';
            foreach ($valu as $from => $prop) {
                $historyHtml .= '<td rowspan="3">' . $GLOBAL_STAFF_FIO[$from] . '</td>';
                $fstr = '<tr>';
                $tstr = '<tr>';
                foreach ($prop as $pk => $val) {
                    $historyHtml .= '<td>' . $pk . '</td>';
                    $fstr .= '<td>' . str_replace('"', '', $val['was']) . '</td>';
                    $tstr .= '<td>' . str_replace('"', '', $val['set']) . '</td>';
                }
            }
            $historyHtml .= '</tr>';
            $historyHtml .= $fstr . '</tr>';
            $historyHtml .= $tstr . '</tr>';
        }
        $historyHtml .= '</table>';
        $obj['order_history'] = $historyHtml;
        $obj['uuid_orig'] = $obj['uuid'];
        // END GET HISTORY
        // START common_recall_date
        if ($obj['common_recall_date']) {
            $obj['common_recall_time'] = date('H:i:s', strtotime($obj['common_recall_date']));
            $obj['common_recall_date'] = date('Y-m-d', strtotime($obj['common_recall_date']));
        }
        // END common_recall_date
        // START date_otl
        if ($obj['date_otl']) {
            $obj['time_otl'] = date('H:i:s', strtotime($obj['date_otl']));
            $obj['date_otl'] = date('Y-m-d', strtotime($obj['date_otl']));
        }
        // END date_otl

        echo json_encode(array(
            "success" => true,
            'executing' => (microtime(true) - $time_1),
            "sql_history" => $orderQueryHistory,
            "data" => $obj
        ));
    } else {
        echo json_encode(array(
            "success" => false,
            "msg" => "Not found",
        ));
    }


//////////////////////////////////
// END GET[ID]
} else {
//////////////////////////////////
// START GET LIST
    $dobrikUseWhere = false;

    ApiLogger::addLogVarExport('START');

    // collect request parameters
    $start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
    $count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
    if (!empty($_REQUEST['ogr'])) {
        $count = (int) $_REQUEST['ogr'];
    }
    $filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
    $sort = my_mysqli_real_escape_string(isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id');
    $dir = my_mysqli_real_escape_string(isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'DESC');
    // GridFilters sends filters as an Array if not json encoded
    if (is_array($filters)) {
        $encoded = false;
    } else {
        $encoded = true;
        $filters = json_decode($filters);
    }

    // initialize variables
    $where = ' 0 = 0 ';
    $qs = '';

    $filtersFieldsArr = array();
    $clientsFiltersArr = array();

    ApiLogger::addLogVarExport($filters);


    // loop through filters sent by client
    if (is_array($filters)) {
        for ($i = 0; $i < count($filters); $i++) {
            $filter = $filters[$i];
            // assign filter data (location depends if encoded or not)
            if ($encoded) {
                $field = $filter->field;
                $value = $filter->value;
                $compare = isset($filter->comparison) ? $filter->comparison : null;
                $filterType = $filter->type;
            } else {
                $field = $filter['field'];
                $value = $filter['data']['value'];
                $compare = isset($filter['data']['comparison']) ? $filter['data']['comparison'] : null;
                $filterType = $filter['data']['type'];
            }
            $field = my_mysqli_real_escape_string($field);
            $value = my_mysqli_real_escape_string($value);
            $compare = my_mysqli_real_escape_string($compare);
            $filterType = my_mysqli_real_escape_string($filterType);

            $filtersFieldsArr[$field] = $filter['data'];

            if (stripos($field, 'date') !== false) {
                $dobrikUseWhere = true;
            }

            if ($field == 'kz_zone') {
                if (isset($GLOBAL_DELIVERY_REGIONS_ARR[$value])) {
                    $where .= "AND kz_delivery IN ('" . implode("','", $GLOBAL_DELIVERY_REGIONS_ARR[$value]) . "')";
                }
                continue;
            } else if (in_array($field, array('id')) && strpos($value, ' ') === false) {
                $filterType = 'numeric';
                $compare = 'eq';
            } else if ($field == 'description_str') {
                $field = 'description';
            } else if (false && in_array($field, array('client_group', 'client_team', 'client_oper_use'))) {
                $field = ($field == 'client_oper_use') ? 'oper_use' : $field;

                $fi = explode(',', $value);
                $tmpClientsFilters = array();
                foreach ($fi as $fik => &$fiv) {
                    if (in_array($field, array('oper_use')) && $fiv == -1) {
                        $tmpClientsFilters[] = "`$field` > 0 ";
                        unset($fi[$fik]);
                    } elseif (in_array($field, array('client_group', 'client_team')) && $fiv == -1) {
                        $tmpClientsFilters[] = "(`$field` IS NULL OR `$field` = 0)";
                        unset($fi[$fik]);
                    }
                }
                if (!empty($fi)) {
                    $tmpClientsFilters[] = "`$field` IN (" . implode(',', $fi) . ")";
                }

                $clientsFiltersArr[] = implode(' AND ', $tmpClientsFilters);

                continue;
            }
            if ($field == 'offer_groups') {
                $ofGroupsArr = explode(',', $value);
                $ofGroupInArr = array();
                foreach ($ofGroupsArr as $groupItem) {
                    $ofGroupInArr = array_merge($ofGroupInArr, $GLOBAL_GROUP_OFFER[$groupItem]);
                    if (empty($groupItem)) {
                        $ofGroupInArr[] = '0';
                    }
                }
                $where .= " AND offer IN ('" . implode("', '", $ofGroupInArr) . "')";
                continue;
            }

            switch ($filterType) {
                case 'string':
                    $value = trim($value);

                    if (in_array($field, array('id', 'uuid', 'phone', 'card_number', 'index', 'kz_code'))) {
                        $tmpIds = explode(' ', $value);
                        $tmpIds[] = -1;
                        $tmpIds = array_diff($tmpIds, array(''));
                        $qs .= " AND `$field` IN ('" . implode("','", $tmpIds) . "')";
                    } else if ($field == 'phone') {
                        $phoneUuidStr = array();
                        foreach ($GLOBAL_COUNTRIES_ARR as $countryItem) {
                            $phoneUuidStr[] = mb_substr(md5(mb_strtoupper($countryItem) . mb_substr($value, -9)), 0, 16);
//                            $phoneUuidStr[] = mb_substr(md5(mb_strtoupper($countryItem) . mb_substr(hide_phone($value, 1), -9)), 0, 16);
                        }
                        $qs .= ' AND uuid IN (' . implode(',', $phoneUuidStr) . ') ';
                    } else if ($field == 'web_id') {
                        $qs .= " AND `" . $field . "` = '" . $value . "' ";
                    } else if (in_array($field, array('other_data'))) {
                        $qs .= " AND `$field` LIKE '%$value%'";
                    } else {
                        $qs .= " AND `$field` LIKE '$value%'";
                    }
                    Break;

                case 'list':
                    if ($field == 'kz_delivery') {
                        if ($value == 'Вся курьерка') {
                            $qs .= " AND `kz_delivery` != 'Почта' ";
                            Break;
                        } else if ($value == 'Работает') {
                            $qs .= " AND `kz_delivery` != 'Почта' AND `kz_delivery` IN ('" . implode("', '", DB::queryFirstColumn('SELECT delivery_type FROM delivery_week_days WHERE active = 1')) . "') ";
                            Break;
                        } else if ($value == 'Не работает') {
                            $qs .= " AND `kz_delivery` != 'Почта' AND `kz_delivery` NOT IN ('" . implode("', '", DB::queryFirstColumn('SELECT delivery_type FROM delivery_week_days WHERE active = 1')) . "') ";
                            Break;
                        }
                    }
                    if (in_array($field, array('status_cur')) && $value == -1) {
                        $qs .= " AND status_cur IN ('" . implode("', '", $GLOBAL_STATUS_CUR_OPLACHEN) . "') ";
                        Break;
                    }
                    if (in_array($field, array('is_cold_staff_id')) && $value == -1) {
                        $qs .= " AND is_cold_staff_id = 0 ";
                        Break;
                    }
                    if (in_array($field, array('staff_id', 'staff_id_orig')) && $value == -1) {
                        $qs .= " AND staff_id NOT IN (" . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ") ";
                        Break;
                    }
                    if (in_array($field, array('staff_id', 'staff_id_orig')) && $value == -2) {
                        $qs .= " AND staff_id IN (" . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ") ";
                        Break;
                    }
                    if (in_array($field, array('kz_operator')) && in_array($value, array(-1, -2))) {

                        $redis = RedisManager::getInstance()->getRedis();
                        $redisData = $redis->hGetAll('operator_logist');
                        $redisDataKC = $redis->hGetAll('operator_logist_kc');
                        $dataKC1 = array();
                        foreach ($redisData as $ii => $vv) {
                            if (trim($redisDataKC[$ii]) === 'KC-1') {
                                $dataKC1[] = $ii;
                            }
                        }

                        $qs .= ' AND `kz_operator` ' . ($value == -1 ? '' : 'NOT ') . 'IN (' . implode(', ', $dataKC1) . ') ';
                        Break;
                    }

                    if ($field == 'responsible') {
                        $fi = explode(',', $value);
                        $tmpStaffArr = array();
                        foreach ($fi as $fiItem) {
                            if ($fiItem < 0 && array_key_exists(abs($fiItem), $GLOBAL_CURATOR_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_CURATOR_STAFF[abs($fiItem)]);
                            } elseif (array_key_exists($fiItem, $GLOBAL_RESPONSIBLE_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_RESPONSIBLE_STAFF[$fiItem]);
                            }
                        }
                        if (empty($tmpStaffArr)) {
                            $qs .= ' AND last_edit IN (-1) ';
                        } else {
                            $qs .= ' AND last_edit IN (' . implode(', ', $tmpStaffArr) . ') ';
                        }
                        Break;
                    }
                    if ($field == 'responsible_cold') {
                        $fi = explode(',', $value);
                        $tmpStaffArr = array();
                        foreach ($fi as $fiItem) {

                            if ($fiItem < 0 && array_key_exists(abs($fiItem), $GLOBAL_CURATOR_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_CURATOR_STAFF[abs($fiItem)]);
                            } elseif (array_key_exists($fiItem, $GLOBAL_RESPONSIBLE_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_RESPONSIBLE_STAFF[$fiItem]);
                            }
                        }
                        if (empty($tmpStaffArr)) {
                            $qs .= ' AND is_cold_staff_id IN (-1) ';
                        } else {
                            $qs .= ' AND is_cold_staff_id IN (' . implode(',', $tmpStaffArr) . ') ';
                        }
                        Break;
                    }
                    if ($field == 'team') {
                        $fi = explode(',', $value);
                        $tmpStaffArr = array();
                        foreach ($fi as $fiItem) {
                            if (array_key_exists($fiItem, $GLOBAL_TEAM_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_TEAM_STAFF[$fiItem]);
                            }
                        }
                        if (!empty($tmpStaffArr)) {
                            $qs .= ' AND last_edit IN (' . implode(', ', $tmpStaffArr) . ') ';
                        } else {
                            $qs .= ' AND last_edit IN (-1) ';
                        }
                        Break;
                    }
                    if ($field == 'dop_tovar') {
                        $qs .= " AND " . $field . " LIKE '%" . $value . "%'";
                    } else {
                        if (strstr($value, ',')) {
                            $fi = explode(',', $value);
                            for ($q = 0; $q < count($fi); $q++) {
                                $fi[$q] = "'" . $fi[$q] . "'";
                            }
                            $value = implode(',', $fi);
                            $qs .= " AND `" . $field . "` IN (" . $value . ")";
                        } elseif (is_array($value)) {
                            $qs .= " AND `" . $field . "` IN (" . implode(',', $value) . ")";
                        } else {
                            $qs .= " AND `" . $field . "` = '" . $value . "'";
                        }
                    }
                    Break;
                case 'boolean':
                    $qs .= " AND " . $field . " = " . ($value);
                    Break;
                case 'numeric':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND " . $field . " = " . $value;
                            Break;
                        case 'lt':
                            $qs .= " AND " . $field . " < " . $value;
                            Break;
                        case 'gt':
                            $qs .= " AND " . $field . " > " . $value;
                            Break;
                    }
                    Break;
                case 'date':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND " . $field . " >= '" . $value . "' AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                            Break;
                        case 'lt':
                            $qs .= " AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                            Break;
                        case 'gt':
                            $qs .= " AND " . $field . " >= '" . $value . "'";
                            Break;
                    }
                    Break;
            }
        }
        $where .= $qs;
    }

    if (!empty($clientsFiltersArr)) {
        $where .= ' AND uuid IN (SELECT uuid FROM `Clients` WHERE ' . implode(' AND ', $clientsFiltersArr) . ')';
    }

//    if (in_array($_SESSION['Logged_StaffId'], array(88189675))) {
//        $where .= ' AND `date` > NOW() - INTERVAL 6 MONTH';
//    }

    $aad = ' ';

    // Доставка сегодня
    if (!empty($_REQUEST['today'])) {
        $where .= " AND `send_status` IN ('Отправлен', 'Предоплата', 'Полная предоплата', 'Частичная предоплата') AND `status_kz` IN ('На доставку', 'Вручить подарок')";
        if (!empty($_SESSION['admincity']) && empty($_SESSION['admin']) && empty($_SESSION['adminsales'])) {
            $where .= " AND kz_delivery IN (" . (empty($_SESSION['delivers']) ? '-1' : $_SESSION['delivers']) . ")
                        AND `date_delivery` BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() + INTERVAL 1 DAY
                        OR
                        `send_status` = 'Оплачен' AND `status_kz` = 'Сделать замену' AND `status_cur` = 'замена сделана' AND `date_delivery` BETWEEN CURDATE() + INTERVAL 1 DAY AND CURDATE() + INTERVAL 2 DAY
                        AND kz_delivery IN (" . (empty($_SESSION['delivers']) ? '-1' : $_SESSION['delivers']) . ")";
        } else {
            $where .= " AND date_delivery <= CURDATE() + INTERVAL 1 DAY";
        }
    }

    if (empty($dobrikUseWhere)) {
//        $where .= ' AND `date` > CURDATE() - INTERVAL ' . (empty($_REQUEST['cold']) && empty($_REQUEST['coldNew']) && empty($_REQUEST['today']) && empty($_REQUEST['history']) ? 1 : 12) . ' MONTH ';
    }

    //////////////////////////
    // КОСТЫЛИКИ
    //////////////////////////
    if (in_array($_SESSION['Logged_StaffId'], array(94448321))) {
        $where .= ' AND staff_id NOT IN (11111111, 22222222, 33333333) AND date > "2018-03-21" AND kz_delivery = "Почта"';
    }
    if (in_array($_SESSION['Logged_StaffId'], array(22299672))) {
        $where .= " AND staff_id = 22299672 ";
    }
    if (!empty($_SESSION['logist']) && strpos($_SESSION['country'], '"kz"') !== false && empty($_SESSION['admin']) && empty($_SESSION['adminsales'])) {
        $where .= ' AND `status_kz` IN ("Обработка", "На доставку", "Отложенная доставка", "Хранение", "На контроль", "Отказ")';
    }
    // END КОСТЫЛИКИ
    //////////////////////////
    // Холодные заказы
    if (empty($_REQUEST['cold']) && empty($_REQUEST['coldNew'])) {
        if (!empty($_SESSION['operatorcold']) && empty($_SESSION['admin']) && empty($_SESSION['adminsales'])) {
            $where .= " AND staff_id IN (22222222, 55555555) AND web_id = {$_SESSION['Logged_StaffId']}";
        }
    } else {
        $prefix = !empty($_REQUEST['coldNew']) ? '_new' : '';

        if (empty($_SESSION['adminsales']) && empty($_SESSION['admin'])) {
            $where .= " AND (is_cold{$prefix} IN (1, 3, 8) OR (is_cold{$prefix} = 2 AND is_cold{$prefix}_staff_id = " . $_SESSION['Logged_StaffId'] . ')) ';
        } else {
            $where .= " AND is_cold{$prefix} IN (1, 2, 3, 8) ";
        }
        if (!empty($_SESSION['Logged_Group']) && empty($_SESSION['admin']) && empty($_SESSION['adminsales'])) {
            $where .= " AND `Group_cold{$prefix}` IN (0, " . $_SESSION['Logged_Group'] . ')';
        }
        if (!empty($_SESSION['staff_oper_use']) && empty($_SESSION['admin']) && empty($_SESSION['adminsales'])) {
            $where .= ' AND `oper_use` IN (' . $_SESSION['staff_oper_use'] . ')';
        }
    }

    if (
            in_array($_SESSION['Logged_StaffId'], array(44917943, 11594646, 67427284, 86208368, 72758398, 60457955, 36710186, 13717713, 35258284)) ||
            ((!empty($_SESSION['adminsales']) || !empty($_SESSION['operatorcold'])) && empty($_SESSION['admin']))
    ) {
        if (($clientGroup = getClientGroupByStaffId($_SESSION['Logged_StaffId'])) && $GLOBAL_CLOSED_CLIENT_GROUPS) {
            $where .= " AND client_group IN ($clientGroup) ";
        }
    }

    if (false
//            &&
//            ($_SESSION['IsResponsible'] && ($clientTeam = $_SESSION['team'])) ||
//            (($responsibleId = $GLOBAL_STAFF_RESPONSIBLE[$_SESSION['Logged_StaffId']]) &&
//            ($clientTeam = DB::queryFirstField('SELECT team FROM Staff WHERE id = %i', $responsibleId)))
    ) {
        $where .= " AND client_team = $clientTeam ";
    }

    if (empty($_REQUEST['today']) && empty($_REQUEST['cold']) && empty($_REQUEST['coldNew'])) {

        if (
                empty($_SESSION['admin']) &&
                empty($_SESSION['adminsales']) &&
                empty($_SESSION['logistcity']) &&
                empty($_SESSION['incomeoper']) &&
                empty($_SESSION['operatorcold']) &&
                empty($_SESSION['operatorrecovery']) &&
                empty($_SESSION['operator']) &&
                empty($_SESSION['webmaster'])
        ) {
            $aad .= " AND status IN ('Подтвержден', 'Предварительно подтвержден', 'Недозвон', 'Перезвонить') ";
        }
        if (!empty($_SESSION['operator']) && empty($_SESSION['admin']) && empty($_SESSION['webmaster'])) {
            $aad .= " AND status IN ('Подтвержден', 'Предварительно подтвержден') AND last_edit = {$_SESSION['Logged_StaffId']} ";
        }
        if (!empty($_SESSION['logist'])) {
            $aad .= " AND status IN ('Подтвержден') AND send_status IN ('Отправлен') AND status_kz IN ('Обработка', 'На контроль') ";
        }
        if (!empty($_REQUEST['history'])) {
//            $aad = " AND status <> 'новая' ";
        }

        $post_arai = array('75535915', '75094928', '53730812', '15228958', '22471464', '81111477');
        $damir_ar = array('61386417', '88831189', '60196849', '77149078');
        $all_ar = array('40361883');
        if (
                empty($_SESSION['operator']) && empty($_SESSION['incomeoper']) && empty($_SESSION['admin']) && empty($_SESSION['adminsales']) &&
                !in_array($_SESSION['Logged_StaffId'], array(95803116, 52891511, 57149264, 30224289, 85346124))
        ) {
            if ($_SESSION['Logged_StaffId'] == '90316241' || $_SESSION['Logged_StaffId'] == '58156779') {
                $where .= " AND (send_status IN ('На отправку','Нет товара','Предоплата') OR (send_status = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND kz_delivery IN ('Астана Курьер','Алматы курьер')  AND date_delivery<NOW()+INTERVAL 23 HOUR AND date_delivery>NOW())) AND `kz_delivery` != 'Почта' ";
            }
            if ($_SESSION['Logged_StaffId'] == '80057503') {
                $where .= " AND (send_status = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND kz_delivery='Алматы курьер'  AND date_delivery>NOW() AND date_delivery<NOW()+INTERVAL 24 HOUR) ";
            } elseif ($_SESSION['adminlogist']) {
                $where .= " AND `kz_delivery` != 'Почта'";
            } elseif ($_SESSION['adminlogistpost'] || $_SESSION['logistprepayment']) {
                $where .= " AND kz_delivery IN ('" . implode("', '", $GLOBAL_DELIVERY_REGIONS_ARR['Предоплатные города']) . "', 'Почта')";
            } elseif ($_SESSION['postlogist']) {
                $where .= " AND status IN ('Подтвержден', 'Предварительно подтвержден') AND kz_delivery = 'Почта' AND send_status IN ('Отправлен', 'Предоплата') AND status_kz IN ('Готов к консультации', 'Заберет', 'На контроль', 'Обработка', 'Отложенная доставка', 'Получен', 'Проверен', 'Хранение') OR (status_kz = 'Заберет' AND take_away_date < NOW())";
            } elseif ($_SESSION['logistprepayment']) {
                $where .= " AND status IN ('Подтвержден', 'Предварительно подтвержден') AND kz_delivery = 'Почта' AND send_status IN ('Отправлен') AND send_status NOT IN ('Предоплата', 'Полная предоплата') AND status_kz IN ('Готов к консультации', 'Заберет', 'На контроль', 'Обработка', 'Отложенная доставка', 'Получен', 'Проверен', 'Хранение') OR (status_kz = 'Заберет' AND take_away_date < NOW()) AND send_status NOT IN ('Предоплата', 'Полная предоплата')";
            } elseif ($_SESSION['operator_bishkek']) {
                $where .= " AND last_edit = {$_SESSION['Logged_StaffId']} ";
            } elseif ($_SESSION['logist']) {
                if (in_array($_SESSION['Logged_StaffId'], $post_arai)) {
                    $where .= " AND send_status IN ('Отправлен') AND status_kz IN ('Отложенная доставка') AND kz_delivery = 'Почта' ";
                } elseif (strpos($_SESSION['country'], 'kzg') !== false || strpos($_SESSION['country'], 'am') !== false || strpos($_SESSION['country'], 'az') !== false) {
                    $where .= " AND send_status IN ('Отправлен', 'На отправку') AND status_kz IN ('Обработка', 'На доставку', 'Отложенная доставка', 'Хранение', 'На контроль', 'Отказ') ";
                } elseif (in_array($_SESSION['Logged_StaffId'], $all_ar)) {
                    $where .= " AND `kz_delivery` != 'Почта' ";
                } else {
                    $where .= " AND send_status IN ('Отправлен') AND `kz_delivery` != 'Почта' ";
                }
            } elseif ($_SESSION['logistcity']) {
//                Не удалять
//                $where .= " AND (send_status = 'Отказ' OR status = 'Отменён') AND `kz_delivery` != 'Почта' ";
            } elseif ($_SESSION['operatorcold']) {
                $where .= " AND 44 = 44 ";
            } elseif (!empty($_SESSION['webmaster']) || strlen($_SESSION['web_access']) > 0 && $_SESSION['web_access'] != '""') {
                // Web-мастер (должен видеть свой айди веба только)
                $where .= " AND country IN (" . $_SESSION['country'] . ") AND `web_id` IN (" . $_SESSION['web_access'] . ") ";
            } else {
                $where .= " AND send_status = 'Отправлен' ";
                $where .= " AND status_kz IN ('Отложенная доставка') AND `kz_delivery` != 'Почта'";
            }
        }
    }

    if ($_SESSION['Logged_StaffId'] == '12319610' && !is_array($filters)) {
        $where .= " AND status IN ('') ";
    }

    if (!empty($_SESSION['offline_island'])) {
        $where = '`last_edit` = ' . $_SESSION['Logged_StaffId'] . $qs;
    }

    /////////////////////////////////////////////////////////////////////
    $whereUnion = '';
    if (!empty($_REQUEST['today'])) {
        $whereUnion = $where;
//        $where .= " AND snapshot_by = 0";
    }

    if (empty($filtersFieldsArr)) {
//        $where .= ' AND date > NOW() - INTERVAL 1 WEEK';
    }

    if ($_SESSION['Logged_StaffId'] == 11111111 && empty($_REQUEST['cold']) && empty($_REQUEST['coldNew'])) {
//        $where .= ' AND (uuid in ("7da956e030076942") OR id IN (1000000, 7841572, 1000003, 7952715, 7971903))';
    }

    $query = 'SELECT ' . ($GLOBAL_CALCULATE_TOTAL_DELIVERY && false ? 'SQL_CALC_FOUND_ROWS' : '') . " *, DATE_FORMAT(stcur_date, '%k') AS stcur_hour, description AS description_str FROM staff_order WHERE country IN (" . $_SESSION['country'] . ") AND $where $aad";
    $queryTotal = 'SELECT COUNT(id) AS delivery_total FROM staff_order WHERE country IN (' . $_SESSION['country'] . ") AND $where $aad";
//    $queryTotal = 'SELECT FOUND_ROWS() AS delivery_total';

    if (!empty($_REQUEST['today']) && false) {
//        $query .= "
//                UNION SELECT
//                    staff_order_snapshot.*,
//                    DATE_FORMAT(staff_order_snapshot.stcur_date, '%k') AS stcur_hour,
//                    staff_order_snapshot.description AS description_str
//                FROM
//                    staff_order_snapshot
//
//                WHERE country IN (" . $_SESSION['country'] . ") AND $whereUnion $aad";
    }

    if (!empty($_REQUEST['konkurs'])) {
        $query .= " ORDER BY uuid, return_date, give_date";
    } elseif (empty($sort) || true) {
        $query .= " ORDER BY `id` DESC";
    } else {
        $query .= " ORDER BY `$sort` $dir";
    }

    if (empty($_REQUEST['export_all']) && empty($_REQUEST['konkurs'])) {
        $query .= " LIMIT $start, $count";
    }

    ApiLogger::addLogVarExport('=== $_REQUEST');
    ApiLogger::addLogVarExport($_REQUEST);
    ApiLogger::addLogVarExport("FIRST QUERY => $query");
    ApiLogger::addLogJson('START');
    ApiLogger::addLogJson($query);

    if (!empty($_REQUEST['mass_change_new'])) {
        ApiLogger::addLogVarExport("####   MASS_CHANGE_NEW START");
        ApiLogger::addLogVarExport($_REQUEST);
        $resp = setMassKz($_REQUEST['type'], $_REQUEST['status'], DB::queryFirstColumn($query), $_REQUEST['ogr']);
        die(json_encode($resp));
    }

    $ordersArray = DB::queryAssArray('id', $query);

    //echo $query;
    ApiLogger::addLogVarExport("FIRST QUERY SUCCESS");
    $loadAvg = sys_getloadavg();
    $total = $GLOBAL_CALCULATE_TOTAL_DELIVERY && $loadAvg[0] < num_cpus() - 8 ? DB::queryFirstField($queryTotal) : '100000';
    ApiLogger::addLogVarExport("TOTAL QUERY SUCCESS => $queryTotal  \nRESULT total => $total");
//    ApiLogger::addLogVarExport("TOTAL QUERY SUCCESS => SELECT FOUND_ROWS() \nRESULT total => $total");
    //////////////////////////
    // GET HISTORY DATA
    $whereHistory = "((property NOT IN ('phone', 'phone_sms')) OR property IS NULL) AND type = 'update'";
    $ordersIds = array_keys($ordersArray);
    $ordersIds[] = $uuIds[] = -1;
    $ordersIdsStr = implode(', ', $ordersIds);
    $orderQueryHistory = "
        SELECT `id`,`date`,`from`,`to`,`type`,`property`,`was`,`set` FROM ActionHistory WHERE $whereHistory AND `to` IN ($ordersIdsStr)
        UNION
        SELECT `id`,`date`,`worker`,`object_id`,`type`,`property`,`was`,`set` FROM ActionHistoryOld WHERE $whereHistory AND `object_name` = 'StaffOrderObj' AND `object_id` IN ($ordersIdsStr)
        UNION
        SELECT `id`,`date`,`worker`,`object_id`,`type`,`property`,`was`,`set` FROM ActionHistoryNew WHERE $whereHistory AND `object_name` = 'StaffOrderObj' AND `object_id` IN ($ordersIdsStr)";
//var_dump($orderQueryHistory); die;
    ApiLogger::addLogVarExport("HISTORY QUERY => $orderQueryHistory");
    //DB::debugMode();
    if (false && empty($_REQUEST['xlsx']) && ($historyData = DB::queryAssArray('id', $orderQueryHistory))) {
        //var_dump($historyData); die;
        $prepHistory = array();
        foreach ($historyData as $histOrderId => $historyItem) {
            $prepHistory[$historyItem['to']][substr($historyItem['date'], 0, 16)][$historyItem['from']][$historyItem['property']]['was'] = $historyItem['was'];
            $prepHistory[$historyItem['to']][substr($historyItem['date'], 0, 16)][$historyItem['from']][$historyItem['property']]['set'] = $historyItem['set'];
        }

        $all_history = array();
        //var_dump($prepHistory); die;
        foreach ($prepHistory as $hk => $hv) {
            if (!isset($all_history[$hk])) {
                $all_history[$hk] = '<table border="1" class="htable">';
            }
            foreach ($hv as $date => $valu) {
                $all_history[$hk] .= '<tr><td rowspan=3>' . $date . '</td>';
                foreach ($valu as $from => $prop) {
                    $all_history[$hk] .= '<td rowspan=3>' . (isset($GLOBAL_STAFF_FIO[$from]) ? $staffData[$from] : $from) . '</td>';
                    $fstr = '<tr>';
                    $tstr = '<tr>';
                    foreach ($prop as $pk => $val) {
                        $all_history[$hk] .= '<td>' . $pk . '</td>';
                        $fstr .= '<td>' . str_replace('"', '', $val['was']) . '</td>';
                        $tstr .= '<td>' . str_replace('"', '', $val['set']) . '</td>';
                    }
                }
                $all_history[$hk] .= '</tr>';
                $all_history[$hk] .= $fstr . '</tr>';
                $all_history[$hk] .= $tstr . '</tr>';
            }
        }
        //var_dump( $all_history); die;
        foreach ($all_history as &$all_historyItem) {
            $all_historyItem .= '</table>';
        }
    }

    // GET HISTORY DATA
    //////////////////////////
    $totalPagesArr = array(
        'total_price_total' => '-',
        'price_total' => '-',
        'post_price_total' => '-'
    );
    //////////////////////////

    $f_offer_data = array();
    $f_kz_delivery_data = array();
    $f_send_status_data = array();
    $f_status_kz_data = array();


    if (is_array($filters) && empty($_REQUEST['history']) && empty($_REQUEST['today']) && empty($_REQUEST['cold']) && empty($_REQUEST['coldNew']) && true) {
        if (in_array($_SESSION['Logged_StaffId'], $GLOBAL_FILTER_CALC_ARR)) {
            $f_status_kz_sql = "SELECT status_kz as status, COUNT(*) AS count FROM staff_order WHERE " . str_replace(" `status_kz` = '", " status_kz NOT LIKE '1", $where) . " $aad GROUP BY status_kz";
            $f_send_status_sql = "SELECT send_status as status, COUNT(*) AS count FROM staff_order WHERE " . str_replace(" `send_status` = '", " send_status NOT LIKE '1", $where) . " $aad GROUP BY send_status";
            $f_offer_sql = "SELECT offer as status, SUM(package) AS count FROM staff_order WHERE " . str_replace(" `offer` = '", " offer NOT LIKE '1", $where) . " $aad GROUP BY offer";
            $f_kz_delivery_sql = "SELECT kz_delivery as status, COUNT(*) AS count FROM staff_order WHERE " . str_replace(" `kz_delivery` = '", " kz_delivery NOT LIKE '1", $where) . " $aad GROUP BY kz_delivery";

            ApiLogger::addLogJson("f_status_kz_sql:\n$f_status_kz_sql");
            $rs_filter = db_execute_query($f_status_kz_sql);
            while ($filterItemObj = mysql_fetch_object($rs_filter)) {
                $f_status_kz_data[$filterItemObj->status] = $filterItemObj->count;
            }

            ApiLogger::addLogJson("f_send_status_sql:\n$f_send_status_sql");
            $rs_filter = db_execute_query($f_send_status_sql);
            while ($filterItemObj = mysql_fetch_object($rs_filter)) {
                $f_send_status_data[$filterItemObj->status] = $filterItemObj->count;
            }

            ApiLogger::addLogJson("f_offer_sql:\n$f_offer_sql");
            $rs_filter = db_execute_query($f_offer_sql);
            while ($filterItemObj = mysql_fetch_object($rs_filter)) {
                $f_offer_data[$filterItemObj->status] = $filterItemObj->count;
            }

            ApiLogger::addLogJson("f_kz_delivery_sql:\n$f_kz_delivery_sql");
            $rs_filter = db_execute_query($f_kz_delivery_sql);
            while ($filterItemObj = mysql_fetch_object($rs_filter)) {
                $f_kz_delivery_data[$filterItemObj->status] = $filterItemObj->count;
            }
        }
        if (count($filters) > 2 && in_array($_SESSION['Logged_StaffId'], $GLOBAL_TOTAL_CALC_DATA) && true) {
            $qsTotalPages = "SELECT SUM(total_price) AS total_price_total, SUM(price) AS price_total, SUM(post_price) AS post_price_total FROM staff_order WHERE $where $aad";
            ApiLogger::addLogJson("qsTotalPages_sql:\n$qsTotalPages");
            $totalPagesArr = DB::queryOneRow($qsTotalPages);
            ApiLogger::addLogVarExport($totalPagesArr);
        }
    }
    ApiLogger::addLogJson('END');

    //////////////////////////
    $currency = getCurrency(date('Y-m-d'), 'Currencys');
    $offper = getCurrency(date('Y-m-d'), 'offper');
    foreach ($ordersArray as $orderId => &$orderItem) {

        $uuIds[] = $orderItem['uuid'];

        if (!empty($GLOBAL_DELIVERY_REGIONS[trim($orderItem['kz_delivery'])])) {
            $orderItem['kz_zone'] = $GLOBAL_DELIVERY_REGIONS[trim($orderItem['kz_delivery'])];
        }

        $orderItem['h'] = 0;
        $dop_price = 0;
        if ($orderItem['country'] == 'kzg') {
            $koef = PRICE_KOEF_KZG;
        } elseif ($orderItem['country'] == 'ru') {
            $koef = PRICE_KOEF_RU;
        } else {
            $koef = PRICE_KOEF_KZ;
        }
        ///////////////////////////////////////////////////////////////
        // main product
        $orderItem['ofprice'] = $orderItem['package'] * (@$_SESSION['dprice_' . $orderItem['offer']][$orderItem['country']] * $koef);
        $other_data = json_decode($orderItem['other_data'], true);
        $offer_property = '';

        if (json_last_error() == JSON_ERROR_NONE) {
            if (isset($other_data["name"]) && isset($other_data["vendor"]) && md5($other_data["name"]) == md5($other_data["vendor"])) {
                unset($other_data["vendor"]);
            }
            $offer_property = implode(' ', $other_data);
        }
        $orderItem['other_data'] = $offer_property;

        ///////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////
        // additional products
        $dopTovarArr = json_decode($orderItem['dop_tovar'], true);
        $dopTovarAll = array();

        if (json_last_error() == JSON_ERROR_NONE && !empty($dopTovarArr['dop_tovar'])) {
//            ApiLogger::addLogVarExport("---------- OOOOKKKKK koef = $koef");
//            ApiLogger::addLogVarExport($dopTovarArr);
            $orderItem['dopTovarArr'] = $dopTovarArr;

            foreach ($dopTovarArr['dop_tovar'] AS $ke => $va) {
                $properties = array();
                $properties_key = array_keys($dopTovarArr);
                //if($orderItem['id'] == '1603626') { var_dump($dopTovarArr['dop_tovar_count'],$va); die;}
//                ApiLogger::addLogVarExport("---------- va = '$va' _SESSION['ofprice_va'] = {$_SESSION['ofprice_' . $va]}");
                $orderItem['h'] += ($dopTovarArr['dop_tovar_count'][$ke]) * $_SESSION['ofprice_' . $va] * $koef;
                foreach ($properties_key AS $property_key) {
                    if (
                            !in_array($property_key, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) &&
                            isset($dopTovarArr[$property_key][$ke]) &&
                            !empty($dopTovarArr[$property_key][$ke])
                    ) {
                        if (
                                isset($dopTovarArr["name"][$ke]) &&
                                isset($dopTovarArr["vendor"][$ke]) &&
                                md5($dopTovarArr["name"][$ke]) == md5($dopTovarArr["vendor"][$ke])
                        ) {
                            unset($dopTovarArr["vendor"][$ke]);
                            $properties[] = $dopTovarArr["name"][$ke];
                        } else {
                            $properties[] = $dopTovarArr[$property_key][$ke];
                        }
                    }
                }

                // читабельное название офферов
                $offer_name = isset($GLOBAL_OFFER_DESC[$dopTovarArr['dop_tovar'][$ke]]) ? $GLOBAL_OFFER_DESC[$dopTovarArr['dop_tovar'][$ke]] : $dopTovarArr['dop_tovar'][$ke];
                $dopTovarAll[] = $offer_name . " " . implode(" ", $properties) . " - " . $dopTovarArr['dop_tovar_count'][$ke] . "шт. (" . round($dopTovarArr['dop_tovar_price'][$ke], 2) . ")";
                $dop_price = $dop_price + (float) $dopTovarArr['dop_tovar_price'][$ke];
            }
//            ApiLogger::addLogVarExport('----------');
        }
        $orderItem['dop_tovar'] = implode("<br>", $dopTovarAll);
        ///////////////////////////////////////////////////////////////

        $orderItem['dop_tovar_price'] = $dop_price;
        //$offper = getCurrency(substr($orderItem['fill_date'],0,7),'offper');

        $rub_now = (array) $currency['RUB'];

        if (!isset($offper[$orderItem['offer']])) {
            $offper[$orderItem['offer']] = 0.6;
        }
        if ((int) $_SESSION['admin'] && empty($_REQUEST['cold']) && empty($_REQUEST['coldNew'])) {
            $orderItem['pay_order'] = (int) round(($orderItem['pay_order'] * $koef));
            if (isset($GLOBAL_OFFER_PAY['pay' . $orderItem['offer']][$orderItem['staff_id']][$orderItem['country']]) && !in_array($orderItem['staff_id'], array('53095975'))) {
                if (is_array($GLOBAL_OFFER_PAY['pay' . $orderItem['offer']][$orderItem['staff_id']][$orderItem['country']])) {
                    foreach ($GLOBAL_OFFER_PAY['pay' . $orderItem['offer']][$orderItem['staff_id']][$orderItem['country']] as $dated => $vald) {
                        if (strtotime($orderItem['date']) > strtotime($dated)) {
                            $orderItem['pay_order'] = (int) round(($vald * $koef));
                        }
                    }
                } else {
                    $orderItem['pay_order'] = (int) round(($GLOBAL_OFFER_PAY['pay' . $orderItem['offer']][$orderItem['staff_id']][$orderItem['country']] * $koef));
                }
            }


            // START Колонка 'C'
            if ($orderItem['package'] > 2 || (strlen($orderItem['dop_tovar']) > 10 && $orderItem['package'] > 1)) {
                $orderItem['oper_zp'] = $orderItem['total_price'] * ($orderItem['country'] == 'kzg' ? 0.054 : 0.036);
            } elseif ($orderItem['package'] > 1 || (strlen($orderItem['dop_tovar']) > 10 && $orderItem['package'] > 0)) {
                $orderItem['oper_zp'] = $orderItem['total_price'] * ($orderItem['country'] == 'kzg' ? 0.042 : 0.036);
            } else {
                $orderItem['oper_zp'] = $orderItem['total_price'] * ($orderItem['country'] == 'kzg' ? 0.0105 : 0.007);
            }

            if ($orderItem['country'] == 'kz') {
                if ((int) $orderItem['total_price'] <= 9999) {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.01;
                } elseif ((int) $orderItem['total_price'] > 9999 && (int) $orderItem['total_price'] <= 19999) {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.03;
                } elseif ((int) $orderItem['total_price'] > 19999 && (int) $orderItem['total_price'] <= 27999) {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.06;
                } else {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.09;
                }
            }

            if ($orderItem['country'] == 'kzg' && in_array($orderItem['offer'], array('black_latte_750', 'black_latte_850', 'nova_derm_porciya', 'tea_parazit_porciya', 'tea_prostatit_porciya', 'tea_sustav_porciya')) && strtotime($orderItem['date']) >= strtotime('2018-09-16')) {
                if ($orderItem['package'] == 1) {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.05;
                } else if ($orderItem['package'] > 1 && $orderItem['package'] <= 4) {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.06;
                } else if ($orderItem['package'] > 4) {
                    $orderItem['oper_zp'] = $orderItem['total_price'] * 0.07;
                }
            }

            if (in_array($orderItem['staff_id'], array(22222222, 33333333, 55555555, 47369504, 57369831, 25937686, 97979449, 93991201, 93375132, 90871721, 91318760, 95873538))) {
                $orderItem['oper_zp'] = $orderItem['total_price'] * 0.11;
            } elseif (in_array($orderItem['staff_id'], array(11112222))) {
                $orderItem['oper_zp'] = $orderItem['total_price'] * 0.1;
            }
            // END Колонка 'C'
            // START Колонка 'F'
            if ($orderItem['kz_delivery'] == 'Почта') {
                if (strtotime($orderItem['fill_date']) < strtotime('2017-01-24')) {
                    $orderItem['f'] = 695 + round($orderItem['total_price'] * 0.01);
                } else {
                    $orderItem['f'] = 635 + round($orderItem['total_price'] * 0.01);
                }
                if ($orderItem['country'] == 'ru') {
                    $orderItem['f'] = 348;
                }
            } else if (isset($GLOBAL_CURIER_PAYMENT[$orderItem['kz_delivery']][$GLOBAL_CURIER_PAYMENT_ASSOC[$orderItem['status_cur']]])) {
                $orderItem['f'] = $GLOBAL_CURIER_PAYMENT[$orderItem['kz_delivery']][$GLOBAL_CURIER_PAYMENT_ASSOC[$orderItem['status_cur']]];
            } else if ($orderItem['status_cur'] == 'Продажа курьер') {
                $curPercent = 0.04;
                if ($orderItem['stcur_hour'] < 13) {
                    $curPercent = 0.08;
                } else if ($orderItem['stcur_hour'] < 18) {
                    $curPercent = 0.06;
                }
                $orderItem['f'] = round($orderItem['total_price'] * $curPercent);
            } else if (in_array($orderItem['status_cur'], array('ОПЛ ДОП', 'ОПЛ ДОП РАСПОЛ'))) {
                ApiLogger::addLogVarDump($orderItem['stcur_date'] . '===>>>' . strtotime($orderItem['stcur_date']) . '===>>>' . $orderItem['stcur_hour']);
                $curPercent = 0.06;
                if ($orderItem['stcur_hour'] >= 9 && $orderItem['stcur_hour'] < 13) {
                    $curPercent = 0.06;
                } else if ($orderItem['stcur_hour'] >= 13 && $orderItem['stcur_hour'] < 18) {
                    $curPercent = 0.05;
                }
                $orderItem['f'] = round($orderItem['total_price'] * $curPercent);
            }
            $orderItem['f'] = (float) $orderItem['f'];
            // END Колонка 'F'
            // START Колонка 'E'
            $orderItem['e'] = calcTariffSettings('e', $orderItem['kz_delivery'], $orderItem['country'], $orderItem['staff_id'], $orderItem['return_date'], $orderItem['total_price']);
            // END Колонка 'E'
            // START Колонка 'G'
            $orderItem['g'] = calcTariffSettings('g', $orderItem['kz_delivery'], $orderItem['country'], $orderItem['staff_id'], $orderItem['return_date'], $orderItem['total_price']);
            // END Колонка 'G'
            // START Колонка 'I'
            $orderItem['i'] = calcTariffSettings('i', $orderItem['kz_delivery'], $orderItem['country'], $orderItem['staff_id'], $orderItem['return_date'], $orderItem['total_price']);
            // END Колонка 'I'
            // START Колонка 'J'
            if ($orderItem['country'] == 'kz') {
                $orderItem['j'] = round($orderItem['total_price'] * 0.0054);
            } elseif ($orderItem['country'] == 'kzg') {
                $orderItem['j'] = round($orderItem['total_price'] * 0.0037);
            } elseif ($orderItem['country'] == 'ru') {
                $orderItem['j'] = round($orderItem['total_price'] * 0.0054);
            } else {
                $orderItem['j'] = (int) $orderItem['j'];
            }
            // END Колонка 'J'

            $orderItem['all_pays'] = $orderItem['total_price'] - ($orderItem['oper_zp'] + $orderItem['pay_order'] + $orderItem['ofprice'] + $orderItem['e'] + $orderItem['f'] + $orderItem['g'] + $orderItem['h'] + $orderItem['i'] + $orderItem['j']);
        }

        $orderItem['kz_admin'] = (int) $orderItem['kz_admin'];

        $orderItem['addr'] = mb_substr($orderItem['addr'], 0, in_array($_SESSION['Logged_StaffId'], array(90316241, 59957884)) ? mb_strlen($orderItem['addr'], 'utf-8') : -4, 'utf-8');

        $orderItem['package'] = (int) $orderItem['package'];
        $orderItem['price'] = $orderItem['price'] * 1;
        $orderItem['total_price'] = $orderItem['total_price'] * 1;

        if (!empty($orderItem['last_edit'])) {
            if (!empty($GLOBAL_STAFF_TEAM[$orderItem['last_edit']])) {
                $orderItem['team'] = $GLOBAL_STAFF_TEAM[$orderItem['last_edit']];
            } else {
                $orderItem['team'] = 0;
            }

            $orderItem['responsible'] = empty($GLOBAL_STAFF_RESPONSIBLE[$orderItem['last_edit']]) ? 0 : $GLOBAL_STAFF_RESPONSIBLE[$orderItem['last_edit']];
            $orderItem['curator'] = empty($GLOBAL_RESPONSIBLE_CURATOR[$orderItem['last_edit']]) ? 0 : $GLOBAL_RESPONSIBLE_CURATOR[$orderItem['last_edit']];
        }

        if (!empty($orderItem['is_cold_staff_id'])) {
            $orderItem['responsible_cold'] = empty($GLOBAL_STAFF_RESPONSIBLE[$orderItem['is_cold_staff_id']]) ? 0 : $GLOBAL_STAFF_RESPONSIBLE[$orderItem['is_cold_staff_id']];
        }

        $orderItem['tip'] = empty($all_history[$orderId]) ? '' : $all_history[$orderId];
        $orderItem['rings'] = '<span id="call_' . $orderId . '"><a href="#" onclick="showRingsGridAuto(\'' . $orderId . '\',\'' . $orderItem['country'] . '\'); return false;">Показать</a></span>';

        $orderItem['phone_orig'] = preg_replace('/\D/', '', $orderItem['phone']);
        if ($orderItem['country'] == 'kz') {
            $orderItem['phone'] = hide_phone($orderItem['phone']);
            $orderItem['phone_sms'] = hide_phone($orderItem['phone_sms']);
        } elseif ($orderItem['country'] == 'kzg') {
            $orderItem['phone'] = '9' . hide_phone(substr($orderItem['phone'], 1));
        }

        $orderItem['next_return_date'] = date('Y-m-d H:i:s', strtotime($obj['return_date']));


        if (($youtubeUrl = $redis->hGet('youtube_urls', $orderItem['id']))) {
            $orderItem['youtube_url'] = $youtubeUrl;
        }
    }
    unset($clientItem, $orderItem);

    if (!empty($_REQUEST['xlsx']) && ($ordersCount = count($ordersArray))) {

        require_once dirname(__FILE__) . '/../lib/excel/excel.class.php';
        $filename = 'Delivery_' . date("YmdHis") . '.xls';

        $columnLabels = json_decode($_REQUEST['cols'], true);

        if (!empty($_REQUEST['konkurs']) && in_array($_SESSION['Logged_StaffId'], array(11111111, 25937686))) {
            $columnLabels = array(
                'id' => 'ID',
                'uuid' => 'Кл ID',
                'offer' => 'Товар',
                'package' => 'Количество',
            );
        }

        $excel = new ExcelWriter($filename);
        $excel->writeLine(array_values($columnLabels));


        if (!empty($_REQUEST['konkurs']) && !empty($_REQUEST['pdf'])) {
            require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'px', 'A4', true, 'UTF-8', false);
            //$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '8192M');

            $qs = " SELECT `id`, `uuid`, `return_date`, `give_date`, `offer`, `package`, `dop_tovar`
                    FROM `staff_order`
                    WHERE   `status` = 'Подтвержден' AND
                            (
                            `return_date` > '2019-01-01 00:00:00' AND `send_status` = 'Оплачен'
                            OR
                            `give_date` > '2019-01-01 00:00:00' AND `send_status` = 'Полная предоплата'
                            ) AND uuid IN %ls
                    ORDER BY uuid, return_date";
            $payedOrders = DB::queryAssArray('id', $qs, $uuIds);

            $currentUuid = $currentKoef = null;
            $konkursStartTime = strtotime(date('2020-01-01'));
            $newOrdersArray = array();
            foreach ($payedOrders as $orderId => $payedOrderItem) {

                if ($payedOrderItem['uuid'] == '483db79e43a57f71') {
                    continue;
                }

                if ($currentUuid != $payedOrderItem['uuid'] || $currentUuid === null) {
                    $currentKoef = 1;
                    $currentUuid = $payedOrderItem['uuid'];
                } else {
                    $currentKoef += 1;
                }

                if (array_key_exists($orderId, $ordersArray)) {
                    if (
                            strtotime($payedOrderItem['return_date']) > $konkursStartTime ||
                            strtotime($payedOrderItem['give_date']) > $konkursStartTime
                    ) {

                        $payedOrderItem['dopTovarArr'] = json_decode($payedOrderItem['dop_tovar'], true);
//                        $offersCount = empty($payedOrderItem['offer']) ? 0 : (int) $payedOrderItem['package'];
                        $offersCount = (int) $payedOrderItem['package'];
                        if (!empty($payedOrderItem['dopTovarArr']['dop_tovar_count'])) {
                            $offersCount += (int) array_sum($payedOrderItem['dopTovarArr']['dop_tovar_count']);
                        }

                        if ($offersCount > 0 && $currentKoef > 0) {
                            $ordersArray[$orderId]['package'] = $offersCount;
                            $newOrdersArray = array_merge($newOrdersArray, array_fill(count($newOrdersArray), $currentKoef * $offersCount, $ordersArray[$orderId]));
                        }
                    }
                }
            }

            $ordersArray = $newOrdersArray;

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT - 0, PDF_MARGIN_TOP + 0, PDF_MARGIN_RIGHT - 0);
            $pdf->SetFont('arial', '', 20, '', true);

            $ost = count($ordersArray) % 50;
            if ($ost > 0 && ($delta = 50 - $ost)) {
                for ($index = 1; $index <= $delta; $index++) {
                    $ordersArray[] = array('id' => '&nbsp;');
                }
            }

            $pagesArr = array_chunk($ordersArray, 50);
            foreach ($pagesArr as $pageItem) {

                $pdf->AddPage();
                $html = '<table align="center" border="1" cellpadding="15" style="width:100%; font-weight: bold;">';
                $rowsArr = array_chunk($pageItem, 5);
                foreach ($rowsArr as $rowItem) {
                    $html .= '<tr>';
                    foreach ($rowItem as $item) {
                        $html .= '<td  height="75">' . $item['id'] . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
                $pdf->writeHTML($html, true, false, false, false, '');
            }

            $pdf->Output(dirname(__FILE__) . '/../tmp/konkurs-kbt_' . time() . '.pdf', 'FI');
            die;
        }


        if (!empty($_REQUEST['konkurs'])) {

            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '8192M');

            $qs = " SELECT `id`, `uuid`, `return_date`, `give_date`, `offer`, `package`, `dop_tovar`
                    FROM `staff_order`
                    WHERE   `status` = 'Подтвержден' AND
                            (
                            `return_date` > '2019-01-01 00:00:00' AND `send_status` = 'Оплачен'
                            OR
                            `give_date` > '2019-01-01 00:00:00' AND `send_status` = 'Полная предоплата'
                            ) AND uuid IN %ls
                    ORDER BY uuid, return_date";
            $payedOrders = DB::queryAssArray('id', $qs, $uuIds);

            $currentUuid = $currentKoef = null;
            $konkursStartTime = strtotime(date('2020-01-01'));
            $newOrdersArray = array();
            foreach ($payedOrders as $orderId => $payedOrderItem) {

                if ($currentUuid != $payedOrderItem['uuid'] || $currentUuid === null) {
                    $currentKoef = 1;
                    $currentUuid = $payedOrderItem['uuid'];
                } else {
                    $currentKoef += 1;
                }

                if (array_key_exists($orderId, $ordersArray)) {
                    if (
                            strtotime($payedOrderItem['return_date']) > $konkursStartTime ||
                            strtotime($payedOrderItem['give_date']) > $konkursStartTime
                    ) {

                        $payedOrderItem['dopTovarArr'] = json_decode($payedOrderItem['dop_tovar'], true);
//                        $offersCount = empty($payedOrderItem['offer']) ? 0 : (int) $payedOrderItem['package'];
                        $offersCount = (int) $payedOrderItem['package'];
                        if (!empty($payedOrderItem['dopTovarArr']['dop_tovar_count'])) {
                            $offersCount += (int) array_sum($payedOrderItem['dopTovarArr']['dop_tovar_count']);
                        }

                        if ($offersCount > 0 && $currentKoef > 0) {
                            $ordersArray[$orderId]['package'] = $offersCount;
                            $newOrdersArray = array_merge($newOrdersArray, array_fill(count($newOrdersArray), $currentKoef * $offersCount, $ordersArray[$orderId]));
                        }
                    }
                }
            }

            $ordersArray = $newOrdersArray;
        }

        foreach ($ordersArray as $dataItem) {
            $temp = array();

            if (in_array($_SESSION['Logged_StaffId'], array(11111111, 25937686))) {
                if ($dataItem['country'] == 'kz') {
                    $dataItem['phone'] = hide_phone($dataItem['phone'], true);
                    $dataItem['phone_sms'] = hide_phone($dataItem['phone_sms'], true);
                } elseif ($dataItem['country'] == 'kzg') {
                    $dataItem['phone'] = '9' . hide_phone(substr($dataItem['phone'], 1), true);
                }
            }

            foreach ($columnLabels as $dataIndex => $fake) {
                $temp[] = empty($dataItem[$dataIndex]) ? '' : strip_tags($dataItem[$dataIndex]);
            }
            $excel->writeLine($temp);
        }
        $excel->close();
    }

    $retArr = array(
        'success' => true,
        'total' => $total,
        'sql' => $query,
        'executing' => (microtime(true) - $time_1),
        'data' => array_values($ordersArray),
        'filter_status_kz' => $f_status_kz_data,
        'filter_send_status' => $f_send_status_data,
        'filter_offer' => $f_offer_data,
        'filter_kz_delivery' => $f_kz_delivery_data,
        'totalPagesData' => $totalPagesArr
    );

    if (!empty($_REQUEST['today'])) {

//        ApiLogger::addLogVarExport('^^^^^^^^^^^^^^^^^^');
//        ApiLogger::addLogVarDump(count($filtersFieldsArr) == 2);
//        ApiLogger::addLogVarDump(!empty($filtersFieldsArr['kz_curier']));
//        ApiLogger::addLogVarDump(stripos($filtersFieldsArr['kz_curier']['value'], ',') === false);
//        ApiLogger::addLogVarDump(!empty($filtersFieldsArr['date_delivery']['comparison']));
//        ApiLogger::addLogVarDump($filtersFieldsArr['date_delivery']['comparison'] == 'eq');
//        ApiLogger::addLogVarDump(reset($ordersArray));
//        ApiLogger::addLogVarExport('^^^^^^^^^^^^^^^^^^');

        $retArr['filters'] = $filtersFieldsArr;

        if (
                count($filtersFieldsArr) == 2 &&
                !empty($filtersFieldsArr['kz_curier']) &&
                stripos($filtersFieldsArr['kz_curier']['value'], ',') === false &&
                !empty($filtersFieldsArr['date_delivery']['comparison']) &&
                $filtersFieldsArr['date_delivery']['comparison'] == 'eq' &&
                ($firstData = reset($ordersArray))
        ) {
            $retArr['register_number'] = $firstData['register_number'];
        }
    }

    $retJson = json_encode($retArr, JSON_UNESCAPED_UNICODE);
    echo $retJson;

//    var_dump($retArr);
//    print(json_last_error_msg());  die;
//    ApiLogger::addLogJson($retJson);
    ApiLogger::addLogVarExport('ALL ENDED');
}
