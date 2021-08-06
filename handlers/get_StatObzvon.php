<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$sta_q = mysql_query(" SELECT id, CONCAT(FirstName,' ',LastName) AS fio, if(Type=1,'Работает', 'Уволен') AS statusop FROM Staff  WHERE 1");
$staff_ar = array();
$staff_stat = array();
while ($rowa = mysql_fetch_array($sta_q)) {
    $staff_ar[$rowa['id']] = $rowa['fio'];
    $staff_stat[$rowa['id']] = $rowa['statusop'];
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
$where1 = "";
$phpwhere = '';
$phpwhere2 = '';
foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1': if (strlen($v)) {
                $where1 .= " AND country = '" . $v . "' ";
            } break;
        case 'p4': if (strlen($v)) {
                $where1 .= " AND last_edit = '" . $v . "' ";
            } break;
        case 'p5': if (strlen($v)) {
                $phpwhere .= $v;
            } break;
        case 'p6': if (strlen($v)) {
                $phpwhere2 .= $v;
            } break;
    }
}

/* if (!strlen($where1)) {
  $where1 = " AND return_date>'" . date('Y-m-d') . " 00:00:00' AND return_date<'" . date('Y-m-d') . " 23:59:59' ";

  } else {
  $where1 .= " AND fill_date>NOW() - INTERVAL 1 month AND fill_date<'" . date('Y-m-d') . " 23:59:59' ";
  }
 */
if (!isset($_REQUEST['p1'])) {
    $where1 .= " AND country = 'KZ' ";
}

$where .= $where1;

ApiLogger::addLogJson('');

$pre_ar = array();
$arr = array();
$itog_ar = array();
$itog_ar2 = array();

//var_dump($GLOBAL_RESPONSIBLE_STAFF); die;
$query = "  SELECT  last_edit AS id,
                    country,
                    SUM(IF(status='Подтвержден' AND send_status = 'Отправлен' AND status_kz = 'Обработка',1,0)) AS obr,
                    SUM(IF(status='Подтвержден' AND send_status = 'Отправлен' AND status_kz = 'Отложенная доставка',1,0)) AS od,
                    SUM(IF(status='Подтвержден' AND send_status = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND fill_date > CURDATE(),1,0)) AS express,
                    SUM(IF(status='Подтвержден' AND send_status = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND DATE_FORMAT(`date_delivery`, '%Y-%m-%d') = CURDATE(), 1, 0)) AS dostavka_now,
                    SUM(IF(status='Подтвержден' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND DATE_FORMAT(`date_delivery`, '%Y-%m-%d') = CURDATE() AND status_cur IN ('" . implode("', '", $GLOBAL_STATUS_CUR_OPLACHEN) . "'), 1, 0)) AS opl,
                    SUM(IF(status='Подтвержден' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND DATE_FORMAT(`date_delivery`, '%Y-%m-%d') = CURDATE() AND status_cur IN ('" . implode("', '", $GLOBAL_STATUS_CUR_OPLACHEN) . "'), total_price, 0)) AS bablo,
                    ROUND((SUM(IF(status='Подтвержден' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND DATE_FORMAT(`date_delivery`, '%Y-%m-%d') = CURDATE() AND status_cur IN ('" . implode("', '", $GLOBAL_STATUS_CUR_OPLACHEN) . "'), 1, 0)) / SUM(IF(status='Подтвержден' AND send_status = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND DATE_FORMAT(`date_delivery`, '%Y-%m-%d') = CURDATE(),1,0)))*100) AS vukup
            FROM staff_order
            WHERE last_edit AND status = 'Подтвержден' AND send_status = 'Отправлен' AND " . $where;

$total_qury = $query;
$query .= " GROUP BY last_edit ";
if ($sort != "") {
    $query .= " ORDER BY " . $sort . " " . $dir;
} else {
    $query .= " ORDER BY last_edit asc ";
}

ApiLogger::addLogJson('');
ApiLogger::addLogJson('');
ApiLogger::addLogJson('$query');
ApiLogger::addLogJson('');
ApiLogger::addLogJson($query);

//echo $query; die;
$rs = mysql_query($query);
$total_ = mysql_query($total_qury);
$total = mysql_num_rows($total_);

$resp_ar = array('56925807', '97137314', '87956601', '46584548', '70623931', '88189675', '98986784', '47447365', '78945378');
while ($obj = mysql_fetch_object($rs)) {
    //var_dump($GLOBAL_RESPONSIBLE_CURATOR[$GLOBAL_STAFF_RESPONSIBLE[$obj->id]],); die;
    $obj->operator = $staff_ar[$obj->id];
    $obj->obr = (int) $obj->obr;
    $obj->bablo = (int) $obj->bablo;
    $obj->od = (int) $obj->od;
    $obj->opl = (int) $obj->opl;
    $obj->express = (int) $obj->express;
    $obj->dostavka_now = (int) $obj->dostavka_now;
    $obj->zp = round($obj->bablo * 0.11);
    $obj->oper_status = $staff_stat[$obj->id];
    $obj->responsible = $staff_ar[$GLOBAL_STAFF_RESPONSIBLE[$obj->id]];
    $obj->curator = $staff_ar[$GLOBAL_RESPONSIBLE_CURATOR[$GLOBAL_STAFF_RESPONSIBLE[$obj->id]]];
    if (strlen($phpwhere))
        if (!in_array($GLOBAL_STAFF_RESPONSIBLE[$obj->id], $GLOBAL_RESPONSIBLE_STAFF[$phpwhere]))
            continue;
    if (strlen($phpwhere2))
        if ($GLOBAL_RESPONSIBLE_CURATOR[$GLOBAL_STAFF_RESPONSIBLE[$obj->id]] != $phpwhere2)
            continue;
    if (in_array($GLOBAL_STAFF_RESPONSIBLE[$obj->id], $resp_ar))
        continue;
    $arr[] = $obj;
}

//$arr[] = $itog_ar;


echo json_encode(array(
    'total' => $total,
    'data' => $arr,
    'sql' => $query,
));

