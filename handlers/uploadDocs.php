<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");



//  проверка файла на ошибки
if (
        $_FILES['document']['error'] != 0
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Ошибка: #" . $_FILES['document']['error']
    )));
}


if (
        $_FILES['document']['size'] <= 100
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Слишком малый размер файла " . $_FILES['document']['name'] . ". Проверьте целостность данных!"
    )));
}
if (
        !isset($_FILES['document']['tmp_name']) ||
        empty($_FILES['document']['tmp_name']) ||
        !file_exists($_FILES['document']['tmp_name'])
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "File not found"
    )));
}

$uploadDir = './../docs/';
$uploadfile = $uploadDir . basename($_FILES['document']['name']);

// Копируем файл из каталога для временного хранения файлов:
if (copy($_FILES['document']['tmp_name'], $uploadfile)) {
    print json_encode(array(
        "success" => TRUE,
        "msg" => 'Файл загружен'
    ));
} else {
    print json_encode(array(
        "success" => FALSE,
        "msg" => 'Файлу жопа'
    ));
}
