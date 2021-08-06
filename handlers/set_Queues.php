<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
mysql_close();
$ext_db_old = asterisk_base();

$request_body = file_get_contents('php://input');
$income = json_decode($request_body);

$id = $income->id;
$accept = (int) $income->accept;
$staff_id = (int) $_GET['staff'];
if ($accept >= 1) {
    $check = mysql_query("SELECT * FROM `queue_member_table` WHERE membername = 'SIP/" . $staff_id . "' AND queue_name = '" . $id . "' ");
    if (!mysql_num_rows($check)) {
        if ($id == 'TorgKZ_P') {
            $penalty = 17;
        } else {
            $penalty = 1;
        }
        $uquery = "INSERT INTO `queue_member_table` (membername,interface,queue_name,penalty)
					VALUES ('SIP/" . $staff_id . "','SIP/" . $staff_id . "','" . $id . "','1'); ";
    }
} else {
    $uquery = "DELETE FROM `queue_member_table` WHERE queue_name = '" . $id . "' AND membername = 'SIP/" . $staff_id . "' ";
}
//echo $uquery; die;
$rs = mysql_query($uquery, $ext_link);
$r = array('success' => true, 'message' => 'ok');

$memcache = MemcacheManager::getInstance()->getMemcache();
$qs = 'SELECT RIGHT(membername, 4) AS Sip, membername, GROUP_CONCAT(queue_name) AS queues FROM asterisk.queue_member_table GROUP BY membername';
$memcache->set(md5('GLOBAL_SIP_QUEUES_ASS'), DB::queryAssData('Sip', 'queues', $qs), false, 300);
echo json_encode($r);
