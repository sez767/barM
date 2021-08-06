<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}

require_once dirname(__FILE__) . "/../lib/db.php";

ApiLogger::addLogJson('==========================================');
ApiLogger::addLogJson('READ START');

// connect to asterisk database
asterisk_base();

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
$where = $whereIncome = '';
$qs = $qsIncome = '';

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

        switch ($filterType) {
            case 'string':
                if ($field === 'Groups') {
                    $having = " HAVING GROUP_CONCAT(DISTINCT d.name ORDER BY d.name ASC SEPARATOR ', ')
                              LIKE '%" . $value . "%'";
                } else if (in_array($field, array('dst', 'uniqueid'))) {
                    $qs .= " AND `$field` = '$value'";

                    // $qsIncome
                    switch ($field) {
                        case 'uniqueid':
                            $qsIncome .= "AND `callid` = '$value'";
                            break;
                        case 'dst':
                            $qsIncome .= "AND `queuename` = '$value'";
                            break;
                        default:
                            $qsIncome .= " AND `$field` = '$value'";
                            break;
                    }
                } else {
                    $qs .= " AND `$field` LIKE '%$value%'";
                    $qsIncome .= " AND `$field` LIKE '%$value%'";
                }
                break;
            case 'list':
                if (strstr($value, ',')) {
                    $fi = explode(',', $value);
                    for ($q = 0; $q < count($fi); $q++) {
                        $fi[$q] = "'" . $fi[$q] . "'";
                    }
                    $value = implode(',', $fi);
                    $qs .= " AND `$field` IN (" . $value . ")";
                } else {
                    $qs .= " AND `$field` = '" . $value . "'";
                }
                break;
            case 'boolean':
                $qs .= " AND `$field` = " . ($value);
                break;
            case 'numeric':
                switch ($compare) {
                    case 'eq':
                        $qs .= " AND `$field` = " . $value;
                        break;
                    case 'lt':
                        $qs .= " AND `$field` < " . $value;
                        break;
                    case 'gt':
                        $qs .= " AND `$field` > " . $value;
                        break;
                }
                break;
            case 'date':
                switch ($compare) {
                    case 'eq':
                        $qs .= " AND `$field` >= '" . $value . "' AND `$field` <= '" . substr($value, 0, 10) . " 23:59:59" . "'";
                        break;
                    case 'lt':
                        $qs .= " AND `$field` < '" . strtotime($value) . "'";
                        break;
                    case 'gt':
                        $qs .= " AND `$field` > '" . strtotime($value) . "'";
                        break;
                }
                break;
        }
    }
    $where .= $qs;
    $whereIncome .= $qsIncome;
}


//echo date('Y-m-d');die;

if (($where = trim($where))) {
    $where .= ' AND `calldate` > CURDATE() - INTERVAL 1 WEEK';
} else {
    $where = ' AND `calldate` > CURDATE() - INTERVAL 1 DAY';
}

$query = "SELECT SQL_CALC_FOUND_ROWS * FROM `cdr` WHERE 1 = 1 $where";

if (empty($sort)) {
    $query .= " ORDER BY `calldate` DESC ";
} else {
    $query .= " ORDER BY `$sort` $dir";
}
$query .= " LIMIT $start, $count";
//die($query);


$jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=queue_list', 360), true);
$cpa_ar = reset($jd_temp);
$jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=cold_statuses', 360), true);
$coldStatuses = reset($jd_temp);

$total = null;
// incoming calls
if (empty($_REQUEST['income'])) {
    ApiLogger::addLogJson("QUERY START: $query");
    $resultData = DB::queryAssArray('uniqueid', $query);
    ApiLogger::addLogJson("QUERY COMPLEATE");
} else {
    $query = "
	SELECT
		DATE_FORMAT(`time`, '%Y-%m-%d %H:%i:%s') + INTERVAL 0 HOUR AS `calldate`,
		IF (COUNT(*) > 2, 'ANSWERED', 'Не успел') AS `disposition`,
		`callid` AS `uniqueid`,
		CONCAT(`queuename`, '/', DATE_FORMAT(`time`, '%Y%m%d'), '/', `callid`) AS `userfield`,
		#MAX(`data3`) AS `duration`,
		`data3` AS `duration`,
                GROUP_CONCAT(IF(CHAR_LENGTH(data2) > 5
                        AND CHAR_LENGTH(data2) < 14,
                    data2,
                    '') SEPARATOR '') AS `src`,
		CONCAT('INC',' ',`queuename`) AS `dst`,
                queuename
	FROM
		`asterisk`.`queue_log`
 	WHERE   1 = 1 $whereIncome
                AND `time` > '" . date('Y-m-d') . "'
                #AND `time` > '" . date('Y-m-d', strtotime('-1 day')) . "'
                AND `queuename` LIKE 'Income%'
                #AND `queuename` IN ('" . implode("','", $cpa_ar) . "')
                #AND CHAR_LENGTH(data2) > 5
                #AND event = 'enterqueue'
        GROUP BY `callid`
	ORDER BY `time` DESC";

    $query .= " LIMIT $start, $count";
    ApiLogger::addLogJson("QUERY INCOME START: $query");
    $resultData = DB::queryAssArray('uniqueid', $query);
    ApiLogger::addLogJson("QUERY INCOME COMPLEATE");
    $total = 1000000;
    ApiLogger::addLogJson("RS: $rs");
}

if ($total === null) {
    ApiLogger::addLogJson("QUERY TOTAL START: 'SELECT FOUND_ROWS()");
    $total = DB::queryFirstField('SELECT FOUND_ROWS()');
    ApiLogger::addLogJson("QUERY TOTAL OK");
}

bari_base();
$callObjectHistoryObj = new CallObjectHistoryObj();
$hisotryData = array();
if (!empty($resultData)) {
    $qsHistory = "SELECT * FROM `{$callObjectHistoryObj->cGetTableName()}` WHERE `object_name` = 'StaffOrderObj' AND `uniqueid` IN %ls";
    $historyData = DB::queryAssArray('uniqueid', $qsHistory, array_keys($resultData));

    $qsCheked = "SELECT DISTINCT `call_id` FROM `ast_calldata` WHERE `call_id` IN %ls";
    $chekedData = DB::queryFirstColumn($qsCheked, array_keys($resultData));
}

//ApiLogger::echoLogVarExport($resultData);
//ApiLogger::echoLogVarExport($historyData);
//ApiLogger::addLogVarExport($resultData);

foreach ($resultData as $uniqueid => &$val) {
    // init START
    $val['src_orig'] = $val['src'];
    $val['src_clear'] = preg_replace('/\D/', '', $val['src']);
    $val['dst_clear'] = preg_replace('/\D/', '', $val['dst']);
    $sipHref = $sipView = '';
    // init END

    $val['country'] = strlen($val['src_clear']) == 12 ? 'kzg' : strlen($val['src_clear']) == 11 ? 'kz' : stripos($val['dst'], '33*') === 0 ? 'kzg' : stripos($val['dst'], '5*') === 0 ? 'kz' : 'kz';

    if (!empty($historyData[$uniqueid])) {
        $val['object_id'] = $historyData[$uniqueid]['object_id'];
        $val['data_int'] = $historyData[$uniqueid]['data_int'];
        $val['created_by'] = $historyData[$uniqueid]['created_by'];
        $val['created_at'] = $historyData[$uniqueid]['created_at'];

        $val['dobrik'] = 'dd1';
        $sipView = $val['object_id'];
        if (($prefPos = stripos($val['dst'], '*'))) {
            $sipHref = 'sip:' . $val['dst'];
        } elseif ($sipView) {
            $sipHref = 'sip:' . ($val['country'] == 'kz' ? '5*' : '33*') . $sipView;
        }
    }

    if (empty($sipHref) && ($prefPos = stripos($val['dst'], '*'))) {
        $val['dobrik'] = 'dd2';
        $sipView = mb_substr($val['dst'], $prefPos + 1);
        $sipHref = 'sip:' . $val['dst'];
    }

    if (empty($sipHref) && in_array(strlen($val['src_clear']), array(11, 12))) {
        $val['dobrik'] = 'dd3';
        $sipView = $val['country'] == 'kz' ? hide_phone('7' . substr($val['src_clear'], -10)) : $val['src_clear'];
        $sipHref = 'sip:' . ($val['country'] == 'kz' ? (mb_stripos(mb_strtolower($val['dst']), 'kgz') === false ? '3*' : '27*') : '24*') . $sipView;
    }

    if (empty($sipHref) && in_array(strlen($val['dst_clear']), array(11, 12))) {
        $val['dobrik'] = 'dd4';
        $sipView = $val['country'] == 'kz' ? hide_phone('7' . substr($val['dst_clear'], -10)) : $val['dst_clear'];
        $sipHref = 'sip:' . ($val['country'] == 'kz' ? (mb_stripos(mb_strtolower($val['dst']), 'kgz') === false ? '3*' : '27*') : '24*') . $sipView;
    }

    if (!empty($sipHref)) {
        $val['src'] = "<a href='$sipHref'>$sipView</a>";
    }

//    $query_checked = mysql_query("SELECT `ast_calldata`.`id` FROM `coffee`.`ast_calldata` WHERE `ast_calldata`.`call_id` = '" . mysql_real_escape_string($val['uniqueid']) . "' LIMIT 1", $db_link_ref);
//    $row_checked = mysql_fetch_array($query_checked, MYSQL_ASSOC);
//    $val['checked'] = (isset($row_checked['id']) ? true : false);
    $val['checked'] = false;
    if ($val['dst'] == 'TorgKZ_P') {
        $val['userfield'] = "TorgKZ_P/" . date('Ymd', strtotime($val['calldate'])) . "/" . $val['uniqueid'];
    }

    $val['checked'] = in_array($uniqueid, $chekedData);
}
//ApiLogger::echoLogJson($resultData);
//die;

echo json_encode(array(
    'total' => $total,
    'data' => array_values($resultData),
    'sql' => $query,
    'sqlHistory' => $qsHistory
));

ApiLogger::addLogJson('READ END');
ApiLogger::addLogJson('==========================================');
