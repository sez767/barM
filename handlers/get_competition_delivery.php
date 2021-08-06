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
//print_r($jd_temp);die;
$cold_statuses = reset($jd_temp);

$jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=operator_logist&key=id', 360), true);
$managers = reset($jd_temp);
// END INIT

$qs = "
SELECT
    COALESCE(kz_operator, 'total') AS kz_operator,
    COUNT(id) AS count
FROM
    staff_order
WHERE
    status = 'Подтвержден'
        AND send_status = 'Отправлен'
        AND `status_kz` IN ('На доставку', 'Вручить подарок')
        AND date_delivery BETWEEN CURDATE() + INTERVAL 1 DAY AND CURDATE() + INTERVAL 1 DAY
GROUP BY kz_operator WITH ROLLUP";
$data = array_reverse(DB::query($qs));
//print_r($data);die;

$total = 0;
$htmlOperStr = $htmlStatusStr = '';

//print_r($cold_statuses);die;
//cold_statuses


foreach ($data as $item) {
//    echo '=item:' . PHP_EOL . print_r($item, true) . '/item=' . PHP_EOL . PHP_EOL;


    if ($item['kz_operator'] == 'total') {
        $total = $item['count'];
        continue;
    }
    if ($item['kz_operator'] > 0) {
        $htmlOperArr[] = (empty($managers[$item['kz_operator']]) ? $item['kz_operator'] : $managers[$item['kz_operator']]) . " - {$item['count']} (" . round($item['count'] / $total * 100) . '%)';
    }
}
$htmlOperStr .= "<br/> " . implode('<br/>' . PHP_EOL, $htmlOperArr) . "<br/><hr/><u><b>Всего - $total  шт.</b></u>";
/////////////////////////////
//echo $htmlOperStr;
echo '<div style="text-align:center;"><b><br>ПО ОПЕРАТОРАМ:</b><br>' . $htmlOperStr . '</div>';
