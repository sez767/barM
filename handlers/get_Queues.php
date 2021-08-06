<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';


asterisk_base();

$qs = " SELECT name as id, name, SUM( if(membername = 'SIP/" . $_GET['staff'] . "',1,0 ) ) AS accept
        FROM queue_table
            LEFT JOIN queue_member_table ON queue_table.name = queue_member_table.queue_name
        WHERE name NOT IN %ls
        GROUP BY name
        ORDER BY name";

$notArr = array(
    'client1_P',
    'client2_P',
    'client3_P',
    'client4_P',
    'clientZam1_1',
    'clientZam1_12',
    'clientZam1_2',
    'clientZam1_21',
    'clientZam1_3',
    'clientZam1_4',
    'clientZam1_5',
    'clientZam2_11',
    'clientZam2_12',
    'clientZam2_21',
    'clientZam2_22',
    'clientZam2_3',
    'clientZam2_4',
    'clientZam2_5',
    'clientZam3_1',
    'clientZam3_12',
    'clientZam3_2',
    'clientZam3_21',
    'clientZam3_3',
    'clientZam3_4',
    'clientZam3_5',
    'clientZam4_1',
    'clientZam4_2',
    'Logist5_P',
    'LogistKZ_P',
    'PremiumKZ_P',
    'TorgKz2_P',
    'TorgKzn2_P',
    'TorgKZn_P',
    'oplachen_post_P',
    'otkaz_post_P',
    'otmena_post_P',
    'Xolod31_P',
    'Xolod32_P',
    'Xolod33_P',
    'Xolod34_P',
    'Xolod36_P'
);

$arr = DB::query($qs, $notArr);

echo json_encode(array(
    'total' => count($arr),
    'data' => $arr
));
