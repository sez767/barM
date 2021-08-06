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


if (!empty($_REQUEST['di'])) {
    $idsArr = array(6640019, 6389465, 6787236, 6271732, 6273382, 6420713, 6385428, 6389465, 6787236, 6271732, 6776154, 6436388, 6272770, 6302016);
    $_REQUEST['ids_data'] = json_encode($idsArr);
}

$ids = array();

if (!empty($_REQUEST['filter'])) {
    // collect request parameters
    $start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
    $count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
    $filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
    $sort = my_mysqli_real_escape_string(isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'date');
    $dir = my_mysqli_real_escape_string(isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'DESC');
    // GridFilters sends filters as an Array if not json encoded
    if (is_array($filters)) {
        $encoded = false;
    } else {
        $encoded = true;
        $filters = json_decode($filters);
    }

    // initialize variables
    $where = ' 0 = 0 ';
    $qs = '';

    $filtersFieldsArr = array();
    $clientsFiltersArr = array();

    ApiLogger::addLogVarExport($filters);


    // loop through filters sent by client
    if (is_array($filters)) {
        for ($i = 0; $i < count($filters); $i++) {
            $filter = $filters[$i];
            // assign filter data (location depends if encoded or not)
            if ($encoded) {
                $field = $filter->field;
                $value = $filter->value;
                $compare = isset($filter->comparison) ? $filter->comparison : null;
                $filterType = $filter->type;
            } else {
                $field = $filter['field'];
                $value = $filter['data']['value'];
                $compare = isset($filter['data']['comparison']) ? $filter['data']['comparison'] : null;
                $filterType = $filter['data']['type'];
            }
            $field = my_mysqli_real_escape_string($field);
            $value = my_mysqli_real_escape_string($value);
            $compare = my_mysqli_real_escape_string($compare);
            $filterType = my_mysqli_real_escape_string($filterType);

            $filtersFieldsArr[$field] = $filter['data'];

            if (stripos($field, 'date') !== false) {
                $dobrikUseWhere = true;
            }

            if ($field == 'kz_zone') {
                if (isset($region_deliv[$value])) {
                    $where .= "AND kz_delivery IN ('" . implode("','", $region_deliv[$value]) . "')";
                }
                continue;
            } else if (in_array($field, array('id', 'uuid')) && strpos($value, ' ') === false) {
                $filterType = 'numeric';
                $compare = 'eq';
            } else if ($field == 'description_str') {
                $field = 'description';
            } else if (in_array($field, array('client_group', 'client_team', 'client_oper_use'))) {
                $field = ($field == 'client_oper_use') ? 'oper_use' : $field;

                $fi = explode(',', $value);
                $tmpClientsFilters = array();
                foreach ($fi as $fik => &$fiv) {
                    if (in_array($field, array('oper_use')) && $fiv == -1) {
                        $tmpClientsFilters[] = "`$field` > 0 ";
                        unset($fi[$fik]);
                    } elseif (in_array($field, array('client_group', 'client_team')) && $fiv == -1) {
                        $tmpClientsFilters[] = "(`$field` IS NULL OR `$field` = 0)";
                        unset($fi[$fik]);
                    }
                }
                if (!empty($fi)) {
                    $tmpClientsFilters[] = "`$field` IN (" . implode(',', $fi) . ")";
                }

                $clientsFiltersArr[] = implode(' AND ', $tmpClientsFilters);

                continue;
            }
            if ($field == 'offer_groups') {
                $ofGroupsArr = explode(',', $value);
                $ofGroupInArr = array();
                foreach ($ofGroupsArr as $groupItem) {
                    $ofGroupInArr = array_merge($ofGroupInArr, $GLOBAL_GROUP_OFFER[$groupItem]);
                    if (empty($groupItem)) {
                        $ofGroupInArr[] = '0';
                    }
                }
                $where .= " AND offer IN ('" . implode("', '", $ofGroupInArr) . "')";
                continue;
            }

            switch ($filterType) {
                case 'string':
                    $value = trim($value);

                    if (in_array($field, array('id', 'uuid', 'index'))) {
                        $tmpIds = explode(' ', $value);
                        $tmpIds[] = -1;
                        $tmpIds = array_diff($tmpIds, array(''));
                        $qs .= " AND `$field` IN ('" . implode("','", $tmpIds) . "')";
                    } else if ($field == 'phone') {
                        $phoneUuidStr = array();
                        foreach ($GLOBAL_COUNTRIES_ARR as $countryItem) {
                            $phoneUuidStr[] = mb_substr(md5(mb_strtoupper($countryItem) . mb_substr($value, -9)), 0, 16);
//                            $phoneUuidStr[] = mb_substr(md5(mb_strtoupper($countryItem) . mb_substr(hide_phone($value, 1), -9)), 0, 16);
                        }
                        $qs .= ' AND uuid IN (' . implode(',', $phoneUuidStr) . ') ';
                    } else if ($field == 'web_id') {
                        $qs .= " AND `" . $field . "` = '" . $value . "' ";
                    } else {
                        $qs .= " AND `$field` LIKE '%$value%'";
                    }
                    break;
                case 'list':
                    if ($value == 'Вся курьерка') {
                        $qs .= " AND kz_delivery != 'Почта' ";
                        Break;
                    }
                    if (in_array($field, array('status_cur')) && $value == -1) {
                        $qs .= " AND status_cur IN ('" . implode("', '", $GLOBAL_STATUS_CUR_OPLACHEN) . "') ";
                        Break;
                    }
                    if (in_array($field, array('staff_id', 'staff_id_orig')) && $value == -1) {
                        $qs .= " AND staff_id NOT IN (" . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ") ";
                        Break;
                    }
                    if (in_array($field, array('staff_id', 'staff_id_orig')) && $value == -2) {
                        $qs .= " AND staff_id IN (" . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ") ";
                        Break;
                    }
                    if ($field == 'responsible') {
                        $fi = explode(',', $value);
                        $tmpStaffArr = array();
                        foreach ($fi as $fiItem) {
                            if ($fiItem < 0 && array_key_exists(abs($fiItem), $GLOBAL_CURATOR_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_CURATOR_STAFF[abs($fiItem)]);
                            } elseif (array_key_exists($fiItem, $GLOBAL_RESPONSIBLE_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_RESPONSIBLE_STAFF[$fiItem]);
                            }
                        }
                        if (empty($tmpStaffArr)) {
                            $qs .= ' AND last_edit IN (-1) ';
                        } else {
                            $qs .= ' AND last_edit IN (' . implode(',', $tmpStaffArr) . ') ';
                        }
                        Break;
                    }
                    if ($field == 'responsible_cold') {
                        $fi = explode(',', $value);
                        $tmpStaffArr = array();
                        foreach ($fi as $fiItem) {

                            if ($fiItem < 0 && array_key_exists(abs($fiItem), $GLOBAL_CURATOR_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_CURATOR_STAFF[abs($fiItem)]);
                            } elseif (array_key_exists($fiItem, $GLOBAL_RESPONSIBLE_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_RESPONSIBLE_STAFF[$fiItem]);
                            }
                        }
                        if (empty($tmpStaffArr)) {
                            $qs .= ' AND is_cold_staff_id IN (-1) ';
                        } else {
                            $qs .= ' AND is_cold_staff_id IN (' . implode(',', $tmpStaffArr) . ') ';
                        }
                        Break;
                    }
                    if ($field == 'team') {
                        $fi = explode(',', $value);
                        $tmpStaffArr = array();
                        foreach ($fi as $fiItem) {
                            if (array_key_exists($fiItem, $GLOBAL_TEAM_STAFF)) {
                                $tmpStaffArr = array_merge($tmpStaffArr, $GLOBAL_TEAM_STAFF[$fiItem]);
                            }
                        }
                        if (!empty($tmpStaffArr)) {
                            $qs .= ' AND last_edit IN (' . implode(',', $tmpStaffArr) . ') ';
                        } else {
                            $qs .= ' AND last_edit IN (-1) ';
                        }
                        Break;
                    }
                    if (strpos($value, '(') > 1) {
                        $sub_val = explode("(", $value);
                        $value = trim($sub_val[0]);
                    }

                    if ($field == 'dop_tovar') {
                        $qs .= " AND " . $field . " LIKE '%" . $value . "%'";
                    } else {
                        if (strstr($value, ',')) {
                            $fi = explode(',', $value);
                            for ($q = 0; $q < count($fi); $q++) {
                                $fi[$q] = "'" . $fi[$q] . "'";
                            }
                            $value = implode(',', $fi);
                            $qs .= " AND `" . $field . "` IN (" . $value . ")";
                        } elseif (is_array($value)) {
                            $qs .= " AND `" . $field . "` IN (" . implode(',', $value) . ")";
                        } else {
                            $qs .= " AND `" . $field . "` = '" . $value . "'";
                        }
                    }
                    Break;
                case 'boolean':
                    $qs .= " AND " . $field . " = " . ($value);
                    Break;
                case 'numeric':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND " . $field . " = " . $value;
                            Break;
                        case 'lt':
                            $qs .= " AND " . $field . " < " . $value;
                            Break;
                        case 'gt':
                            $qs .= " AND " . $field . " > " . $value;
                            Break;
                    }
                    Break;
                case 'date':
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND " . $field . " >= '" . $value . "' AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                            Break;
                        case 'lt':
                            $qs .= " AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                            Break;
                        case 'gt':
                            $qs .= " AND " . $field . " >= '" . $value . "'";
                            Break;
                    }
                    Break;
            }
        }
        $where .= $qs;
    }

    $query = "SELECT id FROM staff_order WHERE country IN (" . $_SESSION['country'] . ") AND $where";
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
                    price,
                    package,
                    kz_delivery,
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
$excel->writeLine(array('', '', '', '<b>Упаковочный лист</b>', '', '', ''));
$excel->writeLine(array('<b>Почта</b>'));
$allArr = array();

while ($obj = mysql_fetch_assoc($rs)) {
//    echo PHP_EOL . 'start ------------------------' . PHP_EOL;
//    print_r($obj);

    if (empty($obj['kz_delivery'])) {
        continue;
    }

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
    // товар в массив
    $allArr[$obj['kz_delivery']][$offer][] = array(
        'offer' => $offer,
        'price' => $obj['price'],
        'package' => $obj['package'],
    );

    $allArr['Все'][$offer][] = array(
        'offer' => $offer,
        'price' => $obj['price'],
        'package' => $obj['package'],
    );

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

            $offer = trim(trim($offerTitle) . ' ' . implode(' ', $properties));

            $allArr['Все'][$offer][] = array(
                'offer' => $offer,
                'price' => $dop_tovar['dop_tovar_price'][$ke],
                'package' => $dop_tovar['dop_tovar_count'][$ke],
            );

            $allArr[$obj['kz_delivery']][$offer][] = array(
                'offer' => $offer,
                'price' => $dop_tovar['dop_tovar_price'][$ke],
                'package' => $dop_tovar['dop_tovar_count'][$ke],
            );
        }
    }
}

//print_r($allArr);
//die('all');


foreach ($allArr as $oks => $oksArr) {
    ksort($oksArr);
    $all_c = 0;
    $excel->writeLine(array('<b>' . $oks . '</b>'));

    foreach ($oksArr as $ok => $ov) {
        $sh = 0;
        foreach ($ov as $kv => $obj) {
            $sh += $obj['package'];
        }
        $excel->writeLine(array($ok, '-', $sh));
        $all_c += $sh;
    }
    $excel->writeLine(array(
        '<b>Итого:</b>',
        '',
        '<b>' . $all_c . '</b>'
    ));
    $excel->writeLine(array(''));
}

$excel->close();
