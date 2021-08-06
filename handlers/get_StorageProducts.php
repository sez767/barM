<?php
session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die;
}
require_once dirname(__FILE__) . '/../lib/db.php';

$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$count = (int) isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
$search = isset($_REQUEST['query']) ? mysql_real_escape_string($_REQUEST['query']) : null;

if (isset($_REQUEST['exists'])) {
    $sql = "
		SELECT
			s.`id` AS storage_id,
			s.`offer_id`,
			o.`offer_name`,
			IF(LENGTH(o.`offer_storage_name`) > 0, o.`offer_storage_name`, o.`offer_desc`) AS `offer_storage_name`,
			IF(op.`property_value` IS NULL, o.`offer_desc`, CONCAT(o.`offer_desc`,'[', op.`property_value`, ']')) AS offer_desc,
			s.`offer_property`,
			op.`property_value`,
			o.`offers_active`
		FROM `storage` as s
		left join `offers` as o on o.offer_id = s.offer_id
		left join `offer_property` as op on op.property_id = s.offer_property
		where (o.`offer_storage_name` LIKE '%" . $search . "%' OR o.`offer_name` LIKE '%" . $search . "%') AND delivery = '" . $_GET['delivery'] . "'";

    $query = mysql_query($sql);
    $names = $result = array();
    $index = 0;
    while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
        $result[$index] = $row;
        $names[$index] = $row['offer_storage_name'];
        $index++;
    }
    $array_lowercase = array_map('strtolower', $names);
    array_multisort($array_lowercase, SORT_ASC, SORT_NATURAL, $result);
} else {
    $sql = "
SELECT
    `offer_id`,
    `offer_name`,
    `offer_desc`,
    `offers_active`,
    IF(LENGTH(`offer_storage_name`) > 0, `offer_storage_name`, `offer_desc`) AS `offer_storage_name`
FROM
	`offers`
WHERE
    `offer_desc` LIKE '%" . $search . "%' OR `offer_name` LIKE '" . $search . "%' ";

    $total_sql = $sql;

    $sql .= " ORDER BY `offer_storage_name` ASC ";
//$sql .= " LIMIT " . $start . "," . $count;

    $query = mysql_query($sql);
    $query_total = mysql_query($total_sql);
    $total = mysql_num_rows($query_total);
    $result = array();

    $sql = "select offer_id, offer_property from storage";
    $qqq = mysql_query($sql);
    $exists = [];
    while ($row = mysql_fetch_assoc($qqq)) {
        $exists[$row['offer_id'] . '_' . $row['offer_property']] = 1;
    }

    while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
        $sql_property = mysql_query("SELECT `property_id`, `property_name`, `property_value` FROM `offer_property` WHERE `property_offer` = '" . (int) $row['offer_id'] . "' AND `property_active` AND `property_name` IN ('color', 'size')");

        $property = array();
        if (mysql_num_rows($sql_property) > 0) {
            while ($row_property = mysql_fetch_array($sql_property, MYSQL_ASSOC)) {
                if (isset($exists[$row['offer_id'] . '_' . $row_property['property_id']])) {
                    continue;
                }
                $property[] = $row_property;
            }
            if (count($property) == 0) {
                continue;
            }
        } else {
            if (isset($exists[$row['offer_id'] . '_0'])) {
                continue;
            }
        }

        $row['properties'] = $property;

        $result[] = $row;
    }
}
print json_encode(array(
    "success" => TRUE,
    "data" => $result,
                //"total" => $total
));
