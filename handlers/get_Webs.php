<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$query = "  SELECT id, staff, web, accept
            FROM UtilWeb
            WHERE staff = '".$_REQUEST['staff']."' ";

$rs = mysql_query($query);
$arr = array();
while ($obj = mysql_fetch_object($rs)) {
    $arr[] = $obj;
}

echo json_encode(array(
    'total' => count($arr),
    'data' => $arr
));