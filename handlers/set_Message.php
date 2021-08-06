<?php

require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

$result = '{"success":false}';

$quer = "INSERT MessageTemplate
                 SET MessageTemplate_Header = '" . $_REQUEST['Header'] . "',
                 MessageTemplate_Text = '" . $_REQUEST['Text'] . "',
                 created_by = " . $_SESSION['Logged_StaffId'] . " ";
$rez = mysql_query($quer);

if ($rez) {
    $ins_id = mysql_insert_id();
    if (strlen($_REQUEST['groups']) > 5) {
        switch ($_REQUEST['groups']) {
            case 'Операторы КЗ': $gr_sel = "SELECT id FROM Staff WHERE Level IN (4,6) AND type = 1 AND location LIKE '%\"kz\"%'";
                break;
            case 'Операторы RU': $gr_sel = "SELECT id FROM Staff WHERE Level IN (4,6) AND type = 1 AND location LIKE '%\"ru\"%'";
                break;
            case 'Операторы КГЗ,АМ,УЗб': $gr_sel = "SELECT id FROM Staff WHERE Level IN (4,6) AND type = 1 AND location LIKE '%\"kzg\"%'";
                break;
            case 'Админы': $gr_sel = "SELECT id FROM Staff WHERE Level IN (1) AND type = 1";
                break;
            case 'Логисты': $gr_sel = "SELECT id FROM Staff WHERE Level IN (2,6) AND type = 1";
                break;
        }
        $gr_rez = mysql_query($gr_sel);
        while ($gr_rezult = mysql_fetch_array($gr_rez)) {
            mysql_query("INSERT Message SET
                    Message_UserId = " . $gr_rezult['id'] . ",
                    Message_Type = 'flash',
                    Message_Status = 'unread',
                    Message_MessageTemplateId = " . $ins_id);
        }
    }

    if ((int) $_REQUEST['user'][0]) {
        foreach ($_REQUEST['user'] as $k => $v) {
            mysql_query("INSERT Message SET
                        Message_UserId = " . (int) $v . ",
                        Message_Type = 'flash',
                        Message_Status = 'unread',
                        Message_MessageTemplateId = " . $ins_id);
        }
    }

    $result = '{"success":true}';
}

echo $result;
?>
