<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

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
        switch ($filterType) {
            case 'string':

                if (stripos($value, ' ') && $field != 'send_status') {
                    $tmpIds = explode(' ', $value);
                    $tmpIds[] = -1;
                    $tmpIds = array_diff($tmpIds, array(''));
                    $qs .= " AND `$field` IN ('" . implode("','", $tmpIds) . "')";
                } else {
                    $qs .= " AND `$field` LIKE '%" . $value . "%'";
                }
                Break;

            case 'list':
                //var_dump($filter['data']);
                if ($value == 'Вся курьерка') {
                    $qs .= " AND kz_delivery != 'Почта' ";
                    Break;
                }
                if (strpos($value, '(') > 1) {
                    $sub_val = explode("(", $value);
                    $value = trim($sub_val[0]);
                }
                //var_dump($value);
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
$ext_db_old = ket_asterisk_base();
$query = "SELECT * FROM asterisk.ussd WHERE" . $where;
$total_qury = $query;
if ($sort != "") {
    $query .= " ORDER BY " . $sort . " " . $dir;
} else {
    $query .= " ORDER BY date DESC ";
}
$query .= " LIMIT " . $start . "," . $count;
//$ext1_link = mysql_connect('212.112.125.198:3306','bari','offroad159753');
//echo $query;
$rs = mysql_query($query, $ext3_link);
//var_dump($rs);
$total_ = mysql_query($total_qury, $ext3_link);
$total = mysql_num_rows($total_);

while ($obj = mysql_fetch_object($rs)) {
    $obj->text = base64_decode($obj->text);
    $arr[] = $obj;
}
echo '{"success":true,"total":"' . $total . '","data":' . json_encode($arr) . '}';
