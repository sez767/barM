<?php
/**
 * @author: Andrii Khvorostyanii
 * Date: 29.09.15
 * Time: 17:54
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
	SELECT
		ah.id AS id,
		CONCAT_WS(' ', FirstName, LastName) AS fio,
		IF(op.`property_value` IS NULL, o.`offer_desc`, CONCAT(o.`offer_desc`,'[', op.`property_value`, ']')) AS product,
		ah.`property` as `type`,
		ah.`set` as `value`,
		st.`delivery`,
		ah.`comment`,
		ah.`date`
	FROM `ActionHistoryNew` AS ah
		LEFT JOIN `Staff` AS s
			ON ah.`from` = s.id
		LEFT JOIN `storage` AS st
			ON st.id = ah.hidden_comment
		LEFT JOIN `offers` AS o
			ON st.offer = o.offer_name
		LEFT JOIN `offer_property` AS op
			ON (op.property_offer = o.offer_id AND op.property_name IN ('color', 'size') AND op.`property_id` = st.`property`)
    WHERE ah.`type` = 'storage' AND ah.`property` <> 'add_to_storage'
    ORDER BY ah.id DESC;
";

$result = [];
$query = mysql_query($sql);
while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    $result[] = $row;
    //var_dump($row);
}

print json_encode(array(
    "success" => TRUE,
    "data" => $result,
));
