<?php

header('Content-Type: application/json; charset=utf-8', true);

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
$redis = RedisManager::getInstance()->getRedis();

$operator_logist = $redis->hGetAll('operator_logist');
foreach ($operator_logist as $ok => $oname) {
    $staff_ar[$ok] = $oname;
}

ApiLogger::addLogVarExport($_REQUEST);
if (!empty($_REQUEST['json'])) {
    $_REQUEST = array_merge($_REQUEST, json_decode($_REQUEST['json'], true));
}
ApiLogger::addLogVarExport($_REQUEST);

$partnersAssArr = DB::queryAssData('id', 'partner_name', 'SELECT * FROM partners');

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
$where1 = "";
if ((int) $_GET['s']) {
    $tdate = 'fill_date';
} else if ((int) $_GET['d']) {
    $tdate = 'return_date';
} else {
    $tdate = 'date';
}
$offer = 'offer';
$group = 'offer';
if ($_REQUEST['p4'] == 'offers') {
    $tdate = 'fill_date';
}
$kostil_DIANA = "   SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),1,0)) AS accepted,
                    SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),price,0)) AS avg_check,";

foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1':
            if (strlen($v)) {
                $where1 .= " AND country = '" . $v . "' ";
            }
            break;
        case 'p2':
            if (strlen($v)) {
                $where1 .= " AND " . $tdate . " > '" . $v . " 00:00:00' ";
            }
            break;
        case 'p3':
            if (strlen($v)) {
                $where1 .= " AND " . $tdate . " < '" . $v . " 23:59:59' ";
            }
            break;
        case 'p4':
            if (strlen($v)) {
                if ((int) $_GET['s']) {
                    switch ($v) {
                        case 'offer':
                            $offer = 'offer';
                            $group = 'offer';
                            break;
                        case 'offer1':
                            $offer = 'offer';
                            $group = 'offer';
                            $where1 .= "AND offer NOT IN ('waist_trainer_1rub','upsize_1rub','ultra100_1rub','shirt-waist-trainer_1rub','serum_1rub',
					'original_parfume_1rub','Eco_Slim1Rub','altai_woman_1rub','epilage_1rub','gialuron_1rub','ultra100',
					'gidronex_1rub','healthyeyes_1rub','intoxic_1rub','lipocarnit_1rub','minoxidil_1rub','nubra-bust_1rub','zenzero_1rub') ";
                            break;
                        case 'offer2':
                            $offer = 'offer';
                            $group = 'offer';
                            $where1 .= "AND offer IN ('waist_trainer_1rub','upsize_1rub','ultra100_1rub','shirt-waist-trainer_1rub','serum_1rub',
                                        'original_parfume_1rub','Eco_Slim1Rub','altai_woman_1rub','epilage_1rub','gialuron_1rub','ultra100',
                                        'gidronex_1rub','healthyeyes_1rub','intoxic_1rub','lipocarnit_1rub','minoxidil_1rub','nubra-bust_1rub','zenzero_1rub') ";
                            break;
                        case 'day':
                            $offer = 'DATE_FORMAT(' . $tdate . ',"%Y-%m-%d") AS offer';
                            $group = 'DATE_FORMAT(' . $tdate . ',"%Y-%m-%d")';
                            break;
                        case 'month':
                            $offer = 'DATE_FORMAT(' . $tdate . ',"%Y-%m") AS offer';
                            $group = 'DATE_FORMAT(' . $tdate . ',"%Y-%m")';
                            break;
                        case 'web':
                            $offer = 'staff_id AS offer';
                            $group = 'staff_id';
                            break;
                        case 'webctr':
                            $offer = 'web_id AS offer';
                            $group = 'web_id';
                            break;
                        case 'curcity':
                            $offer = "CONCAT(kz_delivery,' ',status_cur) AS offer";
                            $group = 'kz_delivery,status_cur';
                            break;
                        case 'cpaweb':
                            $offer = "CONCAT(staff_id,' ',web_id) AS offer";
                            $group = 'staff_id,web_id';
                            break;
                        case 'offlog':
                            $offer = "kz_delivery AS offer";
                            $group = 'kz_delivery';
                            $where1 .= "AND date_delivery >= '" . date('Y-m-d') . " 00:00:00' AND date_delivery < '" . date('Y-m-d') . " 23:59:59' ";
                            break;
                        case 'offtov':
                            $offer = "offer AS offer";
                            $group = 'offer';
                            $where1 .= "AND date_delivery >= '" . date('Y-m-d') . " 00:00:00' AND date_delivery < '" . date('Y-m-d') . " 23:59:59' ";
                            break;
                        case 'delivery':
                            $offer = 'kz_delivery AS offer';
                            $group = 'kz_delivery';
                            $kostil_DIANA = "   SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),1,0)) AS accepted,
                                                SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),price,0)) AS avg_check,";
                            break;
                        case 'operator':
                            $offer = 'kz_operator AS offer';
                            $group = 'kz_operator';
                            break;
                        case 'group' :
                            $offer = '`Group` AS offer';
                            $group = '`Group` ';
                            break;
                        case 'group_cold' :
                            $offer = '`Group_cold` AS offer';
                            $group = '`Group_cold` ';
                            break;
                    }
                } else {
                    switch ($v) {
                        case 'offer':
                            $offer = 'offer';
                            $group = 'offer';
                            break;
                        case 'offers':
                            $offer = 'offer';
                            $group = 'offer';
                            break;
                        case 'offer1':
                            $offer = 'offer';
                            $group = 'offer';
                            $where1 .= "AND offer NOT IN ('waist_trainer_1rub','upsize_1rub','ultra100_1rub','shirt-waist-trainer_1rub','serum_1rub',
					'original_parfume_1rub','Eco_Slim1Rub','altai_woman_1rub','epilage_1rub','gialuron_1rub','ultra100',
					'gidronex_1rub','healthyeyes_1rub','intoxic_1rub','lipocarnit_1rub','minoxidil_1rub','nubra-bust_1rub','zenzero_1rub') ";
                            break;
                        case 'offer2':
                            $offer = 'offer';
                            $group = 'offer';
                            $where1 .= "AND offer IN ('waist_trainer_1rub','upsize_1rub','ultra100_1rub','shirt-waist-trainer_1rub','serum_1rub',
                                        'original_parfume_1rub','Eco_Slim1Rub','altai_woman_1rub','epilage_1rub','gialuron_1rub','ultra100',
                                        'gidronex_1rub','healthyeyes_1rub','intoxic_1rub','lipocarnit_1rub','minoxidil_1rub','nubra-bust_1rub','zenzero_1rub') ";
                            break;
                        case 'day':
                            $offer = 'DATE_FORMAT(' . $tdate . ',"%Y-%m-%d") AS offer';
                            $group = 'DATE_FORMAT(' . $tdate . ',"%Y-%m-%d")';
                            break;
                        case 'time':
                            $offer = 'DATE_FORMAT(' . $tdate . ',"%H") AS offer';
                            $group = 'DATE_FORMAT(' . $tdate . ',"%H")';
                            break;
                        case 'month':
                            $offer = 'DATE_FORMAT(' . $tdate . ',"%Y-%m") AS offer';
                            $group = 'DATE_FORMAT(' . $tdate . ',"%Y-%m")';
                            break;
                        case 'web':
                            $offer = 'staff_id AS offer';
                            $group = 'staff_id';
                            break;
                        case 'webctr':
                            $offer = 'web_id AS offer';
                            $group = 'web_id';
                            break;
                        case 'curcity':
                            $offer = "CONCAT(kz_delivery,' ',status_cur) AS offer";
                            $group = 'kz_delivery,status_cur';
                            break;
                        case 'offlog':
                            $offer = "kz_delivery AS offer";
                            $group = 'kz_delivery';
                            $where1 .= "AND date_delivery >= '" . date('Y-m-d') . " 00:00:00' AND date_delivery < '" . date('Y-m-d') . " 23:59:59' ";
                            break;
                        case 'offtov':
                            $offer = "offer AS offer";
                            $group = 'offer';
                            $where1 .= "AND date_delivery >= '" . date('Y-m-d') . " 00:00:00' AND date_delivery < '" . date('Y-m-d') . " 23:59:59' ";
                            break;
                        case 'cpaweb':
                            $offer = "CONCAT(staff_id,' ',web_id) AS offer";
                            $group = 'staff_id,web_id';
                            break;
                        case 'delivery':
                            $offer = 'kz_delivery AS offer';
                            $group = 'kz_delivery';
                            $kostil_DIANA = "   SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),1,0)) AS accepted,
                                                SUM(IF(status IN ('Подтвержден','Предварительно подтвержден'),price,0)) AS avg_check,";
                            break;
                        case 'group' :
                            $offer = '`Group` AS offer';
                            $group = '`Group` ';
                            break;
                        case 'group_cold' :
                            $offer = '`Group_cold` AS offer';
                            $group = '`Group_cold` ';
                            break;
                        case 'operator':
                            $offer = 'kz_operator AS offer';
                            $group = 'kz_operator';
                            break;
                    }
                }
            }
            break;
        case 'p5':
            if (strlen($v)) {
                $where1 .= " AND last_edit = '" . $v . "' ";
            }
            break;
        case 'p6':
            if (strlen($v)) {
                if ($v > 0) {
                    $where1 .= " AND staff_id = '" . $v . "' ";
                } elseif ($v < 0) {
                    $where1 .= " AND staff_id NOT IN (" . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ") ";
                }
            }
            break;
        case 'p7':
            if (strlen($v)) {
                if ($v == 'Почта') {
                    $where1 .= " AND kz_delivery = '" . $v . "' ";
                } else {
                    $where1 .= " AND kz_delivery != 'Почта'";
                }
            }
            break;
        case 'p8':
            if (strlen($v)) {
                $where1 .= " AND web_id = '" . $v . "' ";
            }
        case 'p9':
            if (is_array($v)) {
                if (!empty($v)) {
                    $where1 .= ' AND `Group` IN (' . implode(', ', $v) . ') ';
                }
            } else if (strlen($v)) {
                $where1 .= " AND `Group` = '" . $v . "' ";
            }
            break;
    }
}

if (!strlen($where1)) {
    $where1 = " AND " . $tdate . ">'" . date('Y-m-d') . " 00:00:00' AND " . $tdate . "<'" . date('Y-m-d') . " 23:59:59' ";
} else {
    $where1 .= "";
}
if (in_array($_SESSION['Logged_StaffId'], array(94448321))) {
    $where1 .= ' AND staff_id NOT IN (' . implode(', ', $GLOBAL_ALL_COLD_STAFF_ARR) . ') AND date > "2018-03-21" AND kz_delivery = "Почта"';
}
$where .= $where1;

$query = "SELECT id, " . $offer . ", kz_operator,
SUM(IF(status IN ('новая','Перезвонить','Недозвон'),1,0)) AS is_processed,
SUM(IF(status IN ('Перезвонить'),1,0)) AS recall,
SUM(IF(status IN ('Недозвон'),1,0)) AS nocall,
SUM(IF(status IN ('Недозвон_ночь'),1,0)) AS nocall_night,
SUM(IF(status_kz IN ('Обработка') AND status IN ('Подтвержден'),1,0)) AS statuskz_obr,
SUM(IF(status_kz IN ('Отложенная доставка') AND status IN ('Подтвержден'),1,0)) AS statuskz_otl,
SUM(IF(`status_kz` IN ('На доставку', 'Вручить подарок') AND status IN ('Подтвержден'),1,0)) AS statuskz_nad,
SUM(IF(status_kz IN ('Проблемный') AND status IN ('Подтвержден'),1,0)) AS statuskz_pro,
SUM(IF(status_kz IN ('Недозвон') AND status IN ('Подтвержден'),1,0)) AS statuskz_ned,
SUM(IF(status_kz IN ('Заберет') AND status IN ('Подтвержден'),1,0)) AS statuskz_zab,
SUM(IF(status_kz IN ('Упакован') AND status IN ('Подтвержден'),1,0)) AS statuskz_ypa,
SUM(IF(status_kz IN ('Хранение') AND status IN ('Подтвержден'),1,0)) AS statuskz_hra,
SUM(IF(status_kz IN ('Груз отгружен') AND status IN ('Подтвержден'),1,0)) AS statuskz_otg,
SUM(IF(status_kz IN ('Груз вручен') AND status IN ('Подтвержден'),1,0)) AS statuskz_vry,
SUM(IF(status_kz IN ('Груз в дороге') AND status IN ('Подтвержден'),1,0)) AS statuskz_vdo,
SUM(IF(status_kz IN ('Обратная доставка отправлена') AND status IN ('Подтвержден'),1,0)) AS statuskz_obr,

SUM(IF(send_status IN ('На отправку') AND status IN ('Подтвержден'),1,0)) AS price_otpravka,
SUM(IF(send_status IN ('Предоплата') AND status IN ('Подтвержден'),1,0)) AS price_predoplata,
SUM(IF(send_status IN ('Полная предоплата') AND status IN ('Подтвержден'),1,0)) AS full_predoplata,
SUM(IF(send_status IN ('Отказ-предоплата') AND status IN ('Подтвержден'),1,0)) AS otkaz_predoplata,
SUM(IF(status_kz IN ('Нет товара') AND status IN ('Подтвержден'),1,0)) AS price_net,
SUM(IF(send_status IN ('Отправлен') AND status IN ('Подтвержден'),1,0)) AS price_otpravlen,
SUM(IF(send_status IN ('Отказ')AND status IN ('Подтвержден'),1,0)) AS price_otkaz,
SUM(IF(send_status IN ('Оплачен') AND status IN ('Подтвержден'),1,0)) AS price_bablo,
SUM(IF(send_status IN ('Оплачен') AND status IN ('Подтвержден'),total_price,0)) AS all_bablo,
SUM(total_price) AS totalp,
SUM(IF(send_status IN ('Оплачен') AND status IN ('Подтвержден'),if(package>1 OR LENGTH(dop_tovar)>10,1,0),0)) AS price_many,
SUM(IF(send_status IN ('Оплачен') AND status IN ('Подтвержден'),if(package=1 AND LENGTH(dop_tovar)<10,1,0),0)) AS price_ones,
" . $kostil_DIANA . "
SUM(IF(status='Подтвержден',if(package>1 OR LENGTH(dop_tovar)>10,1,0),0)) AS many,
SUM(IF(status='Подтвержден',if(package=1 AND LENGTH(dop_tovar)<10,1,0),0)) AS ones,
SUM(IF(status IN ('Отменён'),1,0)) AS noaccepted,
SUM(IF(status IN ('Брак'),1,0)) AS bad,
COUNT(*) AS `all`
FROM staff_order WHERE " . $where;


$total_qury = $query;
if ((int) $_GET['s']) {// $query.= " GROUP BY kz_operator ";
    $query .= " GROUP BY " . $group . " ";
    if ($sort != "") {
        $query .= " ORDER BY " . $sort . " " . $dir;
    } else {
        $query .= " ORDER BY " . $group . " DESC ";
    }
} else {
    $query .= " GROUP BY " . $group . " ";
    if ($sort != "") {
        $query .= " ORDER BY " . $sort . " " . $dir;
    } else {
        $query .= " ORDER BY " . $group . " asc ";
    }
}
//echo $query;
$rs = mysql_query($query);
$total_ = mysql_query($total_qury);
$total = mysql_num_rows($total_);
$arr = array();
$summaryArr = array();
$ust = array('0' => 'Не указано', '1' => 'Доволен', '2' => 'Не доволен', '3' => 'Недозвон', '4' => 'Перезвонить');

$color_ar = array('#FCDFFF', '#F20403', '#F0EA21');
while ($obj = mysql_fetch_object($rs)) {
    if ($_REQUEST['p4'] == 'operator') {
        $obj->offer = $staff_ar[$obj->offer];
    }
    $obj->is_processed = (int) $obj->is_processed;
    $summaryArr['is_processed'][] = $obj->is_processed;

    $obj->recall = (int) $obj->recall;
    $summaryArr['recall'][] = $obj->recall;

    $obj->nocall = (int) $obj->nocall;
    $summaryArr['nocall'][] = $obj->nocall;

    $obj->nocall_night = (int) $obj->nocall_night;
    $summaryArr['nocall_night'][] = $obj->nocall_night;

    $obj->accepted = (int) $obj->accepted;
    $summaryArr['accepted'][] = $obj->accepted;
    $obj->kz_operator = $obj->accepted . ' (100%)';

    $obj->avg_check = (int) $obj->avg_check;
    $summaryArr['avg_check'][] = $obj->avg_check;

    $obj->ones = (int) $obj->ones;
    $summaryArr['ones'][] = $obj->ones;

    $obj->many = (int) $obj->many;
    $summaryArr['many'][] = $obj->many;

    $obj->noaccepted = (int) $obj->noaccepted;
    $summaryArr['noaccepted'][] = $obj->noaccepted;

    $obj->bad = (int) $obj->bad;
    $summaryArr['bad'][] = $obj->bad;

    $obj->all = (int) $obj->all;
    $summaryArr['all'][] = $obj->all;

    $obj->price_ones = (int) $obj->price_ones;
    $summaryArr['price_ones'][] = $obj->price_ones;

    $obj->price_many = (int) $obj->price_many;
    $summaryArr['price_many'][] = $obj->price_many;

    $obj->price_otpravka = (int) $obj->price_otpravka;
    $summaryArr['price_otpravka'][] = $obj->price_otpravka;

    $obj->price_predoplata = (int) $obj->price_predoplata;
    $summaryArr['price_predoplata'][] = $obj->price_predoplata;

    $obj->full_predoplata = (int) $obj->full_predoplata;
    $summaryArr['full_predoplata'][] = $obj->full_predoplata;

    $obj->otkaz_predoplata = (int) $obj->otkaz_predoplata;
    $summaryArr['otkaz_predoplata'][] = $obj->otkaz_predoplata;

    $obj->price_otpravlen = (int) $obj->price_otpravlen;
    $summaryArr['price_otpravlen'][] = $obj->price_otpravlen;

    $obj->price_otkaz = (int) $obj->price_otkaz;
    $summaryArr['price_otkaz'][] = $obj->price_otkaz;

    $obj->price_bablo = (int) $obj->price_bablo;
    $summaryArr['price_bablo'][] = $obj->price_bablo;

    $obj->all_bablo = (int) $obj->all_bablo;
    $summaryArr['all_bablo'][] = $obj->all_bablo;

    $obj->price_obr = (int) $obj->price_obr;
    $summaryArr['price_obr'][] = $obj->price_obr;

    $obj->price_hranenie = (int) $obj->price_hranenie;
    $summaryArr['price_hranenie'][] = $obj->price_hranenie;

    $obj->price_net = (int) $obj->price_net;
    $summaryArr['price_net'][] = $obj->price_net;

    if ($_REQUEST['p4'] == 'web' && isset($partnersAssArr[$obj->offer])) {
        $obj->offer = $partnersAssArr[$obj->offer];
    }

    $obj->kz_operator = $obj->accepted . ' (100%)';

    $obj->vukyp = $obj->price_many + $obj->price_ones;
    $summaryArr['vukyp'][] = $obj->vukyp;


    $obj->price_otpravka_per = ' </b> ' . round(($obj->price_otpravka / $obj->accepted) * 100, 2) . '%</span>';
    $obj->price_predoplata_per = ' </b> ' . round(($obj->price_predoplata / $obj->accepted) * 100, 2) . '%</span>';
    $obj->full_predoplata_per = ' </b> ' . round(($obj->full_predoplata / $obj->accepted) * 100, 2) . '%</span>';
    $obj->otkaz_predoplata_per = ' </b> ' . round(($obj->otkaz_predoplata / $obj->accepted) * 100, 2) . '%</span>';
    $obj->price_doroga_per = ' </b> ' . round(($obj->price_doroga / $obj->accepted) * 100, 2) . '%';
    $obj->price_otgrujen = '<b>' . @$obj->price_otgrujen . ' </b> ' . round((@$obj->price_otgrujen / @$obj->accepted) * 100, 2) . '%';
    $obj->price_otpravlen_per = ' </b> ' . round(($obj->price_otpravlen / $obj->accepted) * 100, 2) . '%';
    $obj->price_otkaz_per = ' </b> ' . round(($obj->price_otkaz / $obj->accepted) * 100, 2) . '%';
    $obj->price_bablo_per = ' </b> ' . round(($obj->price_bablo / $obj->accepted) * 100, 2) . '%';
    $obj->price_bablo_logist = $obj->price_bablo * 300;
    $obj->price_obr_per = ' </b> ' . round((@$obj->price_obr / @$obj->accepted) * 100, 2) . '%';
    $obj->price_hranenie = '<b>' . @$obj->price_hranenie . ' </b> ' . round((@$obj->price_hranenie / @$obj->accepted) * 100, 2) . '%';
    $obj->price_ypac = '<b>' . @$obj->price_ypac . ' </b> ' . round((@$obj->price_ypac / @$obj->accepted) * 100, 2) . '%';
    $obj->price_net = '<b>' . @$obj->price_net . ' </b> ' . round((@$obj->price_net / @$obj->accepted) * 100, 2) . '%';
    $obj->price_vry = '<b>' . @$obj->price_vry . ' </b> ' . round((@$obj->price_vry / @$obj->accepted) * 100, 2) . '%';
    $obj->price_zab = '<b>' . @$obj->price_zab . ' </b> ' . round((@$obj->price_zab / @$obj->accepted) * 100, 2) . '%';
    $obj->totalp = (int) $obj->totalp;
    $arr[] = $obj;
}

if (!empty($arr)) {
    foreach ($summaryArr as $key => &$value) {
        $value = array_sum($value);

        $keyName = $key . '_total';
        $arr[0]->$keyName = $value;
    }
}

///////////////////////////////////////////////////////////
$chart_ar = array();
$chart2_ar = array();

$chart_ar[0]['status'] = 'В обработке';
$chart_ar[1]['status'] = 'Подтвержден';
$chart_ar[2]['status'] = 'Отменён';
$chart_ar[3]['status'] = 'Брак';

$chart_ar[0]['orders'] = $summaryArr['is_processed'];
$chart_ar[1]['orders'] = $summaryArr['accepted'];
$chart_ar[2]['orders'] = $summaryArr['noaccepted'];
$chart_ar[3]['orders'] = $summaryArr['bad'];

$chart2_ar[0]['status'] = 'На отправку';
$chart2_ar[1]['status'] = 'Отправлен';
$chart2_ar[2]['status'] = 'Отказ';
$chart2_ar[3]['status'] = 'Оплачен';
$chart2_ar[4]['status'] = 'Нет товара';

$chart2_ar[0]['money'] = $summaryArr['price_otpravka'];
$chart2_ar[0]['money'] = $summaryArr['price_predoplata'];
$chart2_ar[0]['money'] = $summaryArr['full_predoplata'];
$chart2_ar[0]['money'] = $summaryArr['otkaz_predoplata'];
$chart2_ar[1]['money'] = $summaryArr['price_otpravlen'];
$chart2_ar[2]['money'] = $summaryArr['price_otkaz'];
$chart2_ar[3]['money'] = $summaryArr['price_bablo'];
$chart2_ar[4]['money'] = $summaryArr['price_net'];

//print_r($summaryArr);die;

echo json_encode(
        array(
            // StatStore
            'data' => $arr,
            'total' => $total,
            // StatUsageStore
            'datas' => $chart_ar,
            'totals' => $summaryArr['all'],
            // StatPaystore
            'data2' => $chart2_ar,
            'total2' => $summaryArr['accepted'],
            // DostStore
            'datad' => $arr,
            'totald' => $summaryArr['accepted'],
//            'sql' => $query
        )
);
