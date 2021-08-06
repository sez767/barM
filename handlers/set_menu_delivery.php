<?php

require_once dirname(__FILE__) . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8', true);

ApiLogger::addLogJson('START -------------');
ApiLogger::addLogVarExport($_REQUEST);

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Permission denied"
    )));
}

$actionHistoryObj = new ActionHistoryObj();

$cloneRassrochkaId = 0;
$common_edited_fields = '';
$add_price = '';


$deliv_sms = $configs["delivery_sms"];
$call_count = '';
if ($_FILES['callrecord']['type'] == 'audio/x-m4a' && $_FILES['callrecord']['size'] < 3000000) {

    $uploaddir = '/var/www/kgzsound/';
    $dated = date('YmdHi');
    $uploadfile = $uploaddir . $_GET['id'] . '_' . $dated . ".m4a";

    if (move_uploaded_file($_FILES['callrecord']['tmp_name'], $uploadfile)) {
        $query = mysql_query("INSERT INTO call_staff_order (order_id, callid, sip_id, source, system) VALUES
		('" . $_GET['id'] . "', '" . $_GET['id'] . '_' . $dated . ".m4a', '" . $_SESSION['Sip'] . "', 'HandKZG','0') ");
        $call_count = ' call_count = call_count + 1, ';
    }
}
$result = '{"success":false}';

ApiLogger::addLogJson('START');
ApiLogger::addLogJson('');
ApiLogger::addLogJson('REQUEST');
ApiLogger::addLogJson($_REQUEST);
ApiLogger::addLogJson('');

if ((int) $_GET['id'] && (int) $_GET['id'] < 10000000) {
    $objCh = DB::queryOneRow('SELECT * FROM `staff_order` WHERE id = %i', $_GET['id']);

    $save_date = '';
    if (substr($objCh['phone'], 0, 4) != substr($_POST['phone'], 0, 4)) {
        ApiLogger::addLogVarExport(substr($objCh['phone'], 0, 4));
//        echo $result;
//        die;
    }
    if (($objCh['status'] == 'Подтвержден' or $objCh['status'] == 'Предварительно подтвержден') && $objCh['country'] <> 'ru' && isset($_POST['ext_id']) && (int) @$_POST['ext_id']) {
        $dostavka = " send_status = 'Отправлен', ";
        $dostavka .= " status_kz = '" . mysql_real_escape_string(@$_POST['status_kz']) . "', ";
    } else if (($objCh['status'] == 'Подтвержден') && isset($_POST['ext_id']) && strlen(@$_POST['ext_id']) > 2) {
        $dostavka = " send_status = 'Отправлен', ";
        $dostavka .= " status_kz = 'Свежий', ";
    } else {
        $dostavka = '';
    }
    if (strlen(@$_POST['status_cur']) > 1) {
        $st_cur = " status_cur = '" . mysql_real_escape_string($_POST['status_cur']) . "', ";
    } else {
        $st_cur = '';
    }
    if (isset($_POST['ext_id'])) { //var_dump($_POST);
        if ($_POST['country'] == 'ru' or $_POST['country'] == 'RU') {// die;
            $addr = " addr = '" . mysql_real_escape_string($_POST['city'] . ' ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
        } else {
            $addr = " addr = '" . mysql_real_escape_string($_POST['addr'] . ' ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
        }
        $save_edit = " last_edit = '" . $_SESSION['Logged_StaffId'] . "', ";
        $save_date = " fill_date = NOW(), ";
        $_POST['total_price'] = $_POST['price'];
    } else {


        $save_edit = " last_edit_kz = '" . $_SESSION['Logged_StaffId'] . "', ";
        if ($_POST['status_kz'] != $objCh['status_kz'] && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок')) && $objCh['country'] == 'kz') {

            $save_date = " fill_date_log = NOW(), ";
            //var_dump($objCh['kz_delivery'],$deliv_sms);
            if (in_array($objCh['kz_delivery'], array_keys($deliv_sms))) {
                //var_dump($objCh['kz_delivery'],$deliv_sms);
                sendKcellSMS($objCh['phone'], "Заказ " . $objCh['id'] . " на сумму " . $objCh['total_price'] . " на доставке. По всем вопросам обращаться " . $deliv_sms[$objCh['kz_delivery']], $objCh['id']);
                sendKZSMS($objCh['phone'], "Ваш заказ принят на доставку. Номер вашего менеджера " . $deliv_sms[$objCh['kz_delivery']]);
            }
        }
        ApiLogger::addLogVarExport('--  ELSE  --');
        ApiLogger::addLogVarExport('$objCh:' + $objCh['id']);
        ApiLogger::addLogVarExport($objCh);
        ApiLogger::addLogVarExport($_POST);

        if (
                ($objCh['country'] == 'ru' or $objCh['country'] == 'RU') &&
                ($objCh['send_status'] == 'Отправлен' || @$_POST['send_status'] == 'Отправлен') &&
                $_POST['status_kz'] == 'Упакован принят' &&
                $_POST['status_kz'] != $objCh['status_kz'] &&
                $objCh['kz_delivery'] == 'Почта'
        ) {
            $updateArr = array(
                'delivery_date' => DB::sqlEval('NOW()'),
                'status_kz' => 'Упакован принят',
                'log_data' => '',
            );
            if (ACTIVE_DELIVERY_ID == 1) {
                $updateArr['log_data'] = sendLogos($objCh);
                if ($updateArr['log_data'] != '{"status":"OK"}') {
                    $updateArr['status_kz'] = 'На контроль';
                }
            } elseif (ACTIVE_DELIVERY_ID == 2) {
                $apiBetaPro = new ApiBetaPro();
                $updateArr['log_data'] = $apiBetaPro->sendBetaPro($objCh, false);
                if (!empty($updateArr['log_data']['error'])) {
                    $updateArr['status_kz'] = 'На контроль';
                    $updateArr['logos_desc'] = $updateArr['log_data']['error']['msg'];
                    $updateArr['log_data'] = $updateArr['log_data']['error'];
                }
                $updateArr['log_data'] = json_encode($updateArr['log_data']);
            }
            //var_dump($lsend); die;
            $delivResp = json_decode($updateArr['log_data'], true);
            $delivResp['deliv_id'] = ACTIVE_DELIVERY_ID;
            $updateArr['log_data'] = json_encode($delivResp);
            DB::update('staff_order', $updateArr, 'id = %i', $objCh['id']);
        }
        $addr = " addr = '" . mysql_real_escape_string($_POST['addr'] . ' ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
        if ($_POST['status_kz'] != $objCh['status_kz'] && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок'))) {
            $st_cur = " status_cur = 'УТОЧНИТЬ', ";
        }
        if ($_POST['send_status'] == $objCh['send_status'] && $_POST['status_kz'] == $objCh['status_kz'] && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок')) && $_POST['send_status'] == 'Отправлен' && substr($_POST['date_delivery'], 0, 10) != substr($objCh['date_delivery'], 0, 10) && strlen($_POST['date_delivery']) > 5) {
            $st_cur = " status_cur = 'УТОЧНИТЬ', ";
        }
    }

    // дополнительный товар
    if (isset($_POST['dop_tovar']) && count($_POST['dop_tovar']) > 0) {
        $counter = 0;
        $array = array();

        foreach ($_POST['dop_tovar']['offer'] AS $key => $val) {
            foreach ($_POST['dop_tovar'] AS $attribute => $values) {
                if ($attribute == 'offer') {
                    $attribute = 'dop_tovar';
                }

                if ($attribute == 'count') {
                    $attribute = 'dop_tovar_count';
                }

                if ($attribute == 'price') {
                    $attribute = 'dop_tovar_price';
                }

                $array[$attribute][$counter] = (isset($values[$key]) ? $values[$key] : "");
            }

            $counter++;
        }

        $dop_tovar = " dop_tovar = '" . mysql_real_escape_string(json_encode($array, JSON_UNESCAPED_UNICODE)) . "', ";
    } else {
        $dop_tovar = " dop_tovar = '[]', ";
    }

    if (isset($_POST['deliv_cost'])) {
        $deliv_cost = " deliv_cost = '" . (float) $_POST['deliv_cost'] . "', ";
    } else {
        $deliv_cost = '';
    }

    $status_ar = array('новая', 'Подтвержден', 'Отменён', 'Перезвонить', 'Недозвон', 'Брак', 'Уже получил заказ');

    if ($objCh['status_kz'] == 'Нет товара' && @$_POST['send_status'] == 'Отказ') {
        $_POST['send_status'] = $objCh['send_status'];
    }

    if (isset($_POST['pay_type'])) {
        $pay_type = "pay_type = '" . (int) ($_POST['pay_type']) . "',";
    } else {
        $pay_type = '';
    }
    if ($objCh['send_status'] != @$_POST['send_status'] && @$_POST['send_status'] == 'Оплачен') {
        $add_bablo = ' return_date = NOW(), ';
        if (!empty($pay_type) || !empty($objCh['pay_type'])) {
            $cloneRassrochkaId = $objCh['id'];
        }

        if ($objCh['staff_id'] == '53095975') {
            $ore = orderBonus($objCh['phone'], $_GET['id'], $objCh['price'], $objCh['package']);
        }
    } elseif ($objCh['send_status'] != @$_POST['send_status'] && @$_POST['send_status'] == 'Отправлен') {
        $add_bablo = ' delivery_date = NOW(), ';
    } else {
        $add_bablo = '';
    }
    if ($objCh['status_kz'] != @$_POST['status_kz'] && @$_POST['status_kz'] == 'Возврат денег') {
        $vozvrat_bablo = ' date_vozvrat = NOW(), ';
    } else {
        $vozvrat_bablo = '';
    }
    if ($objCh['status_kz'] != @$_POST['status_kz'] && @$_POST['status_kz'] == 'Груз вручен') {
        $vozvrat_bablo = ' date_vruchen = NOW(), ';
    }
    if ($_POST['status_kz'] == 'Скинут предоплату' && !empty($_POST['date_otl']) && !in_array($_POST['date_otl'], array('0000-00-00', '0000-00-00 00:00:00'))) {
        $common_edited_fields .= " date_otl = '" . mysql_real_escape_string($_POST['date_otl'] . ' ' . $_POST['time_otl']) . "', ";
    }
    ApiLogger::addLogVarExport('$common_edited_fields');
    ApiLogger::addLogVarExport($_POST['status_kz'] == 'Скинут предоплату');
    ApiLogger::addLogVarExport(isset($_POST['date_otl']));
    ApiLogger::addLogVarExport(!in_array($_POST['date_otl'], array('0000-00-00', '0000-00-00 00:00:00')));
    ApiLogger::addLogVarExport($common_edited_fields);
    ApiLogger::addLogVarExport('--');


    if (strlen(@$_POST['deferred_date']) > 8 && @$_POST['deferred_date'] != '0000-00-00 00:00:00') {
        $def_date = " deferred_date = '" . mysql_real_escape_string(@$_POST['deferred_date']) . "', ";
    } else {
        $def_date = '';
    }


    // Атрибуты товара
    $other_data = array();

    if (isset($_POST['offer_property']) && is_array($_POST['offer_property']) && count($_POST['offer_property']) > 0) {
        foreach ($_POST['offer_property'] AS $type => $property) {
            if (is_array($property)) {
                $property = array_slice($property, 0);
                if (isset($property[0])) {
                    $other_data[$type] = $property[0];
                }
            }
        }
    }

    if (strlen(@$_POST['city']) > 1) {
        $city = "city = '" . mysql_real_escape_string(@$_POST['city']) . "',";
    } else {
        $city = '';
    }
    if (!empty($_POST['date_delivery']) && strlen($_POST['date_delivery']) > 5) {
        $date_delivery = "date_delivery = '" . mysql_real_escape_string($_POST['date_delivery']) . "',";
    } else {
        $date_delivery = '';
    }
    if (strlen(@$_POST['region']) > 1) {
        $region = "region = '" . mysql_real_escape_string($_POST['region']) . "',";
    } else {
        $region = '';
    }
    if (strlen(@$_POST['building']) > 0) {
        $building = "building = '" . mysql_real_escape_string($_POST['building']) . "',";
    } else {
        $building = '';
    }
    if (strlen(@$_POST['flat']) > 0) {
        $flat = "flat = '" . mysql_real_escape_string($_POST['flat']) . "',";
    } else {
        $flat = '';
    }
    if (strlen(@$_POST['street']) > 1) {
        $street = "street = '" . mysql_real_escape_string($_POST['street']) . "',";
    } else {
        $street = '';
    }
    if ((int) @$_POST['oper_use']) {
        $oper_use = "oper_use = '" . mysql_real_escape_string($_POST['oper_use']) . "',";
    } else {
        $oper_use = '';
    }
    if (strlen(@$_POST['district']) > 1) {
        $district = "district = '" . mysql_real_escape_string($_POST['district']) . "',";
    } else {
        $district = '';
    }
    if (strlen(@$_POST['city_region']) > 5) {
        $city_region = "city_region = '" . mysql_real_escape_string($_POST['city_region']) . "',";
    } else {
        $city_region = '';
    }

    if (!empty($_REQUEST['kz_admin'])) {
        $common_edited_fields .= "kz_admin = '" . mysql_real_escape_string($_REQUEST['kz_admin']) . "',";
    }

    if (!empty($_REQUEST['kz_operator']) && $objCh['kz_operator'] != $_REQUEST['kz_operator']) {
        if ($objCh['send_status'] == 'Отправлен' && in_array($objCh['status_kz'], array('На доставку', 'Вручить подарок')) && !empty($objCh['kz_operator'])) {

        } else {
            $common_edited_fields .= "kz_operator = '" . mysql_real_escape_string($_REQUEST['kz_operator']) . "',";
        }
        ApiLogger::addLogVarExport($common_edited_fields);
    }

    if (isset($_POST['is_cold'])) {
        $is_cold = "is_cold = '" . (int) ($_POST['is_cold']) . "',";
    } else {
        $is_cold = '';
    }

    if (isset($_POST['pay_status'])) {
        $pay_status = "pay_status = '" . (int) ($_POST['pay_status']) . "',";
    } else {
        $pay_status = '';
    }

    if ($objCh['status_kz'] != $_POST['status_kz'] && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок')) && $objCh['status_kz'] != '') {
        $city_region = "city_region = '',";
    }

    if (!empty($_SESSION['incomeoper']) && !empty($_REQUEST['status_income']) && $objCh['status_income'] != $_REQUEST['status_income']) {
        $_REQUEST['status_income'] = (int) $_REQUEST['status_income'];
        // deliv_cost испольуется сейчас как "Оператор входящей линии"
        $common_edited_fields .= "status_income = {$_REQUEST['status_income']}, deliv_cost = {$_SESSION['Logged_StaffId']},";
    }

    if ((int) $_POST['total_price'] > 1) {
        $add_tprice = "total_price = '" . mysql_real_escape_string($_POST['total_price']) . "',";
    } elseif (isset($_POST['total_price']) && !(int) $_POST['total_price']) {
        $add_tprice = "total_price = '0',";
    } elseif ($objCh['total_price'] != $objCh['price'] && (int) $_POST['total_price']) {
        $add_tprice = "total_price = '" . mysql_real_escape_string($objCh['total_price']) . "',";
    } else {
        $add_tprice = "total_price = '" . mysql_real_escape_string($_POST['price']) . "',";
    }
    if (isset($_POST['post_price'])) {
        $add_tprice .= "post_price = '" . mysql_real_escape_string($_POST['post_price']) . "',";
    }
//    $add_tprice = "post_price = '" . mysql_real_escape_string($_POST['post_price']) . "',";


    $add_phone = '';
    if ($_POST['status_cur'] != $objCh['status_cur']) {
        $_POST['status_check'] = ' ';
        $_POST['control_status'] = ' ';
    }
    //var_dump($_POST);
    if ($objCh['status'] != 'Подтвержден' && ($_POST['status'] == 'Подтвержден' or $_POST['status'] == 'Перезвонить' or ( $_POST['status'] == 'Отменён' && $objCh['status'] == 'Предварительно подтвержден'))) {
        $st_add = " status = '" . mysql_real_escape_string($_POST['status']) . "', ";
    } else {
        $st_add = '';
    }
    if ($objCh['status'] == 'Предварительно подтвержден' && $_POST['send_status'] == 'Оплачен') {
        $_POST['send_status'] = $objCh['send_status']; // Диана чат 11.08.16
    }

    if (
            !empty($_POST['offer']) &&
            (in_array($objCh['staff_id'], array(57369831, 11113333)) || in_array($_SESSION['Logged_StaffId'], array(66629642, 61386417)))
    ) {
        $common_edited_fields .= " offer = '" . mysql_real_escape_string($_POST['offer']) . "', ";
    }

    if (
            !empty($_POST['price']) &&
            (in_array($objCh['staff_id'], array(57369831, 11113333)) || in_array($_SESSION['Logged_StaffId'], array(66629642, 61386417, 88189675, 12019085, 24511553)))
    ) {
        $add_price = " price = '" . mysql_real_escape_string($_POST['price']) . "',";
    }

    if (!empty($_POST['phone_sms']) && in_array($objCh['country'], array('kz', 'kzg')) && hide_phone($objCh['phone_sms'], true) != $_POST['phone_sms']) {
        $common_edited_fields .= "phone_sms = '{$_POST['phone_sms']}', ";
    }
    if (!empty($_POST['status_income']) && $_POST['status_income'] != $objCh['status_income']) {
        $common_edited_fields .= "status_income = '{$_POST['status_income']}', ";
    }
    if (!empty($_POST['post_part_price'])) {
        $common_edited_fields .= "post_part_price = '{$_POST['post_part_price']}', ";
    }
    if (!empty($_POST['courier_group']) && $_POST['courier_group'] != $objCh['courier_group']) {
        $common_edited_fields .= "courier_group = '{$_POST['courier_group']}', ";
    }
    if (!empty($_POST['check_number']) && $_POST['check_number'] != $objCh['check_number']) {
        $common_edited_fields .= "check_number = '{$_POST['check_number']}', ";
    }
    if (
            !empty($_POST['status_kz']) && $_POST['status_kz'] != $objCh['status_kz'] ||
            !empty($_POST['send_status']) && $_POST['send_status'] != $objCh['send_status'] ||
            !empty($_POST['status_cur']) && $_POST['status_cur'] != $objCh['status_cur'] ||
            !empty($_POST['kz_curier']) && $_POST['kz_curier'] != $objCh['kz_curier'] ||
            !empty($_POST['date_delivery']) && $_POST['date_delivery'] != $objCh['date_delivery']
    ) {
        $common_edited_fields .= "log_data = '" . CommonObject::getAdminId() . "', ";
    }

    if (empty($_POST['send_status'])) {
        $_POST['send_status'] = $objCh['send_status'];
    }

    $query = " UPDATE `staff_order` SET
		`description` = '" . mysql_real_escape_string(@$_POST['description']) . "',
		`deliv_desc` = '" . mysql_real_escape_string(@$_POST['deliv_desc']) . "',
		`kz_curier` = '" . (empty($_POST['kz_curier']) ? 0 : mysql_real_escape_string($_POST['kz_curier'])) . "',
		`fio` = '" . mysql_real_escape_string($_POST['fio']) . "',
		`index` = '" . mysql_real_escape_string(@$_POST['index']) . "',
		`control_admin` = '" . mysql_real_escape_string(@$_POST['control_admin']) . "',
		`control_status` = '" . mysql_real_escape_string(@$_POST['control_status']) . "',
		`package` = '" . (float) $_POST['package'] . "',
		`not_rus` = '" . (int) @$_POST['not_rus'] . "',
		`other_data` = '" . mysql_real_escape_string(@json_encode($other_data, JSON_UNESCAPED_UNICODE)) . "',
		`send_status` = '" . mysql_real_escape_string(@$_POST['send_status']) . "',
		`status_kz` = '" . mysql_real_escape_string(@$_POST['status_kz']) . "',
        " . ((isset($_POST['take_away_date']) && !empty($_POST['take_away_date'])) ? "`take_away_date` = '" . mysql_real_escape_string($_POST['take_away_date']) . "'," : "") . "
        `status_check` = '" . (isset($_POST['status_check']) && !empty($_POST['status_check']) ? mysql_real_escape_string($_POST['status_check']) : "") . "',
		`recall_date` = '" . (int) @$_POST['recall_date'] . "',
		$city
		$oper_use
		$date_delivery
		$st_add
		$add_price
		$is_cold
		$pay_type
		$pay_status
		$add_tprice
		$add_phone
		$city_region
		$region
		$building
		$flat
		$street
        $district
        $addr
        $def_date
        $st_cur
        $save_date
        $save_edit
        $add_bablo
        $vozvrat_bablo
        $dostavka
        $dop_tovar
        $deliv_cost
        $call_count
        $common_edited_fields
		kz_delivery = '" . (empty($_POST['kz_delivery']) ? $objCh['kz_delivery'] : mysql_real_escape_string($_POST['kz_delivery'])) . "',
		kz_code = '" . mysql_real_escape_string(@$_POST['kz_code']) . "'
	WHERE
		`id` = '" . (int) $_GET['id'] . "'
	LIMIT 1";


    ApiLogger::addLogJson('-------------');
    ApiLogger::addLogVarExport($_REQUEST);
    ApiLogger::addLogVarExport("!! common_edited_fiels => $common_edited_fields");
    ApiLogger::addLogVarExport($query);

    if (!in_array($_SESSION['Logged_StaffId'], array(11111111)) || true) {
        $rez = mysql_query($query);
    } else {
        die($query);
    }

    // DOBRIK
    if (
            $objCh['country'] == 'kz' &&
            $objCh['status'] == 'Подтвержден' &&
            $_POST['kz_delivery'] == 'Почта' &&
            $_POST['send_status'] == 'Отправлен' &&
            $_POST['status_kz'] == 'Свежий' &&
            $_POST['status_kz'] != $objCh['status_kz']
    ) {
        ApiLogger::addLogVarExport('$objChSMS:' + $objCh['id']);
        sendKcellSMS($objCh['phone'], '', $objCh['id'], true);
    }

    cloneRassrochka($cloneRassrochkaId);
//    ApiLogger::addLogVarExport('$objCh:');
//    ApiLogger::addLogVarExport($objCh);

    if ($rez) {

        foreach ($_POST as $pk => $pv) {
            if (isset($objCh[$pk])) {
                if ($objCh[$pk] != $pv) {
                    $actionHistoryObj->save('StaffOrderObj', (int) $_GET['id'], 'update', $pk, $objCh[$pk], $pv, 'set_menu_delivery');
                }
            }
        }

        //////////////////////////////////////////////////////////////////////////////
        // Start Storage
        if ($_POST['status'] == 'Подтвержден' || $objCh['status'] == 'Подтвержден') {
            $storage = new Storage($_GET['id'], $_POST['send_status'], $objCh['send_status'], $_POST['status_kz'], $objCh['status_kz'], (int) $objCh['package'] - (int) $_POST['package']);
        }

        if ($_POST['status'] == 'Перезвонить' && $objCh['country'] == 'kz') {
            //if((date('H')>19 or date('H')<8) && $_POST['staff_id'] == '53074289') goto ren;
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $pre_query = mysql_query("SELECT * FROM orders_pool WHERE order_id = '" . (int) $_GET['id'] . "' AND is_processed = 0 AND end_date > NOW() ");
            if (mysql_num_rows($pre_query) == 0) {
                $is_sets = mysql_query("INSERT INTO orders_pool (prio,start_date,end_date,phonenumber,order_id,queue_name,other_data)
				VALUES ('0',NOW() + INTERVAL " . (int) $_POST['recall_date'] . " HOUR, NOW() + INTERVAL " . ((int) $_POST['recall_date'] + 10) . " HOUR ,'8" . substr($_POST['phone_sms'], 1, strlen($_POST['phone_sms'])) . "','" . (int) $_GET['id'] . "','TorgKZ_P','" . mysql_real_escape_string(json_encode(array('staff_id' => $_POST['staff_id']))) . "')");
            }
        }

        if ($_POST['status'] == 'Перезвонить' && ($objCh['country'] == 'ru')) {
            //if((date('H')>19 or date('H')<8) && $_POST['staff_id'] == '53074289') goto ren;
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $pre_query = mysql_query("SELECT * FROM orders_pool WHERE order_id = '" . (int) $_GET['id'] . "' AND is_processed = 0 AND end_date > NOW() ");
            if (mysql_num_rows($pre_query) == 0) {
                $is_sets = mysql_query("INSERT INTO orders_pool (prio,start_date,end_date,phonenumber,order_id,queue_name,other_data)
				VALUES ('0',NOW() + INTERVAL " . ((int) $_POST['recall_date'] + 1) . " HOUR, NOW() + INTERVAL " . ((int) $_POST['recall_date'] + 3) . " HOUR ,'7" . substr($_POST['phone_sms'], 1, strlen($_POST['phone_sms'])) . "','" . (int) $_GET['id'] . "','TorgRU_P','" . mysql_real_escape_string(json_encode(array('staff_id' => $_POST['staff_id']))) . "')");
            }
        }

        if ($objCh['status'] == 'Подтвержден' && $objCh['country'] == 'kzg' && $_POST['send_status'] == 'Отправлен' && !in_array($objCh['status_kz'], array('На доставку', 'Вручить подарок')) && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок')) && $objCh['kz_delivery'] != 'Почта') {
//            $iskgz = sendSms('996' . substr($objCh['phone'], -9), "Заказ " . $objCh['id'] . " на сумму " . (int) $objCh['price'] . " на доставке. Инфо: +77711018139 ");
        }

        $poz_ar = array('Подтвержден', 'Предварительно подтвержден', 'Отменён', 'Брак', 'Уже получил заказ', 'Черный список');
        if (in_array($_POST['status'], $poz_ar)) {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1
			 WHERE order_id = '" . (int) $_GET['id'] . "' AND queue_name IN ('TorgRU_P','TorgKZ_P') ");
        }

        $pozs_ar = array('Хранение');
        if (strlen(@$_POST['status_kz']) > 5 && !in_array(@$_POST['status_kz'], $pozs_ar)) {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1
			 WHERE order_id = '" . (int) $_GET['id'] . "' AND queue_name = 'PostKZ_P' ");
        }

        $pozs_ar = array('Обработка', 'Груз в дороге', 'Отложенная доставка', 'Перезвонить', 'На доставку', 'Вручить подарок');
        if (@$_POST['queue'] == 'LogistKZ_P' && strlen(@$_POST['status_kz']) > 1 && !in_array(@$_POST['status_kz'], $pozs_ar)) {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1
			 WHERE order_id = '" . (int) $_GET['id'] . "' AND queue_name = 'LogistKZ_P' ");
        }

        if (@$_POST['queue'] == 'KgzDelivery_P' && strlen(@$_POST['status_kz']) > 1 && @$_POST['status_kz'] != 'Обработка') {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1
			 WHERE order_id = '" . (int) $_GET['id'] . "' AND queue_name = 'KgzDelivery_P' ");
        }
        $result = '{"success":true}';
    }
} elseif ((int) $_GET['id'] === 0) {
    if ($_POST['status'] == 'Подтвержден') {
        $dostavka = " send_status = 'Отправлен', ";
        $dostavka .= " status_kz = 'Свежий', ";
    } else {
        $dostavka = '';
    }

    $addr = " addr = '" . mysql_real_escape_string($_POST['addr'] . ' ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
    $save_edit = " last_edit = '" . $_SESSION['Logged_StaffId'] . "', ";
    $save_date = " fill_date = NOW(), ";

    if (isset($_POST['dop_tovar'])) {
        $dop_tovar = " dop_tovar = '" . json_encode(array("dop_tovar" => @$_POST['dop_tovar'], "dop_tovar_price" => @$_POST['tmpprice'], "dop_tovar_count" => @$_POST['dop_tovar_count'])) . "', ";
    } else {
        $dop_tovar = '';
    }
    if (isset($_POST['deliv_cost'])) {
        $deliv_cost = " deliv_cost = '" . (float) @$_POST['deliv_cost'] . "', ";
    } else {
        $deliv_cost = '';
    }
    if ((int) @$_POST['del']) {
        $dop_tovar = " dop_tovar = '', ";
    }

    $query = "INSERT INTO staff_order SET
                `price` = '" . mysql_real_escape_string($_POST['price']) . "',
                `total_price` = '" . mysql_real_escape_string($_POST['price']) . "',
                `phone` = '" . mysql_real_escape_string($_POST['phone']) . "',
                `description` = '" . mysql_real_escape_string(@$_POST['description']) . "',
                `fio` = '" . mysql_real_escape_string($_POST['fio']) . "',
                `district` = '" . mysql_real_escape_string($_POST['district']) . "',
                `index` = '" . mysql_real_escape_string(@$_POST['index']) . "',
                `building` = '" . mysql_real_escape_string(@$_POST['building']) . "',
                `flat` = '" . mysql_real_escape_string(@$_POST['flat']) . "',
                `street` = '" . mysql_real_escape_string(@$_POST['street']) . "',
                `package` = '" . (float) $_POST['package'] . "',
                `staff_id` = '11111111',
                `country` = '" . ((strlen(@$_GET['country']) >= 2) ? @$_GET['country'] : 'kz') . "',
                `offer` = '" . mysql_real_escape_string(@$_POST['offer']) . "',
                `city` = '" . mysql_real_escape_string(@$_POST['city']) . "',
                `recall_date` = '" . (int) @$_POST['recall_date'] . "',
                $addr
                $save_date
                $save_edit
                $add_bablo
                $dostavka
                $dop_tovar
                $deliv_cost
                `kz_delivery` = '" . mysql_real_escape_string($_POST['kz_delivery']) . "',
                `status` = '" . mysql_real_escape_string($_POST['status']) . "'";
    //echo $query;
    $rez = mysql_query($query);
    $newId = mysql_insert_id();
    if ($rez) {
        foreach ($_POST as $pk => $pv) {
            $actionHistoryObj->save('StaffOrderObj', $newId, 'insert', $pk, '', $pv, 'set_Menu5');
        }
        $result = '{"success":true}';
    }
    //error_log($query);
} elseif ((int) $_GET['id'] == 10000000) {
    $queryCh = " SELECT * FROM staff_order WHERE id = " . (int) $_GET['oid'];
    $rsCh = mysql_query($queryCh);
    $objCh = mysql_fetch_assoc($rsCh);
    $query = "INSERT INTO staff_order SET
                `price` = '" . mysql_real_escape_string($objCh['price']) . "',
                `total_price` = '" . mysql_real_escape_string($objCh['price']) . "',
                `phone` = '" . mysql_real_escape_string($objCh['phone']) . "',
                `description` = '',
                `fio` = '" . mysql_real_escape_string($objCh['fio']) . "',
                `district` = '" . mysql_real_escape_string($objCh['district']) . "',
                `index` = '" . mysql_real_escape_string(@$objCh['index']) . "',
                `building` = '" . mysql_real_escape_string(@$objCh['building']) . "',
                `flat` = '" . mysql_real_escape_string(@$objCh['flat']) . "',
                `street` = '" . mysql_real_escape_string(@$objCh['street']) . "',
                `package` = '1',
                `staff_id` = '11111111',
                `country` = 'kz',
                `last_edit` = '" . $_SESSION['Logged_StaffId'] . "',
                `offer` = '" . mysql_real_escape_string(@$objCh['offer']) . "',
                `city` = '" . mysql_real_escape_string(@$objCh['city']) . "',
                `recall_date` = '" . (int) @$_POST['recall_date'] . "',
                `addr` = '" . mysql_real_escape_string(@$objCh['addr']) . "',
				`kz_delivery` = '" . mysql_real_escape_string(@$objCh['kz_delivery']) . "',
                `status` = 'новая'";
    //echo $query; die;
    $rez = mysql_query($query);
    //var_dump($rez); die;
    $laid = mysql_insert_id();
    if ($rez) {
        /* foreach ($_POST as $pk => $pv) {
          $actionHistoryObj->save('StaffOrderObj', $laid, 'insert', $pk, '', $pv, 'set_Menu');
          } */
        $result = '{"success":true,"id":' . $laid . '}';
    }
    echo $result;
    die;
}
echo $result;
