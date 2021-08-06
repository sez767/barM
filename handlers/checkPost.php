<?php
require_once dirname(__FILE__) . '/../lib/db.php';
include_once dirname(__FILE__) . '/../services/xml.class.php';

$data = array('request' =>
    array(
        'transaction' => array(
            'id' => 'biz.tt.TtTrackingMgt#selExternalTrackingDetail',
        ),
        'dataSet' => array(
            'fields' => array(
                'RGT_NO' => trim($_GET['kz_code']),
                'LOCALE' => 'ru',
            ),
        ),
        ));

$xml = Array2XML::createXML('DeliveryRequest', $data);
$POST = $xml->saveXML();
$rez = checkPost($POST);
var_dump($rez);
die;
$rez = XML2Array::createArray($rez);

//if($rez['response']['dataSet']['message']['result']=='Error') continue;
//else { var_dump($rez); die;}
//die;
$data = $rez['response']['dataSet']['recordSet'][1]['record'];
var_dump($data);

//$max_key = max(array_keys($data));

function checkPost($xml) {
    $url = 'http://89.218.48.17:8080/colvir-mail-info/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);

    return $result['EXE'];
}
