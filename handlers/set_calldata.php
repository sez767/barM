<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}

if (
        !isset($_POST['id']) ||
        !isset($_POST['action']) ||
        empty($_POST['id']) ||
        empty($_POST['action'])
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Invalid attributes"
    )));
}

require_once dirname(__FILE__) . "/../lib/db.php";

switch ($_POST['action']) {
    case "select":
        $query = mysql_query("
            INSERT INTO
                `ast_calldata`
            SET
                `call_id` = '" . mysql_real_escape_string($_POST['id']) . "'
        ");
        break;
    case "deselect":
        $query = mysql_query("
            DELETE FROM
                `ast_calldata`
            WHERE
                `call_id` = '" . mysql_real_escape_string($_POST['id']) . "'
        ");
        break;
    default:
        die(json_encode(array(
            "success" => FALSE,
            "msg" => "Undefined action"
        )));
        break;
}

if ($query) {
    die(json_encode(array(
        "success" => TRUE,
        "msg" => "Success"
    )));
}

die(json_encode(array(
    "success" => FALSE,
    "msg" => "Failed"
)));
