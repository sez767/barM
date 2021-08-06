<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$sta_q = mysql_query(" SELECT id, CONCAT(FirstName,' ',LastName) as fio FROM Staff  WHERE 1");
$staff_ar = array();

$redis = RedisManager::getInstance()->getRedis();

$operator_logist = $redis->hGetAll('operator_logist');
while ($rowa = mysql_fetch_array($sta_q)) {
    $staff_ar[$rowa['id']] = $rowa['fio'];
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
        if ($field == 'id') {
            $field == 'a.id';
        }
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
$moneyWhere = "";
foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1':
            if (strlen($v)) {
                $where1 .= " AND country = '" . $v . "' ";
                $moneyWhere .= " AND country = '" . $v . "' ";
            }
            break;
        case 'p2':
            if (strlen($v)) {
                $where1 .= " AND return_date > '" . $v . " 00:00:00' ";
                $moneyWhere .= " AND return_date > '" . $v . " 00:00:00' ";
            }
            break;
        case 'p3':
            if (strlen($v)) {
                $where1 .= " AND return_date < '" . $v . " 23:59:59' ";
                $moneyWhere .= " AND return_date < '" . $v . " 23:59:59' ";
            }
            break;
        //case 'p4': if(strlen($v)) { $where1.= " AND offer = '".$v."' "; $moneyWhere .=  " AND offer = '".$v."' ";} break;
        case 'p4':
            if ((int) $v) {
                $where1 .= " AND kz_operator = '" . $v . "' ";
                $moneyWhere .= " AND kz_operator = '" . $v . "' ";
            }
            break;
        case 'p5':
            if ((int) $v) {
                $where1 .= " AND kz_admin = '" . $v . "' ";
                $moneyWhere .= " AND kz_admin = '" . $v . "' ";
            }
            break;
        case 'p6':
            $zp = (int) $v;
            break;
        case 'p7':
            if ($v == 'Почта') {
                $where1 .= " AND kz_delivery = '" . $v . "' ";
                $moneyWhere .= " AND kz_delivery = '" . $v . "' ";
            } else {
                $where1 .= " AND kz_delivery != 'Почта' ";
                $moneyWhere .= " AND kz_delivery != 'Почта' ";
            }
            break;
    }
}
if (!strlen($where1)) {
    $where1 = " AND return_date>'" . date('Y-m-d') . " 00:00:00' AND return_date<'" . date('Y-m-d') . " 23:59:59' ";
    $moneyWhere = " AND return_date>'" . date('Y-m-d') . " 00:00:00' AND return_date<'" . date('Y-m-d') . " 23:59:59' ";
} else
    $where1 .= " ";
$where .= $where1;
/* SUM(if(send_status IN ('Оплачен'),1,0)) as price_bablo,
  $pre_query1 = "SELECT SUM(if(send_status IN ('Оплачен'),1,0)) as price_bablo,
  SUM(if(return_date<'2015-04-28 00:00:00',if(send_status IN ('Оплачен')
  AND package>1,price*0.03,0),if(send_status IN ('Оплачен') AND package>1,price*0.027,price*0.01))) as data_bablo,
  SUM(if(send_status IN ('Оплачен') AND package>1,price*0.027,0)) as upsale_bablo,
  SUM(if(send_status IN ('Оплачен') AND package<2,price*0.01,0)) as one_bablo,
  SUM(if(send_status IN ('Оплачен'),price,0)) as bablo,
  last_edit_kz
  FROM staff_order WHERE last_edit_kz  ".$moneyWhere."   "; */
$pre_query1 = "SELECT if(send_status IN ('Оплачен'),1,0) as price_bablo,
if(return_date<'2015-04-28 00:00:00',if(send_status IN ('Оплачен') AND package>1,price*0.03,0),
if(send_status IN ('Оплачен') AND package>1,price*0.027,price*0.01)) as data_bablo,
if(send_status IN ('Оплачен') AND package>1,price*0.027,0) as upsale_bablo,
if(send_status IN ('Оплачен') AND package<2,price*0.01,0) as one_bablo,
if(send_status IN ('Оплачен'),price,0) as bablo,
dop_tovar, package,
kz_operator
 FROM staff_order WHERE kz_operator " . $moneyWhere . "   ";

$rs11 = mysql_query($pre_query1);
$pre_ar = array();
$arr = array();
$itog_ar = array();
$itog_ar2 = array();

while ($obj1 = mysql_fetch_assoc($rs11)) {


    if ((isJson($obj1['dop_tovar']) or $obj1['package'] > 1)) {
        $pre_ar[$obj1['kz_operator']]['data_bablo'] += $obj1['bablo'] * 0.027;
    } else
        $pre_ar[$obj1['kz_operator']]['data_bablo'] += $obj1['bablo'] * 0.01;

    $pre_ar[$obj1['kz_operator']]['price_bablo'] += $obj1['price_bablo'];
    //$pre_ar[$obj1['last_edit_kz']]['data_bablo'] += $obj1['data_bablo'];
    $pre_ar[$obj1['kz_operator']]['new_bablo'] += $obj1['upsale_bablo'] + $obj1['one_bablo'];
    $pre_ar[$obj1['kz_operator']]['bablo'] += $obj1['bablo'];
    $itog_ar['price_bablo'] += $obj1['price_bablo'];
    $itog_ar['data_bablo'] += round($obj1['data_bablo']);
    $itog_ar['new_bablo'] += round($obj1['upsale_bablo'] + $obj1['one_bablo']);
    $itog_ar['bablo'] += $obj1['bablo'];
}
//var_dump($pre_ar);
$query = "SELECT kz_operator as id,
SUM(if(status IN ('Перезвонить'),1,0)) as recall,
SUM(if(status IN ('Недозвон'),1,0)) as nocall,
SUM(if(status IN ('Подтвержден'),1,0)) as accept,
SUM(if(status IN ('Подтвержден'),price,0)) as accept_bablo,
sum(IF(status='Подтвержден',price,0)) as avg_check,
sum(IF(send_status='Отказ',1,0)) as otkaz,
sum(IF(send_status='Оплачен',1,0)) as opl,
sum(IF(status='Подтвержден',price,0)) as avg_check,
SUM(if(status IN ('Отменён'),1,0)) as cancel,
SUM(if(status IN ('Брак'),1,0)) as bad,
COUNT(*) as `all`
FROM staff_order WHERE kz_operator AND " . $where;
//error_log($query);
$total_qury = $query;
$query .= " GROUP BY kz_operator ";
if ($sort != "") {
    $query .= " ORDER BY " . $sort . " " . $dir;
} else {
    $query .= " ORDER BY accept asc ";
}
//echo $query;
$rs = mysql_query($query);
$total_ = mysql_query($total_qury);
$total = mysql_num_rows($total_);
//var_dump($query);
$color_ar = array('#FFDFFF', '#FFf304', '#FFEA21');
$cc = 0;
$chart_ar = array();
while ($obj = mysql_fetch_object($rs)) {
    $itog_ar['recall'] += $obj->recall;
    $itog_ar['nocall'] += $obj->nocall;
    $itog_ar['accept'] += $obj->accept;
    $itog_ar['cancel'] += $obj->cancel;
    $itog_ar['avg_check'] += $obj->avg_check;
    $itog_ar['otkaz'] += $obj->otkaz;
    $itog_ar['opl'] += $obj->opl;
    $itog_ar['bad'] += $obj->bad;
    $itog_ar['all'] += $obj->all;
    $itog_ar['accept_bablo'] += $obj->accept_bablo;
    $chart_ar[$cc]['status'] = trim($staff_ar[$obj->id]);
    $chart_ar[$cc]['orders'] = (int) $obj->accept;
    $obj->avg_check = round($obj->avg_check / $obj->accept);
    $obj->recall = '<span style="background-color:' . (round(($obj->recall / $obj->all) * 100) > 30 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->recall . ' </b> ' . round(($obj->recall / $obj->all) * 100, 2) . '%';
    $obj->cancel = '<span style="background-color:' . (round(($obj->cancel / $obj->all) * 100) > 30 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->cancel . ' </b> ' . round(($obj->cancel / $obj->all) * 100, 2) . '%';
    $obj->nocall = '<span style="background-color:' . (round(($obj->nocall / $obj->all) * 100) > 30 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->nocall . ' </b> ' . round(($obj->nocall / $obj->all) * 100, 2) . '%';
    $obj->accept = '<span style="background-color:' . (round(($obj->accept / $obj->all) * 100) < 50 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->accept . ' </b> ' . round(($obj->accept / $obj->all) * 100, 2) . '%';
    $obj->bad = '<b>' . $obj->bad . ' </b> ' . round(($obj->bad / $obj->all) * 100, 2) . '%';
    $obj->otkaz = $obj->otkaz . ' ' . round(($obj->otkaz / $obj->all) * 100, 2) . '%';
    $obj->opl = $obj->opl . ' ' . round(($obj->opl / $obj->all) * 100, 2) . '%';
    $obj->bablo = $pre_ar[(int) $obj->id]['bablo'];
    $obj->data_bablo = round($pre_ar[(int) $obj->id]['data_bablo']);
    $obj->new_bablo = round($pre_ar[(int) $obj->id]['new_bablo']);
    $obj->price_bablo = $pre_ar[(int) $obj->id]['price_bablo'];
    $obj->whosets = $operator_logist[$obj->id];

    $cc++;
    $arr[] = $obj;
}
//var_dump($arr);
$chart2_ar = array();
$chart2_ar[0]['status'] = 'На отправку';
$chart2_ar[1]['status'] = 'Груз в дороге';
$chart2_ar[2]['status'] = 'Груз отгружен';
$chart2_ar[3]['status'] = 'Отправлен';
$chart2_ar[4]['status'] = 'Отказ';
$chart2_ar[5]['status'] = 'Оплачен';

$chart2_ar[0]['money'] = (int) $itog_ar['price_otpravka'];
$chart2_ar[1]['money'] = (int) $itog_ar['price_doroga'];
$chart2_ar[2]['money'] = (int) $itog_ar['price_otgrujen'];
$chart2_ar[3]['money'] = (int) $itog_ar['price_otpravlen'];
$chart2_ar[4]['money'] = (int) $itog_ar['price_otkaz'];
$chart2_ar[5]['money'] = (int) $itog_ar['price_bablo'];

$itog_ar['last_edit_kz'] = '<b>Итого:</b>';
$itog_ar['avg_check'] = round($itog_ar['avg_check'] / $itog_ar['accept']);
$itog_ar['recall'] = '<b>' . $itog_ar['recall'] . ' </b> ' . round(($itog_ar['recall'] / $itog_ar['all']) * 100, 2) . '%';
$itog_ar['nocall'] = '<b>' . $itog_ar['nocall'] . ' </b> ' . round(($itog_ar['nocall'] / $itog_ar['all']) * 100, 2) . '%';
$itog_ar['accept'] = '<b>' . $itog_ar['accept'] . ' </b> ' . round(($itog_ar['accept'] / $itog_ar['all']) * 100, 2) . '%';
$itog_ar['bad'] = '<b>' . $itog_ar['bad'] . ' </b> ' . round(($itog_ar['bad'] / $itog_ar['all']) * 100, 2) . '%';
//var_dump($chart_ar);
$arr[] = $itog_ar;
echo '{"total":"' . $total . '","data":' . json_encode($arr) . ',"datas":' . json_encode($chart_ar) . ',"data2":' . json_encode($chart2_ar) . ',"datad":' . json_encode($arr) . '}';
