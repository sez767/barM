<?php

require_once dirname(__FILE__) . '/../lib/CommonManagers.php';


$request_body = file_get_contents('php://input');
$post = array();
$post = (array) json_decode($request_body);
$method = $_REQUEST['method'];
//var_dump($post); die;
switch ($method) {
    case 'read':
        $respond = read();
        break;
    case 'update':
        $respond = update($post);
        break;
    case 'insert':
        $respond = insert($post);
        break;
    case 'delete':
        $respond = delete($post);
        break;
    case 'max':
        $respond = maxi();
        break;
}

echo json_encode($respond);

function read() {
    $redis = RedisManager::getInstance()->getRedis();
    $resultik = false;
    $black_list = $redis->hGetAll('black_list');
    foreach ($black_list as $k => $v) {
        $resultik[] = array('id' => $k, 'Phone' => $v);
    }
    return $resultik;
}

function maxi() {
    $redis = RedisManager::getInstance()->getRedis();
    $resultik = false;
    $black_list = $redis->hGetAll('black_list');
    $resultik = count($black_list);
    return $resultik;
}

function update($atributes) {
    $resultik = false;
    $maxId = maxi();
    if ($atributes['id'] == ($maxId + 1)) {
        return insert($atributes);
    } else {
        $redis = RedisManager::getInstance()->getRedis();
        //var_dump( array($atributes['id'],$atributes['Phone'],$maxId));
        $resultik = $redis->hMset('black_list', array($atributes['id'] => $atributes['Phone']));
        return $resultik;
    }
}

function insert($atributes) {
    $redis = RedisManager::getInstance()->getRedis();
    $resultik = false;
    //var_dump( array($atributes['id'],$atributes['Phone']));

    $resultik = $redis->hMset('black_list', array($atributes['id'] => $atributes['Phone']));
    return $resultik;
}

function delete($atributes) {
    $redis = RedisManager::getInstance()->getRedis();
    $resultik = false;
    foreach ($atributes as $key => $value) {
        $atributes[$key] = $value;
    }
    $query = "DELETE FROM LdapGroups WHERE Ldap_id = '" . $atributes['id'] . "'";

    return $resultik;
}
