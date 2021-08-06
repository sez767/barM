<?php

require_once dirname(__FILE__) . "/../lib/db.php";
$Versions = array();
if ((int) $_GET['t']) {
    if ((int) $_GET['t'] == 1) {
        $VUQuery = "SELECT offer_id AS id, offer_desc AS value FROM offers WHERE 1 ORDER BY value";
    } else {
        $VUQuery = "SELECT offer_name AS id, offer_name AS value FROM offers WHERE offers_active ORDER BY value";
    }
    $VUResult = mysql_query($VUQuery) or $Result = false;
} else {
    $VUQuery = "SELECT id, CONCAT(FirstName,' ',LastName,' (',Bonuses,')') AS value FROM Staff WHERE Bonuses > 100 ";
    $VUResult = mysql_query($VUQuery) or $Result = false;
}
while ($VUResultRow = mysql_fetch_array($VUResult, MYSQL_ASSOC)) {
    $Versions[] = array($VUResultRow["id"], $VUResultRow["value"],);
}
echo json_encode($Versions);
