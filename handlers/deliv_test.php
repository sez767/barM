<?php
/**
 * Created by PhpStorm.
 * User: Zver
 * Date: 06.09.2018
 * Time: 10:45
 */

// УПЛ 2

header('Content-Type: text/html; charset=utf-8', true);

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("Location: /login.html");
    die();
}
require_once dirname(__FILE__) . "/../lib/db.php";
require_once dirname(__FILE__) . "/../lib/excel/excel.class.php";



$pre_query = mysql_query("SELECT * FROM `offers` WHERE 1");
$query = mysql_query("
	SELECT
		`id`,
		`fio`,
		`addr`,
		`offer`,
		`other_data`,
		`price`,
		`package`,
		`kz_delivery`,
		`dop_tovar`
	FROM
		`coffee`.`staff_order`
	WHERE
          (offer = 'lucem' OR INSTR(dop_tovar,'lucem')) AND send_status = 'Оплачен'
	ORDER BY
		`id`,
		`offer`,
		`package`
");

$excel = new ExtExcelWriter("deliv_list_v2_" . date('d-m-Y-H-i-s') . '.xls');

$excel->writeLine(array(''));
$excel->writeLine(array('Дата скачивания', date('d-m-Y')));
$excel->writeLine(array('', '', '', '<b>Упаковочный лист</b>', '', '', ''));
$excel->writeLine(array('<b>Почта</b>'));

$all_ar = array();

while ($obj = mysql_fetch_assoc($query)) {
    if (empty($obj['kz_delivery'])) {
        break;
    }

    // обработка атрибутов товара
    $other_data = json_decode($obj['other_data'], true);

    if (json_last_error() == JSON_ERROR_NONE) {
        krsort($other_data);
        $offer = $GLOBAL_OFFER_DESC[$obj['offer']] . " " . implode(" ", $other_data);
    } else {
        $offer = $GLOBAL_OFFER_DESC[$obj['offer']];
    }

    // товар в массив
    $all_ar[$obj['kz_delivery']][$offer][] = array(
        'offer' => $offer,
        'price' => $obj['price'],
        'package' => $obj['package'],
    );

    // обработка дополнительного товара
    $dop_tovar = json_decode($obj['dop_tovar'], true);
    $dop_tovar_all = array();

    if (
        json_last_error() == JSON_ERROR_NONE &&
        isset($dop_tovar['dop_tovar']) &&
        is_array($dop_tovar['dop_tovar'])
    ) {
        foreach ($dop_tovar['dop_tovar'] AS $ke => $va) {
            $properties = array();
            $properties_key = array_keys($dop_tovar);

            foreach ($properties_key AS $property_key) {
                if (
                    !in_array($property_key, array('dop_tovar', 'dop_tovar_price', 'dop_tovar_count')) &&
                    isset($dop_tovar[$property_key][$ke]) &&
                    !empty($dop_tovar[$property_key][$ke])
                ) {
                    $properties[] = $dop_tovar[$property_key][$ke];
                }
            }

            krsort($properties);
            $offer = $GLOBAL_OFFER_DESC[$dop_tovar['dop_tovar'][$ke]] . " " . implode(" ", $properties);

            $all_ar[$obj['kz_delivery']][$offer][] = array(
                'offer' => $offer,
                'price' => $dop_tovar['dop_tovar_price'][$ke],
                'package' => $dop_tovar['dop_tovar_count'][$ke],
            );
        }
    }
}

foreach ($all_ar as $oks => $all_arr) {
    ksort($all_arr);
    $all_c = 0;
    $excel->writeLine(array('<b>' . $oks . '</b>'));

    foreach ($all_arr as $ok => $ov) {
        $sh = 0;

        foreach ($ov as $kv => $obj) {
            $sh += $obj['package'];
        }

        $excel->writeLine(array($ok, '-', $sh));

        $all_c += $sh;
    }

    $excel->writeLine(array('<b>Итого:</b>', '<b>' . $all_c . '</b>'));
    $excel->writeLine(array(''));
}

$excel->close();
