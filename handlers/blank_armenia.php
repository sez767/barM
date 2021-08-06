<?php
error_reporting(0);
ini_set("display_errors", 0);

require_once dirname(__FILE__) . '/../lib/db.php';
require_once dirname(__FILE__) . '/../lib/tcpdf/tcpdf.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Authorisation required"
    )));
}


if (!isset($_GET['id_str'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "No IDs found"
    )));
}

$ids = array_diff(explode(',', $_GET['id_str']), array(''));

if (count($ids) == 0) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "No IDs found"
    )));
}

$pre_query = mysql_query("SELECT * FROM offers WHERE 1");
$offer_staff_desc = array();
while ($objs = mysql_fetch_assoc($pre_query)) {
    if (is_null($objs['staff_id'])) {
        $offer_staff_desc['no'][$objs['offer_name']] = $GLOBAL_OFFER_DESC[$objs['offer_name']];
    } else {
        $staffs = explode(',', $objs['staff_id']);
        foreach ($staffs as $staff) {
            $offer_staff_desc[$staff][$objs['offer_name']] = $GLOBAL_OFFER_DESC[$objs['offer_name']];
        }
    }
}

$qs = "SELECT
        `id`,
        `ext_id`,
        `fio`,
        `city`,
        `addr`,
        `index`,
        `price`,
        `country`,
        `kz_code`,
        `offer`,
        `staff_id`
    FROM
        `coffee`.`staff_order`
    WHERE
        `id` IN (" . implode(',', $ids) . ")
    LIMIT " . count($ids);
//die($qs);
$query = mysql_query($qs);

if (mysql_num_rows($query) == 0) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "No orders found"
    )));
}

$x = 25;
$y = 20;
$xr = $x + $x + 390;

// 595px x 842px
$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'px', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins($x, $y);
$pdf->setFontSubsetting(true);
$pdf->SetAutoPageBreak(false, 0);

while ($row = mysql_fetch_array($query)) {
    // new page
    $pdf->AddPage("L", "A4");

    // step 1 / box
    $pdf->Line($x, $y, $x + 390, $y);
    $pdf->Line($x, $y, $x, $y + 555);
    $pdf->Line($x + 390, $y, $x + 390, $y + 555);
    $pdf->Line($x, $y + 555, $x + 390, $y + 555);
    $pdf->Line($x, $y + 278, $x + 390, $y + 278);
    $pdf->Line($x + 60, $y, $x + 60, $y + 555);

    // step 2 / lines
    // first part
    $pdf->Line($x + 60, $y + 15, $x + 390, $y + 15);
    // second part
    $pdf->Line($x + 60, $y + 15 + 278, $x + 390, $y + 15 + 278);
    // first part
    $pdf->Line($x + 60, $y + 94, $x + 390, $y + 94);
    $pdf->Line($x + 60, $y + 108, $x + 390, $y + 108);
    // second part
    $pdf->Line($x + 60, $y + 375, $x + 390, $y + 375);
    $pdf->Line($x + 60, $y + 389, $x + 390, $y + 389);
    // first part
    $pdf->Line($x + 60, $y + 205, $x + 390, $y + 205);
    $pdf->Line($x + 60, $y + 205 + 10 * 1, $x + 390, $y + 205 + 10 * 1);
    $pdf->Line($x + 60, $y + 205 + 10 * 2, $x + 390, $y + 205 + 10 * 2);
    $pdf->Line($x + 60, $y + 205 + 10 * 3, $x + 390, $y + 205 + 10 * 3);
    $pdf->Line($x + 60, $y + 205 + 10 * 4, $x + 390, $y + 205 + 10 * 4);
    $pdf->Line($x + 60, $y + 205 + 10 * 5, $x + 390, $y + 205 + 10 * 5);
    $pdf->Line($x + 60, $y + 205 + 10 * 6, $x + 390, $y + 205 + 10 * 6);
    // second part
    $pdf->Line($x + 60, $y + 205 * 2 + 70, $x + 390, $y + 205 * 2 + 70);
    $pdf->Line($x + 60, $y + 205 * 2 + 70 + 10 * 1, $x + 390, $y + 205 * 2 + 70 + 10 * 1);
    $pdf->Line($x + 60, $y + 205 * 2 + 70 + 10 * 2, $x + 390, $y + 205 * 2 + 70 + 10 * 2);
    $pdf->Line($x + 60, $y + 205 * 2 + 70 + 10 * 3, $x + 390, $y + 205 * 2 + 70 + 10 * 3);
    $pdf->Line($x + 60, $y + 205 * 2 + 70 + 10 * 4, $x + 390, $y + 205 * 2 + 70 + 10 * 4);
    $pdf->Line($x + 60, $y + 205 * 2 + 70 + 10 * 5, $x + 390, $y + 205 * 2 + 70 + 10 * 5);
    $pdf->Line($x + 60, $y + 205 * 2 + 70 + 10 * 6, $x + 390, $y + 205 * 2 + 70 + 10 * 6);
    // first part
    $pdf->Line($x + 320, $y + 205, $x + 320, $y + 255);
    // second part
    $pdf->Line($x + 320, $y + 205 * 2 + 70, $x + 320, $y + 255 * 2 + 20);

    // step 3 / Cells
    $pdf->SetFont('dejavusans', '', 6);
    // first part
    $cell_width = 17;
    $cell_height = 15;
    $pdf->SetXY($x + 62, 64);
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $pdf->SetXY($x + 62 + 238, 64);
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $cell_width = 15;
    $pdf->SetXY($x + 62, 158);
    $pdf->Cell($cell_width, $cell_height, '1', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '3', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '8', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '4', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '2', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '1', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '3', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '3', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $pdf->SetXY($x + 62 + 238, 158);
    $pdf->Cell($cell_width, $cell_height, '1', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '5', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $pdf->SetXY($x + 62 + 280, 158);
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    // second part
    $cell_width = 17;
    $cell_height = 15;
    $pdf->SetXY($x + 62, 64 + 280);
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $pdf->SetXY($x + 62 + 238, 64 + 280);
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $cell_width = 15;
    $pdf->SetXY($x + 62, 158 + 280);
    $pdf->Cell($cell_width, $cell_height, '1', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '3', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '8', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '4', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '9', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '2', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '1', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '3', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '3', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $pdf->SetXY($x + 62 + 238, 158 + 280);
    $pdf->Cell($cell_width, $cell_height, '1', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '5', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');
    $pdf->SetXY($x + 62 + 280, 158 + 280);
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'LTB');
    $pdf->Cell($cell_width, $cell_height, '', 'L');

    // step 4 / Text
    // first part
    $pdf->SetFont('dejavusans', '', 6);
    $pdf->Text($x, $y + 25, 'Номер заказа');
    $pdf->Text($x, $y + 35, $row["ext_id"]);
    $pdf->Text($x + 100, $y + 20, $row["fio"]);
    $pdf->Text($x, $y + 97, 'Кассир:');
    $pdf->Text($x + 115, $y + 216, trim($row["kz_code"]));
    $pdf->Text($x + 320, $y + 246, $row["price"]);
    $pdf->Text($x + 125, $y + 256, num2str($row["price"], "am"));

    $pdf->SetFont('dejavusans', 'B', 6);
    $pdf->Text($x + 199, $y + 1, 'КВИТАНЦИЯ №');
    $pdf->Text($x + 185, $y + 6, 'на оплату наличными');
    $pdf->Text($x + 60, $y + 20, 'Плательщик');
    $pdf->Text($x + 60, $y + 36, 'ИИН Плательщика');
    $pdf->Text($x + 60 + 238, $y + 36, 'КОД');
    $pdf->Text($x + 60, $y + 60, '4');
    $pdf->Text($x + 60, $y + 108, 'Получатель платежа');
    $pdf->Text($x + 60, $y + 130, 'БИН/ИНН');
    $pdf->Text($x + 300, $y + 130, 'КБЕ');
    $pdf->Text($x + 342, $y + 130, 'КНП');
    $pdf->Text($x + 60, $y + 153, 'ИИК');
    $pdf->Text($x + 60, $y + 160, 'Банк');
    $pdf->Text($x + 160, $y + 206, 'Наименование платежа');
    $pdf->Text($x + 342, $y + 206, 'Сумма');
    $pdf->Text($x + 60, $y + 216, 'Номер посылки:');
    $pdf->Text($x + 60, $y + 246, 'ВСЕГО (сумма цифрами):');
    $pdf->Text($x + 60, $y + 256, 'ВСЕГО (прописью):');
    $pdf->Text($x + 60, $y + 269, 'Дата «____» ________________ 20_____г. Подпись Плательщика _______________');

    $pdf->setColor('text', 255, 0, 0);
    $pdf->Text($x + 60, $y + 167, 'БИК');
    $pdf->setColor('text', 0, 0, 0);

    $pdf->SetFont('dejavusans', '', 4);
    $pdf->Text($x + 118, $y + 26, '(фамилия и инициалы)');
    $pdf->Text($x + 150, $y + 118, '(организация)');

    $pdf->SetFont('dejavusans', '', 7);
    $pdf->Text($x + 60, $y + 83, $row["addr"]);

    $pdf->SetFont('dejavusans', 'BU', 6);
    $pdf->Text($x + 60, $y + 73, 'Адрес и телефон Плательщика:');

    // second part
    $h = 278;
    $pdf->SetFont('dejavusans', '', 6);
    $pdf->Text($x, $y + 25 + $h, 'Номер заказа');
    $pdf->Text($x, $y + 35 + $h, $row["ext_id"]);
    $pdf->Text($x + 100, $y + 20 + $h, $row["fio"]);
    $pdf->Text($x, $y + 98 + $h, 'Кассир:');
    $pdf->Text($x + 115, $y + 213 + $h, trim($row["kz_code"]));
    $pdf->Text($x + 320, $y + 243 + $h, $row["price"]);
    $pdf->Text($x + 125, $y + 253 + $h, num2str($row["price"], "am"));

    $tovar = array();
    if (preg_match('/([a-zA-Zа-яА-Я_0-9])\w+\s-\s((\d)+?)/', $row['offer'])) {
        $offers = explode(',', $row['offer']);
        foreach ($offers as $ke => $ve) {
            $offe = explode(' - ', $ve);
            $row['package'] = trim($offe[1]);
            $prod = '';

            if (isset($offer_staff_desc[$row['staff_id']])) {
                $prod = (isset($offer_staff_desc[$row['staff_id']][trim($offe[0])]) ? $offer_staff_desc[$row['staff_id']][trim($offe[0])] : trim($offe[0])) . ' - ' . $row['package'] . 'шт.';
            } else {
                $prod = (isset($offer_staff_desc['no'][trim($offe[0])]) ? $offer_staff_desc['no'][trim($offe[0])] : trim($offe[0])) . ' - ' . $row['package'] . 'шт.';
            }

            if (in_array($row['staff_id'], ['85935432', '97975817', '51742940']) && strlen($row['sale_option'])) {
                if ($row['staff_id'] == '97975817') {
                    $sale_options = explode('|', $row['sale_option']);
                    if (isset($sale_options[$ke])) {
                        $options = explode(' - ', $sale_options[$ke]);
                        if (isset($options[1]) && strlen(trim($options[1]))) {
                            $prod .= ' - ' . trim($options[1]);
                        }
                    }
                } else {
                    $sale_options = explode(',', $row['sale_option']);
                    if (isset($sale_options[$ke])) {
                        $prod .= ' - ' . $sale_options[$ke];
                    }
                }
            }

            $tovar[] = $prod;
        }
    }

    $row["offer"] = implode(',', $tovar);

    $pdf->Text($xr, $y + 437, trim($row["offer"]));

    $pdf->Text($xr, $y + 447, "Техподдержка +77718049962, +77719960212");

    $pdf->SetFont('dejavusans', 'B', 6);
    $pdf->Text($x + 199, $y + 1 + $h, 'КВИТАНЦИЯ №');
    $pdf->Text($x + 185, $y + 6 + $h, 'на оплату наличными');
    $pdf->Text($x + 60, $y + 20 + $h, 'Плательщик');
    $pdf->Text($x + 60, $y + 38 + $h, 'ИИН Плательщика');
    $pdf->Text($x + 60 + 238, $y + 38 + $h, 'КОД');
    $pdf->Text($x + 60, $y + 62 + $h, '4');
    $pdf->Text($x + 60, $y + 111 + $h, 'Получатель платежа');
    $pdf->Text($x + 60, $y + 132 + $h, 'БИН/ИНН');
    $pdf->Text($x + 300, $y + 132 + $h, 'КБЕ');
    $pdf->Text($x + 342, $y + 132 + $h, 'КНП');
    $pdf->Text($x + 60, $y + 155 + $h, 'ИИК');
    $pdf->Text($x + 60, $y + 161 + $h, 'Банк');
    $pdf->Text($x + 160, $y + 203 + $h, 'Наименование платежа');
    $pdf->Text($x + 342, $y + 203 + $h, 'Сумма');
    $pdf->Text($x + 60, $y + 213 + $h, 'Номер посылки:');
    $pdf->Text($x + 60, $y + 243 + $h, 'ВСЕГО (сумма цифрами):');
    $pdf->Text($x + 60, $y + 253 + $h, 'ВСЕГО (прописью):');
    $pdf->Text($x + 60, $y + 268 + $h, 'Дата «____» ________________ 20_____г. Подпись Плательщика _______________');

    $pdf->setColor('text', 255, 0, 0);
    $pdf->Text($x + 60, $y + 168 + $h, 'БИК');
    $pdf->setColor('text', 0, 0, 0);

    $pdf->SetFont('dejavusans', '', 4);
    $pdf->Text($x + 118, $y + 27 + $h, '(фамилия и инициалы)');
    $pdf->Text($x + 150, $y + 119 + $h, '(организация)');

    $pdf->SetFont('dejavusans', '', 7);
    $pdf->Text($x + 60, $y + 85 + $h, $row["addr"]);

    $pdf->SetFont('dejavusans', 'BU', 6);
    $pdf->Text($x + 60, $y + 75 + $h, 'Адрес и телефон Плательщика:');

    // step 5 / right text
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->Text($xr, $y, 'Пост Ресурс');
    $pdf->Text($xr, $y + 15, 'ЗАО "Банк ВТБ" Армения');
    $pdf->Text($xr, $y + 30, 'К/С 3011181355550000002');
    $pdf->Text($xr, $y + 45, '№ счета 16065003261800');

    $pdf->Text($xr + 47, $y + 118, $row["price"]);
    $pdf->Text($xr + 90, $y + 148, $row["price"]);
    $pdf->Text($xr, $y + 133, num2str($row["price"], "am"));
    $pdf->Text($xr, $y + 163, num2str($row["price"], "am"));

    $pdf->Text($xr + 100, $y + 253, "Имя: " . $row["fio"]);
    $pdf->Text($xr + 100, $y + 268, "Адрес: " . $row["addr"]);
    $pdf->Text($xr + 100, $y + 283, "Город: " . $row["city"]);
    $pdf->Text($xr + 100, $y + 298, "Индекс: " . $row["index"]);

    $pdf->SetFont('dejavusans', 'U', 8);
    $pdf->Text($xr, $y + 118, 'Ценность:');
    $pdf->Text($xr, $y + 148, 'Наложенный платеж:');

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Text($xr, $y + 422, "Номер Заказа: " . $row["ext_id"]);

    if (trim($row["kz_code"])) {
        $pdf->Image("http://baribarda.com/lib/barcode/html/image.php??code=code128&o=1&dpi=72&t=20&r=3&rot=0&text=" . trim($row["kz_code"]) . "&f1=0&f2=10&a1=&a2=&a3=", $xr, $y + 470, 200, 40);

        $pdf->SetXY($xr + 10, $y + 510);
        $pdf->Cell(180, 15, trim($row["kz_code"]), 0, 0, 'C', FALSE, '', 4);
    }
}

$pdf->Output(dirname(__FILE__) . '/../tmp/blank.pdf', 'FI');

/**
 * Возвращает сумму прописью
 * @author runcore
 * @uses morph(...)
 */
function num2str($num, $country = "kz") {
    $currencies = array(
        "kz" => array(
            array('тыин', 'тыин', 'тыин', 1),
            array('тенге', 'тенге', 'тенге', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        ),
        "kg" => array(
            array('тыйин', 'тыйин', 'тыйин', 1),
            array('сом', 'сом', 'сом', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        ),
        "am" => array(
            array('лум', 'лум', 'лум', 1),
            array('драм', 'драм', 'драм', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        ),
    );

    $unit = isset($currencies[$country]) ? $currencies[$country] : $currencies["kz"];

    $nul = 'ноль';
    $ten = array(
        array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
    );
    $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
    $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
    $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
    //
    list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub) > 0) {
        foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
            if (!intval($v)) {
                continue;
            }
            $uk = sizeof($unit) - $uk - 1; // unit key
            $gender = $unit[$uk][3];
            list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2 > 1) {
                $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
            } else {
                $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            }
            // units without rub & kop
            if ($uk > 1) {
                $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            }
        } //foreach
    } else {
        $out[] = $nul;
    }
    $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
    $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n > 10 && $n < 20)
        return $f5;
    $n = $n % 10;
    if ($n > 1 && $n < 5)
        return $f2;
    if ($n == 1)
        return $f1;
    return $f5;
}

/**
 * Вывод текущей даты на русском
 */
function currentFullDate() {
    $monthes = array(
        1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
        5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
        9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
    );

    return (date('d') . " " . $monthes[(date('n'))] . " " . date('Y'));
}
