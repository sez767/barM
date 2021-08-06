<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once (dirname(__FILE__) . "/../lib/class.staff.php");

//print_r($_REQUEST);die;

$ret = array();

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
$fqueArr = array();
// loop through filters sent by client
if (is_array($filters)) {
//    print_r($filters);die;
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
        $field = mysql_real_escape_string($field);
        $value = mysql_real_escape_string($value);
        $compare = mysql_real_escape_string($compare);
        $filterType = mysql_real_escape_string($filterType);

        if ($field == 'Group') {
            $field = 'Bonuses';
        }

        switch ($filterType) {
            case 'string':
                $value = trim($value);

                if (in_array($field, array('id'))) {
                    $tmpIds = explode(' ', $value);
                    $tmpIds[] = -1;
                    $tmpIds = array_diff($tmpIds, array(''));
                    $qs .= " AND `$field` IN ('" . implode("','", $tmpIds) . "')";
                } else {
                    $qs .= " AND `$field` LIKE '%$value%'";
                }
                Break;

            case 'list':
                if (strstr($value, ',')) {
                    $value = explode(',', $value);
                    foreach ($value AS $k => $userItem) {
                        if ($field == 'Level') {
                            $value[$k] = "'" . bindec($userItem) . "'";
                        } else {
                            $value[$k] = "'" . $userItem . "'";
                        }
                    }
                    $value = implode(',', $value);
                    if ($value) {
                        $qs .= " AND $field IN (" . $value . ')';
                    }
                } elseif (is_array($value)) {
                    $qs .= " AND $field IN (" . implode(',', $value) . ')';
                } else {
                    if ($field == 'Level') {
                        $qs .= " AND $field & $value ";
                    } elseif ($field == 'Location') {
                        $qs .= " AND " . $field . " LIKE '%" . $value . "%'";
                    } else {
                        if ($field == 'queues') {
                            $fque .= $value . ",";
                            $fqueArr[] = $value;
                        } else {
                            $qs .= " AND " . $field . " = '" . $value . "'";
                        }
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
                        $qs .= " AND " . $field . " < '" . $value . "'";
                        Break;
                    case 'gt':
                        $qs .= " AND " . $field . " > '" . $value . "'";
                        Break;
                }
                Break;
        }
    }
    $where .= $qs;
}

$adds = '';
if ((int) $_REQUEST['show_all']) {
    $adds .= 'AND Ban = 1';
} elseif ((int) $_REQUEST['show_staff']) {
    $adds .= 'AND Ban = 0';
} else {
    $adds .= 'AND Type = 1';
}

// Видимость только своих подопечных
$staff = new Staff($_SESSION['Logged_StaffId']);
if (array_key_exists($_SESSION['Logged_StaffId'], $GLOBAL_CURATOR_STAFF) && $staff->IsCurator) {
    $adds .= ' AND id IN (' . implode(', ', $GLOBAL_CURATOR_STAFF[$_SESSION['Logged_StaffId']]) . ')';
}
if (array_key_exists($_SESSION['Logged_StaffId'], $GLOBAL_RESPONSIBLE_STAFF) && $_SESSION['adminsales']) {
    $adds .= ' AND id IN (' . implode(', ', $GLOBAL_RESPONSIBLE_STAFF[$_SESSION['Logged_StaffId']]) . ')';
}

$query = "SELECT SQL_CALC_FOUND_ROWS * FROM Staff WHERE $where $adds";


ApiLogger::addLogJson('START');
ApiLogger::addLogVarExport($query);


ApiLogger::addLogJson($query);
if ($sort != '') {
    $query .= " ORDER BY $sort $dir";
} else {
    $query .= ' ORDER BY id DESC';
}
$query .= " LIMIT $start, $count";

$ret['sql'] = $query;

$arr = DB::query($query);
$total = DB::queryFirstField('SELECT FOUND_ROWS()');

asterisk_base();

//print_r($fqueArr);die('|');
$astData = DB::queryAssData('Sip', 'queues', 'SELECT RIGHT(membername, 4) AS Sip, GROUP_CONCAT(queue_name) AS queues FROM asterisk.queue_member_table GROUP BY RIGHT(membername, 4)');
foreach ($arr as $k => &$userItem) {
    // Очередя
    if (array_key_exists($userItem['Sip'], $astData)) {
        $userItem['queues'] = $astData[$userItem['Sip']];
    }
    // Права

    $levelArr = array();
    if ($userItem['Level'] & 1) {
        $levelArr[] = 'Администратор';
    }
    if ($userItem['Level'] & 2) {
        $levelArr[] = 'Логист';
    }
    if ($userItem['Level'] & 4) {
        $levelArr[] = 'Оператор';
    }
    if ($userItem['Level'] & 8) {
        $levelArr[] = 'Админ логистики';
    }
    if ($userItem['Level'] & 16) {
        $levelArr[] = 'Админ логистики Почта';
    }
    if ($userItem['Level'] & 32) {
        $levelArr[] = 'Контроль качества';
    }
    if ($userItem['Level'] & 64) {
        $levelArr[] = 'Город админ';
    }
    if ($userItem['Level'] & 128) {
        $levelArr[] = 'Логист почта';
    }
    if ($userItem['Level'] & 256) {
        $levelArr[] = 'Оператор входящей';
    }
    if ($userItem['Level'] & 512) {
        $levelArr[] = 'Холодный оператор';
    }
    if ($userItem['Level'] & 1024) {
        $levelArr[] = 'Оператор клиники';
    }
    if ($userItem['Level'] & 2048) {
        $levelArr[] = 'WhatsApp оператор';
    }
    if ($userItem['Level'] & 4096) {
        $levelArr[] = 'Админ продаж';
    }
    if ($userItem['Level'] & 8192) {
        $levelArr[] = 'Web-мастер';
    }
    if ($userItem['Level'] & 16384) {
        $levelArr[] = 'На опыте';
    }
    if ($userItem['Level'] & 32768) {
        $levelArr[] = 'Оператор посева';
    }
    if ($userItem['Level'] & 65536) {
        $levelArr[] = 'Оператор Бишкек';
    }
    if ($userItem['Level'] & 131072) {
        $levelArr[] = 'Бишкек логист';
    }
    if ($userItem['Level'] & 262144) {
        $levelArr[] = 'Бишкек Админ логистики';
    }
    if ($userItem['Level'] & 524288) {
        $levelArr[] = 'Оффлайн островок';
    }
    if ($userItem['Level'] & 1048576) {
        $levelArr[] = 'Логист предоплаты';
    }
    $userItem['Group'] = $userItem['Bonuses'];
    $userItem['Level'] = implode(', ', $levelArr);
}

$ret['total'] = $total;
$ret['data'] = $arr;

echo json_encode($ret);
