<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$query1 = " SELECT max(kz_code) AS maxcode
            FROM staff_order
            WHERE `date` > NOW() - INTERVAL 1 WEEK AND country = 'kz' AND kz_code <> '' AND status = 'Подтвержден'
                    AND kz_delivery != 'Почта' AND substring(kz_code,1,4) = 'KR01' ";
$rs1 = mysql_query($query1);
$obj1 = mysql_fetch_assoc($rs1);
$maxcode = (int) substr($obj1['maxcode'], 4, strlen($obj1['maxcode']) - 2);
$query2 = mysql_query("SELECT * FROM staff_order WHERE country = 'kz'
AND kz_code = '' AND status = 'Подтвержден' AND kz_delivery != 'Почта'
 AND id IN (" . substr($_GET['ids'], 0, strlen($_GET['ids']) - 1) . ")
				ORDER BY offer,package");
$i = 0;
while ($obj = mysql_fetch_assoc($query2)) {
    $tmp = $maxcode + $i + 1;
    switch (strlen((string) $tmp)) {
        case 1: $vuvu = '000000' . (string) $tmp;
            break;
        case 2: $vuvu = '00000' . (string) $tmp;
            break;
        case 3: $vuvu = '0000' . (string) $tmp;
            break;
        case 4: $vuvu = '000' . (string) $tmp;
            break;
        case 5: $vuvu = '00' . (string) $tmp;
            break;
        case 6: $vuvu = '0' . (string) $tmp;
            break;
        case 7: $vuvu = (string) $tmp;
            break;
    }
    $query3 = "UPDATE staff_order SET kz_code = 'KR01" . $vuvu . "KZ'
				WHERE country = 'kz' AND kz_code = '' AND status = 'Подтвержден' AND id='" . $obj['id'] . "' AND kz_delivery != 'Почта' LIMIT 1  ";
    //var_dump($query3); die;
    $rs3 = mysql_query($query3);
    $i++;
}
?>
