<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';


$text = "Уведомление! Посылка " . $_GET['code'] . " прибыла в ваше почтовое отделение";
sendKcellSMS($_GET['phone'], $text, 1);
echo '{"success":true}';
