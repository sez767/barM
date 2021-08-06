<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
// collect request parameters
$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 400;
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
                if ($field == 'Groups') {
                    $having = " HAVING GROUP_CONCAT(DISTINCT d.name ORDER BY d.name ASC SEPARATOR ', ')
                                  LIKE '%" . $value . "%'";
                    Break;
                } else {
                    $qs .= " AND " . $field . " LIKE '" . $value . "%'";
                    Break;
                }
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
                if ($field == 'date_ch')
                    $field = 'b.`date`';
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

$query = "SELECT a.date as date_podtv,b.`set` as st_change,CONCAT(c.`FirstName`,' ',c.`LastName`) as fio_podtv,
 a.`to` as id, b.`date` as date_ch, b.`set` as fio_ch,CONCAT(d.`FirstName`,' ',d.`LastName`) as fio_ch,
e.kz_delivery as kz_deliv
 FROM ActionHistoryNew as a
LEFT JOIN ActionHistoryNew as b ON b.`to` = a.`to`
LEFT JOIN staff_order as e ON e.id = a.`to`
LEFT JOIN Staff as c ON c.id = a.`from`
LEFT JOIN Staff as d ON d.id = b.`from`
WHERE a.`set`='Подтвержден' AND a.was<>'Подтвержден'
	AND a.property='status' AND b.`set`<> 'Подтвержден'
    AND b.`property`= 'status' AND b.date>a.date
    AND a.date>NOW() - INTERVAL 1 month
    AND a.`from` NOT IN ('37239410','30224289','30356360','76488859','79319411','47671128') AND " . $where;
if ((int) $_GET['summ'] == 1)
    $query = "SELECT a.date as date_podtv,CONCAT(a.`set`,' - ',b.`set`) as st_change,CONCAT(c.`FirstName`,' ',c.`LastName`) as fio_podtv,
 a.`to` as id, b.`date` as date_ch, a.`set` as st_Was,CONCAT(d.`FirstName`,' ',d.`LastName`) as fio_ch,
e.kz_delivery as kz_deliv
 FROM ActionHistoryNew as a
LEFT JOIN ActionHistoryNew as b ON b.`to` = a.`to`
LEFT JOIN staff_order as e ON e.id = a.`to`
LEFT JOIN Staff as c ON c.id = a.`from`
LEFT JOIN Staff as d ON d.id = b.`from`
WHERE e.`status`='Подтвержден'
	AND a.property='price' AND b.`set`<> a.`set`
    AND b.`property`= 'price' AND b.date>a.date AND b.date>e.fill_date
    AND a.date>NOW() - INTERVAL 1 month AND " . $where;
$total_qury = $query;
if ($sort != "") {
    $query .= " ORDER BY " . $sort . " " . $dir;
} else {
    $query .= " ORDER BY a.date DESC ";
}
//$query.= " LIMIT " . $start . "," . $count;
//echo $query;
$rs = mysql_query($query);
$total_ = mysql_query($total_qury);
$total = mysql_num_rows($total_);
$arr = array();
while ($obj = mysql_fetch_object($rs)) {
    $arr[] = $obj;
}
echo '{"total":"' . $total . '","data":' . json_encode($arr) . '}';
?>
