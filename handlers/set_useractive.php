<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$result = array('success' => false);

if ((int) $_GET['id']) {

    $staffObj = new StaffObj($_GET['id']);

    $updateArr = array('Type' => (int) $_GET['Type']);

    $origSip = 0;

    if ((int) $_GET['Type']) {
        $updateArr['Password'] = md5(rand(10000000, 99999999));
    } else {
        $updateArr['Password'] = 'Fuck OFF';
        $updateArr['Level'] = 0;
//        $updateArr['Location'] = '';
//        $updateArr['Sip'] = 0;
        $origSip = $staffObj->cGetLoadedValues('Sip');
    }

    if ($staffObj->cSave($updateArr)) {
        $result = array('success' => true);
    }

    if ($origSip > 0) {
        asterisk_base();
        DB::update('sip_buddies', array('md5secret' => 'Fuck OFF'), 'name = %i', $origSip);
    }
}

echo json_encode($result);
