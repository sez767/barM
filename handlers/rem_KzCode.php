<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$query2 = mysql_query("UPDATE staff_order SET kz_code = '' WHERE status = 'Подтвержден' AND kz_delivery = 'Почта'
 AND id IN (" . substr($_GET['ids'], 0, strlen($_GET['ids']) - 1) . ")");
