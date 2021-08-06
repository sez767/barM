<?php

header('Content-Type: application/json; charset=utf-8', true);

session_start();

require_once dirname(__FILE__) . "/../lib/db.php";
require_once dirname(__FILE__) . "/../lib/class.staff.php";

// collect request parameters
$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
$sort = isset($_REQUEST['sort']) ? mysql_real_escape_string($_REQUEST['sort']) : '';
$dir = isset($_REQUEST['dir']) ? mysql_real_escape_string($_REQUEST['dir']) : 'DESC';
$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;

// GridFilters sends filters as an Array if not json encoded
if (is_array($filters)) {
    $encoded = false;
} else {
    $encoded = true;
    $filters = json_decode($filters, true);
}

// initialize variables
$where = ' 1 = 1 ';
$qs = '';

// loop through filters sent by client
if (is_array($filters)) {
    for ($i = 0; $i < count($filters); $i++) {
        $filter = $filters[$i];

        $field = $filter['field'];
        $value = $filter['data']['value'];
        $compare = isset($filter['data']['comparison']) ? $filter['data']['comparison'] : null;
        $filterType = $filter['data']['type'];

        $field = mysql_real_escape_string($field);
        $value = mysql_real_escape_string($value);
        $compare = mysql_real_escape_string($compare);
        $filterType = mysql_real_escape_string($filterType);

        switch ($filterType) {
            case 'string':
                $qs .= " AND `" . $field . "` LIKE '%" . $value . "%'";
                break;

            case 'list':
                if (strstr($value, ',')) {
                    $fi = explode(',', $value);
                    for ($q = 0; $q < count($fi); $q++) {
                        $fi[$q] = "'" . $fi[$q] . "'";
                    }

                    $value = implode(',', $fi);
                    $qs .= " AND " . $field . " IN (" . $value . ")";
                } elseif (is_array($value)) {
                    $qs .= " AND " . $field . " IN (" . implode(',', $value) . ")";
                } else {
                    $qs .= " AND " . $field . " = '" . $value . "'";
                }
                break;

            case 'boolean':
                $qs .= " AND " . $field . " = " . ($value);
                break;

            case 'numeric':
                switch ($compare) {
                    case 'eq':
                        $qs .= " AND " . $field . " = " . $value;
                        break;
                    case 'lt':
                        $qs .= " AND " . $field . " < " . $value;
                        break;
                    case 'gt':
                        $qs .= " AND " . $field . " > " . $value;
                        break;
                }
                break;

            case 'date':
                switch ($compare) {
                    case 'eq':
                        $qs .= " AND " . $field . " >= '" . $value . "' AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                        break;
                    case 'lt':
                        $qs .= " AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                        break;
                    case 'gt':
                        $qs .= " AND " . $field . " >= '" . $value . "'";
                        break;
                }
                break;
        }
    }

    $where .= $qs;
}

$sql = "
    SELECT SQL_CALC_FOUND_ROWS `uuid`
    FROM `staff_order`
    WHERE $where AND `status` = 'Подтвержден'
    GROUP BY `uuid`
    HAVING count(`id`) > 1
    LIMIT $start, $count";
//echo $sql;

$ordersIds = DB::queryOneColumn('uuid', $sql);
$total = DB::queryFirstField('SELECT FOUND_ROWS()');

$sqlClients = "
    SELECT
        `staff_order`.`id`,
        `staff_order`.`offer`,
        `offers`.`offer_desc` AS `offer_name`,
        `staff_order`.`uuid`,
        `staff_order`.`phone`,
        `staff_order`.`fio`,
        `staff_order`.`country`,
        `staff_order`.`status`,
        `staff_order`.`send_status`,
        `staff_order`.`status_kz`,
        `staff_order`.`staff_id`,
        `staff_order`.`fill_date`
    FROM
        `staff_order`
    LEFT JOIN
        `offers` ON `offers`.`offer_name` = `staff_order`.`offer`
    WHERE $where AND
        `staff_order`.`status` = 'Подтвержден' AND
        `staff_order`.`uuid` IN %li";

echo json_encode(array(
    "success" => true,
    "data" => DB::query($sqlClients, $ordersIds),
    "total" => $total
));
