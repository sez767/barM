<?php

require_once dirname(__FILE__) . '/../lib/db.php';

header('Content-Type: text/javascript; charset=utf-8');

error_reporting(E_ALL);
ini_set("display_errors", true);

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}


$storage = new Storage();
$result = $storage->resync();

print json_encode($result);
