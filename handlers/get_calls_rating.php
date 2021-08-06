<?php
require_once dirname(__FILE__) . "/../lib/db.php";

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}


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
// initialize variables
$where = ' 0 = 0 ';
$qs = '';

// loop through filters sent by client
if (is_array($filters)) {
    for ($i = 0; $i < count($filters); $i++) {
        $filter = $filters[$i];
        // assign filter data (location depends if encoded or not)
        if ($encoded) {
            $field = $filter['field'];
            $value = $filter['value'];
            $compare = isset($filter['comparison']) ? $filter['comparison'] : null;
            $filterType = $filter['type'];
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

        if ($field == 'id') {
            $field == 'a.id';
        }

        switch ($filterType) {
            case 'string':
                $qs .= " AND " . $field . " LIKE '%" . $value . "%'";
                break;
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
                        $qs .= " AND " . $field . " >= '" . $value . "' AND " . $field . " <= '" . substr($value, 0, 10) . " 23:59:59" . "'";
                        break;
                    case 'lt':
                        $qs .= " AND " . $field . " < '" . strtotime($value) . "'";
                        break;
                    case 'gt':
                        $qs .= " AND " . $field . " > '" . strtotime($value) . "'";
                        break;
                }
                break;
        }
    }
    $where .= $qs;
}

$query = "
	SELECT
		`cid`,
		`order_id`,
		`callid`,
		`sip_id`,
		`date`,
		`source`,
		`system`,
		`point`
	FROM
		`call_staff_order`
	WHERE source = 'IncomeTorgKZ' AND
		" . $where;

if ($sort != "") {
    $query .= " ORDER BY " . $sort . " " . $dir;
} else {
    $query .= " ORDER BY `cid` DESC ";
}

print "/* " . $query . " */";

$total_qury = $query;
$query .= " LIMIT " . $start . "," . $count;

$rs = mysql_query($query);
$total_ = mysql_query($total_qury);
$total = mysql_num_rows($total_);

$result = array();

while ($obj = mysql_fetch_object($rs)) {

    $url = 'http://call.baribarda.com/call.php?file=' . $obj->source . '/' . date('Ymd', strtotime($obj->date)) . '/' . $obj->callid . '&type=2';
    $headers = get_headers($url);
    $headers = json_encode($headers);
    $pre_he = explode("Content-Length: ", $headers);
    $pre_h = explode('"', $pre_he[1]);
    if (strlen($headers) > 20) {
        $obj->callid = '<a href="' . $url . '">' . $obj->callid . ' - ' . round($pre_h[0] / 3000) . '  &#9742</a>';
    }

    $obj->point = (int) $obj->point;
    $result[] = $obj;
}

// return response to client
echo json_encode(array(
    "data" => $result,
    "total" => $total
));
