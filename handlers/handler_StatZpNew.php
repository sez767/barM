<?php

// http://baribarda.com/handlers/get_DelivList_1.php?ids_data=[6787236,6776154,6640019,6436388,6420713,6389465,6385428,6302016,6273382,6272770,6271732]
// http://baribarda.com/handlers/get_DelivList_1.php?ids_data=[6385428]

require_once dirname(__FILE__) . "/../lib/db.php";
// require_once dirname(__FILE__) . "/../lib/excel/excel.class.php";
require_once dirname(__FILE__) . "/excel.inc.php";

// УПЛ
ini_set("display_errors", 0);
ini_set('memory_limit', '6024M');

header('Content-Type: text/html; charset=utf-8', true);

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}


$ids = array();

if (!empty($_REQUEST)) {
    // collect request parameters

    $qs = '';

    $filtersFieldsArr = array();
    $clientsFiltersArr = array();

    ApiLogger::addLogVarExport($filters);


    // loop through filters sent by client

    $query = "SELECT id FROM staff_order WHERE country IN (". $_SESSION['country'] . ") 
    AND  country IN ('" . $_REQUEST['country'] . "') AND return_date > '".$_REQUEST['startDate']."' 
    AND return_date < '".$_REQUEST['endDate']."'";
    //var_dump($query); die;
    $ids = DB::queryFirstColumn($query);
} else {
    if (!empty($_REQUEST['ids_data'])) {
        $ids = json_decode($_REQUEST['ids_data'], true);
    }

    foreach ($ids as $key => $id) {
        if (strlen($id) == 0 || $id <= 0) {
            unset($ids[$key]);
        }
    }
}

if (count($ids) == 0) {
    die('Нет заказов для печати');
}

//print_r($ids);

$query = " SELECT   id,
                    fio,
                    addr,
                    offer,
                    other_data,
                    total_price as price,
                    package,
                    kz_delivery,
                    last_edit,
                    dop_tovar
            FROM    staff_order
            WHERE   status IN ('Подтвержден', 'Предварительно подтвержден') AND
                    country IN ('kz', 'ru', 'kzg') AND
                    `id` IN (" . implode(',', $ids) . ')
            ORDER BY    id, offer, package';
//var_dump($query); die;
$rs = mysql_query($query);

$excel = new ExcelWriter("deliv_list_" . date('Y-m-d H-i-s') . '.xls');

$excel->writeLine(array(''));
$excel->writeLine(array('Дата скачивания', date('d-m-Y')));
if(strlen($_REQUEST['responsible'])) $excel->writeLine(array('', '', '', '<b>Статистика 2</b>', '', '', ''));
else $excel->writeLine(array('', '', '', '<b>Статистика 1</b>', '', '', ''));

$allArr = array();

while ($obj = mysql_fetch_assoc($rs)) {
//    echo PHP_EOL . 'start ------------------------' . PHP_EOL;
//    print_r($obj);

    /*if (empty($obj['kz_delivery'])) {
        continue;
    }*/

    $offerTitle = isset($GLOBAL_OFFER_DESC[$obj['offer']]) ? $GLOBAL_OFFER_DESC[$obj['offer']] : $obj['offer'];

    // обработка атрибутов товара
    $otherData = json_decode($obj['other_data'], true);

    if (json_last_error() == JSON_ERROR_NONE) {
        if (isset($otherData['name'])) {
            unset($otherData['name']);
        }

        sort($otherData);

        foreach ($otherData as $key => $value) {
            if (!empty($value)) {
                $otherData[$key] = trim($otherData[$key]);
                $otherData[$key] = preg_replace("/\s{2,}/", " ", $otherData[$key]);
                $otherData[$key] = str_replace(array("\n", "\t"), "", $otherData[$key]);
            }
        }

        $offer = trim($offerTitle) . " " . implode(" ", $otherData);
    } else {
//        echo '!!! json_error !!!' . PHP_EOL;
        $offer = $offerTitle;
    }
    $offer = trim($offer);

//    echo PHP_EOL . '$offer => ' . $offer . PHP_EOL;
If(strlen($_REQUEST['responsible'])){
if(!in_array($obj['last_edit'],$GLOBAL_RESPONSIBLE_STAFF[$_REQUEST['responsible']])) continue;
    $allArr[$obj['last_edit']][] = array(
        'offer' => $obj['last_edit'],
        'price' => $obj['price'],
        'zp' => round(($GLOBAL_ACTIVE_OFFERS[$offer]['offer_percent'] * $obj['price'])/100),
        'package' => $obj['package'],
    );
} else {
    // товар в массив
    $allArr[$offer][] = array(
        'offer' => $offer,
        'price' => $obj['price'],
        'package' => $obj['package'],
    );
}
    // обработка дополнительного товара
    $dop_tovar = json_decode($obj['dop_tovar'], true);

    if (json_last_error() == JSON_ERROR_NONE && isset($dop_tovar['dop_tovar']) && is_array($dop_tovar['dop_tovar'])) {
        foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {
            $properties = array();
            unset($dop_tovar['dop_tovar']['name']);
            $properties_key = array_keys($dop_tovar);

            foreach ($properties_key AS $property_key) {
                if (
                    !in_array($property_key, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) &&
                    isset($dop_tovar[$property_key][$ke]) &&
                    !empty($dop_tovar[$property_key][$ke])
                ) {
                    $properties[] = trim($dop_tovar[$property_key][$ke]);
                }
            }

            sort($properties);

            $offerTitle = isset($GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]]) ? $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] : $dop_tovar['dop_tovar'][$ke];

            foreach ($properties as $key => $value) {
                if (!empty($value)) {
                    // $properties[$key] = strtolower($value);
                    $properties[$key] = trim($properties[$key]);
                    $properties[$key] = preg_replace("/\s{2,}/", " ", $properties[$key]);
                    $properties[$key] = str_replace(array("\n", "\t"), "", $properties[$key]);
                }
            }

            $offer = trim($offerTitle) . " " . implode(" ", $properties);
            If(strlen($_REQUEST['responsible'])){
                if(!in_array($obj['last_edit'],$GLOBAL_RESPONSIBLE_STAFF[$_REQUEST['responsible']])) continue;
                $allArr[$obj['last_edit']][] = array(
                    'offer' => $obj['last_edit'],
                    'price' => $dop_tovar['dop_tovar_price'][$ke],
                    'zp' => round(($GLOBAL_ACTIVE_OFFERS[$offer]['offer_percent'] * $dop_tovar['dop_tovar_price'][$ke])/100),
                    'package' => $dop_tovar['dop_tovar_count'][$ke],
                );
            } else {
                $allArr[$offer][] = array(
                    'offer' => $offer,
                    'price' => $dop_tovar['dop_tovar_price'][$ke],
                    'package' => $dop_tovar['dop_tovar_count'][$ke],
                );
            }
        }
    }
}

//print_r($allArr);
//die('all');

$all_c = 0;
$all_p = 0;
$all_zp = 0;
    foreach ($allArr as $ok => $ov) {
        $sh = 0;
        $pr = 0;
        $zp = 0;
        foreach ($ov as $kv => $obj) {
            $sh += $obj['package'];
            $pr += $obj['price'];
            $zp += $obj['zp'];
        }
        if(strlen($_REQUEST['responsible'])) {
            $excel->writeLine(array($GLOBAL_STAFF_FIO[$ok], '-', $sh,$pr, round($pr/$sh),$zp));
        }
        else {
            $excel->writeLine(array($ok, '-', $sh,$pr, round($pr/$sh)));
        }
        $all_c += $sh;
        $all_p += $pr;
        $all_zp += $zp;
    }
if(strlen($_REQUEST['responsible'])) {
    $excel->writeLine(array(
        '<b>Итого:</b>',
        '',
        '<b>' . $all_c . '</b>',
        '<b>' . $all_p . '</b>',
        '<b>' . round($all_p / $all_c) . '</b>',
        '<b>' . round($all_zp) . '</b>'
    ));
}
else {
    $excel->writeLine(array(
        '<b>Итого:</b>',
        '',
        '<b>' . $all_c . '</b>',
        '<b>' . $all_p . '</b>',
        '<b>' . round($all_p / $all_c) . '</b>'
    ));
}
    $excel->writeLine(array(''));


$excel->close();
