<?php

include_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header('location: /login.html');
    die();
}

$resp = array('success' => false);

if (($id = (int) $_REQUEST['id'])) {

    foreach ($_REQUEST as $rKey => $rVal) {
        if (stripos($rKey, 'question_') === 0) {
            $tmp = explode('_', $rKey);

            if (count($tmp) == 3) {
                $oprosnikAnswersObj = new OprosnikAnswersObj($tmp[2]);
//                print_r($oprosnikAnswersObj->cGetValues());
                $oprosnikAnswersObj->cSetValues('answer', $rVal);
            } elseif (!empty($rVal)) {
                $oprosnikAnswersObj = new OprosnikAnswersObj();
                $oprosnikAnswersObj->cSetValues(array(
                    'order_id' => $id,
                    'question_id' => $tmp[1],
                    'answer' => $rVal
                ));
            }

            $oprosnikAnswersObj->cSave();
        }
    }

    $resp['success'] = true;
}

echo json_encode($resp);
