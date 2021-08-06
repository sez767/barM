<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$table_name = 'asterisk.cdr';
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
        if ($field == 'id')
            $field == 'a.id';
        switch ($filterType) {
            case 'string':

                $qs .= " AND " . $field . " LIKE '" . $value . "%'";
                Break;
            case 'list':
                if (strstr($value, ',')) {
                    $fi = explode(',', $value);
                    for ($q = 0; $q < count($fi); $q++) {
                        $fi[$q] = "'" . $fi[$q] . "'";
                    }
                    $value = implode(',', $fi);
                    $qs .= " AND " . $field . " IN (" . $value . ")";
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
                        $qs .= " AND " . $field . " >= '" . strtotime($value) . "' AND " . $field . " <= '" . substr($value, 0, 9) . " 23:59:59'";
                        Break;
                    case 'lt':
                        $qs .= " AND " . $field . " < '" . strtotime($value) . "'";
                        Break;
                    case 'gt':
                        $qs .= " AND " . $field . " > '" . strtotime($value) . "'";
                        Break;
                }
                Break;
        }
    }
    $where .= $qs;
}
$where1 = '';
$id = "DATE_FORMAT(calldate,'%Y-%m-%d')";
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');
foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1': if (strlen($v)) {
                switch ($v) {
                    case 'day': $id = " DATE_FORMAT(calldate,'%Y-%m-%d')";
                        break;
                    case 'month': $id = " DATE_FORMAT(calldate,'%Y-%m')";
                        break;
                    case 'oper': $id = " src ";
                        break;
                }
            }
            break;
        case 'p2': if (strlen($v))
                $start_date = $v;
            break;
        case 'p3': if (strlen($v))
                $end_date = $v;
            break;
    }
}
//if(!strlen($where1)) { $where1 = " AND staff_id = '17721023' AND date>'".date('Y-m-d')." 00:00:00' AND date<'".date('Y-m-d')." 23:59:59' "; $id = " DATE_FORMAT(date,'%Y-%m-%d') as ids  "; }
//$where .= $where1;
mysql_close();
$ext_db_old = asterisk_base();
$add_remedio = " dst LIKE '8*%' ";
$query = "SELECT COUNT(*) as count, " . $id . " as period,
			 SUM(billsec) as billsec,
			 ROUND(AVG(billsec)) as avg_call,
			 MAX(billsec) as max_call, MIN(billsec) as min_call,
			 SUM(duration) as duration,
			 MAX(duration-billsec) as max_hold_time, MIN(duration-billsec) as min_hold_time
				FROM " . $table_name . " WHERE " . $add_remedio . "
			 AND calldate > '" . $start_date . " 00:00:00' AND calldate < '" . $end_date . " 23:59:59'
			 AND disposition in ('ANSWERED')"; //error_log($query);
$query .= " GROUP by " . $id;
//echo $query; die;
$rs = mysql_query($query);
$arr = array();
$i = 0;
while ($obj = mysql_fetch_assoc($rs)) {

    $arr[$i] = $obj;
    $arr[$i]['price'] = '';
    $arr[$i]['billsec'] = pr_time($arr[$i]['billsec']);
    $arr[$i]['avg_call'] = pr_time($arr[$i]['avg_call']);
    $arr[$i]['duration'] = pr_time($arr[$i]['duration']);
    $i++;
}

echo '{"total":"1","data":' . json_encode($arr) . '}';
?>
