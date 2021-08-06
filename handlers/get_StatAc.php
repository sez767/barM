<?php

session_start();
$ukr_ar = array('17729178', '26823714', '37239410', '30224289', '30356360', '76488859', '79319411', '47671128', '72480483', '80911164', '21630264', '44440873', '62443980', '77767205', '10578031');
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

//print_r($_REQUEST);die;

$sta_q = mysql_query(" SELECT id, CONCAT(FirstName,' ',LastName) AS fio FROM Staff  WHERE 1");
$staff_ar = array();
$curr = getCurrency(date('Y-m-d'), 'Currencys');

//$currency['kzg'] = (array) $curr['KGS'];
$currency['am'] = (array) $curr['AMD'];
$currency['ru'] = (array) $curr['RUB'];
$currency['uz'] = (array) $curr['UZS'];
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
//if(!isset($_REQUEST['p1'])) { $where1.= " AND country = 'KZ' "; $moneyWhere .=  " AND country = 'KZ' ";}
foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1': if (strlen($v)) {
                $where1 .= " AND country = '" . $v . "' ";
                $moneyWhere .= " AND country = '" . $v . "' ";
            } break;
        case 'p2': if (strlen($v)) {
                $where1 .= " AND fill_date > '" . $v . " 00:00:00' ";
                $moneyWhere .= " AND return_date > '" . $v . " 00:00:00' ";
            } break;
        case 'p3': if (strlen($v)) {
                $where1 .= " AND fill_date < '" . $v . " 23:59:59' ";
                $moneyWhere .= " AND return_date < '" . $v . " 23:59:59' ";
            } break;
        case 'p4': if (strlen($v)) {
                $where1 .= " AND offer = '" . $v . "' ";
                $moneyWhere .= " AND offer = '" . $v . "' ";
            } break;
    }
}

if (in_array($_SESSION['Logged_StaffId'], array(94448321))) {
    $where1 .= ' AND staff_id NOT IN (11111111, 22222222, 33333333, 55555555) AND date > "2018-03-21" AND kz_delivery = "Почта"';
    $moneyWhere .= ' AND staff_id NOT IN (11111111, 22222222, 33333333, 55555555) AND date > "2018-03-21" AND kz_delivery = "Почта"';
}

if (!strlen($where1)) {
//    START https://www.wunderlist.com/webapp#/tasks/4375129586
//    $where1 = " AND fill_date>'" . date('Y-m-d') . " 00:00:00' AND fill_date<'" . date('Y-m-d') . " 23:59:59' ";
    $where1 = " AND return_date>'" . date('Y-m-d') . " 00:00:00' AND return_date<'" . date('Y-m-d') . " 23:59:59' ";
//    END https://www.wunderlist.com/webapp#/tasks/4375129586


    $moneyWhere = " AND return_date>'" . date('Y-m-d') . " 00:00:00' AND return_date<'" . date('Y-m-d') . " 23:59:59' ";
} else {
    $where1 .= " ";
}

if (!isset($_REQUEST['p1'])) {
    $where1 .= " AND country = 'KZ' ";
    $moneyWhere .= " AND country = 'KZ' ";
}

if (!empty($_REQUEST['p100'])) {
    if ($_REQUEST['p100'] > 0) {
        $where1 .= " AND staff_id = '{$_REQUEST['p100']}' ";
        $moneyWhere .= " AND staff_id = '{$_REQUEST['p100']}' ";
    } else {
        $where1 .= " AND staff_id NOT IN (11111111, 22222222, 33333333, 55555555, 47369504) ";
        $moneyWhere .= " AND staff_id NOT IN (11111111, 22222222, 33333333, 55555555, 47369504) ";
    }
}

if (!empty($_REQUEST['p110'])) {
    $where1 .= " AND kz_delivery " . ($_REQUEST['p110'] == 'Вся курьерка' ? " != 'Почта'" : " = '{$_REQUEST['p110']}'");
    $moneyWhere .= " AND kz_delivery " . ($_REQUEST['p110'] == 'Вся курьерка' ? " != 'Почта'" : " = '{$_REQUEST['p110']}'");
}
if (!empty($_REQUEST['p120'])) {
    $staffIds = DB::queryFirstColumn('SELECT id FROM Staff WHERE Responsible = %i', $_REQUEST['p120']);
    $where1 .= ' AND last_edit IN (' . implode(', ', $staffIds) . ')';
    $moneyWhere .= ' AND last_edit IN (' . implode(', ', $staffIds) . ')';
}
$where .= $where1;
/* SUM(IF(send_status IN ('Оплачен'),1,0)) AS price_bablo,
  $pre_query1 = "SELECT SUM(IF(send_status IN ('Оплачен'),1,0)) AS price_bablo,
  SUM(IF(return_date<'2015-04-28 00:00:00',if(send_status IN ('Оплачен')
  AND package>1,total_price*0.03,0),if(send_status IN ('Оплачен') AND package>1,total_price*0.027,total_price*0.01))) AS data_bablo,
  SUM(IF(send_status IN ('Оплачен') AND package>1,total_price*0.027,0)) AS upsale_bablo,
  SUM(IF(send_status IN ('Оплачен') AND package<2,total_price*0.01,0)) AS one_bablo,
  SUM(IF(send_status IN ('Оплачен'),total_price,0)) AS bablo,
  last_edit
  FROM staff_order WHERE last_edit  ".$moneyWhere."   "; */
$news_ar = array();
$pre_quer = "SELECT COUNT(*) AS co, last_new
 FROM staff_order WHERE last_new AND " . $where . "   GROUP by last_new ";
//var_dump( $pre_quer);
$rs14 = mysql_query($pre_quer);
while ($obj14 = mysql_fetch_assoc($rs14)) {
    $news_ar[(int) $obj14['last_new']] = $obj14['co'];
}
$operator_cold = getStaffListByRole('operatorcold');
$operator_recovery = getStaffListByRole('operatorrecovery');
$operator_prem = array_merge(array_keys($operator_cold), array_keys($operator_recovery));

$pre_query1 = "SELECT if(send_status IN ('Оплачен'),1,0) AS price_bablo,
if(send_status IN ('Оплачен') AND (package>1 OR LENGTH(dop_tovar)>10),total_price * if(country='kzg',0.054,0.036),
if(send_status IN ('Оплачен'),total_price*if(country='kzg',0.0105,if(last_edit IN (" . IMPLODE($operator_prem, ',') . "),0.11,0.007)),0)) AS data_bablos,
if(send_status IN ('Оплачен') AND last_edit IN (" . IMPLODE($operator_prem, ',') . "),total_price * 0.11,
    if(send_status IN ('Оплачен') AND (total_price<=9999),total_price * 0.01,
        if(send_status IN ('Оплачен') AND total_price>9999 AND total_price<=19999,total_price * 0.03,
            if(send_status IN ('Оплачен') AND total_price>19999 AND total_price<=27999,total_price * 0.06,total_price * 0.09)))) AS data_bablo,
if(send_status IN ('Оплачен') AND ((package>1 AND LENGTH(dop_tovar)>10) OR package>2),total_price*if(country='kzg',0.054,if(last_edit IN (" . IMPLODE($operator_prem, ',') . "),0.11,0.036)),0) AS upbablo,
if(send_status IN ('Оплачен') AND ((package=0 AND LENGTH(dop_tovar)>10) OR (package=1 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.0105,if(last_edit IN (" . IMPLODE($operator_prem, ',') . "),0.11,0.007)),0) AS onebablo,
if(send_status IN ('Оплачен') AND ((package=1 AND LENGTH(dop_tovar)>10) OR (package=2 AND  LENGTH(dop_tovar)<10)),total_price*if(country='kzg',0.042,if(last_edit IN (" . IMPLODE($operator_prem, ',') . "),0.11,0.028)),0) AS twobablo,
if(send_status IN ('Оплачен') AND (package>1 OR LENGTH(dop_tovar)>1),total_price*if(country='kzg',0.054,if(last_edit IN (" . IMPLODE($operator_prem, ',') . "),0.11,0.036)),0) AS upsale_bablo,
if(send_status IN ('Оплачен') AND package<2,total_price*if(country='kzg',0.0105,if(last_edit IN (" . IMPLODE($operator_prem, ',') . "),0.11,0.007)),0) AS one_bablo,
if(send_status IN ('Оплачен'),price,0) AS bablo,
dop_tovar, package, country, total_price, offer, date,
last_edit
 FROM staff_order WHERE last_edit " . $moneyWhere . "   ";
//echo $pre_query1; die;
ApiLogger::addLogJson('');
ApiLogger::addLogJson('');
ApiLogger::addLogJson('$pre_query1');
ApiLogger::addLogJson('');
ApiLogger::addLogJson($pre_query1);


$rs11 = mysql_query($pre_query1);
$pre_ar = array();
$arr = array();
$itog_ar = array();
$itog_ar2 = array();
$country_ar = array();

$redis = RedisManager::getInstance()->getRedis();

$Percents = $redis->hGetAll('OperPays');
$Stats = $redis->hGetAll('OperStat');
$AVG = $redis->hGetAll('OperAVG');
$PercentsKG = $redis->hGetAll('OperPaysKG');
$StatsKG = $redis->hGetAll('OperStatKG');
$AVGKG = $redis->hGetAll('OperAVGKG');


$AllPercent = $redis->hGetAll('AVGP');

$PercentAKz = round($AllPercent[0] * 100);
$PercentAKg = round($AllPercent[1] * 100);
//var_dump($pre_query1); die;
while ($obj1 = mysql_fetch_assoc($rs11)) {
    if (isset($Percents[$obj1['last_edit']])) {
        $Percent = round(($Percents[$obj1['last_edit']]) * 100);
    } else {
        $Percent = $PercentAKz;
    }
    if (isset($Stats[$obj1['last_edit']])) {
        $Stat = $Stats[$obj1['last_edit']];
    } else {
        $Stat = 8000;
    }

    if (isset($AVG[$obj1['last_edit']])) {
        $AVGs = $AVG[$obj1['last_edit']];
    } else {
        $AVGs = 12000;
    }

    $itog_ar[$obj1['last_edit']]['stat'] = $Stat;

    if ($Percent > $PercentAKz) {
        $koef = 1 + ((($Percent - $PercentAKz) * 2) / 100);
    } else {
        $koef = 1 + ((($Percent - $PercentAKz) * 2) / 100);
    }


    if (isset($PercentsKG[$obj1['last_edit']])) {
        $PercentKG = round(($PercentsKG[$obj1['last_edit']]) * 100);
    } else {
        $PercentKG = $PercentAKg;
    }
    if (isset($StatsKG[$obj1['last_edit']])) {
        $StatKG = $StatsKG[$obj1['last_edit']];
    } else {
        $StatKG = 1000;
    }

    if (isset($AVGKG[$obj1['last_edit']])) {
        $AVGsKG = $AVGKG[$obj1['last_edit']];
    } else {
        $AVGsKG = 3000;
    }

    //$itog_ar[$obj1['last_edit']]['stat'] = $StatKG;

    if ($PercentKG > $PercentAKg) {
        $koefKG = 1 + ((($PercentKG - $PercentAKg) * 2) / 100);
    } else {
        $koefKG = 1 + ((($PercentKG - $PercentAKg) * 2) / 100);
    }

    $country_ar[$obj1['country']][] = $obj1['last_edit'];
    //var_dump($currency); die;
    if (isset($currency[$obj1['country']]) and $obj1['country'] <> 'kz') {
        $obj1['upsale_bablo'] = ($obj1['upsale_bablo'] * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant'];
        $obj1['one_bablo'] = ($obj1['one_bablo'] * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant'];
        //$obj1['news_bablo'] = ($obj1['data_bablo'] * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant'];
        $obj1['news_bablo'] = (($obj1['onebablo'] + $obj1['twobablo'] + $obj1['upbablo']) * $currency[$obj1['country']]['description']) / $currency[$obj1['country']]['quant'];

        if ($obj1['country'] == 'kzg') {
            if (strtotime($obj1['date']) > strtotime('2018-09-16') && in_array($obj1['offer'], array('black_latte_750', 'black_latte_850', 'nova_derm_porciya', 'tea_parazit_porciya', 'tea_prostatit_porciya', 'tea_sustav_porciya'))) {
                switch ($obj1['package']) {
                    case '0':
                    case '1': $perce = 0.05;
                        break;
                    case '2':
                    case '3':
                    case '4': $perce = 0.06;
                        break;
                    case '5': $perce = 0.07;
                        break;
                    default:
                        $perce = 0.07;
                        break;
                }
                //if($obj1['last_edit']=='31910933') var_dump(($currency[$obj1['country']]['description'] / $currency[$obj1['country']]['quant']) , $koefKG , $obj1['total_price'] , $perce );
                var_dump($koefKG,$perce,$currency); die;
                $pre_ar[$obj1['last_edit']]['news_bablo'] += round($koefKG * $obj1['total_price'] * $perce * ($currency[$obj1['country']]['description'] / $currency[$obj1['country']]['quant']));
                $itog_ar['new_bablo'] += round($koefKG * $obj1['total_price'] * $perce * ($currency[$obj1['country']]['description'] / $currency[$obj1['country']]['quant']));
            } else {
                $pre_ar[$obj1['last_edit']]['news_bablo'] += round($koefKG * $obj1['news_bablo']);
                $itog_ar['new_bablo'] += round($koefKG * $obj1['news_bablo']);
            }
        } else {
            $pre_ar[$obj1['last_edit']]['news_bablo'] += $obj1['news_bablo'];
            $itog_ar['new_bablo'] += $obj1['news_bablo'];
        }
    } else {
        $pre_ar[$obj1['last_edit']]['news_bablo'] += $koef * ($obj1['onebablo'] + $obj1['twobablo'] + $obj1['upbablo']);
        $itog_ar['new_bablo'] += round($koef * ($obj1['onebablo'] + $obj1['twobablo'] + $obj1['upbablo']));
        //$itog_ar['data_bablo'] += round($koef * $obj1['data_bablo']);
    }
    /* if((is_json($obj1['dop_tovar']) or $obj1['package']>1)){
      $pre_ar[$obj1['last_edit']]['data_bablo'] += $obj1['data_bablo'];
      }
      else */
    $pre_ar[$obj1['last_edit']]['data_bablo'] += $koef * $obj1['data_bablo'];
    $pre_ar[$obj1['last_edit']]['onebablo'] += $obj1['onebablo'];
    $pre_ar[$obj1['last_edit']]['twobablo'] += $obj1['twobablo'];
    $pre_ar[$obj1['last_edit']]['upbablo'] += $obj1['upbablo'];
    $pre_ar[$obj1['last_edit']]['Percent'] = $Percent;
    $pre_ar[$obj1['last_edit']]['AVG'] = $AVGs;
    $pre_ar[$obj1['last_edit']]['PercentKG'] = $PercentKG;
    $pre_ar[$obj1['last_edit']]['AVGKG'] = $AVGsKG;
    $pre_ar[$obj1['last_edit']]['price_bablo'] += $obj1['price_bablo'];
    //$pre_ar[$obj1['last_edit']]['data_bablo'] += $obj1['data_bablo'];
    $pre_ar[$obj1['last_edit']]['new_bablo'] += $obj1['upsale_bablo'] + $obj1['one_bablo'];
    $pre_ar[$obj1['last_edit']]['bablo'] += $obj1['bablo'];
    //$pre_ar[$obj1['last_edit']]['news_bablo'] += $obj1['news_bablo'];
    $itog_ar['price_bablo'] += $obj1['price_bablo'];
    $itog_ar['data_bablo'] += round($obj1['data_bablo']);
    //$itog_ar['news_bablo'] += round($obj1['onebablo']+$obj1['twobablo']+$obj1['upbablo']);
    //$itog_ar['new_bablo'] += round($pre_ar[$obj1['last_edit']]['news_bablo']);
    //$itog_ar['new_bablo'] += round($obj1['upsale_bablo'] + $obj1['one_bablo']);
    $itog_ar['bablo'] += $obj1['bablo'];
}
//var_dump($country_ar);
$query = "  SELECT  last_edit AS id,
                    SUM(IF(status IN ('Перезвонить'),1,0)) AS recall,
                    SUM(IF(status IN ('Недозвон'),1,0)) AS nocall,
                    SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),1,0)) AS accept,
                    SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),total_price,0)) AS accept_bablo,
                    sum(IF(status IN ('Подтвержден','Предварительно подтвержден') AND web_id<>'287',total_price,0)) AS avg_check,
                    max(IF(status IN ('Подтвержден','Предварительно подтвержден') AND web_id<>'287',total_price,0)) AS avg_checkm,
                    sum(IF(send_status = 'Отказ',1,0)) AS otkaz,
                    sum(IF(send_status = 'Оплачен',1,0)) AS vukup,
                    SUM(IF(status IN ('Отменён'),1,0)) AS cancel,
                    SUM(IF(status IN ('Брак'),1,0)) AS bad,
                    COUNT(*) AS `all`
            FROM staff_order WHERE last_edit AND " . $where;
//error_log($query);
$total_qury = $query;
$query .= " GROUP BY last_edit ";
if ($sort != '') {
    $query .= " ORDER BY $sort $dir";
} else {
    $query .= " ORDER BY accept asc ";
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
//var_dump($query); die;
$color_ar = array('#FFDFFF', '#FFf304', '#FFEA21');
$cc = 0;
$chart_ar = array();
while ($obj = mysql_fetch_object($rs)) {
    $itog_ar['recall'] += $obj->recall;
    $itog_ar['nocall'] += $obj->nocall;
    $itog_ar['accept'] += $obj->accept;
    $itog_ar['cancel'] += $obj->cancel;
    $itog_ar['vukup'] += $obj->vukup;
    $itog_ar['avg_check'] += $obj->avg_check;
    //$itog_ar['avg_checkm'] += $obj->avg_check;
    $itog_ar['otkaz'] += $obj->otkaz;
    $itog_ar['bad'] += $obj->bad;
    $itog_ar['all'] += $obj->all;
    $itog_ar['accept_bablo'] += $obj->accept_bablo;
    $chart_ar[$cc]['status'] = trim($staff_ar[$obj->id]);
    $chart_ar[$cc]['orders'] = (int) $obj->accept;
    $obj->avg_check = round($obj->avg_check / $obj->accept);
    if (isset($news_ar[(int) $obj->id])) {
        $obj->news = $news_ar[(int) $obj->id];
    } else {
        $obj->news = 0;
    }
    $obj->vukup = '<span style="background-color:' . (round(($obj->vukup / $obj->accept) * 100) < 50 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->vukup . ' </b> ' . round(($obj->vukup / $obj->accept) * 100, 2) . '%';
    $obj->recall = '<span style="background-color:' . (round(($obj->recall / $obj->all) * 100) > 30 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->recall . ' </b> ' . round(($obj->recall / $obj->all) * 100, 2) . '%';
    $obj->cancel = '<span style="background-color:' . (round(($obj->cancel / $obj->all) * 100) > 30 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->cancel . ' </b> ' . round(($obj->cancel / $obj->all) * 100, 2) . '%';
    $obj->nocall = '<span style="background-color:' . (round(($obj->nocall / $obj->all) * 100) > 30 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->nocall . ' </b> ' . round(($obj->nocall / $obj->all) * 100, 2) . '%';
    $obj->accept = '<span style="background-color:' . (round(($obj->accept / $obj->all) * 100) < 50 ? $color_ar[1] : '#FFF') . '"><b>' . $obj->accept . ' </b> ' . round(($obj->accept / $obj->all) * 100, 2) . '%';
    $obj->bad = '<b>' . $obj->bad . ' </b> ' . round(($obj->bad / $obj->all) * 100, 2) . '%';
    $obj->otkaz = $obj->otkaz . ' ' . round(($obj->otkaz / $obj->all) * 100, 2) . '%';
    $obj->bablo = $pre_ar[(int) $obj->id]['bablo'];
    if($obj->id=='72102581') { var_dump($obj); die;}
    if (in_array($obj->id, $country_ar['kzg'])) {
        $obj->stat = $itog_ar[(int) $obj->id]['statKG'] . ' (' . $pre_ar[(int) $obj->id]['PercentKG'] . '%) (' . $pre_ar[(int) $obj->id]['AVGKG'] . ')';
    } else {
        $obj->stat = $itog_ar[(int) $obj->id]['stat'] . ' (' . $pre_ar[(int) $obj->id]['Percent'] . '%) (' . $pre_ar[(int) $obj->id]['AVG'] . ')';
    }

    $obj->data_bablo = round($pre_ar[(int) $obj->id]['data_bablo']);
    if (in_array($_SESSION['Logged_StaffId'], $ukr_ar)) {
        $obj->new_bablo = round(round($pre_ar[(int) $obj->id]['news_bablo']) * 0.6);
    } else {
        $obj->new_bablo = round($pre_ar[(int) $obj->id]['news_bablo']);
    }
    $obj->price_bablo = $pre_ar[(int) $obj->id]['price_bablo'];
    $obj->whosets = $staff_ar[$obj->id];

    $cc++;
    $arr[] = $obj;
}
//var_dump($arr); die;
$chart2_ar = array();
$chart2_ar[0]['status'] = 'На отправку';
$chart2_ar[1]['status'] = 'Груз в дороге';
$chart2_ar[2]['status'] = 'Груз отгружен';
$chart2_ar[3]['status'] = 'Отправлен';
$chart2_ar[4]['status'] = 'Отказ';
$chart2_ar[5]['status'] = 'Оплачен';

$chart2_ar[0]['money'] = (int) @$itog_ar['price_otpravka'];
$chart2_ar[1]['money'] = (int) @$itog_ar['price_doroga'];
$chart2_ar[2]['money'] = (int) @$itog_ar['price_otgrujen'];
$chart2_ar[3]['money'] = (int) @$itog_ar['price_otpravlen'];
$chart2_ar[4]['money'] = (int) @$itog_ar['price_otkaz'];
$chart2_ar[5]['money'] = (int) @$itog_ar['price_bablo'];

$itog_ar['last_edit'] = '<b>Итого:</b>';
$itog_ar['vukup'] = '<b>' . $itog_ar['vukup'] . ' </b> ' . round(($itog_ar['vukup'] / $itog_ar['accept']) * 100, 2) . '%';
$itog_ar['avg_check'] = round($itog_ar['avg_check'] / $itog_ar['accept']);
$itog_ar['recall'] = '<b>' . $itog_ar['recall'] . ' </b> ' . round(($itog_ar['recall'] / $itog_ar['all']) * 100, 2) . '%';
$itog_ar['nocall'] = '<b>' . $itog_ar['nocall'] . ' </b> ' . round(($itog_ar['nocall'] / $itog_ar['all']) * 100, 2) . '%';
$itog_ar['accept'] = '<b>' . $itog_ar['accept'] . ' </b> ' . round(($itog_ar['accept'] / $itog_ar['all']) * 100, 2) . '%';

$itog_ar['bad'] = '<b>' . $itog_ar['bad'] . ' </b> ' . round(($itog_ar['bad'] / $itog_ar['all']) * 100, 2) . '%';
//var_dump($chart_ar); die;
$arr[] = $itog_ar;


echo json_encode(array(
    'total' => $total,
    'data' => $arr,
    'datas' => $chart_ar,
    'data2' => $chart2_ar,
    'sql1' => $pre_query1,
    'sql2' => $query,
    '' => $arr,
));

