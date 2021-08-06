<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
include_once (dirname(__FILE__) . "/../lib/db.php");
$project = $_GET['project'];
$offer = $_GET['offer'];
//var_dump($_GET); die;
$query = "INSERT INTO offerInfo
			SET
				offer_info = 	'" . mysql_real_escape_string($_POST['text']) . "',
				offer_logdesc = '" . mysql_real_escape_string($_POST['logtext']) . "',
				offer_nocall =  '" . mysql_real_escape_string($_POST['nocalltext']) . "',
				offer_recall =  '" . mysql_real_escape_string($_POST['recalltext']) . "',
				offer_name = 	'" . mysql_real_escape_string($offer) . "',
				country = '" . mysql_real_escape_string($project) . "'";
//echo $query; die;
$rs = mysql_query($query);
if ($rs) {
    echo 'Информация добавлена';
} else {
    $querys = "UPDATE offerInfo
			SET
				offer_info = 	'" . mysql_real_escape_string($_POST['text']) . "',
				offer_logdesc = '" . mysql_real_escape_string($_POST['logtext']) . "',
				offer_nocall = '" . mysql_real_escape_string($_POST['nocalltext']) . "',
				offer_recall = '" . mysql_real_escape_string($_POST['recalltext']) . "'
            WHERE offer_name = '" . mysql_real_escape_string($offer) . "'
			AND country = '" . $project . "'";
    $rst = mysql_query($querys);
    //echo $querys; die;
    if ($rst)
        echo 'Информация обновлена';
    else
        echo 'Ошибка сохранения';
}
?>
