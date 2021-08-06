<?php
//die(print_r($_REQUEST, true));

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}

require_once dirname(__FILE__) . '/../lib/db.php';

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

$where1 = "";

if ((int) $_GET['s']) {
    $tdate = 'fill_date';
} else {
    $tdate = 'date';
}

$offer = 'offer';
$group = 'offer';

foreach ($_REQUEST as $k => $v) {
    switch ($k) {
        case 'p1': if (strlen($v))
                $where1 .= " AND country = '" . $v . "' ";
            break;
        case 'p2': if (strlen($v))
                $where1 .= " AND " . $tdate . " > '" . $v . " 00:00:00' ";
            break;
        case 'p3': if (strlen($v))
                $where1 .= " AND " . $tdate . " < '" . $v . " 23:59:59' ";
            break;
        case 'p4': if (strlen($v)) {
                if ((int) $_GET['s']) {
                    switch ($v) {
                        case 'offer': $offer = 'offer';
                            $group = 'offer';
                            break;
                        case 'day': $offer = 'DATE_FORMAT(fill_date,"%Y-%m-%d") as offer';
                            $group = 'DATE_FORMAT(fill_date,"%Y-%m-%d")';
                            break;
                        case 'month': $offer = 'DATE_FORMAT(fill_date,"%Y-%m") as offer';
                            $group = 'DATE_FORMAT(fill_date,"%Y-%m")';
                            break;
                        case 'web': $offer = 'staff_id as offer';
                            $group = 'staff_id';
                            break;
                        case 'webctr': $offer = 'web_id as offer';
                            $group = 'web_id';
                            break;
                        case 'curcity': $offer = "CONCAT(kz_delivery,' ',status_cur) as offer";
                            $group = 'kz_delivery,status_cur';
                            break;
                        case 'delivery':$offer = 'kz_delivery as offer';
                            $group = 'kz_delivery';
                            break;
                    }
                } else {
                    switch ($v) {
                        case 'offer': $offer = 'offer';
                            $group = 'offer';
                            break;
                        case 'day': $offer = 'DATE_FORMAT(date,"%Y-%m-%d") as offer';
                            $group = 'DATE_FORMAT(date,"%Y-%m-%d")';
                            break;
                        case 'month': $offer = 'DATE_FORMAT(date,"%Y-%m") as offer';
                            $group = 'DATE_FORMAT(date,"%Y-%m")';
                            break;
                        case 'web': $offer = 'staff_id as offer';
                            $group = 'staff_id';
                            break;
                        case 'webctr': $offer = 'web_id as offer';
                            $group = 'web_id';
                            break;
                        case 'curcity': $offer = "CONCAT(kz_delivery,' ',status_cur) as offer";
                            $group = 'kz_delivery,status_cur';
                            break;
                        case 'delivery':$offer = 'kz_delivery as offer';
                            $group = 'kz_delivery';
                            break;
                    }
                }
            }
            break;
        case 'p5':
            if (strlen($v)) {
                if ($v == 'Почта') {
                    $where1 .= " AND kz_delivery = '" . $v . "' ";
                } else {
                    $where1 .= " AND kz_delivery != 'Почта'";
                }
            }
            break;
    }
}

if (!strlen($where1)) {
    $where1 = " AND " . $tdate . ">'" . date('Y-m-d') . " 00:00:00' AND " . $tdate . "<'" . date('Y-m-d') . " 23:59:59' ";
} else {
    $where1 .= "";
}

$where1 .= " AND `status` = 'Подтвержден' AND `send_status` = 'Отправлен' ";

//print $where1;
//die;

$where .= $where1;

/*
  Обработка
  Отложенная доставка
  На доставку
  Проблемный
  Упакован на почте
  Заберет
  Упакован
  Хранение
  Упакован принят
  Обратная доставка отправлена
  Груз вручен
  Груз в дороге
  Получен
  Располовинен
  Нет товара
  Проверен
  Свежий
  Автоответчик
  Перезвонить
  Сделать замену
  Возврат денег
 */

/*
  $query = "SELECT id, " . $offer . ", kz_operator,
  SUM(if(status IN ('новая','Перезвонить','Недозвон'),1,0)) as is_processed,
  SUM(if(status IN ('Перезвонить'),1,0)) as recall,
  SUM(if(status IN ('Недозвон'),1,0)) as nocall,
  SUM(if(status_kz IN ('Обработка') AND status IN ('Подтвержден'),1,0)) as statuskz_obr,
  SUM(if(status_kz IN ('Отложенная доставка') AND status IN ('Подтвержден'),1,0)) as statuskz_otl,
  SUM(if(status_kz IN ('На доставку') AND status IN ('Подтвержден'),1,0)) as statuskz_nad,
  SUM(if(status_kz IN ('Проблемный') AND status IN ('Подтвержден'),1,0)) as statuskz_pro,
  SUM(if(status_kz IN ('Недозвон') AND status IN ('Подтвержден'),1,0)) as statuskz_ned,
  SUM(if(status_kz IN ('Заберет') AND status IN ('Подтвержден'),1,0)) as statuskz_zab,
  SUM(if(status_kz IN ('Упакован') AND status IN ('Подтвержден'),1,0)) as statuskz_ypa,
  SUM(if(status_kz IN ('Хранение') AND status IN ('Подтвержден'),1,0)) as statuskz_hra,
  SUM(if(status_kz IN ('Груз отгружен') AND status IN ('Подтвержден'),1,0)) as statuskz_otg,
  SUM(if(status_kz IN ('Груз вручен') AND status IN ('Подтвержден'),1,0)) as statuskz_vry,
  SUM(if(status_kz IN ('Груз в дороге') AND status IN ('Подтвержден'),1,0)) as statuskz_vdo,
  SUM(if(status_kz IN ('Обратная доставка отправлена') AND status IN ('Подтвержден'),1,0)) as statuskz_obr,
  SUM(if(send_status IN ('На отправку') AND status IN ('Подтвержден'),1,0)) as price_otpravka,
  SUM(if(send_status IN ('Нет товара') AND status IN ('Подтвержден'),1,0)) as price_net,
  SUM(if(send_status IN ('Отправлен') AND status IN ('Подтвержден'),1,0)) as price_otpravlen,
  SUM(if(send_status IN ('Отказ')AND status IN ('Подтвержден'),1,0)) as price_otkaz,
  SUM(if(send_status IN ('Оплачен') AND status IN ('Подтвержден'),1,0)) as price_bablo,
  SUM(if(status IN ('Подтвержден','Предварительно подтвержден'),1,0)) as accepted,
  sum(IF(status='Подтвержден',price,0)) as avg_check,
  SUM(if(status IN ('Отменён'),1,0)) as noaccepted,
  SUM(if(status IN ('Брак'),1,0)) as bad,
  COUNT(*) as `all`
  FROM staff_order WHERE " . $where;
 */

$query = "SELECT * FROM `staff_order` WHERE " . $where;

//echo $query;
//error_log($query);
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
$itog_ar = array();
$itog_ar2 = array();
$color_ar = array('#FCDFFF', '#F20403', '#F0EA21');
while ($obj = mysql_fetch_array($rs, MYSQL_ASSOC)) {
    /*
      $itog_ar['is_processed'] += $obj->is_processed;
      $itog_ar['recall'] += $obj->recall;
      $itog_ar['nocall'] += $obj->nocall;
      $itog_ar['accepted'] += $obj->accepted;
      $itog_ar['avg_check'] += $obj->avg_check;
      $itog_ar['noaccepted'] += $obj->noaccepted;
      $itog_ar['bad'] += $obj->bad;
      $itog_ar['all'] += $obj->all;

      $itog_ar['kz_operator'] += $obj->accepted;
      $itog_ar['price_otpravka'] += $obj->price_otpravka;
      $itog_ar['price_otpravlen'] += $obj->price_otpravlen;
      $itog_ar['price_otkaz'] += $obj->price_otkaz;
      $itog_ar['price_bablo'] += $obj->price_bablo;
      $itog_ar['price_obr'] += $obj->price_obr;
      $itog_ar['price_hranenie'] += $obj->price_hranenie;
      $itog_ar['price_net'] += $obj->price_net;

      $obj->kz_operator = $obj->accepted .' (100%)';
      $obj->price_otpravka_per = ' </b> '.round(($obj->price_otpravka/$obj->accepted)*100,2).'%</span>';
      $obj->price_doroga_per = ' </b> '.round(($obj->price_doroga/$obj->accepted)*100,2).'%';
      $obj->price_otgrujen = '<b>'.$obj->price_otgrujen . ' </b> '.round(($obj->price_otgrujen/$obj->accepted)*100,2).'%';
      $obj->price_otpravlen_per =  ' </b> '.round(($obj->price_otpravlen/$obj->accepted)*100,2).'%';
      $obj->price_otkaz_per = ' </b> '.round(($obj->price_otkaz/$obj->accepted)*100,2).'%';
      $obj->price_bablo_per = ' </b> '.round(($obj->price_bablo/$obj->accepted)*100,2).'%';
      $obj->price_obr_per = ' </b> '.round(($obj->price_obr/$obj->accepted)*100,2).'%';
      $obj->price_hranenie = '<b>'.$obj->price_hranenie . ' </b> '.round(($obj->price_hranenie/$obj->accepted)*100,2).'%';
      $obj->price_ypac = '<b>'.$obj->price_ypac . ' </b> '.round(($obj->price_ypac/$obj->accepted)*100,2).'%';
      $obj->price_net = '<b>'.$obj->price_net . ' </b> '.round(($obj->price_net/$obj->accepted)*100,2).'%';
      $obj->price_vry = '<b>'.$obj->price_vry . ' </b> '.round(($obj->price_vry/$obj->accepted)*100,2).'%';
      $obj->price_zab = '<b>'.$obj->price_zab . ' </b> '.round(($obj->price_zab/$obj->accepted)*100,2).'%';
      $obj->itogo = $obj->accepted;
      $obj->avg_check = round($obj->avg_check/$obj->accepted);
      $obj->is_processed = '<span style="background-color:'.(round(($obj->is_processed/$obj->all)*100)>50?$color_ar[1]:'#FFF').'"><b>'.$obj->is_processed . ' </b> '.round(($obj->is_processed/$obj->all)*100,2).'%</span>';
      $obj->recall = '<span style="background-color:'.(round(($obj->recall/$obj->all)*100)>30?$color_ar[1]:'#FFF').'"><b>'.$obj->recall . ' </b> '.round(($obj->recall/$obj->all)*100,2).'%';
      $obj->nocall = '<span style="background-color:'.(round(($obj->nocall/$obj->all)*100)>30?$color_ar[1]:'#FFF').'"><b>'.$obj->nocall . ' </b> '.round(($obj->nocall/$obj->all)*100,2).'%';
      $obj->accepted = '<span style="background-color:'.(round(($obj->accepted/$obj->all)*100)<50?$color_ar[1]:'#FFF').'"><b>'.$obj->accepted . ' </b> '.round(($obj->accepted/$obj->all)*100,2).'%';
      $obj->noaccepted = '<span style="background-color:'.(round(($obj->noaccepted/$obj->all)*100)>25?$color_ar[1]:'#FFF').'"><b>'.$obj->noaccepted . ' </b> '.round(($obj->noaccepted/$obj->all)*100,2).'%';
      $obj->bad = '<b>'.$obj->bad . ' </b> '.round(($obj->bad/$obj->all)*100,2).'%';
     */

    $arr[] = $obj;
}

/*
  $chart_ar = array();
  $chart2_ar = array();

  $chart_ar[0]['status'] = 'В обработке';
  $chart_ar[1]['status'] = 'Подтвержден';
  $chart_ar[2]['status'] = 'Отменён';
  $chart_ar[3]['status'] = 'Брак';
  $chart_ar[0]['orders'] = (int)$itog_ar['is_processed'];
  $chart_ar[1]['orders'] = (int)$itog_ar['accepted'];
  $chart_ar[2]['orders'] = (int)$itog_ar['noaccepted'];
  $chart_ar[3]['orders'] = (int)$itog_ar['bad'];

  $chart2_ar[0]['status'] = 'На отправку';
  $chart2_ar[1]['status'] = 'Отправлен';
  $chart2_ar[2]['status'] = 'Отказ';
  $chart2_ar[3]['status'] = 'Оплачен';
  $chart2_ar[4]['status'] = 'Нет товара';

  $chart2_ar[0]['money'] = (int)$itog_ar['price_otpravka'];
  $chart2_ar[1]['money'] = (int)$itog_ar['price_otpravlen'];
  $chart2_ar[2]['money'] = (int)$itog_ar['price_otkaz'];
  $chart2_ar[3]['money'] = (int)$itog_ar['price_bablo'];
  $chart2_ar[4]['money'] = (int)$itog_ar['price_net'];

  $itog_ar['offer'] = '<b>Итого:</b>';
  $itog_ar['itogo'] = $itog_ar['accepted'];
  $itog_ar['avg_check'] = round($itog_ar['avg_check']/$itog_ar['accepted']);
  $itog_ar['is_processed'] = '<b>'.$itog_ar['is_processed'] . ' </b> '.round(($itog_ar['is_processed']/$itog_ar['all'])*100,2).'%';
  $itog_ar['recall'] = '<b>'.$itog_ar['recall'] . ' </b> '.round(($itog_ar['recall']/$itog_ar['all'])*100,2).'%';
  $itog_ar['nocall'] = '<b>'.$itog_ar['nocall'] . ' </b> '.round(($itog_ar['nocall']/$itog_ar['all'])*100,2).'%';
  $itog_ar['accepted'] = '<b>'.$itog_ar['accepted'] . ' </b> '.round(($itog_ar['accepted']/$itog_ar['all'])*100,2).'%';
  $itog_ar['noaccepted'] = '<b>'.$itog_ar['noaccepted'] . ' </b> '.round(($itog_ar['noaccepted']/$itog_ar['all'])*100,2).'%';
  $itog_ar['bad'] = '<b>'.$itog_ar['bad'] . ' </b> '.round(($itog_ar['bad']/$itog_ar['all'])*100,2).'%';

  $arr[] = $itog_ar;
 */

echo '{"total":"' . $total . '","data":' . json_encode($arr) . ',"datas":' . json_encode($chart_ar) . ',"data2":' . json_encode($chart2_ar) . ',"datad":' . json_encode($arr) . '}';
