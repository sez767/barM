<?php

header('Content-Type: text/javascript; charset=utf-8');
require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$res = array('success' => false);

if ((int) $_REQUEST['id']) {

    $updateArr = array(
        'offer_name' => $_REQUEST['offer_name'],
        'offer_percent' => $_REQUEST['offer_percent'],
        'offer_desc' => $_REQUEST['offer_desc'],
        'offer_logname' => $_REQUEST['offer_logname'],
        'offer_longdesc' => $_REQUEST['offer_longdesc'],
        'offer_group' => $_REQUEST['offer_group'],
        'offer_price' => (int) $_REQUEST['offer_price'],
        'offer_clientprice' => (int) $_REQUEST['offer_clientprice'],
        'offer_payment' => (float) $_REQUEST['offer_payment'],
        'offers_active' => (int) $_REQUEST['offers_active'],
        'offer_accept' => (int) $_REQUEST['offer_accept'],
        'offer_acceptKgz' => (int) $_REQUEST['offer_acceptKgz'],
        'offer_acceptRu' => (int) $_REQUEST['offer_acceptRu'],
        'offer_show_in_cold_kz' => (int) $_REQUEST['offer_show_in_cold_kz'],
        'offer_show_in_cold_kgz' => (int) $_REQUEST['offer_show_in_cold_kgz'],
        'offer_show_in_cold_uz' => (int) $_REQUEST['offer_show_in_cold_uz']
    );

    if (DB::update('offers', $updateArr, 'offer_id = %i', $_REQUEST['id'])) {
        $res['success'] = true;
    }
} else {
    $insertArr = array(
        'offer_name' => $_REQUEST['offer_name'],
        'offer_percent' => $_REQUEST['offer_percent'],
        'offer_desc' => $_REQUEST['offer_desc'],
        'offer_logname' => $_REQUEST['offer_logname'],
        'offer_longdesc' => $_REQUEST['offer_longdesc'],
        'offer_group' => $_REQUEST['offer_group'],
        'offer_price' => (int) $_REQUEST['offer_price'],
        'offer_clientprice' => (int) $_REQUEST['offer_clientprice'],
        'offer_payment' => (float) $_REQUEST['offer_payment'],
        'offers_active' => (int) $_REQUEST['offers_active'],
        'offer_accept' => (int) $_REQUEST['offer_accept'],
        'offer_acceptKgz' => (int) $_REQUEST['offer_acceptKgz'],
        'offer_acceptRu' => (int) $_REQUEST['offer_acceptRu'],
        'offer_show_in_cold_kz' => (int) $_REQUEST['offer_show_in_cold_kz'],
        'offer_show_in_cold_kgz' => (int) $_REQUEST['offer_show_in_cold_kgz'],
        'offer_show_in_cold_uz' => (int) $_REQUEST['offer_show_in_cold_uz']
    );

    if (DB::insert('offers', $insertArr) && ($_REQUEST['id'] = DB::insertId())) {
        $res['success'] = true;
    }
}

ApiLogger::addLogVarExport('files');
ApiLogger::addLogVarExport($_FILES);

if (!empty($_REQUEST['id']) && !empty($_FILES) && ($file = reset($_FILES)) && !empty($file['name'])) {
//    DB::update('offers', array('offer_photo' => ''), 'offer_id = %i', $_REQUEST['id']);
    $res['success'] = false;

    ApiLogger::addLogVarExport($file);

    if (empty($file['tmp_name'])) {
        $res['msg'] = "Файл '{$file['name']}' не был загружен на сервер (возможно из-за размера > 2Mб)!";
    } else {
        $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');
        if (!in_array(end(explode('.', strtolower($file['name']))), $allowedExtensions)) {
            $res['msg'] = "Файл '{$file['name']}' не допустимого типа!";
        } else {
            $new_file_name = "offer_{$_REQUEST['id']}." . end(explode('.', strtolower($file['name'])));
            if (move_uploaded_file($file['tmp_name'], dirname(__FILE__) . "/../photos/product/$new_file_name")) {
                $updateArr = array(
                    'offer_photo' => $new_file_name
                );
                if (DB::update('offers', $updateArr, 'offer_id = %i', $_REQUEST['id'])) {
                    $res['success'] = true;
                    $res['msg'] = "Файл '{$file['name']}' успешно загружен!";
                }
            }
        }
    }
}

ApiLogger::addLogVarExport($res);
echo json_encode($res);
