<?php
session_start();




if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}

include_once (dirname(__FILE__) . "/../lib/db.php");

$phone = isset($_GET["phone"]) && !empty($_GET["phone"]) ? $_GET["phone"] : NULL;

/*$query = "SELECT phone FROM staff_order
				WHERE id = " . $phone;
$rs = mysql_query($query);
$obj = mysql_fetch_assoc($rs);

$phone = $obj['phone'];*/
//var_dump($phone);
//$result = iconv('cp1251', 'utf-8', getKZSMStat($phone));
$result =  getKcellSMS($phone);
$result = substr($result,3,strlen($result));
//var_dump($phone,$result); die;
//var_dump($result);
//$result = explode("\n", $result);
$result = json_decode( $result, true);

/*
foreach ($result AS $key => $value) {
    $result[$key] = explode(",", $value);

    foreach ($result[$key] AS $item_key => $item_value) {
        unset($result[$key][$item_key]);
        $item_value = trim($item_value);
        $item_value = explode("=", $item_value, 2);

        $item_value[0] = isset($item_value[0]) ? strtolower(trim($item_value[0])) : "";
        $item_value[1] = isset($item_value[1]) ? trim($item_value[1]) : "";

        // if ($item_value[0] == "check_time" || $item_value[0] == "send_date") {
        //     $item_value[1] = date_parse($item_value[1]);
        //     $item_value[1] = $item_value[1]['year'] . "-" . $item_value[1]['month'] . "-" . $item_value[1]['day'] . " " . $item_value[1]['hour'] . ":" . $item_value[1]['minute'] . ":" . $item_value[1]['second'];
        // }

        $result[$key][$item_value[0]] = $item_value[1];
    }
}
*/
// print "<pre>";
// print_r($result);
// die;

if (isset($result[0]["error"])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => $result[0]["error"]
    )));
    $result = array();
}

echo json_encode(array(
    "success" => TRUE,
    "data" => $result
));
