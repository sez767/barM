<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$clientsObj = new ClientsObj($_REQUEST['uuid']);
$ret = array(
    'success' => false
);
if (!empty($_REQUEST['uuid'])) {


    if (!empty($_REQUEST['client_recall_date'])) {
        $_REQUEST['client_recall_date'] = $_REQUEST['client_recall_date'] . ' ' . $_REQUEST['client_recall_time'];
    }

    $resp['client_recall_date'] = empty($_REQUEST['client_recall_date']) ? DB::sqlEval('NULL') : $_REQUEST['client_recall_date'];

    if (in_array($_REQUEST['client_deal_status'], array(1, 3, 4))) {
//{id: '1', value: 'Не согласен'},
//{id: '3', value: 'Брак'},
//{id: '4', value: 'Черный список (просит никогда ему не звонить)'},
        $_REQUEST['oper_use'] = 0;
    }

    if ($_REQUEST['client_deal_status'] == 2 && !empty($resp['client_recall_date'])) {
        $insTemplArr = array(
            'MessageTemplate_Header' => "Перезвонить клиенту: {$_REQUEST['fio']}",
            'MessageTemplate_AdditionalData' => json_encode(array('uuid' => $_REQUEST['uuid'])),
            'MessageTemplate_Text' => "Перезвоните\nФИО: {$_REQUEST['fio']}\nв: {$resp['client_recall_date']}",
            'created_by' => $_SESSION['Logged_StaffId']
        );
        if (DB::insert('MessageTemplate', $insTemplArr) && ($templId = DB::insertId())) {
            $insMessArr = array(
                'Message_UserId' => $_SESSION['Logged_StaffId'],
                'Message_Type' => 'reminder',
                'Message_MessageTemplateId' => $templId,
                'Message_ActualTime' => $resp['client_recall_date']
            );
            DB::insert('Message', $insMessArr);
        }
    }

    if ($clientsObj->cGetLoadedValues()) {
        $data = $clientsObj->update($_REQUEST);
        $ret = array(
            'success' => true,
            'data' => $data
        );
    }
}

echo json_encode($ret);
