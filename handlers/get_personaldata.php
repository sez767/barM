<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");
$query = "SELECT a.id, FirstName, LastName, Email, Birthday, Phone,
a.Bonuses-b.earning_staff AS Bonuses, a.Domain
FROM Staff AS a
LEFT JOIN staff_earnings AS b ON b.staff_id = a.id
    WHERE a.id = '" . $_SESSION['Logged_StaffId'] . "'";
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
?>
