<?php

header('Content-Type: text/html; charset=utf-8', true);

require_once dirname(__FILE__) . '/../lib/db.php';

if (empty($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

echo '
<style>
    hr.styleOne {border-top: 2px dotted #8c8b8b;margin-bottom:15px;}
    hr.styleAll {border-top: 5px double #8c8b8b;margin-bottom:15px;}
    hr.styleEnd {margin-bottom:50px;}
</style>';

// INIT
$nowTime = time();
//$nowTime = strtotime('2018-11-4');

$cellName = strtolower(date('M', $nowTime)) . (date('j', $nowTime) > 15 ? '2' : '') . '_stolov';

$eatYearMonthText = date('M ', $nowTime);
$eatYearMonthSql = date('Y-m-', $nowTime);
$betweenText = $eatYearMonthText . (date('j', $nowTime) > 15 ? '16' : '01') . " .. $eatYearMonthText" . (date('j', $nowTime) > 15 ? date('t', $nowTime) : '15');
$betweenSql = "'$eatYearMonthSql" . (date('j', $nowTime) > 15 ? '16' : '01') . "' AND '" . $eatYearMonthSql . (date('j', $nowTime) > 15 ? date('t', $nowTime) . ' 23:59:59' : '15 23:59:59') . "'";
// END INIT
//////////////////////////////////////////////
// Оператор логист

if ($_SESSION['logist'] || $_SESSION['adminlogist'] || $_SESSION['admin']) {
    $redis = RedisManager::getInstance()->getRedis(); // ketkz

    echo '<h3>МОЯ ЗАРПЛАТА</h3><br/>';

    $jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=operator_logist&key=id', 360), true);
    $operatorLogist = reset($jd_temp);

    // delivery_mass_admin
    $adminLogist = $redis->hGetAll('delivery_mass_admin');

    switch ($_REQUEST['country']) {
        case 'kz':
            // KZ
            $nightAdminIds = $redis->hGetAll('kz_admins_night_list');
            $diapazons = $redis->hGetAll('kz_operator_logist_persents');
            $diapazonsDay = $redis->hGetAll('kz_admins_day_persents');
            $diapazonsNight = $redis->hGetAll('kz_admins_night_persents');
            break;
        case 'kzg':
            // KGZ
            $nightAdminIds = $redis->hGetAll('kgz_admins_night_list');
            $diapazons = $redis->hGetAll('kgz_operator_logist_persents');
            $diapazonsDay = $redis->hGetAll('kgz_admins_day_persents');
            $diapazonsNight = $redis->hGetAll('kgz_admins_night_persents');

            break;

        default:
            break;
    }

    ksort($diapazons);
    ksort($diapazonsDay);
    ksort($diapazonsNight);

    $qs = " SELECT
                    kz_operator,
                    SUM(total_price) AS day_total,
                    DATE_FORMAT(`return_date`,'%Y-%m-%d') AS return_date_format
                FROM
                    staff_order
                WHERE
                status = 'Подтвержден'
                        AND country = '{$_REQUEST['country']}'
                        AND send_status = 'Оплачен'
                        AND total_price IS NOT NULL
                        AND return_date BETWEEN $betweenSql
                        AND kz_operator > 0
                GROUP BY kz_operator, return_date_format
                ORDER BY kz_operator, return_date_format";
//    die($qs);

    $data = DB::query($qs);
    $currKzOperatorTotal = array('payed' => 0, 'zp' => 0);
    $kassaTotal = array();
    $currKzOperator = null;
    foreach ($data as $dayData) {
        if ($currKzOperator != $dayData['kz_operator']) {

            if ($currKzOperator !== null) {
                echo "<br/> <b>ИТОГО ОПЛ: <font style='color:blue;'>{$currKzOperatorTotal['payed']}</font></b>";
                echo "<br/> <b>ИТОГО ЗП: <font style='color:blue;'>{$currKzOperatorTotal['zp']}</font></b> <hr class='styleOne'/>";
            } else {
                echo "<hr class='styleOne'/>";
            }

            echo "<b>{$operatorLogist[$dayData['kz_operator']]}: </b>";

            $currKzOperator = $dayData['kz_operator'];
            $currKzOperatorTotal = array('payed' => 0, 'zp' => 0);
        }

        $proc = getDiapazonKoef($diapazons, $dayData['day_total']);
        $currDayZp = round($dayData['day_total'] * $proc / 100);
        echo "<br/> {$dayData['return_date_format']}: сумма={$dayData['day_total']} (<b>$proc%</b>), ЗП: <font style='color:blue;'>$currDayZp</font>) ";
        $currKzOperatorTotal['payed'] += $dayData['day_total'];
        $currKzOperatorTotal['zp'] += $currDayZp;
        $kassaTotal[] = $dayData['day_total'];
    }
    echo "<br/> <b>ИТОГО ОПЛ: <font style='color:blue;'>{$currKzOperatorTotal['payed']}</font></b>";
    echo "<br/> <b>ИТОГО ЗП: <font style='color:blue;'>{$currKzOperatorTotal['zp']}</font></b> <hr class='styleAll'/>";
    echo "<p style='font-weight:bolder;color:green;'>КАССА ИТОГО: <font style=''>" . array_sum($kassaTotal) . "</font></p> <hr class='styleAll styleEnd'/>";

//
//////////////////////////////////////////////
// Админ логистики
// По статусу отправки
    if ($_SESSION['adminlogist'] || $_SESSION['admin']) {

        echo '<font style="color:blue;">По статусу отправки: "<b>Оплачен</b>"</font><br/>' . PHP_EOL;
        echo "<font style='color:red;'>Оборотка за: $betweenText</font><br/>";

        $qs = " SELECT
                    kz_admin,
                    SUM(total_price) AS day_total,
                    DATE_FORMAT(`return_date`,'%Y-%m-%d') as return_date_format
                FROM
                    staff_order
                WHERE
                    status = 'Подтвержден'
                        AND country = '{$_REQUEST['country']}'
                        AND send_status = 'Оплачен'
                        AND total_price IS NOT NULL
                        AND return_date BETWEEN $betweenSql
                        AND kz_admin > 0
                GROUP BY kz_admin, return_date_format
                ORDER BY kz_admin, return_date_format";
//        die($qs);

        $data = DB::query($qs);
        $currKzAdminTotal = array('payed' => 0, 'zp' => 0);
        $kassaTotal = array();
        $currKzAdmin = null;
        foreach ($data as $dayData) {
            if ($currKzAdmin != $dayData['kz_admin']) {

                if ($currKzAdmin !== null) {
                    echo "<br/> <b>ИТОГО ОПЛ: <font style='color:blue;'>{$currKzAdminTotal['payed']}</font>";
                    echo "<br/> <b>ИТОГО ЗП: <font style='color:blue;'>{$currKzAdminTotal['zp']}</font></b> <hr class='styleOne'/>";
                } else {
                    echo "<hr class='styleOne'/>";
                }

                echo "<b>{$adminLogist[$dayData['kz_admin']]}: </b>";

                $currKzAdmin = $dayData['kz_admin'];
                $currKzAdminTotal = array('payed' => 0, 'zp' => 0);
            }

            $proc = getDiapazonKoef(in_array($dayData['kz_admin'], $nightAdminIds) ? $diapazonsNight : $diapazonsDay, $dayData['day_total']);
            $currDayZp = round($dayData['day_total'] * $proc / 100);
            echo "<br/> {$dayData['return_date_format']}: сумма={$dayData['day_total']} (<b>$proc%</b>), ЗП: <font style='color:blue;'>$currDayZp</font>) ";
            $currKzAdminTotal['payed'] += $dayData['day_total'];
            $currKzAdminTotal['zp'] += $currDayZp;
            $kassaTotal[] = $dayData['day_total'];
        }
        echo "<br/> <b>ИТОГО ОПЛ: <font style='color:blue;'>{$currKzAdminTotal['payed']}</font>";
        echo "<br/> <b>ИТОГО ЗП: <font style='color:blue;'>{$currKzAdminTotal['zp']}</font></b> <hr class='styleAll'/>";
        echo "<p style='font-weight:bolder;color:green;'>КАССА ИТОГО: <font style=''>" . array_sum($kassaTotal) . "</font></p> <hr class='styleAll styleEnd'/>";

//////////////////////////////////////////////
// Админ логистики
// По статусу курьера

        echo '<font style="color:blue;">По статусу курьера: "<b>ОПЛ Общие</b>"</font><br/>' . PHP_EOL;
        echo "<font style='color:red;'>Оборотка за: " . date('Y-m-d') . "</font><br/>";

        $qs = " SELECT
                    kz_admin,
                    SUM(total_price) AS day_total,
                    DATE_FORMAT(`stcur_date`,'%Y-%m-%d') as return_date_format
                FROM
                    staff_order
                WHERE
                    status = 'Подтвержден'
                        AND country = '{$_REQUEST['country']}'
                        AND status='Подтвержден' AND `status_kz` IN ('На доставку') AND status_cur IN ('" . implode("', '", $GLOBAL_STATUS_CUR_OPLACHEN) . "')
                        AND total_price IS NOT NULL
                        AND stcur_date > CURDATE()
                        AND kz_admin > 0
                GROUP BY kz_admin, return_date_format
                ORDER BY kz_admin, return_date_format";
//        die($qs);

        $data = DB::query($qs);
        $currKzAdminTotal = array('payed' => 0, 'zp' => 0);
        $kassaTotal = array();
        $currKzAdmin = null;
        foreach ($data as $dayData) {
            if ($currKzAdmin != $dayData['kz_admin']) {

                if ($currKzAdmin !== null) {
                    echo "<br/> <b>ИТОГО ОПЛ: <font style='color:blue;'>{$currKzAdminTotal['payed']}</font>";
                    echo "<br/> <b>ИТОГО ЗП: <font style='color:blue;'>{$currKzAdminTotal['zp']}</font></b> <hr class='styleOne'/>";
                } else {
                    echo "<hr class='styleOne'/>";
                }

                echo "<b>{$adminLogist[$dayData['kz_admin']]}: </b>";

                $currKzAdmin = $dayData['kz_admin'];
                $kassaTotal[] = $dayData['day_total'];
                $currKzAdminTotal = array('payed' => 0, 'zp' => 0);
            }

            $proc = getDiapazonKoef(in_array($dayData['kz_admin'], $nightAdminIds) ? $diapazonsNight : $diapazonsDay, $dayData['day_total']);
            $currDayZp = round($dayData['day_total'] * $proc / 100);
            echo "<br/> {$dayData['return_date_format']}: сумма={$dayData['day_total']} (<b>$proc%</b>), ЗП: <font style='color:blue;'>$currDayZp</font>) ";

            $currKzAdminTotal['payed'] += $dayData['day_total'];
            $currKzAdminTotal['zp'] += $currDayZp;
        }
        echo "<br/> <b>ИТОГО ОПЛ: <font style='color:blue;'>{$currKzAdminTotal['payed']}</font>";
        echo "<br/> <b>ИТОГО ЗП: <font style='color:blue;'>{$currKzAdminTotal['zp']}</font></b> <hr class='styleAll'/>";
        echo "<p style='font-weight:bolder;color:green;'>КАССА ИТОГО: <font style=''>" . array_sum($kassaTotal) . "</font></p> <hr class='styleAll'/>";
    }
}