<?php

/**
 * Сбор данных о заказах с доставкой на "сегодня" и отправка на E-Mail курьерам
 * Заказы с доставкой на сегодня
 * Упаковочный лист
 * Конверт
 */
# error_reporting(E_ALL);
# ini_set("display_errors", 1);

require_once dirname(__FILE__) . '/../lib/db.php';

$_SESSION['api_log_enable'] = true;

if (!isset($_SESSION['Logged_StaffId'])) {
    header('Location: login.html');
    die;
}

header('Content-Type: text/html; charset=utf-8');
$ids = array();

if (!empty($_REQUEST['ids_data'])) {
    $ids = json_decode($_REQUEST['ids_data'], true);
}
foreach ($ids as $key => $id) {
    if (strlen($id) == 0 || $id <= 0) {
        unset($ids[$key]);
    }
}
if (count($ids) == 0) {
    die(json_encode(array(
        'success' => false,
        'data' => array('Empty set of IDs')
    )));
}

$result = array();

$staffOrderObj = new StaffOrderObj();
$sql = "SELECT id FROM `{$staffOrderObj->cGetTableName()}` WHERE id IN %li AND logos_desc LIKE 'ketkz_id:%' AND logos_desc IS NOT NULL";
if (($sendedIds = DB::queryFirstColumn($sql, $ids))) {
    foreach ($sendedIds as $sendedId) {
        $result['data'][] = "$sendedId - было отправлено ранее";
    }
}

//
// Start baribarda selection
//
$sql = "SELECT *, 32 AS kz_delivery FROM `{$staffOrderObj->cGetTableName()}` WHERE id IN %li AND (logos_desc NOT LIKE 'ketkz_id:%' OR logos_desc IS NULL)";
//die($sql);
$dataArr = DB::query($sql, $ids);

if (count($dataArr) == 0) {
    $result['data'][] = 'Не найдено ни одного заказа на отправку';
} else {

    foreach ($dataArr as $orderItem) {
        $se = sendToKet($orderItem, array('uid' => 85935432, 'secret' => 'zPGMr1Sy'));

        if (!empty($se['result']['id'])) {
            $updateArr = array(
                'logos_desc' => 'ketkz_id:' . $se['result']['id'],
                'is_send' => 1
            );
            DB::update($staffOrderObj->cGetTableName(), $updateArr, 'id = %i', $orderItem['id']);
            $result['data'][] = "{$orderItem['id']} - отправлено";
        } else {
            $result['data'][] = "Ошибка отправки заказа {$orderItem['id']}";
        }
    }
}

//////////////////////////////////////////////////////////////////////
//
// Показать результат
print json_encode($result);

function sendToKet($orderItem, $conf) {
    $preSale = json_decode($orderItem['other_data'], true);
    $saleOption = '';
    if (json_last_error() == JSON_ERROR_NONE) {
        if (!empty($preSale['vendor'])) {
            $saleOption .= $preSale['vendor'] . ',';
        }
        if (!empty($preSale['size'])) {
            $saleOption .= $preSale['size'] . ',';
        }
        if (!empty($preSale['type'])) {
            $saleOption .= $preSale['type'] . ',';
        }
    }
    $dopTovar = json_decode($orderItem['dop_tovar'], true);
    $offerTmp = trim($orderItem['offer']) . ' - ' . $orderItem['package'] . ', ';

    //var_dump($dop_tovar); die;
    if (json_last_error() == JSON_ERROR_NONE && !empty($dopTovar['dop_tovar'])) {
        foreach ($dopTovar['dop_tovar'] AS $key => $value) {
            $dopTovarTmp = array();
            foreach ($dopTovar AS $attr => $a) {
                if ($attr == 'dop_tovar') {
                    $dopTovarTmp['offer'] = $dopTovar['dop_tovar'][$key];
                } elseif ($attr == 'dop_tovar_count') {
                    $dopTovarTmp['package'] = $dopTovar['dop_tovar_count'][$key];
                } elseif ($attr == 'dop_tovar_price') {
                    $dopTovarTmp['price'] = $dopTovar['dop_tovar_price'][$key];
                } elseif ($attr == 'size') {
                    $saleOption .= $dopTovar['size'][$key] . ',';
                } elseif ($attr == 'vendor') {
                    $saleOption .= $dopTovar['vendor'][$key] . ',';
                } elseif ($attr == 'type') {
                    $saleOption .= $dopTovar['type'][$key] . ',';
                } else {
                    $dopTovarTmp[$attr] = $dopTovar[$attr][$key];
                }
            }
            $offerTmp .= trim($dopTovarTmp['offer']) . ' - ' . $dopTovarTmp['package'] . ', ';
        }
        //var_dump($offer_tmp); die;
    }
    if (strlen($offerTmp) > 2) {
        $offerTmp = substr($offerTmp, 0, strlen($offerTmp) - 2);
    }
    if (strlen($saleOption) > 2) {
        $saleOption = substr($saleOption, 0, strlen($saleOption) - 1);
    }

    $data = array(
        'order_id' => $orderItem['id'], // id заявки внешний для синхронизации
        'phone' => $orderItem['phone'], // номер телефона заказчика товара *
        'name' => $orderItem['fio'], // ФИО заказчика
        'price' => $orderItem['total_price'], // стоимость товара
        'total_price' => $orderItem['total_price'],
        'description' => $orderItem['description'],
        'deliv_desc' => $orderItem['deliv_desc'],
        'kz_code' => $orderItem['kz_code'],
        'region' => $orderItem['region'],
        'city' => $orderItem['city'],
        'district' => $orderItem['district'],
        'street' => $orderItem['street'],
        'building' => $orderItem['building'],
        'flat' => $orderItem['flat'],
        'country' => mb_strtolower($orderItem['country']), // Гео локация *
        'addr' => trim($orderItem['addr']), // адрес
        'offer' => $offerTmp,
        'sale_option' => $saleOption,
        'index' => $orderItem['index'], // наименование товара *
        'kz_delivery' => $orderItem['kz_delivery'],
        'secret' => $conf['secret']  // секретный ключ АПИ *
    );

    $uid = $conf['uid'];

    ApiLogger::addLogVarExport($data);

    $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
    $hash_str = strlen($dataJson) . md5($uid);
    $hash = hash('sha256', $hash_str);

    $url = 'http://ketkz.com/api/send_order.php?uid=' . $uid . '&hash=' . $hash;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $dataJson));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Pragma: no-cache'));

    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);

    ApiLogger::addLogVarExport($result);

    return json_decode($result['EXE'], true);
}
