<?php

require_once dirname(__FILE__) . '/../lib/db.php';

/*if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}*/

header('Content-Type: text/html; charset=utf-8');

if (true) {
  $sip = '';
    $sips = '';
    $psip = '';

  foreach($GLOBAL_RESPONSIBLE_STAFF[$_GET['sip']] as $k => $v){
      if(strlen($GLOBAL_STAFF_SIP[$v])) {
          $sip .= $GLOBAL_STAFF_SIP[$v].',';
          $sips .= '"'.$GLOBAL_STAFF_SIP[$v].'",';
          $psip .= " dstchannel like 'SIP/".$GLOBAL_STAFF_SIP[$v]."%' OR";
      }
  }
$sip = substr($sip,0,strlen($sip)-1);
    $sips = substr($sips,0,strlen($sips)-1);
    $psip = substr($psip,0,strlen($psip)-2);
    $date = $_REQUEST['date'];
    $pre_sql = "SELECT calldate as startDate, calldate + INTERVAL duration SECOND as endDate, substring(channel,5,4) as taskName, 'call' as status  FROM cdr WHERE  calldate>'$date' AND calldate<'$date 23:59:59' AND substring(channel,5,4) IN ($sips)";
    $pred_sql = "SELECT calldate as startDate, calldate + INTERVAL duration SECOND as endDate, substring(dstchannel,5,4) as taskName, 'pcall' as status  
FROM cdr WHERE  calldate>'$date' AND calldate<'$date 23:59:59' AND ( $psip )";
    //var_dump($pred_sql); die;
    // collect request parameters select * from cdr where dcontext='predictive-connect' and dstchannel like 'SIP/4225%' limit 1;
    $start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
    $count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
    $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
    $dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : 'ASC';
    $filters = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
    $sort = mysql_real_escape_string($sort);
    $dir = mysql_real_escape_string($dir);
    // GridFilters sends filters as an Array if not json encoded
    // query the database
    //$ext_db_old = change_base_slave();
    $query = "SELECT start_date as startDate, if(end_date IS NULL, NOW(),end_date) as endDate, sip as taskName, 'Inuse' as status 
FROM `registration_log` WHERE DATE_FORMAT(start_date,'%Y-%m-%d')='$date' AND sip IN ($sip)";
    if ($sort != "") {
        $query .= " ORDER BY " . $sort . " " . $dir;
    }
    $total_qury = $query;

    db_close();
    $ext_db_old = asterisk_base();

    $arr = array();
    $rss = mysql_query($pre_sql);
    while ($obj = mysql_fetch_object($rss)) {
        $obj->taskName = $GLOBAL_STAFF_FIO[$GLOBAL_SIP_STAFF['SIP/'.$obj->taskName]].' '.$obj->taskName;
        $arr[] = $obj;
    }

    $rsss = mysql_query($pred_sql);
    while ($obj = mysql_fetch_object($rsss)) {
        $obj->taskName = $GLOBAL_STAFF_FIO[$GLOBAL_SIP_STAFF['SIP/'.$obj->taskName]].' '.$obj->taskName;
        $arr[] = $obj;
    }

    $rs = mysql_query($query);
    $i = 0;
    $sar = array();
    //var_dump($GLOBAL_SIP_STAFF); die;
    while ($obj = mysql_fetch_object($rs)) {
        $obj->taskName = $GLOBAL_STAFF_FIO[$GLOBAL_SIP_STAFF['SIP/'.$obj->taskName]].' '.$obj->taskName;
        $arr[] = $obj;
        $sar[$obj->taskName] = $obj->taskName;

        $i++;
    }

    // return response to client
    echo json_encode(array(
        'total' => true,
        'data' => $arr,
       // 'taskName' => explode(',',$sip),
        'taskName' => array_keys($sar),
        'sql' => $query
    ));
} else {
    echo json_encode(array("success" => false));
}
