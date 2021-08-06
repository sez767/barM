<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

if ((int) $_GET['id']) {
    $result = '{"success":false}';
    $query = "SELECT * FROM offer_payment WHERE id_payment = '" . (int) $_GET['id'] . "'";
    $rs = mysql_query($query);
    $arr = array();
    while ($obj = mysql_fetch_object($rs)) {
        $tmpData = json_encode($obj);
        $tmpData = substr($tmpData, 1, strlen($tmpData) - 2); // strip the [ and ]
        $tmpData = str_replace("\\/", "/", '{"success":true,"data":' . $tmpData . '}'); // unescape the slashes
        $tmpData = '{"success":true,"data":' . json_encode($obj) . '}';
        $result = $tmpData;
    }
    echo $result;
} else {
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
    $query = "SELECT *,id_payment as id FROM offer_payment WHERE  $where";
    $total_qury = $query;
    if ($sort != "") {
        $query .= " ORDER BY " . $sort . " " . $dir;
    } else {
        $query .= " ORDER BY id_payment DESC";
    }
    $query .= " LIMIT " . $start . "," . $count;
    $rs = mysql_query($query);
    $total_ = mysql_query($total_qury);
    $total = mysql_num_rows($total_);
    $arr = array();
    while ($obj = mysql_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo json_encode(array(
        "data" => $arr,
        "total" => $total,
            // "sql" => $query
    ));
}
