<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");


$data = file_get_contents('php://input');

if (($data = json_decode($data, true))) {

    $staffOrderObj = new StaffOrderObj($data['id']);
    if ($staffOrderObj->cGetLoadedValues()) {

        if (empty($_REQUEST['field'])) {
            $ret = array(
                'success' => true,
                'msg' => 'Invalid row field'
            );
        } else {
            $field = $_REQUEST['field'];
            $updateArr = array($field => $data[$field]);

            if (in_array($field, array('description_str'))) {
                $updateArr = array('description' => $data[$field]);
            }

            if (in_array($field, array('status_kz', 'send_status', 'status_cur', 'kz_curier'))) {
                $updateArr['log_data'] = CommonObject::getAdminId();
            }

            if ($field == 'status_cur' && strlen($data[$field]) > 1) {
                $updateArr['status_check'] = '';
                $updateArr['control_status'] = '';
            } elseif ($field == 'status_mail_reset') {
                $updateArr['status_mail_reset_date'] = DB::sqlEval('NOW()');
            }

            ApiLogger::addLogVarExport('$updateArr');
            ApiLogger::addLogVarExport($updateArr);

            if (in_array($_SESSION['Logged_StaffId'], array(11111111))) {
//                print_r($updateArr);
//                die();
            }

            if (in_array($field, array('youtube_url'))) {
                $redis = RedisManager::getInstance()->getRedis();
                $redis->hMset('youtube_urls', array($data['id'] => $data['youtube_url']));
            }

            if (($changedArr = $staffOrderObj->cSave($updateArr))) {

                ApiLogger::addLogVarExport($changedArr);

                $ret = array(
                    'success' => true,
                    "sql" => $changedArr
                );
            } else {
                $ret = array(
                    'success' => true,
                    'msg' => 'Already set'
                );
            }
        }
    } else {
        $ret = array(
            'success' => true,
            'msg' => 'Invalid record ID'
        );
    }
} else {
    $ret = array(
        'success' => true,
        'msg' => "Not found required data"
    );
}

echo json_encode($ret);
