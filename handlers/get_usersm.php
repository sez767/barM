<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Permission denied"
    )));
}

header('Content-Type: text/html; charset=utf-8', true);

$filter = mysql_real_escape_string($_POST['searchString']);
if (empty($filter)) {
    $filter = mysql_real_escape_string($_GET['query']);
}
$VUQuery = "SELECT id
    ,CONCAT( LastName, ' ', FirstName) as value
              FROM Staff
     WHERE LENGTH(FirstName)>2 AND LENGTH(LastName)>2 AND ( FirstName LIKE '%$filter%' OR LastName LIKE '%$filter%' )
              ORDER BY LastName
             ";
$VUResult = db_execute_query($VUQuery) or $Result = false;
$row = mysql_num_rows($VUResult);
while ($VUResultRow = mysql_fetch_array($VUResult, MYSQL_ASSOC)) {
    $REFERALS[] = array($VUResultRow["id"], $VUResultRow["value"],);
}
if ($row == 0)
    $response = '["",""]';
else
    $response = json_encode($REFERALS);
echo $response;
?>
