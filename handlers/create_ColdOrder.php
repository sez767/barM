<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
include_once (dirname(__FILE__) . "/../lib/db.php");

//die;ж

$resp = array();
if (($id = (int) $_REQUEST['id']) && ($insertData = DB::queryFirstRow('SELECT phone, country, fio, `index`, district, status, city, city_region, addr, street, building, flat FROM staff_order WHERE id = %i', $id))) {
//    ФИО, Индекс,Район, Город, Район Города, Адрес, Улица, Дом, Квартира
    $insertData['staff_id'] = 22222222;
    $insertData['ext_id'] = "$id" . '_' . time();
    $insertData['last_edit'] = $_SESSION['Logged_StaffId'];

    DB::insert('staff_order', $insertData);
    if (($resp['newId'] = DB::insertId())) {
        $resp['data'] = DB::queryOneRow('SELECT * FROM staff_order WHERE id = %i', $resp['newId']);
    }

    DB::update('staff_order', array('is_cold' => 0, 'is_cold_new_id' => $resp['newId']), 'id = %i', $id);
    // is_cold_out_date устанавливается на триггере базы
    // DB::update('staff_order', array('is_cold' => 0, 'is_cold_new_id' => $resp['newId'], 'is_cold_out_date', DB::sqlEval('NOW()')), 'id = %i', $id);
}
echo json_encode($resp);




//
//SELECT
// c.id, op.id as ext_id, op.is_cold_new_id, c.staff_id, c.staff_id
//FROM
// staff_order op
// JOIN
// staff_order c ON c.id = op.is_cold_new_id
//#and c.staff_id is null
//#and c.staff_id = 22222222
//WHERE
// op.is_cold_new_id;
//
//

