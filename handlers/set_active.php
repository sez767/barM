<?php

header('Content-Type: text/javascript; charset=utf-8');
include('../base_connect.php');

require_once dirname(__FILE__) . '/../lib/db.php';

$result = array('success' => false);

if ((int) $_GET['id']) {
    $secret = substr(uniqid(rand(), true), 0, 16);

    $query = mysql_query("SELECT created_at, Email FROM Staff WHERE id = '" . (int) $_GET['id'] . "' ");
    $pre_pass = mysql_fetch_assoc($query);
    $pass = strrev(strtotime($pre_pass['created_at']));
    $quer = "UPDATE Staff
			 SET
			 Ban = '0',
			 Secret = '" . $secret . "'
			 WHERE id = '" . (int) $_GET['id'] . "' ";
    //var_dump($pre_pass); die;
    $rez = mysql_query($quer);
    if ($rez) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $message = 'Данные для авторизации в системе pocupay.com <br> Логин: ' . (int) $_GET['id'] . ',<br> Пароль: ' . $pass . '<br> Ключ для работы по API ' . $secret;
        $headers1 = "X-SPAM: no";
        $headers2 = "X-SPAM-STATUS: 0";
        $newAddressArr[] = trim($pre_pass['Email']);
        $newSubject = "Подтверждение регистрации Pocupay";
        $newBody = $message;
        $mailed = mail($pre_pass['Email'], "Подтверждение регистрации Pocupay", $message, "From: admin@pocupay.com\r\n" . "X-Mailer: PHP/" . phpversion());
        $result['success'] = true;
        $result['message'] = ((DobrMailSender::sendMailGetaway($newAddressArr, $newSubject, $newAttachmentArr, $newBody, $newFromName)) ? 'Сообщение отправлено' : 'Сообщение не отправлено ' . $pre_pass['Email']);
    }
}

echo json_encode($result);
