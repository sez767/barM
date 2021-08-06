<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';

if (strlen($_GET['phones']) > 4) {
    $text = 'www.kbt-store.com служба поддержки клиентов 2442, www.instagram.com/kbt.group';
    $phone = '7' . substr(preg_replace("([^0-9])", "", $_GET['phones']), -10);
    $rez = sendKcellSMS($phone, $text);
}
echo '{"success":true}';
