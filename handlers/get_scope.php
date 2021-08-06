<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

header('Content-Type: text/plain; charset=utf-8');

$prefix = $_REQUEST['staff_id'] == 33333333 ? 'recovery' : 'cold';

// INIT
$stTemp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . "/handlers/get_StoreData.php?data={$prefix}_statuses&key=id", 360), true);
$statuses = reset($stTemp);

$manTemp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=manager&key=id&type=value', 360), true);
$managers = reset($manTemp);
// END INIT

$addSql = $_SESSION['admin'] ? '' : " AND is_{$prefix}_staff_id = {$_SESSION['Logged_StaffId']} ";

$qs = "
SELECT
    COALESCE(is_$prefix, 'total') AS is_$prefix,
    COALESCE(is_{$prefix}_staff_id, 'sub_total') AS is_{$prefix}_staff_id,
    COUNT(id) AS count
FROM
    staff_order
WHERE
    (is_$prefix IN (1 , 2, 3) OR is_{$prefix}_out_date > CURDATE())
    $addSql
GROUP BY is_$prefix , is_{$prefix}_staff_id  WITH ROLLUP";
$data = array_reverse(DB::query($qs));
//print_r($data);

$total = $sub_total = $curr_is = 0;
$htmlOperStr = $htmlStatusStr = '';


foreach ($data as $item) {
//    echo '=item:' . PHP_EOL . print_r($item, true) . '/item=' . PHP_EOL . PHP_EOL;


    if ($item['is_$prefix'] == 'total') {
        $total = $item['count'];
        $htmlOperArr = $htmlStatusArr = array();
        continue;
    } else if ($item["is_{$prefix}_staff_id"] == 'sub_total') {

        if ($htmlOperArr) {
            $htmlStatusArr[] = "{$statuses[$curr_is]} - $sub_total (" . round($sub_total / $total * 100) . '%)';

            $htmlOperStr .= "<br/><u><b>{$statuses[$curr_is]} - $sub_total  шт.:</b></u><br/> " . implode('<br/>' . PHP_EOL, $htmlOperArr) . '<br/>';
        }

        $sub_total = $item['count'];
        $curr_is = $item["is_{$prefix}"];
        $htmlOperArr = array();
        continue;
    }

    if ($item["is_{$prefix}_staff_id"] > 0) {
        $htmlOperArr[] = "{$item["is_{$prefix}_staff_id"]} {$managers[$item["is_{$prefix}_staff_id"]]} - {$item['count']} (" . round($item['count'] / $sub_total * 100) . '%)';
        $htmlOperArr[] = "{$managers[$item["is_{$prefix}_staff_id"]]} - {$item['count']} (" . round($item['count'] / $sub_total * 100) . '%)';
    }
}
$htmlStatusArr[] = "{$statuses[$curr_is]} - $sub_total (" . round($sub_total / $total * 100) . '%)';
$htmlOperStr .= "<br/><u><b>{$statuses[$curr_is]} - $sub_total  шт.:</b></u><br/> " . implode('<br/>' . PHP_EOL, $htmlOperArr) . '<br/>';
/////////////////////////////

$htmlStatusStr = implode('<br/>' . PHP_EOL, $htmlStatusArr);

//echo $htmlOperStr;
echo '<div style="text-align:center;"><b>ПО СТАТУСАМ - ' . $total . '  шт.:</b><br><br>' . $htmlStatusStr . '<b><br><hr/>ПО ОПЕРАТОРАМ:</b><br>' . $htmlOperStr . '</div>';
