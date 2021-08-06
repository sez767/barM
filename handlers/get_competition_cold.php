<?php

header('Content-Type: text/html; charset=utf-8', true);

session_start();
//echo date('Y-m-d H:i:s');
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

// INIT
$jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=cold_statuses&key=id', 360), true);
$cold_statuses = reset($jd_temp);

$jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=manager&key=id&type=value', 360), true);
$managers = reset($jd_temp);
// END INIT

$qs = "
SELECT
    COALESCE(is_cold, 'total') AS is_cold,
    COALESCE(is_cold_staff_id, 'sub_total') AS is_cold_staff_id,
    COUNT(id) AS count
FROM
    staff_order
WHERE
    (is_cold IN (5) AND is_cold_out_date > CURDATE())
GROUP BY is_cold , is_cold_staff_id  WITH ROLLUP";
$data = array_reverse(DB::query($qs));
//print_r($data);

$total = $sub_total = $curr_is_cold = 0;
$htmlOperStr = $htmlStatusStr = '';

//print_r($cold_statuses);die;
//cold_statuses


foreach ($data as $item) {
//    echo '=item:' . PHP_EOL . print_r($item, true) . '/item=' . PHP_EOL . PHP_EOL;


    if ($item['is_cold'] == 'total') {
        $total = $item['count'];
        $htmlOperArr = array();
        continue;
    } else if ($item['is_cold_staff_id'] == 'sub_total') {

        if ($htmlOperArr) {
//            $htmlOperStr .= "<br/><u><b>{$cold_statuses[$curr_is_cold]} - $sub_total  шт.:</b></u><br/> " . implode('<br/>' . PHP_EOL, $htmlOperArr) . '<br/>';
        }

        $sub_total = $item['count'];
        $curr_is_cold = $item['is_cold'];
        $htmlOperArr = array();
        continue;
    }

    if ($item['is_cold_staff_id'] > 0) {
        $htmlOperArr[] = "{$managers[$item['is_cold_staff_id']]} - {$item['count']} (" . round($item['count'] / $sub_total * 100) . '%)';
    }
}
$htmlOperStr .= "<br/> " . implode('<br/>' . PHP_EOL, $htmlOperArr) . "<br/><hr/><u><b>За сегодня - $sub_total  шт.</b></u>";
/////////////////////////////
//echo $htmlOperStr;
echo '<div style="text-align:center;"><b><br>ПО ОПЕРАТОРАМ:</b><br>' . $htmlOperStr . '</div>';
