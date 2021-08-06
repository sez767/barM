<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}
require_once dirname(__FILE__) . '/../lib/db.php';

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
    $filters = json_decode($filters, true);
}

$filter_condition = '';
// loop through filters sent by client
if (is_array($filters)) {
    foreach ($filters AS $filter) {
        // assign filter data (location depends if encoded or not)
        if ($encoded) {
            $field = $filter['field'];
            $value = $filter['value'];
            $compare = isset($filter->comparison) ? $filter['comparison'] : null;
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

        if ($field == "offer_storage_name") {
            $field = "CONCAT(`offers`.`offer_desc`, ' ', `storage`.`property`)";
        }

        switch ($filterType) {
            case 'string':
                $filter_condition .= " AND " . $field . " LIKE '%" . $value . "%'";
                break;
            case 'list':
                if (strstr($value, ',')) {
                    $value = explode(",", $value);

                    foreach ($value AS $k => $v) {
                        if ($field == 'Level') {
                            $value[$k] = "'" . bindec($v) . "'";
                        } else {
                            $value[$k] = "'" . $v . "'";
                        }
                    }

                    $value = implode(',', $value);

                    if ($value) {
                        $filter_condition .= " AND " . $field . " IN (" . $value . ")";
                    }
                } elseif (is_array($value)) {
                    $filter_condition .= " AND " . $field . " IN (" . implode(',', $value) . ")";
                } else {
                    if ($field == 'Level') {
                        $filter_condition .= " AND " . $field . " = '" . bindec($value) . "'";
                    } elseif ($field == 'Location') {
                        $filter_condition .= " AND " . $field . " LIKE '%" . $value . "%'";
                    } else {
                        if ($field == 'queues') {
                            $fque .= $value . ",";
                        } else {
                            $filter_condition .= " AND " . $field . " = '" . $value . "'";
                        }
                    }
                }
                break;
            case 'boolean':
                $filter_condition .= " AND " . $field . " = " . ($value);
                break;
            case 'numeric':
                switch ($compare) {
                    case 'eq':
                        $filter_condition .= " AND " . $field . " = " . $value;
                        break;
                    case 'lt':
                        $filter_condition .= " AND " . $field . " < " . $value;
                        break;
                    case 'gt':
                        $filter_condition .= " AND " . $field . " > " . $value;
                        break;
                }
                break;
            case 'date':
                switch ($compare) {
                    case 'eq':
                        $filter_condition .= " AND " . $field . " >= '" . $value . "' AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                        break;
                    case 'lt':
                        $filter_condition .= " AND " . $field . " < '" . $value . "'";
                        break;
                    case 'gt':
                        $filter_condition .= " AND " . $field . " > '" . $value . "'";
                        break;
                }
                break;
        }
    }
}

// if (strlen($sort) > 0) {
// 	$sort = stripcslashes($sort);
// 	$sortt = json_decode($sort, true);
// 	$sort = $sortt[0];
// }

$dateStart = isset($_REQUEST['date_start']) ? date('Y-m-d', strtotime($_REQUEST['date_start'])) : date('Y-m-d');
$dateEnd = isset($_REQUEST['date_end']) ? date('Y-m-d', strtotime($_REQUEST['date_end'])) : date('Y-m-d', strtotime('+7 day'));

if (isset($_REQUEST['deliv_val'])) {
    $delivery = $_REQUEST['deliv_val'];
    $_SESSION['deliv_val'] = $_POST['deliv_val'];
} else {
    $delivery = 'Астана Курьер';
}

$query = "
    SELECT
        `storage`.`id` AS `id`,
        `storage`.`hash` AS `hash`,
        `storage`.`offer` AS `offer`,
        `storage`.`delivery` AS `delivery`,
        `storage`.`property` AS `property`,
        `offers`.`offer_desc` AS `title`
    FROM
        `storage`
    JOIN
        `offers` ON `offers`.`offer_name` = `storage`.`offer`
    WHERE
        `offers`.`offers_active` = 1 AND
        `storage`.`delivery` = '" . mysql_real_escape_string($delivery) . "'
        " . $filter_condition . "
";

$total_sql = $query;

if ($sort != "") {
    $query .= " ORDER BY `" . $sort['property'] . "` " . $sort['direction'];
} else {
    $query .= " ORDER BY `storage`.`date` DESC ";
}
$rs = mysql_query($query);

$total_query = mysql_query($total_sql);
$total = mysql_num_rows($total_query);

$data = $names = array();
$index = 0;

while ($obj = mysql_fetch_object($rs)) {
    $sql = 'select id, storage_id, action_type, action_value, staff_id, `datetime`, staff_offer_id, `deleted`, action_shit from `storage_action` where `deleted` = 0 and `storage_id` = ' . $obj->id . ' and datetime between \'' . ($dateStart . ' 00:00:00') . '\' AND \'' . ($dateEnd . ' 23:59:59') . '\';';

    $query = mysql_query($sql);

    $qqq = 'select action_value from storage_action where `deleted` = 0 and storage_id = ' . $obj->id . ' and action_type = \'rest_start\' and `datetime` <= \'' . ($dateStart . ' 00:00:00') . '\' order by `datetime` desc limit 1;';

    $sqlRestStart = mysql_query($qqq);
    $restStartQqq = mysql_fetch_assoc($sqlRestStart);
    $restStart = $restStartQqq['action_value'];

    $dayDiff = ceil((strtotime($dateEnd . ' 23:59:59') - strtotime($dateStart . ' 00:00:00')) / (60 * 60 * 24));
    $dayDiff = ($dayDiff < 1) ? 1 : $dayDiff;

    $obj->rest_start = $restStart;
    $obj->send = 0;
    $obj->return = 0;
    $obj->income = 0;
    $obj->write_off = 0;
    $obj->rest_end = 0;
    $obj->end_after = 0;

    if (mysql_num_rows($query) > 0) {
        while ($store = mysql_fetch_object($query)) {
            switch ($store->action_type) {
                case 'send':
                    $obj->send += $store->action_value;
                    break;
                case 'return':
                    $obj->return += $store->action_value;
                    break;
                case 'income':
                    $obj->income += $store->action_value;
                    break;
                case 'write_off':
                    $obj->write_off += $store->action_value;
                    break;
            }
        }
    }

    $obj->rest_end = $obj->rest_start - $obj->send + $obj->return;

    $obj->rest_end = $obj->rest_end + $obj->income - $obj->write_off;

    if (($obj->send - $obj->return) > 0) {
        $obj->end_after = round($obj->rest_end / (($obj->send - $obj->return) / $dayDiff));
    }

    $obj->rest_need = ((($obj->send - $obj->return) / $dayDiff) * 10) - $obj->rest_end;

    $obj->offer_storage_name = trim($obj->title . " " . $obj->property);

    $data[] = $obj;

    $names[$index] = strtolower($obj->offer_storage_name);
    $index++;
}

$array_lowercase = array_map('strtolower', $names);

//array_multisort($array_lowercase, SORT_ASC, SORT_NATURAL, $data);

print json_encode(array(
    "total" => $total,
    "data" => array_values($data)
));
