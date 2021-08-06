<?php

require dirname(__FILE__) . "/../lib/db.php";
error_reporting(E_ALL);

//header('Content-Type: application/json; charset=utf-8;');
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=reestrAE_' . date('Y-m-d H:i') . '.csv');
if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Authorisation required"
    )));
}
$out = fopen('php://output', 'w');
//require dirname(__FILE__) . "/../lib/excel/excel.inc.php";

if (empty($_GET['id_str']) || strlen($_GET['id_str']) <= 1) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "You must specify at least one ID"
    )));
}

$label = array(
    "attribute" => "Атрибут",
    "color" => "Цвет",
    "size" => "Размер",
    "type" => "Тип",
    "vendor" => "Модель",
    "name" => "Название",
    "description" => "Описание",
    "count" => "Количество",
    "price" => "Цена"
);

$sql = "
    SELECT
        `id` AS `id`,
        `fio` AS `fio`,
        `phone` AS `phone`,
        `addr` AS `address`,
        `country` AS `country`,
        `kz_delivery` AS `city`,
        `price` AS `amount`,
        `package` AS `package`,
        `deliv_desc` AS `note`,
        `offer` AS `offer`,
        `other_data` AS `offer_attributes`,
        `dop_tovar` AS `dop_tovar`
    FROM
        `coffee`.`staff_order`
    WHERE
        `country` = 'ae' AND
        `id` IN (" . substr($_GET['id_str'], 0, strlen($_GET['id_str']) - 1) . ")
    ORDER BY
        `id`
";

$query = mysql_query($sql);

//$excel = new ExcelWriter("register_uae_" . date('Ymd') . '.xls');
fputcsv($out, array(
    'Order Reference',
    'Customer Name',
    'E-Mail',
    'Phone',
    'Address',
    'Country',
    'City',
    'Payment type',
    'Amount',
    'No. of Package',
    'Description',
    'Note'
)); /*
  $excel->writeLine(array(
  'Order Reference',
  'Customer Name',
  'E-Mail',
  'Phone',
  'Address',
  'Country',
  'City',
  'Payment type',
  'Amount',
  'No. of Package',
  'Description',
  'Note'
  ));
 */
if (mysql_num_rows($query)) {
    while ($row = mysql_fetch_assoc($query)) {
        $description = array();

        $row["offer_attributes"] = json_decode($row["offer_attributes"], true);

        if (json_last_error() != JSON_ERROR_NONE || !$row["offer_attributes"]) {
            $row["offer_attributes"] = array();
        }

        $row["dop_tovar"] = json_decode($row["dop_tovar"], true);

        if (json_last_error() != JSON_ERROR_NONE || !$row["dop_tovar"]) {
            $row["dop_tovar"] = array();
        }

        $row["offer_attributes"]["count"] = $row["package"];

        $offer_attributes = array();

        foreach ($row["offer_attributes"] AS $attribute => $value) {
            $offer_attributes[] = $label[$attribute] . ": " . $value;
        }

        $description[] = (isset($GLOBAL_OFFER_DESC[$row["offer"]]) ? $GLOBAL_OFFER_DESC[$row["offer"]] : $row["offer"]) . " (" . implode(', ', $offer_attributes) . ")";

        if (isset($row["dop_tovar"]["dop_tovar"]) && count($row["dop_tovar"]["dop_tovar"]) > 0) {
            $offers_label = array_keys($row["dop_tovar"]);

            foreach ($row["dop_tovar"]["dop_tovar"] AS $key => $value) {
                $attributes = array();
                $attributes[] = (isset($label["count"]) ? $label["count"] : "count") . ": " . $row["dop_tovar"]["dop_tovar_count"][$key];

                foreach ($offers_label AS $offer_label) {
                    if (in_array($offer_label, array("dop_tovar", "dop_tovar_price", "dop_tovar_count"))) {
                        continue;
                    }

                    $attributes[] = (isset($label[$offer_label]) ? $label[$offer_label] : $offer_label) . ": " . $row["dop_tovar"][$offer_label][$key];
                }

                $description[] = (isset($GLOBAL_OFFER_DESC[$value]) ? $GLOBAL_OFFER_DESC[$value] : $value) . " (" . implode(', ', $attributes) . ")";
            }
        }

        $description = implode("<br>", $description);
        fputcsv($out, array(
            $row["id"],
            $row["fio"],
            "dikonya2010@mail.ru",
            $row["phone"],
            $row["address"],
            "United Arab Emirates",
            $row["city"],
            "COD",
            $row["amount"],
            $row["package"],
            $description,
            $row["note"]
        ));
        /* $excel->writeLine(array(
          $row["id"],
          $row["fio"],
          "dikonya2010@mail.ru",
          $row["phone"],
          $row["address"],
          "United Arab Emirates",
          $row["city"],
          "COD",
          $row["amount"],
          $row["package"],
          $description,
          $row["note"]
          )); */
    }
}
fclose($out);
//$excel->close();
