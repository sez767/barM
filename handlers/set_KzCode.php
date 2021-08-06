<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

if (!empty($_REQUEST['ids_data'])) {
    $idsData = json_decode($_REQUEST['ids_data'], true);
}

//print_r($idsData);die;

if (($ret = setMassBarCode($idsData))) {
    echo json_encode(array(
        'success' => true,
        'data' => $ret
    ));
} else {
    echo json_encode(array('success' => false));
}
