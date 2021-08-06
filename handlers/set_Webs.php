<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$request_body = file_get_contents('php://input');
$income = json_decode($request_body);

$id = $income->id;
$accept = (int) $income->accept;
$staff_id = (int) $_REQUEST['staff'];

$check = mysql_query("UPDATE `UtilWeb` SET accept = '" . $accept . "'  WHERE id = '" . $id . "' ");

$r = array('success' => true, 'message' => 'ok');
