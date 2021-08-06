<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$result = array('success' => false);

$qs = " SELECT  m.Message_Id AS `Id`,
                m.created_at AS `Timestamp`,
                '' AS `Filter`,
                t.created_by AS `From`,
                m.Message_UserId AS `To`,
                Message_MessageTemplateId AS `MessageTemplateId`,
                Message_Type AS `Type`,
                MessageTemplate_Header AS `Header`,
                MessageTemplate_Text AS `Text`,
                '' AS 'SendEmail'
        FROM Message m
            LEFT JOIN MessageTemplate t ON t.MessageTemplate_Id = m.Message_MessageTemplateId
        WHERE m.Message_Id = %i";

if (($mData = DB::queryOneRow($qs, $_GET['id']))) {
    $mData['SendEmail'] = $mData['SendEmail'] ? 'yes' : 'no';
    $result['success'] = true;
    $result['data'] = $mData;
}

echo json_encode($result);
