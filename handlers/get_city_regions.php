<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
$redis = RedisManager::getInstance()->getRedis();

switch ($_GET['type']) {
    case "getAll":
        $couriers = array();
        $recouriers = $redis->hGetAll("Currier");
        $reKGZ = $redis->hGetAll("CurrierKGZ");
        $reUz = $redis->hGetAll("CurrierUZ");
        $regions = $redis->hGetAll("CurrierRegions");

        foreach ($recouriers as $k => $v) {
            $couriers[$k . ''] = $v;
        }
        foreach ($reKGZ as $k => $v) {
            $couriers[$k . ''] = $v;
        }
        foreach ($reUz as $k => $v) {
            $couriers[$k . ''] = $v;
        }
        $data = array();
        //var_dump($couriers);
        foreach ($couriers AS $ckey => $cvalue) {
            $children = array();

            foreach ($regions AS $rkey => $rvalue) {
                if (strpos($rkey, (string) $ckey) === 0) {
                    $children[] = array(
                        'zip' => (string) $rkey,
                        "region" => $rvalue
                    );
                }
            }

            $data[] = array(
                'zip' => (string) $ckey,
                'city' => $cvalue,
                'children' => $children
            );
        }

        $result = array(
            'success' => TRUE,
            'data' => $data,
            'total' => count($data)
        );
        break;

    // Get all courier cities
    case 'cities':
        $Courier = $redis->hGetAll('Currier');
        $kgCurier = $redis->hGetAll('CurrierKGZ');
        $uzCurier = $redis->hGetAll('CurrierUZ');
        $coar = array();
        foreach ($Courier as $kk => $vv) {
            $coar[(string) $kk] = $vv;
        }
        foreach ($kgCurier as $kk => $vv) {
            $coar[(string) $kk] = $vv;
        }
        foreach ($uzCurier as $kk => $vv) {
            $coar[(string) $kk] = $vv;
        }
        $data = array();

        foreach ($coar AS $key => $value) {
            $data[] = array(
                'zip' => $key,
                'city' => $value
            );
        }

        $result = array(
            'success' => TRUE,
            'data' => $data,
            'total' => count($data)
        );
        break;

    // Get all courier regions
    /*
      case "regions":
      $regions = $redis->hGetAll("CurrierRegions");
      $data = array();

      foreach ($regions AS $key => $value) {
      $data[] = array(
      'zip' => $key,
      "region" => $value
      );
      }

      $result = array(
      'success' => TRUE,
      'data' => $data,
      'total' => count($data)
      );
      break;
     */

    // Other action
    default:
        $result = array(
            'success' => TRUE,
            'data' => array(),
            'total' => 0,
            "msg" => "Not found key"
        );
        break;
}

print json_encode($result);
