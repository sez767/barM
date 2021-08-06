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

$whereReturnDate = $whereGiveDate = $whereFillDate = $whereCommonDate = '';

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
                $whereFillDate .= " AND fill_date > '" . $v . " 00:00:00'";
                $whereCommonDate .= " AND is_common_status_date > '" . $v . " 00:00:00'";
            }
            break;
        case 'p3':
            if (strlen($v)) {
                $whereReturnDate .= " AND return_date < '" . $v . " 23:59:59'";
                $whereFillDate .= " AND fill_date < '" . $v . " 23:59:59'";
                $whereCommonDate .= " AND is_common_status_date < '" . $v . " 23:59:59'";
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
                $whereFillDate .= " AND fill_date > '" . $v . " 00:00:00'";
                $whereCommonDate .= " AND is_common_status_date > '" . $v . " 00:00:00'";
            }
            break;
        case 'p6':
            if (strlen($v)) {
                $whereGiveDate .= " AND give_date < '" . $v . " 23:59:59'";
                $whereFillDate .= " AND fill_date < '" . $v . " 23:59:59'";
                $whereCommonDate .= " AND is_common_status_date < '" . $v . " 23:59:59'";
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
    $whereFillDate = ' AND fill_date > CURDATE()';
    $whereCommonDate = '  AND is_common_status_date > CURDATE() ';
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

// Курьерка
ApiLogger::addLogVarExport('-------------------');
$queryKur = "  SELECT
                last_edit AS id,
                SUM(IF(status = 'Подтвержден' AND send_status = 'Оплачен' AND kz_delivery != 'Почта' AND price IS NOT NULL AND return_date > fill_date, price, 0)) AS B_KUR,
                SUM(IF(status = 'Подтвержден' AND send_status = 'Оплачен' AND kz_delivery = 'Почта' AND total_price IS NOT NULL AND return_date > fill_date, total_price, 0)) AS B_POST_TOTAL
            FROM staff_order so
                LEFT JOIN Staff s ON s.id = so.last_edit
            WHERE $where $whereReturnDate AND last_edit > 0
            GROUP BY last_edit
            ORDER BY Responsible, id";

//if (!empty($sort)) {
//    $queryKur .= PHP_EOL . " ORDER BY $sort $dir";
//} else {
//    $queryKur .= PHP_EOL . ' ORDER BY B_KUR DESC';
//}

ApiLogger::addLogVarExport('$queryKur START =>>>> ');
ApiLogger::addLogVarExport($queryKur);
$statKurData = DB::queryAssArray('id', $queryKur);
ApiLogger::addLogVarExport('$queryKur END =>>>> ');


// Почта
ApiLogger::addLogVarExport('-------------------');
$queryPost = "  SELECT
                last_edit AS id,
                SUM(IF(status = 'Подтвержден' AND kz_delivery = 'Почта' AND send_status IN ('Предоплата', 'Полная предоплата', 'Оплачен') AND price IS NOT NULL AND give_date > fill_date, price, 0)) AS B_POST,
                SUM(IF(status = 'Подтвержден' AND kz_delivery = 'Почта' AND send_status IN ('Предоплата', 'Полная предоплата', 'Отказ-предоплата', 'Оплачен') AND post_price IS NOT NULL AND give_date > fill_date, post_price, 0)) AS B_POST_PREDOPLATA
            FROM staff_order so
                LEFT JOIN Staff s ON s.id = so.last_edit
            WHERE $where $whereGiveDate AND last_edit > 0
            GROUP BY last_edit
            ORDER BY id";
ApiLogger::addLogVarExport('$queryPost START =>>>> ');
ApiLogger::addLogVarExport($queryPost);
$statPostData = DB::queryAssArray('id', $queryPost);
ApiLogger::addLogVarExport('$queryPost END =>>>> ');


//print_r($statKurData);
//print_r($statPostData);
// START Объединяем массивы
foreach ($statKurData as $id => $value) {
    // #B_POST оборотка Почта
    if (!array_key_exists($id, $statPostData)) {
        $statPostData[$id] = array(
            'id' => $id,
            'B_POST' => 0,
            'B_POST_PREDOPLATA' => 0
        );
    }
    $statKurData[$id] += $statPostData[$id];
    unset($statPostData[$id]);
}
foreach ($statPostData as $id => $value) {
    // #B_POST оборотка Почта
    if (!array_key_exists($id, $statKurData)) {
        $statKurData[$id] = array(
            'id' => $id,
            'B_KUR' => 0,
            'B_POST_TOTAL' => 0
        );
    }
    $statKurData[$id] += $statPostData[$id];
}
// END Объединяем массивы
//print_r($statKurData);die;
//
// Ассоциативный массив условий: staff_id => % условия
$staffPayOptionAss = DB::queryAssData('id', 'PaymentOption', 'SELECT id, PaymentOption FROM coffee.Staff');
$staffPayBaseAss = DB::queryAssData('id', 'PaymentBase', 'SELECT id, PaymentBase FROM coffee.Staff');

$queryWorkAll = "SELECT
                    is_cold_staff_id as id,
                    DATE_FORMAT(`is_common_status_date`, '%Y-%m-%d') AS D,
                    count(DISTINCT id) as day_count
                FROM
                    staff_order
                WHERE " . str_replace('last_edit', 'is_cold_staff_id', $where) . " $whereCommonDate AND is_cold_staff_id > 0
                GROUP BY is_cold_staff_id, D
                HAVING day_count > 0
                ORDER BY is_cold_staff_id";
ApiLogger::addLogVarExport('$queryWorkAll START =>>>> ');
$statAllWorkRawData = DB::query($queryWorkAll);
ApiLogger::addLogVarExport('$queryWorkAll END =>>>> ');

$statAllWorkData = array();
foreach ($statAllWorkRawData as $rawItem) {
    $statAllWorkData[$rawItem['id']] = empty($statAllWorkData[$rawItem['id']]) ? 1 : $statAllWorkData[$rawItem['id']] + 1;
}

foreach ($statAllWorkData as $id => $countDays) {
    if (array_key_exists($id, $statKurData)) {
        $statKurData[$id]['D'] = $countDays;
    } else {
        $statKurData[$id] = array(
            'D' => $countDays
        );
    }
}

//print_r($statKurData);die;

foreach ($statKurData as $id => &$statItem) {
    $statItem['responsible_id'] = empty($GLOBAL_STAFF_RESPONSIBLE[$statItem['id']]) ? 0 : $GLOBAL_STAFF_RESPONSIBLE[$statItem['id']];

    // #B оборотка Курьерка
    $statItem['B_KUR'];
    // #B_POST оборотка Почта
    $statItem['B_POST'];
    // #B_BONUS_OBOROTKA Бонус оборотка
    $statItem['B_BONUS_OBOROTKA'] = $statItem['B_KUR'] + $statItem['B_POST_TOTAL'] + $statItem['B_POST_PREDOPLATA'];
    // #B оборотка общая
    $statItem['B'] = $statItem['B_KUR'] + $statItem['B_POST'];

    $statItem['D']; // #D количество рабочих дней

    $statItem['C'] = $staffPayOptionAss[$id]; // #C Условия (из карточки клиента тянутся)
    $statItem['E'] = $staffPayBaseAss[$id]; // #E Ставка
    // Получение % бонусов согласно установленным настройкам
    $statItem['F'] = empty($statItem['E']) ? getStaffResponsibleDiapazonKoef($statItem['id'], $bonusesSettings, $statItem['B']) : 0; // #F

    $statItem['G'] = round($statItem['B'] * $statItem['C'] / 100); // #G
    $statItem['H'] = $statItem['D'] * $statItem['E']; // #H
    $statItem['I'] = round($statItem['G'] * $statItem['F'] / 100); // #I

    $statItem['J'] = $statItem['G'] + $statItem['H'] + $statItem['I'];  // #J


    if ($statItem['B'] > 0) {
        $statItem['P'] = round($statItem['J'] / $statItem['B'] * 100, 2);  // #P
    }

    $statItem['staff_id'] = $statItem['id'];
    $statItem['id'] = $GLOBAL_STAFF_FIO[$statItem['id']];
    $statItem['responsible'] = $GLOBAL_STAFF_FIO[$statItem['responsible_id']];
}

ApiLogger::addLogVarExport('$statPostData');
ApiLogger::addLogVarExport($statPostData);

echo json_encode(array(
    'total' => count($statKurData),
    'data' => array_values($statKurData),
    'sqlKur' => $queryKur,
    'sqlPost' => $queryPost,
    'sqlWorkDays' => $queryWorkAll
));

