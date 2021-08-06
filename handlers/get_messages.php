<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

// collect request parameters
$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
$dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'ASC';
$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
//$sort = mysql_real_escape_string($sort);
$sortik = json_decode($sort);
$sort = "`" . mysql_real_escape_string(@$sortik[0]->property) . "`";
$dir = mysql_real_escape_string(@$sortik[0]->direction);
if ($sort == '`type`') {
    $sort = "ActionHistoryNew.`type`";
}
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
        $field = "`$field`";
        if ($field == '`type`') {
            $field = "ActionHistoryNew.`type`";
        }
        if ($field == '`Message_UserId`') {
            $field = "a.`LastName`";
        }
        if ($field == '`Message_UserName`') {
            $field = "a.`FirstName`";
        }
        if ($field == '`Creator`') {
            $field = "b.`LastName`";
        }
        if ($field == '`CreatorName`') {
            $field = "b.`FirstName`";
        }
        switch ($filterType) {
            case 'string':
                $qs .= " AND " . $field . " LIKE '%" . $value . "%'";
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
                        $qs .= " AND " . $field . " = '" . date('Y-m-d', strtotime($value)) . "'";
                        Break;
                    case 'lt':
                        $qs .= " AND " . $field . " < '" . date('Y-m-d', strtotime($value)) . "'";
                        Break;
                    case 'gt':
                        $qs .= " AND " . $field . " > '" . date('Y-m-d', strtotime($value)) . "'";
                        Break;
                }
                Break;
        }
    }
    $where .= $qs;
}
if (!(int) $_SESSION['admin'])
    $who_query = "(a.id = '" . $_SESSION['Logged_StaffId'] . "' OR b.id='" . $_SESSION['Logged_StaffId'] . "') AND ";
else
    $who_query = '';
// query the database
$query = "SELECT
                    Message_Id
                    ,created_at
                    ,a.FirstName AS Message_UserName
                    ,b.LastName AS Creator
                    ,b.FirstName AS CreatorName
                    ,a.LastName AS Message_UserId
                    ,Message_Type
                    ,Message_Status
                    ,Message_ReadTimestamp
                    ,MessageTemplate_Header
                    ,Message_MessageTaskId
                FROM Message
                LEFT JOIN MessageTemplate on Message_MessageTemplateId = MessageTemplate_Id
                LEFT JOIN Staff as a on a.id = Message_UserId
                LEFT JOIN Staff as b on b.id = created_by
              WHERE " . $who_query . $where;
if ($sort != "``") {
    $query .= " ORDER BY " . $sort . " " . $dir;
}
$rs = mysql_query($query);
$total = mysql_num_rows($rs);
$query .= " LIMIT " . $start . "," . $count;
$rs = mysql_query($query);
$arr = array();
while ($obj = mysql_fetch_object($rs)) {
    $arr[] = $obj;
}
// return response to client
echo '{"total":"' . $total . '","data":' . json_encode($arr) . '}';
?>
