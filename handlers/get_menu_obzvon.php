<?php
ini_set("display_errors", 0);
error_reporting(E_ERROR);
/**
 * Обзвон
 */
header('Content-Type: application/json; charset=utf-8', true);
session_start();
// var_dump($_REQUEST);
$time_1 = microtime(true);

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        'success' => false,
        'msg' => 'Permission denied'
    )));
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/class.staff.php';
// require_once("../../DevExtreme/LoadHelper.php");
// spl_autoload_register(array("DevExtreme\LoadHelper", "LoadModule"));

// use DevExtreme\DbSet;
// use DevExtreme\DataSourceLoader;

if (!empty($_GET['id'])) {
    $obj = array();

    // get order
    $sql = "SELECT
                *,
                `date` as `date`,
                IF (LENGTH(`phone_sms`) > 5, `phone_sms`, '') AS `phone_sms`,
                `building` as `house`,
                IF(card_number = 0, '', card_number) AS card_number
            FROM `staff_order` as `a`
            WHERE `id` = %i AND (`is_get` = 0 OR (`is_get` = 1 AND `who_get` = %i))";

    ApiLogger::addLogVarExport('START BY ID');
    ApiLogger::addLogVarExport("sql: $sql");
    $obj = DB::queryOneRow($sql, $_REQUEST['id'], $_SESSION['Logged_StaffId']);

    // Выборка всех офферов и их свойств
    // Для формирования списка дополнительных товаров и их свойств в анкете
    $offersWithProperies = array();
    foreach ($GLOBAL_ACTIVE_OFFERS_ASS as $offerId => $offerItem) {
        if ($offerItem['offer_name'] && $offerItem['offers_active'] > 0) {

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

    if (!empty($obj)) {

        $qs = 'SELECT COUNT(id) FROM staff_order WHERE status = "Подтвержден" AND send_status IN ("Отправлен", "Предоплата") AND uuid = %s';
        $obj['have_orders'] = DB::queryFirstField($qs, $obj['uuid']);

        DB::update('staff_order', array('is_get' => 1, 'time_get' => DB::sqlEval('NOW()'), 'who_get' => $_SESSION['Logged_StaffId']), 'id = %i', $_REQUEST['id']);
        $actionHistoryObj = new ActionHistoryObj();
        $actionHistoryObj->save('StaffOrderObj', $obj['id'], 'update', 'who_get', $obj['who_get'], $_SESSION['Logged_StaffId']);
    } else {
        $obj['offers_with_properies'] = $offersWithProperies;
        $obj['is_get'] = '1';

        die(json_encode(array(
            'success' => true,
            'executing' => (microtime(true) - $time_1),
            'data' => $obj
        )));
    }

    $obj['cert'] = file_exists('/var/www/baribarda.com/resources/cert/' . $obj['offer'] . '.jpg');
    $orig_offer = $obj['offer'];

    $obj['ids'] = array();
    $obj['history'] = 'Первый заказ';
    $obj['payed_curr_year_count'] = 0;

    if (($biletCount = getKonkusBiletCount(array($obj)))) {
        $obj['konkus_bilet_count'] = reset($biletCount);
    }
    if (($clientData = DB::queryFirstRow('SELECT order_payed_2020_count, order_payed_2020_total, client_worries FROM Clients WHERE uuid = %s', $obj['uuid']))) {
        $clientData['order_payed_2020_count'] = (int) $clientData['order_payed_2020_count'];
        $clientData['order_payed_2020_total'] = (int) $clientData['order_payed_2020_total'];
        $obj = array_merge($obj, $clientData);
    }

    $obj['phone_orig'] = preg_replace('/\D/', '', $obj['phone']);
    if ($obj['country'] == 'kz') {
        $obj['phone'] = hide_phone($obj['phone']);
        $obj['phone_sms'] = hide_phone($obj['phone_sms']);
    } elseif ($obj['country'] == 'kzg') {
        $obj['phone'] = '9' . hide_phone(substr($obj['phone'], 1));
        $obj['phone_sms'] = '9' . hide_phone(substr($obj['phone_sms'], 1));
    }

    if ($obj['phone_sms'] == '9000000') {
        $obj['phone_sms'] = '';
    }

    ///////////////////////////////////////////////////////
    $sqlHistory = "
        SELECT
            `id`,
            IF(`return_date` > '2019-01-01 00:00:00' AND `status` = 'Подтвержден' AND `send_status` = 'Оплачен', 1, 0) AS payed_curr_year_count,
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
    if (($historyData = DB::query($sqlHistory, $obj['uuid']))) {
        $obj['history'] = '<table cellspacing="1" cellpadding="3" border="1">
            <tr>
                <td width="40">id</td>
                <td width="80">дата</td>
                <td width="120">ФИО</td>
                <td width="120">Адрес</td>
                <td width="120">Источник</td>
                <td width="120">Название товара</td>
                <td width="120">Количество в заказе</td>
                <td width="120">Статус заказа</td>
                <td width="120">Статус доставки</td>
                <td width="120">Статус посылки</td>
                <td width="120">Подстатус (коммент)</td>
            </tr>';

        foreach ($historyData as $historyItem) {
            $obj['payed_curr_year_count'] += $historyItem['payed_curr_year_count'];
            $obj['ids'][] = $historyItem['id'];

            $obj['history'] .= sprintf(
                    "<tr" . ($historyItem['staff_id'] == 11112222 ? ' style="color: blue; font-weight: bold; font-size: 18px;"' : '') . ">
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td style='color:green;'>%s</td>
                    <td style='color:%s'>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                </tr>", '<a href="#" onclick="javascript:CreateMenuDelivery({order_id: ' . $historyItem['id'] . '},,\'kz\');">' . $historyItem['id'] . '</a>', $historyItem['fill_date'], $historyItem['fio'], $historyItem['addr'], $GLOBAL_PARTNERS[$historyItem['staff_id']], $historyItem['offer'], $historyItem['package'], $historyItem['status'], $historyItem['send_status'] == 'Оплачен' ? 'green' : 'red', $historyItem['send_status'], $historyItem['status_kz'], $historyItem['description']
            );
        }

        $obj['history'] .= '</table>';
    }
    ///////////////////////////////////////////////////////

    $obj['rings'] = "<span id='calls_" . $obj['id'] . "'><a href='javascript:void(0)' onclick='showRingsAnket(\"" . $obj['id'] . "\",\"" . $obj['country'] . "\"); return false;'>" . $obj['call_count'] . "</a></span>";
    $obj['ids'] = json_encode($obj['ids']);

    //
    // формируем список всех свойств продукта
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
    // Дополнительный товар
    //
    $dop_tovar = json_decode($obj['dop_tovar'], true);
    $dop_tovar_tmp = array();
    $obj['dop_tovar'] = array();

    if (!empty($dop_tovar['dop_tovar']) && is_array($dop_tovar['dop_tovar'])) {
        $labels = array(
            'attribute' => 'Атрибут',
            'color' => 'Цвет',
            'size' => 'Размер',
            'type' => 'Тип',
            'vendor' => 'Модель',
            'name' => 'Название',
            'description' => 'Описание',
            'dop_tovar' => '',
            'dop_tovar_count' => 'Количество',
            'dop_tovar_price' => 'Цена',
        );

        $description = array();

        foreach ($dop_tovar['dop_tovar'] AS $key => $value) {
            foreach ($dop_tovar AS $attr => $a) {
                if ($attr == 'dop_tovar') {
                    $dop_tovar_tmp['offer'] = $dop_tovar['dop_tovar'][$key];
                } elseif ($attr == 'dop_tovar_count') {
                    $dop_tovar_tmp['count'] = $dop_tovar['dop_tovar_count'][$key];
                } elseif ($attr == 'dop_tovar_price') {
                    $dop_tovar_tmp['price'] = $dop_tovar['dop_tovar_price'][$key];
                } else {
                    $dop_tovar_tmp[$attr] = $dop_tovar[$attr][$key];
                }
            }

            $obj['dop_tovar'][] = $dop_tovar_tmp;
        }
    }

    $obj['offers_with_properies'] = $offersWithProperies;


    ApiLogger::addLogVarExport('||||||||||||||||||||||||| OprosnikObj');
    if (false) {
        $oprosnikObj = new OprosnikObj();
        $obj['oprosnik'] = $oprosnikObj->buidOprosnik($obj['id']);
    } else {
        $obj['oprosnik'] = false;
    }

    echo json_encode(array(
        'success' => true,
        'sql' => $sql,
        'executing' => (microtime(true) - $time_1),
        'data' => $obj
    ));
} else {
    $dobrikUseWhere = false;
    $sort = '';
    $dir = 'DESC';
    // collect request parameters
    $start = (int) isset($_REQUEST['skip']) ? $_REQUEST['skip'] : 0;
    $count = (int) isset($_REQUEST['take']) ? $_REQUEST['take'] : 100;
    if(isset($_REQUEST['sort'])) {
        $presort = json_decode($_REQUEST['sort'], true);
        $sort = isset($presort['selector']) ? $presort['selector'] : '';
        $dir = ($presort['desc']) ? 'DESC' : 'ASC';
    }
    
    var_dump($sort);

    $filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
    $sort = my_mysqli_real_escape_string($sort);
    $dir = my_mysqli_real_escape_string($dir);
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

            if (stripos($field, 'date') !== false) {
                $dobrikUseWhere = true;
            }

            if ($field == 'offer_groups') {
                $ofGroupsArr = explode(',', $value);
                $ofGroupInArr = array();
                foreach ($ofGroupsArr as $groupItem) {
                    $ofGroupInArr = array_merge($ofGroupInArr, $GLOBAL_GROUP_OFFER[$groupItem]);
                }
                $where .= " AND offer IN ('" . implode("', '", $ofGroupInArr) . "')";
                continue;
            } else if ($field == 'id' && strpos($value, ' ') === false) {
                $filterType = 'numeric';
                $compare = 'eq';
            }

            switch ($filterType) {
                case 'string':
                    $value = trim($value);

                    if (in_array($field, array('id', 'card_number'))) {
                        $tmpIds = explode(' ', $value);
                        $tmpIds[] = -1;
                        $tmpIds = array_diff($tmpIds, array(''));
                        $qs .= " AND `$field` IN ('" . implode("','", $tmpIds) . "')";
                    } else {
                        $qs .= " AND `$field` LIKE '" . $value . "%'";
                    }
                    Break;
                case 'list':
                    if (in_array($field, array('staff_id', 'staff_id_orig')) && $value == -1) {
                        $qs .= " AND staff_id NOT IN (22222222, 33333333, 55555555) ";
                        Break;
                    }
                    if (in_array($field, array('staff_id', 'staff_id_orig')) && $value == -2) {
                        $qs .= " AND staff_id IN (97979449 , 93991201, 93375132, 90871721, 91318760, 95873538, 99171796, 47369504, 57369831, 11111111, 33333333, 32818339, 78017798, 49152384, 48514518, 71171003, 48061934, 45033811, 42655111, 20217943, 47063460, 36481874, 31769332, 22222222, 55555555, 55557777, 55556666) ";
                        Break;
                    }

                    if (strstr($value, ',')) {
                        $fi = explode(',', $value);
                        for ($q = 0; $q < count($fi); $q++) {
                            $fi[$q] = "'" . $fi[$q] . "'";
                        }
                        $value = implode(',', $fi);
                        $qs .= " AND `" . $field . "` IN (" . $value . ")";
                    } else {
                        $qs .= " AND `" . $field . "` = '" . $value . "'";
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

    if (empty($dobrikUseWhere)) {
        $where .= ' AND `date` > CURDATE() - INTERVAL 1 WEEK ';
    }

//    die($where);
    $ukr_ar = array('37239410', '77777777', '30224289', '30356360', '76488859', '79319411', '47671128', '72480483', '80911164', '21630264', '44440873', '62443980', '77767205');

    $str_add = ' ';
    $all_in_condition = '';

    if (((int) date('H') > 8 && (int) date('H') < 21)) {
        // Дневное время
        $statusTime_KZ = " status IN ('новая', 'Перезвонить', 'Отменён') ";
        $statusTime_KZG = "'новая', 'Недозвон', 'Перезвонить', 'Недозвон_ночь'";
    } else {
        // Ночное время
        $statusTime_KZ = " status IN ('новая', 'Недозвон', 'Недозвон_ночь') ";
        $statusTime_KZG = "'новая', 'Недозвон', 'Перезвонить', 'Недозвон_ночь'";
    }

    if (true) {
        // Медет, брд, для всех ролей в разделе Обзвон, убрать заказ со статусом Подтвержден, Отменён, предварительно подтверждён
        $str_add .= " AND status NOT IN ('Подтвержден', 'Отменён', 'Предварительно подтвержден', 'Брак', 'Заказано у конкурентов', 'Заказ уже обработан', 'Недб', 'Уже получил заказ', 'Черный список')";
    }

    if (!empty($_SESSION['Logged_Group']) && ($_SESSION['admin'] + $_SESSION['operatorcold'] + $_SESSION['operatorrecovery']) == 0) {
//        $str_add .= " AND (`Group` = '" . $_SESSION['Logged_Group'] . "' OR `Group` = 0)";
    }
    if (!empty($_SESSION['operatorcold']) && empty($_SESSION['admin'])) {
        if ($GLOBAL_STAFF_CURATOR[$_SESSION['Logged_StaffId']] == 44917943) {
//            $str_add .= " AND (web_id = 666666 OR staff_id IN (22222222, 55555555) AND last_edit = {$_SESSION['Logged_StaffId']})";
            $str_add .= " AND (web_id = 666666 AND last_edit = {$_SESSION['Logged_StaffId']})";
        } else {
            $str_add .= " AND staff_id IN (22222222, 55555555) AND last_edit = {$_SESSION['Logged_StaffId']}";
        }
    }
    if (($_SESSION['operator']) > 0 && empty($_SESSION['admin'])) {
//        $str_add .= " AND staff_id NOT IN (22222222, 33333333, 55555555, 47369504, 57369831, 25937686, 97979449, 93991201, 93375132, 90871721, 91318760, 95873538)";
        $str_add .= ' AND staff_id NOT IN (' . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ')';
//        $str_add .= " AND offer IN ('laminary', 'lucem', 'lucem_vacci')";
//        $all_in_condition = " AND (((( " . $statusTime_KZ . " OR (status = 'новая' AND `Group` = 3)) AND country = 'kz') OR (country = 'kz' AND status = 'Перезвонить') OR (country = 'kz' AND status IN ('новая', 'Недозвон', 'Недозвон_ночь') AND staff_id = 53095975)) OR (status IN (" . $statusTime_KZG . ") AND country = 'az') OR (status IN (" . $statusTime_KZG . ") AND country = 'ru') OR (status IN (" . $statusTime_KZG . ") AND country = 'am') OR (country = 'ae') OR (country = 'uz') OR (status IN (" . $statusTime_KZG . ") AND country = 'kzg'))";
        $all_in_condition = " AND (((country = 'kz' AND status = 'Перезвонить') OR (country = 'kz' AND status IN ('новая', 'Недозвон', 'Недозвон_ночь') AND staff_id = 53095975)) OR (status IN (" . $statusTime_KZG . ") AND country = 'az') OR (status IN (" . $statusTime_KZG . ") AND country = 'ru') OR (status IN (" . $statusTime_KZG . ") AND country = 'am') OR (country = 'ae') OR (country = 'uz') OR (status IN (" . $statusTime_KZG . ") AND country = 'kzg'))";
    } else {
        //$all_in_condition = " AND (((( status IN (" . $statusTime_KZ . ") AND country = 'kz') OR ( status IN (" . $statusTime_KZG . ") AND not_rus=1 AND country = 'kz')) OR (country = 'kz' AND status = 'Перезвонить' )) OR (status IN (" . $statusTime_KZG . ") AND country = 'az') OR (status IN (" . $statusTime_KZG . ") AND country = 'ru') OR (status IN (" . $statusTime_KZG . ") AND country = 'am') OR ( country = 'ae')  OR ( country = 'uz') OR (status IN (" . $statusTime_KZG . ") AND country = 'kzg'))";
    }

    if (!in_array($_SESSION['Logged_StaffId'], $ukr_ar)) {
        $where .= " AND `date` < NOW() - INTERVAL 5 SECOND ";
    }

    if (in_array($_SESSION['Logged_StaffId'], array(63088983, 72181767, 65132823, 36946627))) {
        $where .= " AND `offer` IN ('Eco_Slim', 'virgin_star') AND staff_id = '66629642' ";
    }

    if (in_array($_SESSION['Logged_StaffId'], array(22299672))) {
        $where .= " AND `staff_id` = 22299672 ";
    }

    $query = 'SELECT    ' . ($GLOBAL_CALCULATE_TOTAL_OBZVON ? 'SQL_CALC_FOUND_ROWS' : '') . "
                        *
            FROM `staff_order`
            WHERE `country` IN (" . $_SESSION['country'] . ") $all_in_condition AND $where $str_add";

    $queryTotal = "SELECT COUNT(id) AS obzvon_total FROM staff_order WHERE country IN (" . $_SESSION['country'] . ") $all_in_condition AND $where $str_add";
    $queryTotal = "SELECT FOUND_ROWS() AS obzvon_total ";

   if ($sort != "") {
        $query .= " ORDER BY `" . $sort . "` " . $dir;
    } else {
    $query .= " ORDER BY `date` DESC ";
    }

    // $query .= " LIMIT $start, $count";

    ApiLogger::addLogVarExport('START');
    ApiLogger::addLogVarExport($query);
    $ordersArray = DB::query($query);

    //echo $query;
    ApiLogger::addLogVarExport("FIRST QUERY SUCCESS");
    $loadAvg = sys_getloadavg();
    $total = $GLOBAL_CALCULATE_TOTAL_OBZVON && $loadAvg[0] < num_cpus() - 8 ? DB::queryFirstField($queryTotal) : '100000';
    ApiLogger::addLogVarExport("TOTAL QUERY SUCCESS => $queryTotal  \nRESULT total => $total");

    foreach ($ordersArray as &$orderItem) {
        $orderItem['phone_orig'] = preg_replace('/\D/', '', $orderItem['phone']);
        if ($orderItem['country'] == 'kz') {
            $orderItem['phone'] = hide_phone($orderItem['phone']);
            $orderItem['phone_sms'] = hide_phone($orderItem['phone_sms']);
        } elseif ($orderItem['country'] == 'kzg') {
            $orderItem['phone'] = '9' . hide_phone(substr($orderItem['phone'], 1));
        }
        $orderItem['rings'] = '<span id="call_' . $orderItem['id'] . '"><a href="#" onclick="showRingsGrid(\'' . $orderItem['id'] . '\',\'' . $orderItem['country'] . '\'); return false;">Показать</a></span>';
    }

    $ret = array(
        'total' => $total,
        'sql' => $query,
        'executing' => (microtime(true) - $time_1), 'data' => $ordersArray
    );

    print json_encode($ret);
}
