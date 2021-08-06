<?php

header('Content-Type: text/javascript; charset=utf-8');

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$result = '{"success":false}';
$data = (array) json_decode($_POST['FormData']);
//var_dump($data); die;
if ((int) $_GET['id']) {
    $quer = "
		UPDATE
			offer_payment
		SET
			`offer_id` = '" . mysql_real_escape_string($data['offer_id']) . "',
			`country_payment` = '" . mysql_real_escape_string($data['country_payment']) . "',
			`date_payment` = '" . mysql_real_escape_string($data['date_payment']) . "',
			`web_payment` = '" . mysql_real_escape_string($data['web_payment']) . "',
			`offer_cost` = '" . mysql_real_escape_string($data['offer_cost']) . "',
			`cpa_payment` = '" . mysql_real_escape_string($data['cpa_payment']) . "'

		WHERE
			id_payment = '" . (int) $_GET['id'] . "'
	";

    $rez = mysql_query($quer);

    if ($rez) {
        $result = '{"success":true}';
    }
} else {
    $quer = "
		INSERT INTO
			`offer_payment`
		SET
			`offer_id` = '" . mysql_real_escape_string($data['offer_id']) . "',
			`country_payment` = '" . mysql_real_escape_string($data['country_payment']) . "',
			`date_payment` = '" . mysql_real_escape_string($data['date_payment']) . "',
			`web_payment` = '" . mysql_real_escape_string($data['web_payment']) . "',
			`offer_cost` = '" . mysql_real_escape_string($data['offer_cost']) . "',
			`cpa_payment` = '" . mysql_real_escape_string($data['cpa_payment']) . "'
	";
    //echo $quer;
    $rez = mysql_query($quer);

    if ($rez) {
        $result = '{"success":true}';
    }
}

echo $result;
