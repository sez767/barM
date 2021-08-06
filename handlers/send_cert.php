<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("Location: login.html");
    die;
}
include_once dirname(__FILE__) . "/../lib/db.php";
require_once dirname(__FILE__) . '/../ini/php_mailer_inited.php';

$offer = $_GET['offer'];
try {
    $newSubject = 'Сертификат на товар ' . $offer;
    $newBody = "Шлем мы Вам сертификат, чтоб сомнения развеять, покупайте наш товар и получите эффект. Рахмет";
    $newAttachmentArr[] = '/var/www/baribarda.com/resources/cert/' . $offer . '.jpg';
    $newAddressArr[] = $_GET['mail'];

    $se = DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName);

    if ($se) {
        $result['data'][] = "Письмо для " . $_GET['mail'] . " отправлено на E-Mail " . $_GET['mail'];
    } else {
        $result['data'][] = "Ошибка отправки письма для " . $_GET['mail'] . " на E-Mail " . $_GET['mail'];
    }
} catch (PHPMailer\PHPMailer\Exception $e) {
    $result['data'][] = $e->errorMessage();
} catch (PHPMailer\PHPMailer\Exception $e) {
    $result['data'][] = $e->getMessage();
}
