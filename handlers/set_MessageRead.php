<?php

header('Content-Type: text/html; charset=utf-8', true);
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        'success' => false,
        'msg' => "Permission denied"
    )));
}
require_once dirname(__FILE__) . '/../lib/db.php';

$result = array(
    'success' => false
);

if (($messId = (int) $_REQUEST['id'])) {
    if (DB::update('Message', array('Message_Status' => 'read', 'Message_ReadTimestamp' => DB::sqlEval('NOW()')), 'Message_Id = %i', $messId)) {
        $result['success'] = true;
    }
}

echo json_encode($result);
