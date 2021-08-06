<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}

$_GET['country'] = trim(@$_GET['country']);

if (empty($_GET['country'])) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array(),
        "total" => 0,
        "msg" => "Empty `country` value"
    )));
}

$_GET['city'] = trim(@$_GET['city']);

if (empty($_GET['city'])) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array(),
        "total" => 0,
        "msg" => "Empty `city` value"
    )));
}

$country = strtoupper($_GET['country']);
$city = $_GET['city'];

if (!in_array($country, array('KZ', 'KZG'))) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array(),
        "total" => 0,
        "msg" => "Invalid `country` value"
    )));
}

if ($city == "Почта") {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array(),
        "total" => 0,
        "msg" => "Invalid `city` value"
    )));
}

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
$redis = RedisManager::getInstance()->getRedis();

switch ($country) {
    case "KZ":
        $couriers = $redis->hGetAll("Currier");
        $regions = $redis->hGetAll("CurrierRegions");

        $data = array();
        $cityKey = 0;

        foreach ($couriers AS $ckey => $cvalue) {
            if (strtoupper($cvalue) == strtoupper($city)) {
                $cityKey = $ckey;
                break;
            }
        }

        foreach ($regions AS $rkey => $rvalue) {
            if (preg_match("/^" . (string) $cityKey . "_[0-9]+/", $rkey)) {
                $data[] = array(
                    "zip" => (string) $rkey,
                    "region" => $rvalue
                );
            }
        }

        $result = array(
            "success" => TRUE,
            "data" => $data,
            "total" => count($data)
        );
        break;
    case "KZG":
        $couriers = $redis->hGetAll('CurrierKGZ');
        $regions = $redis->hGetAll("CurrierRegions");

        $data = array();
        $cityKey = 0;

        foreach ($couriers AS $ckey => $cvalue) {
            if (strtoupper($cvalue) == strtoupper($city)) {
                $cityKey = $ckey;
                break;
            }
        }

        foreach ($regions AS $rkey => $rvalue) {
            if (preg_match("/^" . (string) $cityKey . "_[0-9]+/", $rkey)) {
                $data[] = array(
                    "zip" => (string) $rkey,
                    "region" => $rvalue
                );
            }
        }

        $result = array(
            "success" => TRUE,
            "data" => $data,
            "total" => count($data)
        );
        break;
    default:
        $result = array(
            "success" => TRUE,
            "data" => array(),
            "total" => 0,
            "msg" => "Undefined error"
        );
        break;
}

print json_encode($result);
