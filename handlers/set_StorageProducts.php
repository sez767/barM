<?php

require_once dirname(__FILE__) . '/../lib/db.php';

header('Content-Type: text/javascript; charset=utf-8');
session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}

$offer = $_POST['storage']['offer'];
$offer_attributes = "";

if (
        isset($_POST['storage']['attributes']) &&
        count($_POST['storage']['attributes']) > 0
) {
    ksort($_POST['storage']['attributes']); // сортировка свойств по ключу (по возростанию)
    $offer_attributes = "[" . implode("][", $_POST['storage']['attributes']) . "]";
}

$storage_hash = md5($offer . $offer_attributes . $_POST['storage']['delivery']);

$query_storage = mysql_query("SELECT `id` FROM `storage` WHERE `hash` = '" . $storage_hash . "' LIMIT 1");

if (mysql_num_rows($query_storage) == 0) {
    if ($_GET['type'] == "write_off") {
        die(json_encode(array(
            "success" => FALSE,
            "msg" => "Нет товара на складе. Чтобы списать товар надо внести его на склад!"
        )));
    }

    $query_offers = mysql_query("SELECT `offer_name` FROM `offers` WHERE `offer_name` = '" . mysql_real_escape_string($offer) . "' LIMIT 1");

    if (mysql_num_rows($query_offers) == 0) {
        die(json_encode(array(
            "success" => FALSE,
            "msg" => "Оффер " . $offer . " не найден. Добавьте его в редакторе товаров!"
        )));
    }

    $row_offers = mysql_fetch_array($query_offers, MYSQL_ASSOC);

    // добавить запись в `storage`
    mysql_query("
        INSERT INTO
            `storage`
        SET
            `hash` = '" . $storage_hash . "',
            `offer` = '" . mysql_real_escape_string($offer) . "',
            `property` = '" . mysql_real_escape_string($offer_attributes) . "',
            `delivery` = '" . mysql_real_escape_string($_POST['storage']['delivery']) . "',
            `quantity` = 0,
            `comment` = '" . mysql_real_escape_string(substr($_POST['storage']['comment'], 0, 1500)) . "'
    ");

    $storage_id = mysql_insert_id();
} else {
    // ID товара в складе
    $row_storage = mysql_fetch_array($query_storage, MYSQL_ASSOC);
    $storage_id = $row_storage['id'];
}

$query_storage_action = mysql_query("
    INSERT INTO
        `storage_action`
    SET
        `storage_id` = " . $storage_id . ",
        `action_type` = '" . mysql_real_escape_string($_GET['type']) . "',
        `delivery` = '" . mysql_real_escape_string($_POST['storage']['delivery']) . "',
        `action_value` = " . ((int) $_POST['storage']['count']) . ",
        `staff_id` = " . $_SESSION['Logged_StaffId'] . "
");

if ($query_storage_action) {

    $storage = new Storage();

    switch ($_GET['type']) {
        case "income":
            $storage->updateStorage($storage_hash, ((int) $_POST['storage']['count']), "add");
            break;
        case "write_off":
            $storage->updateStorage($storage_hash, ((int) $_POST['storage']['count']), "remove");

            if (
                    isset($_POST['storage']['shipment']) &&
                    isset($_POST['storage']['shipment_delivery'])
            ) {
                $storage_shipment_hash = md5($offer . $offer_attributes . $_POST['storage']['shipment_delivery']);

                $query_storage = mysql_query("SELECT `id` FROM `storage` WHERE `hash` = '" . $storage_shipment_hash . "' LIMIT 1");

                if (mysql_num_rows($query_storage) == 0) {
                    $query_offers = mysql_query("SELECT `offer_name` FROM `offers` WHERE `offer_name` = '" . mysql_real_escape_string($offer) . "' LIMIT 1");

                    if (mysql_num_rows($query_offers) == 0) {
                        die(json_encode(array(
                            "success" => FALSE,
                            "msg" => "Оффер " . $offer . " не найден. Добавьте его в редакторе товаров!"
                        )));
                    }

                    $row_offers = mysql_fetch_array($query_offers, MYSQL_ASSOC);

                    // добавить запись в `storage`
                    mysql_query("
                        INSERT INTO
                            `storage`
                        SET
                            `hash` = '" . $storage_shipment_hash . "',
                            `offer` = '" . mysql_real_escape_string($offer) . "',
                            `property` = '" . mysql_real_escape_string($offer_attributes) . "',
                            `delivery` = '" . mysql_real_escape_string($_POST['storage']['shipment_delivery']) . "',
                            `quantity` = 0,
                            `comment` = 'Выгрузка с " . mysql_real_escape_string($_POST['storage']['delivery']) . "'
                    ");

                    $storage_id = mysql_insert_id();
                } else {
                    // ID товара в складе
                    $row_storage = mysql_fetch_array($query_storage, MYSQL_ASSOC);
                    $storage_id = $row_storage['id'];
                }

                $storage->updateStorage($storage_shipment_hash, ((int) $_POST['storage']['count']), "add");

                mysql_query("
                    INSERT INTO
                        `storage_action`
                    SET
                        `storage_id` = " . $storage_id . ",
                        `action_type` = 'income',
                        `delivery` = '" . mysql_real_escape_string($_POST['storage']['shipment_delivery']) . "',
                        `action_value` = " . ((int) $_POST['storage']['count']) . ",
                        `staff_id` = " . $_SESSION['Logged_StaffId'] . "
                ");
            }
            break;
    }

    $result = array(
        "success" => TRUE,
        // "msg" => "Товар " . $offer_title . " добавлен на склад!",
        "id" => mysql_insert_id()
    );
} else {
    $result = array(
        "success" => FALSE,
        "msg" => mysql_error()
    );
}

echo json_encode($result);
