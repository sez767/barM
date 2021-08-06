<?php
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
if (1) {

    // collect request parameters
    $start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
    $count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
    $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'date';
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
                        $qs .= " AND " . $field . " LIKE '%" . $value . "%'";
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
                    switch ($compare) {
                        case 'eq':
                            $qs .= " AND " . $field . " >= '" . $value . "' AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59'";
                            Break;
                        case 'lt':
                            $qs .= " AND " . $field . " < '" . $value . "'";
                            Break;
                        case 'gt':
                            $qs .= " AND " . $field . " > '" . $value. "'";
                            Break;
                    }
                    Break;
            }
        }
        $where .= $qs;
    }

    $sip_where = "";

    // query the database
    $query = "SELECT * FROM `Stat_sip`
            WHERE " . $where . " " . $sip_where . " group by id ";
    if ($sort != "") {
        $query .= " ORDER BY " . $sort . " " . $dir;
    }
    //$total_qury = $query;
    $query .= " LIMIT " . $start . "," . $count;
    //echo $query;
    $rs = db_execute_query($query);
    $total = db_execute_query("SELECT COUNT(*) FROM `Stat_sip` WHERE " . $where . " " . $sip_where);
    //$total = db_execute_query($total_);
    //error_log($query);
    $total = mysql_result($total, 0, 0);
    $arr = array();
    while ($obj = mysql_fetch_object($rs)) {
        $arr[] = $obj;
    }
    // return response to client
    echo '{"total":"' . $total . '","data":' . json_encode($arr) . '}';
} else {
    echo "{'success':false}";
}
?>
