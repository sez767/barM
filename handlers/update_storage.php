<?php

/**
 * @author: Andrii Khvorostyanii
 * Date: 30.09.15
 * Time: 11:25
 * @email: byyy@andriyco.in.ua
 */
session_set_cookie_params(10800);
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

$sql = "
	select o.*, op.*
	from `offers` as o
	left join `offer_property` as op on (op.property_offer = o.offer_id AND op.property_name IN ('color', 'size'))
	LEFT JOIN `storage` AS s ON (s.offer_id = o.offer_id AND s.offer_property = (IF(op.property_id IS NULL, 0, op.property_id) ))
	where `s`.id IS NULL AND offers_active;";
$query = mysql_query($sql);
if (mysql_num_rows($query) > 0) {

    while ($row = mysql_fetch_object($query)) {
        if (!is_null($row->property_id)) {

        }

        $delivAll = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=delivery_couriers&key=value&type=value', 360), true);
        foreach ($delivAll['data'] as $dk => $delivery) {
            $sql = "
				insert into `storage` SET
				`offer_id` = " . $row->offer_id . ",
				`offer_property` = " . (is_null($row->property_id) ? 0 : $row->property_id) . ",
				`delivery` = '" . mysql_real_escape_string($delivery) . "',
				`quantity` = 0,
				`comment` = 'added by system'
			";
            //var_dump($sql); die;
            $queryS = mysql_query($sql);
        }
    }
}
echo json_encode([
    'result' => true
]);
