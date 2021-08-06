<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
include_once (dirname(__FILE__) . "/../lib/db.php");

$resp = array('success' => false);

//$logFileName = substr(basename(__FILE__), 0, -4) . "-staff-{$_SESSION['Logged_StaffId']}";
ApiLogger::setLogFile(null, $logFileName);

ApiLogger::addLogVarExport('$_REQUEST:');
ApiLogger::addLogVarExport($_REQUEST);

$plusStatusFieldPrefix = "is_{$_REQUEST['plus']}";

if (
        ($parentId = (int) $_REQUEST['id']) &&
        ($plusStatus = $_REQUEST[$plusStatusFieldPrefix]) &&
        ($origData = DB::queryFirstRow("SELECT * FROM staff_order WHERE id = %i", $parentId))
) {

    $staffOrderObj = new StaffOrderObj($parentId);

    $resp = array(
//        'last_edit' => $_SESSION['Logged_StaffId'], // жудкий баг ни в коем случае не раскоментировать (перетирается оператора обзвона)
        $plusStatusFieldPrefix => $plusStatus,
        "{$plusStatusFieldPrefix}_staff_id" => $_SESSION['Logged_StaffId']
    );

    if (isset($_REQUEST['common_recall_date'])) {
        $resp['common_recall_date'] = empty($_REQUEST['common_recall_date']) ? DB::sqlEval('NULL') : $_REQUEST['common_recall_date'];
    }
    $resp['common_cancel_type'] = empty($_REQUEST['common_cancel_type']) ? '' : $_REQUEST['common_cancel_type'];


    if ($plusStatus == 8 && !empty($_REQUEST['common_interested_category'])) {
        // Заинтересован
        $resp['common_interested_category'] = $_REQUEST['common_interested_category'];
//        $resp['deferred_date'] = $_REQUEST['deferred_date'];
    }

    if (isset($_REQUEST['description'])) {
        $resp['description'] = $_REQUEST['description'];
    }

    if (in_array($origData['staff_id'], array(47369504, 25937686, 31769332, 36481874, 47063460, 20217943, 42655111, 45033811, 48061934, 71171003, 48514518, 49152384, 78017798, 57369831, 99171796, 91318760, 90871721, 93375132, 95873538, 93991201, 97979449))) {
        $resp['fio'] = isset($_REQUEST['fio']) ? $_REQUEST['fio'] : $origData['fio'];
        $resp['kz_delivery'] = isset($_REQUEST['kz_delivery']) ? $_REQUEST['kz_delivery'] : $origData['kz_delivery'];
    }

    if ($plusStatus == 5) {
        // 5 - Внести заказ
        ////////////////
        // создаем анкету
        // ФИО, Индекс,Район, Город, Район Города, Адрес, Улица, Дом, Квартира
        ////////////////
        $fieldsToCopy = array('phone', 'client_group', 'country', 'fio', 'index', 'district', 'status', 'city', 'city_region', 'addr', 'street', 'currency', 'building', 'flat', 'staff_id', 'birthday');
        foreach ($fieldsToCopy as $fieldName) {
            // Берем обновленые значения, если таковые есть, иначе значения из оригиналной заявки
            $insertData[$fieldName] = isset($resp[$fieldName]) ? $resp[$fieldName] : $origData[$fieldName];
        }

        unset($insertData[$plusStatusFieldPrefix]);
        $insertData['status'] = 'новая';
        $insertData['staff_id_orig'] = $insertData['staff_id'];

        if (!in_array($insertData['staff_id'], array(55555555, 47369504, 57369831, 25937686, 97979449, 93991201, 93375132, 90871721, 91318760, 95873538))) {
            // БРД, Модуль Холодные заказы, сейчас заказы под источником "Холодные заказы КЕТ" если нажать кнопку "внести заказ" он сохраняет по умолчанию под источником "Холодные заказы"
            // нам нужно чтобы заказы которые зашли с КЕТ, при нажатии кнопку "внести заказ", сохранялись под источником Холодные КЕТ
            $insertData['staff_id'] = in_array($_REQUEST['plus'], array('cold', 'cold_new')) ? 22222222 : 33333333;
        }

        $insertData['web_id'] = $_SESSION['Logged_StaffId'];
        $insertData['ext_id'] = $parentId;
        $insertData['fill_date'] = DB::sqlEval("NOW()");
        $insertData['last_edit'] = $_SESSION['Logged_StaffId'];
        $insertData["{$plusStatusFieldPrefix}_staff_id"] = $_SESSION['Logged_StaffId'];

        ApiLogger::addLogVarExport('$insertData:');
        ApiLogger::addLogVarExport($insertData);

        $newStaffOrderObj = new StaffOrderObj();
        $newStaffOrderObj->cSave($insertData);

        if (($newId = $newStaffOrderObj->cGetId())) {
            $resp["{$plusStatusFieldPrefix}_new_id"] = $newId;

            $callParentSearchQs = " SELECT * FROM call_staff_order
                    WHERE order_id  = %i AND
                    `date` BETWEEN '{$newStaffOrderObj->cGetValues('date')}' - INTERVAL 1 HOUR AND '{$newStaffOrderObj->cGetValues('date')}'
                    ORDER BY cid DESC";
//            ApiLogger::addLogJson($callParentSearchQs);

            if (($callParentData = DB::queryFirstRow($callParentSearchQs, $parentId))) {
                unset($callParentData['cid']);
                $callParentData['order_id'] = $newId;
                DB::insert('call_staff_order', $callParentData);
            }
        }
    }

    if (in_array($plusStatus, array(2, 4, 5, 6)) && ($uuid = $staffOrderObj->cGetValues('uuid'))) {
        // 2 - Надо перезвонить
        // 4 - Не согласен
        // 5 - Внести заказ
        // 6 - Холодный (брак)
//        $qs = "SELECT id, is_cold_out_date FROM {$staffOrderObj->cGetTableName()} WHERE id != %i AND uuid = %s";
//        $origColdStatusDateArr = DB::queryAssData('id', 'is_cold_out_date', $qs, $staffOrderObj->cGetId(), $uuid);


        $addWhere = '';
        if ($plusStatus == 5) {
            $addWhere = " AND id != $newId";
        }
        DB::update($staffOrderObj->cGetTableName(), array($plusStatusFieldPrefix => $plusStatus), "$plusStatusFieldPrefix IN (0, 1, 2, 3, 4, 6, 7, 10, 11, 12) AND uuid = %s $addWhere", $uuid);

//        foreach ($origColdStatusDateArr as $orderId => $isColdOutDateOrig) {
//            DB::update($staffOrderObj->cGetTableName(), array('is_cold_out_date' => $isColdOutDateOrig), "id = %i", $orderId);
//        }
    }

    if (in_array($plusStatus, array(2, 4)) && !empty($resp['common_recall_date'])) {
        // 2 - Надо перезвонить
        // 4 - Не согласен
        $insTemplArr = array(
            'MessageTemplate_Header' => "Перезвонить {$_REQUEST['plus']}",
            'MessageTemplate_AdditionalData' => json_encode(array('order_id' => $parentId)),
            'MessageTemplate_Text' => "Перезвоните",
            'created_by' => $_SESSION['Logged_StaffId']
        );
        if (DB::insert('MessageTemplate', $insTemplArr) && ($templId = DB::insertId())) {
            $insMessArr = array(
                'Message_UserId' => $_SESSION['Logged_StaffId'],
                'Message_Type' => 'reminder',
                'Message_MessageTemplateId' => $templId,
                'Message_ActualTime' => $resp['common_recall_date']
            );
            DB::insert('Message', $insMessArr);
        }
    }
//    if ($plusStatus == 3 && $_REQUEST['plus'] == 'cold' && $origData['country'] != 'kzg') {
//        Звонок с Медетом - и попросиб убрать эту плюшку, что бы они оставались в своей группе
//        $resp['Group_cold'] = 2;
//    }
    if ($plusStatus == 8 && in_array($_REQUEST['plus'], array('cold', 'cold_new')) && $origData['country'] == 'kz' && !empty($resp['common_interested_category'])) {
//        Звонок с Гульден - в статусе заинтересован - автоматически должны попадать в холодную группу 2
//        ApiLogger::addLogVarExport($_REQUEST);
        if (!empty($GLOBAL_RESPONSIBLE_CURATOR[$_SESSION['Logged_StaffId']])) {
            if ($_SESSION['Logged_StaffId'] == 63077972 || $GLOBAL_RESPONSIBLE_CURATOR[$_SESSION['Logged_StaffId']] == 63077972 || $GLOBAL_RESPONSIBLE_CURATOR[$GLOBAL_STAFF_RESPONSIBLE[$_SESSION['Logged_StaffId']]] == 63077972) {
                $resp['Group_cold'] = 2;
            }
            if ($_SESSION['Logged_StaffId'] == 44917943 || $GLOBAL_RESPONSIBLE_CURATOR[$_SESSION['Logged_StaffId']] == 44917943 || $GLOBAL_RESPONSIBLE_CURATOR[$GLOBAL_STAFF_RESPONSIBLE[$_SESSION['Logged_StaffId']]] == 44917943) {
                $resp['Group_cold'] = 3;
            }
            if ($_SESSION['Logged_StaffId'] == 57637454 || $GLOBAL_RESPONSIBLE_CURATOR[$_SESSION['Logged_StaffId']] == 57637454 || $GLOBAL_RESPONSIBLE_CURATOR[$GLOBAL_STAFF_RESPONSIBLE[$_SESSION['Logged_StaffId']]] == 57637454) {
                $resp['Group_cold'] = 4;
            }
        }
    }

    ApiLogger::addLogVarExport('$resp:');
    ApiLogger::addLogVarExport($resp);
    if ($staffOrderObj->cSave($resp)) {
        $callObjectHistoryObj = new CallObjectHistoryObj();
        $qs = "SELECT id FROM {$callObjectHistoryObj->cGetTableName()} WHERE uniqueid = %s";
        if (!empty($_REQUEST['cdr_call']) && ($hId = DB::queryFirstField($qs, $_REQUEST['cdr_call']))) {

            ApiLogger::addLogVarExport('SAVE CDR_CALL');
            $callObjectHistoryObj->cSetId($hId);
            $upArr = array(
                'data_name' => $plusStatusFieldPrefix,
                'data_int' => $plusStatus,
                'data_str' => json_encode($resp)
            );
            ApiLogger::addLogVarExport($upArr);
            $callObjectHistoryObj->cSave($upArr);
        }
    }

    /////////////////////////////////
    // START ANKET CLOSE PROTECT
    $redis = RedisManager::getInstance()->getRedis();
    $sip = empty($GLOBAL_STAFF_SIP[$_SESSION['Logged_StaffId']]) ? 0 : $GLOBAL_STAFF_SIP[$_SESSION['Logged_StaffId']];
    if ($sip && $redis->exists('SIP/' . $sip) && false) {
        $resultRow = $redis->hGetAll('SIP/' . $sip);

        if ($resultRow['cdr_status'] == 3 && in_array($resultRow['cdr_queuename'], $GLOBAL_QUEUE_CLOSE_PROTECTED_ARR)) {
            $redis->hMset('SIP/' . $sip, array('cdr_status' => 2));
        }
    }
    // END ANKET CLOSE PROTECT
    /////////////////////////////////


    $resp['new_id'] = $newId;
}

echo json_encode($resp);

ApiLogger::addLogVarExport('======= END');

