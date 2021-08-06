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

$redis = RedisManager::getInstance()->getRedis();

ApiLogger::addLogJson('START');
ApiLogger::addLogJson('');
ApiLogger::addLogJson('GET-DATA');
ApiLogger::addLogJson($_GET);
ApiLogger::addLogJson('POST-DATA');
ApiLogger::addLogJson($_POST);
ApiLogger::addLogJson('');

$deliv_sms = $configs["delivery_sms"];
$call_count = '';
if ($_FILES['callrecord']['type'] == 'audio/x-m4a' && $_FILES['callrecord']['size'] < 3000000) {

    $uploaddir = '/var/www/kgzsound/';
    $dated = date('YmdHi');
    $uploadfile = $uploaddir . $_GET['id'] . '_' . $dated . ".m4a";

    if (move_uploaded_file($_FILES['callrecord']['tmp_name'], $uploadfile)) {
        $query = mysql_query("INSERT INTO call_staff_order (order_id,callid,sip_id,source,system) VALUES
		('" . $_GET['id'] . "', '" . $_GET['id'] . '_' . $dated . ".m4a', '" . $_SESSION['Sip'] . "', 'HandKZG','0') ");
        $call_count = ' call_count = call_count+1, ';
    } else {

    }
}

$result = array("success" => false);
$group_add = '';
$common_edited_fiels = '';

if (!empty($_POST['date_delivery'])) {
    $common_edited_fiels .= "`date_delivery` = '" . mysql_real_escape_string($_POST['date_delivery']) . "',";
}

if (!empty($_GET['id'])) {

    $objCh = DB::queryOneRow('SELECT * FROM `staff_order` WHERE id = %i', $_GET['id']);
    ApiLogger::addLogVarExport('DOBRIK $objCh:');
    ApiLogger::addLogVarExport($objCh);

    $save_date = '';
    // Проверка бонусов
    if (
            $objCh['country'] == 'kz' && $_POST['status'] == 'Подтвержден' && $objCh['web_id'] == '287' && $_POST['status'] != $objCh['status'] &&
            (int) $_POST['is_bonus'] && $_POST['price_nbonus'] < $_POST['price']
    ) {
        removeBonus($objCh['phone'], (int) $_GET['id'], $_POST['price'] - $_POST['price_nbonus']);
        $_POST['price'] = $_POST['price_nbonus'];
    }
    if ($objCh['country'] <> 'ru' and $_POST['kz_delivery'] != 'Почта') {
        if (($_POST['status'] == 'Подтвержден' || $_POST['status'] == 'Предварительно подтвержден')) {
            $storage_send_status = "Отправлен";
            $dostavka = " send_status = 'Отправлен', ";
            $_POST['status_cur'] = 'УТОЧНИТЬ';
            $dostavka .= " status_kz = '" . (isset($_POST['status_kz']) ? mysql_real_escape_string($_POST['status_kz']) : "") . "', ";
        } else {
            $dostavka = '';
            $storage_send_status = '';
        }
    } else {
        if (($_POST['status'] == 'Подтвержден')) {
            $dostavka = " send_status = 'Отправлен', ";
            $storage_send_status = "Отправлен";

            if ($objCh['country'] == 'ru') {
                $dostavka .= " status_kz = 'Свежий', ";
                $storage_send_status = "Свежий";
            } elseif ($_POST['kz_delivery'] == 'Почта') {
                $dostavka .= " status_kz = 'Свежий', ";
                $storage_send_status = "Свежий";
            } elseif ($objCh['country'] == 'ae') {
                $dostavka .= " status_kz = 'Свежий', ";
                $storage_send_status = "Свежий";
            } else {
                $dostavka .= " status_kz = '" . $_POST['status_kz'] . "', ";
                $storage_send_status = $_POST['status_kz'];
            }
        } else {
            $dostavka = '';
            $storage_send_status = '';
        }
    }

    $st_cur = " status_cur = '" . mysql_real_escape_string(@$_POST['status_cur']) . "', ";
    if ((int) @$_GET['fixed'] > 100000) {
        $st_cur = " fixed = '" . (int) @$_GET['fixed'] . "', ";
    } else {
        $fixed = '';
    }
    if (strtotime($_POST['recall_dates']) > strtotime(date('Y-m-d H:i'))) {
        //$add_dates = " recall_dates = NOW() + INTERVAL ".(int)@$_POST['recall_date']." HOUR , ";
        $add_dates = " recall_dates = '" . date('Y-m-d H:i:s', strtotime($_POST['recall_dates'])) . "' , ";
    }

//    if (!empty($_POST['card_number'])) {
//        $common_edited_fiels .= " card_number = '" . mysql_real_escape_string($_POST['card_number']) . "', ";
//    }

    if (isset($_POST['ext_id'])) {
        if (@$_POST['country'] == 'ru' || @ $_POST['country'] == 'RU') {// die;
            $addr = " addr = '" . mysql_real_escape_string($_POST['city'] . ' ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
        } else {
            $addr = " addr = '" . mysql_real_escape_string($_POST['addr'] . '  ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
        }

        if (!empty($_POST['offer']) && $objCh['status'] != 'Подтвержден') {
            $common_edited_fiels .= " offer = '" . mysql_real_escape_string($_POST['offer']) . "', ";
        }

        $common_edited_fiels .= " sex =  '" . (int) $_POST['sex'] . "', ";
        $common_edited_fiels .= " age =  '" . (int) $_POST['age'] . "', ";

        if (!empty($_POST['birthday'])) {
            $common_edited_fiels .= " birthday = '" . $_POST['birthday'] . "', ";
        }
        if (isset($_POST['pay_type'])) {
            $common_edited_fiels .= " pay_type = '" . (int) $_POST['pay_type'] . "', ";
        }
        if (isset($_POST['pay_status'])) {
            $common_edited_fiels .= " pay_status = '" . (int) $_POST['pay_status'] . "', ";
        }

        $common_edited_fiels .= " last_edit = '" . $_SESSION['Logged_StaffId'] . "', ";
        if ($objCh['status'] == 'новая' && $objCh['status'] != $_POST['status']) {
            $common_edited_fiels .= " last_new = '" . $_SESSION['Logged_StaffId'] . "', ";
        }
        $save_date = " fill_date = NOW(), ";
        $_POST['total_price'] = $_POST['price'];
    } else {
        if ($_POST['status_kz'] != $objCh['status_kz'] && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок'))) {
            $common_edited_fiels .= " last_edit_kz = '" . $_SESSION['Logged_StaffId'] . "', ";
            $save_date = " fill_date_log = NOW(), ";
        }
        if (
                $objCh['country'] == 'ru' &&
                $objCh['send_status'] == 'Отправлен' &&
                @$_POST['status_kz'] == 'Упакован принят' &&
                @$_POST['status_kz'] != $objCh['status_kz']
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
        if ($_POST['send_status'] == $objCh['send_status'] && $_POST['status_kz'] == $objCh['status_kz'] && in_array($_POST['status_kz'], array('На доставку', 'Вручить подарок')) && $_POST['send_status'] == 'Отправлен' && $_POST['date_delivery'] != $objCh['date_delivery']) {
            $st_cur = " status_cur = 'УТОЧНИТЬ', ";
        }
    }

    // дополнительный товар
    if (
            isset($_POST['dop_tovar']) &&
            count($_POST['dop_tovar']) > 0
    ) {
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
        $deliv_cost = " deliv_cost = '" . (float) @$_POST['deliv_cost'] . "', ";
    } else {
        $deliv_cost = '';
    }

    $status_ar = array('новая', 'Подтвержден', 'Отменён', 'Перезвонить', 'Недозвон', 'Брак', 'Уже получил заказ');

    if ($objCh['send_status'] != @$_POST['send_status'] && @$_POST['send_status'] == 'Оплачен') {
        $add_bablo = ' return_date = NOW(), ';
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

    if (!(int) $_SESSION['adminlogist'] && !(int) $_SESSION['adminlogistpost']) {
        //$edit_oper = " kz_operator = '" . mysql_real_escape_string(@$_POST['kz_operator']) . "', ";
	$edit_oper = " kz_operator = '" . (empty($_POST['kz_operator']) ? 0 : mysql_real_escape_string($_POST['kz_operator'])) . "', ";
    } else {
        $edit_oper = '';
    }

    if (strlen(@$_POST['deferred_date']) > 8 && @$_POST['deferred_date'] != '0000-00-00 00:00:00') {
        $def_date = " deferred_date = '" . mysql_real_escape_string(@$_POST['deferred_date']) . "', ";
    } else {
        $def_date = '';
    }
    if (strlen(@$_POST['datetime_otl']) > 8 && @$_POST['datetime_otl'] != '0000-00-00 00:00:00') {
        $common_edited_fiels .= " datetime_otl = '" . mysql_real_escape_string(@$_POST['datetime_otl']) . "', ";
    }

    // Атрибуты товара
    $other_data = array();

    if (
            isset($_POST['offer_property']) &&
            is_array($_POST['offer_property']) &&
            count($_POST['offer_property']) > 0
    ) {
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
    if (strlen(@$_POST['region']) > 1) {
        $region = "region = '" . mysql_real_escape_string(@$_POST['region']) . "',";
    } else {
        $region = '';
    }
    if (strlen(@$_POST['building']) > 0) {
        $building = "building = '" . mysql_real_escape_string(@$_POST['building']) . "',";
    } else {
        $building = '';
    }
    if (strlen(@$_POST['flat']) > 0) {
        $flat = "flat = '" . mysql_real_escape_string(@$_POST['flat']) . "',";
    } else {
        $flat = '';
    }
    if (strlen(@$_POST['street']) > 1) {
        $street = "street = '" . mysql_real_escape_string(@$_POST['street']) . "',";
    } else {
        $street = '';
    }
    if ((int) @$_POST['oper_use']) {
        $oper_use = "oper_use = '" . mysql_real_escape_string(@$_POST['oper_use']) . "',";
    } else {
        $oper_use = '';
    }
    if (strlen(@$_POST['district']) > 1) {
        $district = "district = '" . mysql_real_escape_string(@$_POST['district']) . "',";
    } else {
        $district = '';
    }

    if ($_SESSION['Logged_StaffId'] != '66629642' && $objCh['status'] == 'Подтвержден' && $objCh['country'] != 'kzg') {
        $add_price = '';
        $add_preprice = '';
        $add_postprice = '';
    } else {
        $add_price = "price = '" . mysql_real_escape_string($_POST['price']) . "',";
        $add_preprice = "pre_price = '" . mysql_real_escape_string($_POST['pre_price']) . "',";
        $add_postprice = "post_price = '" . mysql_real_escape_string($_POST['post_price']) . "',";
    }

    $add_tprice = "total_price = '" . mysql_real_escape_string($_POST['total_price']) . "',";

    if (strlen(@$_POST['phone_sms']) == 11 && strlen($objCh['phone_sms']) < 10) {
        $add_phone = "phone_sms = '" . mysql_real_escape_string(@$_POST['phone_sms']) . "',";
    } else {
        $add_phone = '';
    }
    if ($objCh['status'] != 'Подтвержден') {
        $add_status = "`status` = '" . mysql_real_escape_string($_POST['status']) . "',";
    } else {
        $add_status = '';
    }
    $quer = "UPDATE `staff_order` SET
		`description` = '" . mysql_real_escape_string(@$_POST['description']) . "',
		`deliv_desc` = '" . mysql_real_escape_string(@$_POST['deliv_desc']) . "',
		`problem` = '" . mysql_real_escape_string(@$_POST['problem']) . "',
		$edit_oper
`kz_admin` = '" . (empty($_POST['kz_admin']) ? 0 : mysql_real_escape_string($_POST['kz_admin'])) . "',
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
        `city_region` = '" . mysql_real_escape_string(@$_POST['city_region']) . "',
        " . ((isset($_POST['give_date']) && !empty($_POST['give_date'])) ? "`give_date` = '" . mysql_real_escape_string($_POST['give_date']) . "'," : "") . "
        " . ((isset($_POST['give_date']) && !empty($_POST['give_date'])) ? "`give_date` = '" . mysql_real_escape_string($_POST['give_date']) . "'," : "") . "
        `status_check` = '" . (isset($_POST['status_check']) && !empty($_POST['status_check']) ? mysql_real_escape_string($_POST['status_check']) : "") . "',
		`recall_date` = '" . (int) @$_POST['recall_date'] . "',
		$city
		$group_add
		$oper_use
		$add_price
		$add_preprice
		$add_postprice
		$add_tprice
		$add_phone
		$region
		$building
		$flat
		$street
		$district
		$addr
		$def_date
		$st_cur
		$save_date
		$add_bablo
		$vozvrat_bablo
		$dostavka
		$dop_tovar
		$deliv_cost
		$add_dates
		$call_count
		$fixed
		$add_status
		$common_edited_fiels
		`kz_delivery` = '" . mysql_real_escape_string($_POST['kz_delivery']) . "',
		`kz_code` = '" . mysql_real_escape_string(@$_POST['kz_code']) . "'
	WHERE
		`id` = '" . (int) $_GET['id'] . "'
	LIMIT 1";

    ApiLogger::addLogJson('-------------   UPDATE');
    ApiLogger::addLogVarExport('GET:');
    ApiLogger::addLogVarExport($_GET);
    ApiLogger::addLogVarExport('POST:');
    ApiLogger::addLogVarExport($_POST);
    ApiLogger::addLogVarExport("QUERY => $quer");

    if (true || !in_array($_SESSION['Logged_StaffId'], array(11111111))) {
        $rez = mysql_query($quer);
    }

    // DOBRIK
    if (
            true &&
            $objCh['country'] == 'kz' &&
            $_POST['status'] == 'Подтвержден' &&
            $_POST['kz_delivery'] == 'Почта' &&
            $_POST['status_kz'] != $objCh['status_kz']
    ) {
        ApiLogger::addLogVarExport('#extendedLogic# $objChSMS:' + $objCh['id']);
        sendKcellSMS($objCh['phone'], '', $objCh['id'], true);
    }
    if (
            true &&
            $objCh['country'] == 'kz' &&
            $_POST['status'] == 'Подтвержден' &&
            $_POST['status'] != $objCh['status']
    ) {
        ApiLogger::addLogVarExport('#approve_info_sms# $objChSMS:' + $objCh['id']);
        sendKcellSMS('', '#approve_info_sms#', $objCh['id']);
    }

    if ($rez) {
        $actionHistoryObj = new ActionHistoryObj();
        foreach ($_POST as $pk => $pv) {
            if (array_key_exists($pk, $objCh)) {
                if ($objCh[$pk] != $pv) {
                    ApiLogger::addLogVarExport("property = $pk, was = {$objCh[$pk]}, set = $pv");
                    $actionHistoryObj->save('StaffOrderObj', (int) $_GET['id'], 'update', $pk, $objCh[$pk], $pv, 'set_menu_obzvon');
                }
            }
        }

        //////////////////////////////////////////////////////////////////////////////
        // Start Storage
        if ($_POST['status'] == "Подтвержден" || $objCh['status'] == "Подтвержден") {
            $storage = new Storage($_GET['id'], $storage_send_status, $objCh['send_status'], $_POST['status_kz'], $objCh['status_kz'], 0);
        }

        if (strlen($objCh['kz_code']) > 5 && ($_POST['status_kz'] == 'Хранение' && $_POST['status_kz'] != $objCh['status_kz'])) {
            sendKZSms($objCh['phone'], "Уведомление! Посылка " . $objCh['kz_code'] . " прибыла в ваше почтовое отделение");
        }

        $poz_ar = array('Подтвержден', 'Предварительно подтвержден', 'Перезвонить', 'Отменён', 'Брак', 'Уже получил заказ', 'Черный список');
        if (in_array($_POST['status'], $poz_ar)) {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1
			 WHERE order_id = '" . (int) $_GET['id'] . "' ");
        }
        $pozs_ar = array('Хранение');
        if (strlen(@$_POST['status_kz']) > 5 && !in_array(@$_POST['status_kz'], $pozs_ar)) {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1 WHERE order_id = '" . (int) mysql_real_escape_string($_GET['id']) . "' AND queue_name = 'PostKZ_P'");
        }
        $pozs_ar = array('Обработка', 'Груз в дороге', 'Отложенная доставка');

        if ((isset($_POST['queue']) && $_POST['queue'] == 'LogistKZ_P') && strlen($_POST['status_kz']) > 1 && !in_array($_POST['status_kz'], $pozs_ar)) {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $is_sets = mysql_query("UPDATE orders_pool SET is_processed = 1
			 WHERE order_id = '" . (int) $_GET['id'] . "' AND queue_name = 'LogistKZ_P' ");
        }
        if ($_POST['status'] == 'Перезвонить' && $objCh['country'] == 'kzg') {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $pre_query = mysql_query("DELETE FROM orders_pool WHERE order_id = '" . (int) $_GET['id'] . "' AND is_processed = 0 AND end_date > NOW() ");
        }

        if ($_POST['status'] == 'Перезвонить' && $objCh['country'] == 'kz') {
            mysql_close();
            $ext_db_old = asterisk_base();
            $ext_db_slave = mysql_select_db('asterisk', $ext_link);
            $pre_query = mysql_query("DELETE FROM orders_pool WHERE order_id = '" . (int) $_GET['id'] . "' AND is_processed = 0 AND end_date > NOW() ");
        }

        $result = array(
            "success" => true,
            "msg" => "Данные по заявке №" . $_GET['id'] . " обновлены"
        );
    }
} elseif ((int) $_GET['id'] == 0) {

    if ($_POST['status'] == 'Подтвержден') {
        $dostavka = " send_status = 'Отправлен', ";
        $storage_send_status = "Отправлен";

        if ($_POST['country'] == 'ru') {
            $dostavka .= " status_kz = 'Свежий', ";
            $storage_send_status = "Свежий";
        } elseif ($_POST['kz_delivery'] == 'Почта' && $_REQUEST['country'] != 'ru') {
            $dostavka .= " status_kz = 'Свежий', ";
            $storage_send_status = "Свежий";
        } elseif ($_REQUEST['country'] == 'ae') {
            $dostavka .= " status_kz = 'Свежий', ";
            $storage_send_status = "Свежий";
        } else {
            $dostavka .= " status_kz = '" . $_POST['status_kz'] . "', ";
            $storage_send_status = $_POST['status_kz'];
        }
    } else {
        $dostavka = '';
        $storage_send_status = '';
    }

    $addr = " addr = '" . mysql_real_escape_string($_POST['addr'] . ' ' . @$_POST['street'] . ' ' . @$_POST['building'] . ' ' . @$_POST['flat']) . "', ";
    $common_edited_fiels .= " last_edit = '" . $_SESSION['Logged_StaffId'] . "', ";

    if (!empty($_POST['staff_id'])) {
        $common_edited_fiels .= " staff_id = '" . (int) $_POST['staff_id'] . "', ";
    } else {
        $common_edited_fiels .= " staff_id = 11111111, ";
    }
    if (!empty($_POST['web_id'])) {
        $common_edited_fiels .= " web_id = " . (int) $_POST['web_id'] . ", ";
    }
    if (!empty($_SESSION['offline_island'])) {
        $dostavka = "   send_status = 'Оплачен',
                        status_kz = 'Получен', ";
    }

    $save_date = " fill_date = NOW(), ";
    if (strtotime($_POST['recall_dates']) > strtotime(date('Y-m-d H:i'))) {
        //$add_dates = " recall_dates = NOW() + INTERVAL ".(int)@$_POST['recall_date']." HOUR , ";
        $add_dates = " recall_dates = '" . date('Y-m-d H:i:s', strtotime($_POST['recall_dates'])) . "' , ";
    }
    // дополнительный товар
    if (
            isset($_POST['dop_tovar']) &&
            count($_POST['dop_tovar']) > 0
    ) {
        $counter = 0;
        $array = array();

        foreach ($_POST['dop_tovar']['offer'] AS $key => $val) {
            foreach ($_POST['dop_tovar'] AS $attribute => $values) {
                if ($attribute == "offer") {
                    $attribute = "dop_tovar";
                }

                if ($attribute == "count") {
                    $attribute = "dop_tovar_count";
                }

                if ($attribute == "price") {
                    $attribute = "dop_tovar_price";
                }

                $array[$attribute][$counter] = (isset($values[$key]) ? $values[$key] : "");
            }

            $counter++;
        }

        $dop_tovar = " dop_tovar = '" . json_encode($array, JSON_UNESCAPED_UNICODE) . "', ";
    } else {
        $dop_tovar = " dop_tovar = '[]', ";
    }

    if (isset($_POST['deliv_cost'])) {
        $deliv_cost = " deliv_cost = '" . (float) @$_POST['deliv_cost'] . "', ";
    } else {
        $deliv_cost = '';
    }


    if (strlen(@$_POST['deferred_date']) > 8 && @$_POST['deferred_date'] != '0000-00-00 00:00:00') {
        $def_date = " deferred_date = '" . mysql_real_escape_string(@$_POST['deferred_date']) . "', ";
    } else {
        $def_date = '';
    }

    // Атрибуты товара
    $other_data = array();

    if (
            isset($_POST['offer_property']) &&
            is_array($_POST['offer_property']) &&
            count($_POST['offer_property']) > 0
    ) {
        foreach ($_POST['offer_property'] AS $type => $property) {
            if (is_array($property)) {
                $property = array_slice($property, 0);
                if (isset($property[0])) {
                    $other_data[$type] = $property[0];
                }
            }
        }
    }
    //var_dump($_GET);
    $country = (strlen(@$_GET['country']) >= 2) ? $_GET['country'] : 'kz';

    $quer = "INSERT INTO `staff_order` SET
            `price` = '" . mysql_real_escape_string($_POST['price']) . "',
            `total_price` = '" . mysql_real_escape_string($_POST['price']) . "',
            `phone` = '" . mysql_real_escape_string($_POST['phone']) . "',
            `description` = '" . mysql_real_escape_string(@$_POST['description']) . "',
            `deliv_desc` = '" . mysql_real_escape_string(@$_POST['deliv_desc']) . "',
            `problem` = '" . mysql_real_escape_string(@$_POST['problem']) . "',
            `fio` = '" . mysql_real_escape_string($_POST['fio']) . "',
            `district` = '" . mysql_real_escape_string($_POST['district']) . "',
            `index` = '" . mysql_real_escape_string(@$_POST['index']) . "',
            `building` = '" . mysql_real_escape_string(@$_POST['building']) . "',
            `flat` = '" . mysql_real_escape_string(@$_POST['flat']) . "',
            `street` = '" . mysql_real_escape_string(@$_POST['street']) . "',
            `package` = '" . (float) $_POST['package'] . "',
            `country` = '" . $country . "',
            `offer` = '" . mysql_real_escape_string(@$_POST['offer']) . "',
            `other_data` = '" . mysql_real_escape_string(@json_encode($other_data, JSON_UNESCAPED_UNICODE)) . "',
            `city` = '" . mysql_real_escape_string(@$_POST['city']) . "',
            `recall_date` = '" . (int) @$_POST['recall_date'] . "',
            $addr
            $group_add
            $save_date
            $add_dates
            $common_edited_fiels
            $add_bablo
            $dostavka
            $def_date
            $dop_tovar
            $deliv_cost
            `kz_delivery` = '" . mysql_real_escape_string($_POST['kz_delivery']) . "',
            `status` = '" . mysql_real_escape_string($_POST['status']) . "'
    ";

    $uuid = mb_substr(md5(mb_strtoupper($country) . mb_substr(preg_replace('/\D/', '', $_REQUEST['phone']), -9)), 0, 16);
    if (($clientGroup = DB::queryFirstField('SELECT client_group FROM staff_order WHERE client_group IS NOT NULL AND uuid = %s ORDER BY id DESC LIMIT 1', $uuid))) {
        $quer .= ", client_group = $clientGroup ";
    } elseif (($clientGroup = getClientGroupByStaffId($_SESSION['Logged_StaffId']))) {
//        $quer .= ", client_group = $clientGroup ";
//        Тут ошибка получается ", client_group = 1, 2";
    }

    ApiLogger::addLogJson('+++++++++++++++++   INSERT');
    ApiLogger::addLogVarExport('GET:');
    ApiLogger::addLogVarExport($_GET);
    ApiLogger::addLogVarExport('POST:');
    ApiLogger::addLogVarExport($_POST);
    ApiLogger::addLogVarExport('REQUEST:');
    ApiLogger::addLogVarExport($_REQUEST);
    ApiLogger::addLogVarExport($quer);
    
    if (!in_array($_SESSION['Logged_StaffId'], array(11111111)) || true) {
        $rez = mysql_query($quer);
    }

    ApiLogger::addLogVarDump('$rez');
    ApiLogger::addLogVarDump($rez);

    if ($rez) {
        ApiLogger::addLogVarDump('$rez');
        ApiLogger::addLogVarDump($rez);

        $actionHistoryObj = new ActionHistoryObj();
        $newId = mysql_insert_id();
        ApiLogger::addLogVarDump('$newId');
        ApiLogger::addLogVarDump($newId);

        $objCh = DB::queryOneRow('SELECT * FROM `staff_order` WHERE id = %i', $newId);

        if (
                true &&
                $objCh &&
                $objCh['country'] == 'kz' &&
                $objCh['status'] == 'Подтвержден' &&
                $objCh['kz_delivery'] == 'Почта' &&
                $objCh['send_status'] == 'Отправлен' &&
                $objCh['status_kz'] == 'Свежий'
        ) {
            ApiLogger::addLogVarExport('#extendedLogic# $objChSMS:' + $objCh['id']);
            sendKcellSMS($objCh['phone'], '', $objCh['id'], true);
        }

        if (
                true &&
                $objCh['country'] == 'kz' &&
                $objCh['status'] == 'Подтвержден'
        ) {
            ApiLogger::addLogVarExport('#approve_info_sms# $objChSMS:' + $objCh['id']);
            sendKcellSMS('', '#approve_info_sms#', $objCh['id']);
        }

        if ($objCh && $objCh['country'] == 'kz' && $objCh['web_id'] == 666666) {
            DB::query("UPDATE staff_order SET `Group` = %i WHERE id = %i", (($objCh['id'] % 2) + 1), $objCh['id']);
        }

        foreach ($_POST as $pk => $pv) {
            $actionHistoryObj->save('StaffOrderObj', $newId, 'insert', $pk, '', $pv);
        }
        //////////////////////////////////////////////////////////////////////////////
        // Start Storage
        if ($_POST['status'] == 'Подтвержден') {
            $storage = new Storage(mysql_insert_id(), $storage_send_status, '', $_POST['status_kz'], '');
        }

        $result = array('success' => true);
    }
}

echo json_encode($result);
