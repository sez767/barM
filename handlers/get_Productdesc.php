<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$quer = "SELECT offer_longdesc FROM offers
		 WHERE offer_id = '" . (int) $_GET['id'] . "' ";
$rez = mysql_query($quer);
//echo $quer;
$rezu = mysql_fetch_assoc($rez);
echo $rezu['offer_longdesc'];
