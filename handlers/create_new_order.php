<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
include_once (dirname(__FILE__) . "/../lib/db.php");

$resp = array('success' => false);

$copyFields = in_array($_REQUEST['staff_id'], array(11113333)) ? '*' : '`staff_id`, `phone`, `country`, `fio`, `index`, `district`, `status`, `city`, `city_region`, `addr`, `street`, `currency`, `building`, `flat`, `kz_delivery`';

if (empty($_REQUEST['id']) && !empty($_REQUEST['uuid']) && $_REQUEST['staff_id'] == 22222222) {
    if (($_REQUEST['id'] = DB::queryFirstField('SELECT id FROM staff_order WHERE uuid = %s ORDER BY id DESC', $_REQUEST['uuid']))) {
        $clientsObj = new ClientsObj();
        $clientsObj->cSetId($_REQUEST['uuid']);
        // {id: '5', value: 'Заказ оформлен'}
        $clientsObj->cSave(array('client_deal_status' => 5, 'updated_by' => $_SESSION['Logged_StaffId']));
    }
}

if (
        ($staffId = (int) $_REQUEST['staff_id'] ) &&
        ($id = $_REQUEST['id']) &&
        (($insertData = DB::queryFirstRow("SELECT $copyFields FROM staff_order WHERE id = %i", $id)))
) {

    if (!empty($clientsObj) && ($clientFio = $clientsObj->cGetLoadedValues('fio'))) {
        $insertData['fio'] = $clientFio;
    }

    $insertData['staff_id_orig'] = $insertData['staff_id'];
    $insertData['staff_id'] = $staffId;
    $insertData['web_id'] = $_SESSION['Logged_StaffId'];
    $insertData['ext_id'] = "{$id}_new";
    $insertData['fill_date'] = DB::sqlEval("NOW()");
    $insertData['last_edit'] = $_SESSION['Logged_StaffId'];

    if ($_REQUEST['staff_id'] == 11113333) {
        $insertData['status'] = 'Подтвержден';
        $insertData['send_status'] = 'Отправлен';
//        $insertData['return_date'] = DB::sqlEval("NOW()");
        $insertData['offer'] = '';
        $insertData['dop_tovar'] = '';
        $insertData['price'] = 0;
        $insertData['total_price'] = 0;
    } else {
        $insertData['status'] = 'новая';
    }

    if (empty($_REQUEST['id']) && !empty($_REQUEST['uuid']) && $_REQUEST['staff_id'] == 22222222) {
        // БРД, Модуль Клинеты , при новосозданной анкете из модуля, у некоторых заказов не присваивается "Статус отправки - отправлен"
        $insertData['send_status'] = 'Отправлен';
    }

    unset($insertData['id']);
    unset($insertData['date']);
    DB::insert('staff_order', $insertData);
    if (($newId = DB::insertId())) {
        $resp = array(
            'success' => true,
            'new_staff_id' => $insertData['staff_id'],
            'country' => $insertData['country'],
            'new_id' => $newId
        );

        $childOrder = DB::queryFirstRow('SELECT * FROM staff_order WHERE id = %i', $newId);

        $callParentSearchQs = " SELECT * FROM call_staff_order
                    WHERE order_id  = %i AND
                    `date` BETWEEN '{$childOrder['date']}' - INTERVAL 1 HOUR AND '{$childOrder['date']}'
                    ORDER BY cid DESC";

        if (($callParentData = DB::queryFirstRow($callParentSearchQs, $id))) {
            unset($callParentData['cid']);
            $callParentData['order_id'] = $newId;
            DB::insert('call_staff_order', $callParentData);
        }
    }
}

echo json_encode($resp);
