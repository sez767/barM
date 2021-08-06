<?php

session_start();
die;
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$phones = $_GET['phones'];

if (strlen($_GET['ids']) > 5) {
    $indexes = explode(',', substr($_GET['ids'], 0, strlen($_GET['ids']) - 1));

    $text = '';
    foreach ($phones as $kph => $phone) {
        $text .= $indexes[$kph] . '-' . $phone . ' ';
    }

    sendKZSms('7' . $_GET['phones'], $text);
}
//if($query)
echo '{"success":true}';
