<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");
$result = '{"success":false}';

$quer = "UPDATE Staff
		 SET
		 FirstName = '" . mysql_real_escape_string($_POST['FirstName']) . "',
		 LastName = '" . mysql_real_escape_string($_POST['LastName']) . "',
		 Email = '" . mysql_real_escape_string($_POST['Email']) . "',
		 Birthday = '" . mysql_real_escape_string($_POST['Birthday']) . "',
		 Domain = '" . mysql_real_escape_string($_POST['Domain']) . "',
		 Phone = '" . mysql_real_escape_string($_POST['Phone']) . "'
		 WHERE id = '" . (int) $_SESSION['Logged_StaffId'] . "' ";
$rez = mysql_query($quer);
if ($rez) {
    $result = '{"success":true}';
}
echo $result;
