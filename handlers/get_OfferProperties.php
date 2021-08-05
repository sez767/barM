<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_GET['offer'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Invalid offer name",
        "data" => array()
    )));
}

$offer_properties = OfferPropertiesManager::getOfferProperties($_GET['offer']);
if (!empty($_GET['is_unique'])) {
    foreach ($offer_properties AS $key => $value) {
        foreach ($value as $k => $v) {
            $offer_properties[$key][md5($v['value'])] = $v;
            unset($offer_properties[$key][$k]);
        }

        $offer_properties[$key] = array_values($offer_properties[$key]);
    }
}

print json_encode(array(
    "success" => TRUE,
    "data" => $offer_properties
));
