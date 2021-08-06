<?php

include_once (dirname(__FILE__) . "/../lib/db.php");

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$columns = array();
$query = mysql_query("SELECT DISTINCT(kz_delivery) as Field FROM staff_order WHERE date>NOW() - INTERVAL 3 MONTH ORDER BY kz_delivery");
while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
    //if(!stristr($row['Field'],'kz'))
    $columns[] = array(
        "name" => $row['Field'],
        "description" => (!empty($row['Comment']) ? " (" . $row['Comment'] . ")" : "")
    );
}

if (count($columns) > 0) {
    print json_encode(array(
        "success" => TRUE,
        "columns" => $columns
    ));
} else {
    print json_encode(array(
        "success" => FALSE,
        "msg" => "Table `" . $s_table['related_table'] . "` has't any columns"
    ));
}
