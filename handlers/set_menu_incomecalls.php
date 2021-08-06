<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$incomeCallsObj = new IncomeCallsObj($_REQUEST['id']);
$ret = array(
    'success' => false
);

if ($_REQUEST['id'] > 0) {


    if (!empty($_REQUEST['client_recall_date'])) {
        $_REQUEST['client_recall_date'] = $_REQUEST['client_recall_date'] . ' ' . $_REQUEST['client_recall_time'];
    }

    $resp['client_recall_date'] = empty($_REQUEST['client_recall_date']) ? DB::sqlEval('NULL') : $_REQUEST['client_recall_date'];

    if (in_array($_REQUEST['client_deal_status'], array(1, 3, 4))) {
        $_REQUEST['oper_use'] = 0;
    }

    if ($incomeCallsObj->cGetLoadedValues()) {
        $data = $incomeCallsObj->update($_REQUEST);
        $ret = array(
            'success' => true,
            'data' => $data
        );
    }
}

echo json_encode($ret);
