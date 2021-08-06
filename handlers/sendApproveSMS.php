<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
ApiLogger::addLogVarExport('-------');
ApiLogger::addLogVarExport($_REQUEST);

if (strlen($_REQUEST['id']) > 5 && ($staffOrder = new StaffOrderObj($_REQUEST['id']))) {
    $staffOrder;
    if ($_REQUEST['deliv'] == 'Почта') {
        $resp = sendKcellSMS($staffOrder->cGetValues('phone'), '', $staffOrder->cGetId(), true);
    }

    ApiLogger::addLogVarExport($resp);
    ApiLogger::addLogVarExport('-------');

    if ($resp) {
        die('{"success":true}');
    }
}
die('{"success":false}');
