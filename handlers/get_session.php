<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

header('Content-Type: text/html; charset=utf-8');

if (true) {
    $sip = (int) $_REQUEST['sip'];
    $date = $_REQUEST['date'];
    // collect request parameters
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
    $query = "SELECT * FROM `registration_log` WHERE DATE_FORMAT(start_date,'%Y-%m-%d')='$date' AND sip='$sip'";
    if ($sort != "") {
        $query .= " ORDER BY " . $sort . " " . $dir;
    }
    $total_qury = $query;

    db_close();
    $ext_db_old = asterisk_base();
    $rs = mysql_query($query);
    $total_ = mysql_query($total_qury);
    $total = mysql_num_rows($total_);
    //error_log($query);
    #  $total = mysql_result($total, 0, 0);
    $arr = array();
    $i = 0;
    while ($obj = mysql_fetch_object($rs)) {
        $date_ar[$i]['start_date'] = $obj->start_date;
        $date_ar[$i]['end_date'] = $obj->end_date;
        $arr[] = $obj;
        $i++;
    }
    //var_dump($arr); die;
    for ($hour = 0; $hour <= 23; $hour++) {
        if (strlen((string) $hour) == 1) {
            $hour = '0' . $hour;
        }
        for ($min = 0; $min <= 59; $min++) {
            if (strlen((string) $min) == 1) {
                $min = '0' . $min;
            }
            $time_str = $date . " " . $hour . ":" . $min . ":00";
            $time_ar[$time_str] = $time_str;
        }
    }
    $point_ar = array();
    $cou = 0;
    //var_dump($query); die;
    foreach ($time_ar as $k => $v) {
        foreach ($date_ar as $time_k => $time_v) {
            if (strtotime($date_ar[$time_k]['start_date']) < strtotime($v) && strtotime($date_ar[$time_k]['end_date']) > strtotime($v)) {
                $point_ar[$cou]['inuse'] = 1;
            }
        }
        if (!isset($point_ar[$cou]['inuse'])) {
            $point_ar[$cou]['inuse'] = 0;
        }
        $point_ar[$cou]['time'] = $v;
        $cou++;
    }
    // return response to client
    echo json_encode(array(
        'total' => true,
        'data' => $point_ar,
        'sql' => $query
    ));
} else {
    echo json_encode(array("success" => false));
}
