<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}


$_GET['courier'] = (int) $_GET['courier'];

if ($_GET['courier'] <= 0) {
    die(json_encode(array(
        "success" => FALSE,
        "data" => array(),
        "total" => 0
    )));
}

$arr = array();

//
// Start baribarda selection
//
$sql = "
	SELECT
		`id` as ext_id,
		`id` as id,
		`fio`,
		`addr`,
		`kz_curier`,
		`date_delivery`,
		`delivery_time`,
		`deliv_desc`,
		`staff_id`,
		`status_cur`,
		`phone`,
		`offer`,
		`kz_admin`,
		'1' as baribarda,
		`date_vozvrat`,
		`date_poluchen`
	FROM
		`staff_order`
	WHERE
		`kz_curier` = '" . (int) $_GET['courier'] . "' AND
		`send_status` = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок')
		AND date_delivery = CURDATE()
	GROUP BY
		`ext_id`
	ORDER BY
		`id`
";

$query = mysql_query($sql);

while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    $arr[] = $row;
}
//
// End baribarda selection
//

ket_asterisk_base();

$query = "
	SELECT
		`id`,
		`ext_id`,
		`fio`,
		`addr`,
		`kz_curier`,
		`date_delivery`,
		`delivery_time`,
		`deliv_desc`,
		`staff_id`,
		`status_cur`,
		`phone`,
		'0' as baribarda,
		`offer`,
		`kz_admin`,
		`date_vozvrat`,
		`date_poluchen`
	FROM
		coffee.`staff_order`
	WHERE
		`kz_curier` = '" . (int) $_GET['courier'] . "' AND
		`send_status` = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок')
		AND date_delivery = CURDATE()
	GROUP BY
		`ext_id`
	ORDER BY
		`id`
";
//var_dump($query); die;

mysql_select_db('coffee', $ext3_link);
$query = mysql_query($query, $ext3_link);

while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    $arr[] = $row;
}
//
// End ketkz selection
//
//var_dump($arr2); die;
$result = array();


for ($i = 10; $i <= 18; $i++) {
    foreach ($arr AS $row) {
        // 00
        if (!isset($result[md5($i . ':00:00')]['id'])) {
            if ($row['delivery_time'] == $i . ':00:00') {
                $result[md5($i . ':00:00')] = array('id' => '');
            } else {
                $result[md5($i . ':00:00')] = array(
                    "key" => $i . ':00:00', "value" => $i . ':00'
                );
            }
        }

        // 30
        if (!isset($result[md5($i . ':30:00')]['id'])) {
            if ($row['delivery_time'] == $i . ':30:00') {
                $result[md5($i . ':30:00')] = array('id' => '');
            } else {
                $result[md5($i . ':30:00')] = array(
                    "key" => $i . ':30:00', "value" => $i . ':30'
                );
            }
        }
    }
}
//var_dump($result);
foreach ($result as $rk => $rv) {
    if (count($rv) < 2) {
        unset($result[$rk]);
    }
}
$result = array_values($result);

$total = count($result);

print json_encode(array(
    "success" => TRUE,
    "data" => $result,
    "total" => $total
));
