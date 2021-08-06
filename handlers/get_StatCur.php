<?php

session_start();
$ukr_ar = array('17729178', '26823714', '37239410', '30224289', '30356360', '76488859', '79319411', '47671128', '72480483', '80911164', '21630264', '44440873', '62443980', '77767205', '10578031');
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$allColdStaffsArr = getStaffListByRole('operatorcold', false);

// collect request parameters
$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
$dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'DESC';
$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
$sort = mysql_real_escape_string($sort);
$dir = mysql_real_escape_string($dir);
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
// loop through filters sent by client
if (is_array($filters)) {
    for ($i = 0; $i < count($filters); $i++) {
        $filter = $filters[$i];
        // assign filter data (location depends if encoded or not)
        if ($encoded) {
            $field = $filter->field;
            $daysItem = $filter->value;
            $compare = isset($filter->comparison) ? $filter->comparison : null;
            $filterType = $filter->type;
        } else {
            $field = $filter['field'];
            $daysItem = $filter['data']['value'];
            $compare = isset($filter['data']['comparison']) ? $filter['data']['comparison'] : null;
            $filterType = $filter['data']['type'];
        }
        $field = mysql_real_escape_string($field);
        $daysItem = mysql_real_escape_string($daysItem);
        $compare = mysql_real_escape_string($compare);
        $filterType = mysql_real_escape_string($filterType);
        switch ($filterType) {
            case 'string':
                $qs .= " AND " . $field . " LIKE '" . $daysItem . "%'";
                Break;
            case 'list':
                if (strstr($daysItem, ',')) {
                    $fi = explode(',', $daysItem);
                    for ($q = 0; $q < count($fi); $q++) {
                        $fi[$q] = "'" . $fi[$q] . "'";
                    }
                    $daysItem = implode(',', $fi);
                    $qs .= " AND " . $field . " IN (" . $daysItem . ")";
                } else {
                    $qs .= " AND " . $field . " = '" . $daysItem . "'";
                }
                Break;
            case 'boolean':
                $qs .= " AND " . $field . " = " . ($daysItem);
                Break;
            case 'numeric':
                switch ($compare) {
                    case 'eq':
                        $qs .= " AND " . $field . " = " . $daysItem;
                        Break;
                    case 'lt':
                        $qs .= " AND " . $field . " < " . $daysItem;
                        Break;
                    case 'gt':
                        $qs .= " AND " . $field . " > " . $daysItem;
                        Break;
                }
                Break;
            case 'date':
                switch ($compare) {
                    case 'eq':
                        $qs .= " AND " . $field . " >= '" . strtotime($daysItem) . "' AND " . $field . " <= '" . substr($daysItem, 0, 9) . " 23:59:59'";
                        Break;
                    case 'lt':
                        $qs .= " AND " . $field . " < '" . strtotime($daysItem) . "'";
                        Break;
                    case 'gt':
                        $qs .= " AND " . $field . " > '" . strtotime($daysItem) . "'";
                        Break;
                }
                Break;
        }
    }
    $where .= $qs;
}

$whereReturnDate = $whereGiveDate = $wherePostPartDate = '';

if (empty($_REQUEST['p1'])) {
    $_REQUEST['p1'] = 'kz';
}

//print_r($_REQUEST);

foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1':
            if (strlen($v)) {
                $where .= " AND country = '" . $v . "' ";
            }
            break;
        case 'p2':
            if (strlen($v)) {
                $whereReturnDate .= " AND return_date > '" . $v . " 00:00:00'";
            }
            break;
        case 'p3':
            if (strlen($v)) {
                $whereReturnDate .= " AND return_date < '" . $v . " 23:59:59'";
            }
            break;
        case 'p4':
            if (strlen($v)) {
                $where .= " AND offer = '" . $v . "'";
            }
            break;
        case 'p5':
            if (strlen($v)) {
                $whereGiveDate .= " AND give_date > '" . $v . " 00:00:00'";
                $wherePostPartDate .= " AND post_part_date > '" . $v . " 00:00:00'";
            }
            break;
        case 'p6':
            if (strlen($v)) {
                $whereGiveDate .= " AND give_date < '" . $v . " 23:59:59'";
                $wherePostPartDate .= " AND post_part_date < '" . $v . " 23:59:59'";
            }
            break;
        case 'p100':
            if (strlen($v) && ($staffIds = $GLOBAL_RESPONSIBLE_STAFF[$v])) {
                $where .= ' AND last_edit IN (' . implode(', ', $staffIds) . ')';
            }
            break;
        case 'p110':
            if (strlen($v) && ($staffIds = $GLOBAL_CURATOR_STAFF[$v])) {
                $where .= ' AND last_edit IN (' . implode(', ', $staffIds) . ')';
            }
            break;
    }
}

if (empty($whereReturnDate) && empty($whereGiveDate)) {
    $whereReturnDate = ' AND return_date > CURDATE()';
    $whereGiveDate = ' AND give_date > CURDATE()';
}

switch ($_REQUEST['p1']) {
    case 'kz':
        $bonusesSettings = $GLOBAL_COLD_PLANS_BONUSES['kz'];
        break;
    case 'kzg':
        $bonusesSettings = $GLOBAL_COLD_PLANS_BONUSES['kzg'];
        break;
    default:
        $bonusesSettings = array();
        break;
}

// Оплата
ApiLogger::addLogVarExport('-------------------');
$queryOplata = "  SELECT
                last_edit AS id,
                SUM(IF (   status = 'Подтвержден' AND kz_delivery != 'Почта' AND send_status = 'Оплачен' AND price IS NOT NULL
                            AND return_date > fill_date
                            AND (give_date IS NULL AND post_part_date = '0000-00-00 00:00:00')
                            AND post_part_price = 0
                        , price, 0)
                    ) AS B_OPLATA,
                SUM(IF (   status = 'Подтвержден' AND kz_delivery = 'Почта' AND send_status = 'Оплачен' AND total_price IS NOT NULL
                            AND return_date > fill_date
                        , total_price, 0)
                    ) AS B_TOTAL_PRICE_OPLATA,
                SUM(IF (   status = 'Подтвержден' AND kz_delivery != 'Почта' AND send_status = 'Оплачен' AND post_price IS NOT NULL
                            AND post_price > 0
                        , total_price, 0)
                    ) AS B_TOTAL_PRICE_CUR
            FROM staff_order so
                LEFT JOIN Staff s ON s.id = so.last_edit
            WHERE $where $whereReturnDate AND last_edit > 0
            GROUP BY last_edit
            ORDER BY Responsible, id";


//give_date - Дата предоплаты
//`give_date` timestamp NULL DEFAULT NULL,
//
//post_part_date - Дата изм. Частичной Предоплаты
//ADD COLUMN `post_part_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `post_part_price`;
//
//
//return_date - Дата оплаты
//`return_date` timestamp NULL DEFAULT NULL,
//if (!empty($sort)) {
//    $queryOplata .= PHP_EOL . " ORDER BY $sort $dir";
//} else {
//    $queryOplata .= PHP_EOL . ' ORDER BY B_KUR DESC';
//}

ApiLogger::addLogVarExport('$queryOplata START =>>>> ');
ApiLogger::addLogVarExport($queryOplata);
$statOplataData = DB::queryAssArray('id', $queryOplata);
ApiLogger::addLogVarExport('$queryOplata END =>>>> ');


// Предоплата
ApiLogger::addLogVarExport('-------------------');
$queryPredOplata = "  SELECT
                last_edit AS id,
                SUM(IF  (   status = 'Подтвержден' AND send_status IN ('Оплачен', 'Предоплата', 'Полная предоплата', 'Отказ-предоплата')
                            AND price IS NOT NULL
                            AND (give_date > fill_date OR post_part_date > fill_date)
                            AND (give_date IS NOT NULL OR post_part_date > '0000-00-00 00:00:00')
                        , price, 0)
                    ) AS B_PRED_OPLATA,
                SUM(IF  (   status = 'Подтвержден' AND send_status IN ('Оплачен', 'Предоплата', 'Полная предоплата', 'Отказ-предоплата')
                            AND post_price IS NOT NULL
                            AND (give_date > fill_date OR post_part_date > fill_date)
                            AND (give_date IS NOT NULL OR post_part_date > '0000-00-00 00:00:00')
                        , post_price, 0)
                    ) AS B_PART_PRED_OPLATA
            FROM staff_order so
                LEFT JOIN Staff s ON s.id = so.last_edit
            WHERE $where $whereGiveDate AND last_edit > 0
            GROUP BY last_edit
            ORDER BY id";
ApiLogger::addLogVarExport('$queryPredOplata START =>>>> ');
ApiLogger::addLogVarExport($queryPredOplata);
$statPredOplataData = DB::queryAssArray('id', $queryPredOplata);
ApiLogger::addLogVarExport('$queryPredOplata END =>>>> ');


//print_r($statOplataData);
//print_r($statPredOplataData);
// START Объединяем массивы
foreach ($statOplataData as $id => $value) {
    if (!array_key_exists($id, $statPredOplataData)) {
        $statPredOplataData[$id] = array(
            'id' => $id,
            'B_PRED_OPLATA' => 0,
            'B_PART_PRED_OPLATA' => 0
        );
    }
    $statOplataData[$id] += $statPredOplataData[$id];
    unset($statPredOplataData[$id]);
}
foreach ($statPredOplataData as $id => $value) {
    if (!array_key_exists($id, $statOplataData)) {
        $statOplataData[$id] = array(
            'id' => $id,
            'B_OPLATA' => 0,
            'B_TOTAL_PRICE_OPLATA' => 0,
            'B_TOTAL_PRICE_CUR' => 0
        );
    }
    $statOplataData[$id] += $statPredOplataData[$id];
}
// END Объединяем массивы
//print_r($statOplataData);die;
foreach ($statOplataData as $id => &$statItem) {
    $statItem['responsible_id'] = empty($GLOBAL_STAFF_RESPONSIBLE[$statItem['id']]) ? 0 : $GLOBAL_STAFF_RESPONSIBLE[$statItem['id']];

    // #B_OPLATA Оборотка "Без предоплаты"
    $statItem['B_OPLATA'];
    // #B_POST Оборотка "Предоплатные КС"
    $statItem['B_PRED_OPLATA'];
    // #B_OBOROTKA Итоговая оборотка
    $statItem['B_OBOROTKA'] = $statItem['B_OPLATA'] + $statItem['B_PRED_OPLATA'];
    // #B_BONUS_OBOROTKA Оборотка бонус
    $statItem['B_OBOROTKA_BONUS'] = $statItem['B_PART_PRED_OPLATA'] + $statItem['B_TOTAL_PRICE_OPLATA'] + $statItem['B_OPLATA'] + $statItem['B_TOTAL_PRICE_CUR'];

    $statItem['staff_id'] = $statItem['id'];
    $statItem['id'] = $GLOBAL_STAFF_FIO[$statItem['id']];
    $statItem['responsible'] = $GLOBAL_STAFF_FIO[$statItem['responsible_id']];
}

ApiLogger::addLogVarExport('$statPredOplataData');
ApiLogger::addLogVarExport($statPredOplataData);

echo json_encode(array(
    'total' => count($statOplataData),
    'queryOplata' => $queryOplata,
    'queryPredOplata' => $queryPredOplata,
    'data' => array_values($statOplataData)
));

