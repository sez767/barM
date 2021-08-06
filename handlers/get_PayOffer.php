<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once (dirname(__FILE__) . "/../lib/class.staff.php");

$query = "SELECT * FROM offer_payment WHERE id_payment = '" . $_GET['id'] . "'";
$rs = mysql_query($query);
$arr = array();
while ($obj = mysql_fetch_object($rs)) {
    $tmpData = json_encode($obj);
    $tmpData = substr($tmpData, 1, strlen($tmpData) - 2); // strip the [ and ]
    $tmpData = str_replace("\\/", "/", '{"success":true,"data":' . $tmpData . '}'); // unescape the slashes
    $tmpData = '{"success":true,"data":' . json_encode($obj) . '}';
    $result = $tmpData;
}
echo $result;
