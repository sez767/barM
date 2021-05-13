<?php

// phpinfo();

header('Content-Type: text/html; charset=utf-8');
// require_once (dirname(__FILE__) . "/lib/db.php");
// require_once (dirname(__FILE__) . "/lib/class.staff.php");
// session_set_cookie_params(0, '/', '', true, true);
// if (!session_id()) {
//     session_start();
// }
// $ajax = ( isset($_POST['ajax']) ) ? true : false;
// $redirect_to_cabinet = true; // if user is already logged => redirect to cabinet
// $result = array();
// $result['error'] = 'error';
// $actionHistoryObj = new ActionHistoryObj();

if (isset($_POST['login']) && isset($_POST['password'])) {

    $Login = (int) $_POST['login'];
    $PasswordMD5 = md5($_POST['password']);
    $STAFF = new Staff($Login);
    $ip = $_SERVER['REMOTE_ADDR'];
    // var_dump($_SESSION['captcha_keystring']); die;
    //if (isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] == $_POST['CAPTCHA'] ) {

    if ($STAFF->Password == $PasswordMD5 && (int)$STAFF->Type) {
        //var_dump($_SESSION); die;
        //if(!$STAFF->Ban){
        $_SESSION['Logged_StaffId'] = $STAFF->Id;
        $_SESSION['Logged_Group'] = (int) $STAFF->Group;
        $_SESSION['Logged_StaffName'] = trim("$STAFF->FirstName $STAFF->LastName");

        $_SESSION['Sip'] = $STAFF->Sip;
        $_SESSION['team'] = (int) $STAFF->team;
        $_SESSION['Rank'] = (int) $STAFF->Rank;
        $_SESSION['FirstName'] = $STAFF->FirstName;
        $_SESSION['country'] = $STAFF->Location;
        $_SESSION['delivers'] = $STAFF->Delivers;
        $_SESSION['staff_oper_use'] = (int) $STAFF->staff_oper_use;
        $_SESSION['IsResponsible'] = (int) $STAFF->IsResponsible;
        $_SESSION['IsCurator'] = (int) $STAFF->IsCurator;
        $_SESSION['IsPost'] = (int) $STAFF->IsPost;
        // ��?��?��?����
        $_SESSION['admin'] = $STAFF->admin;
        $_SESSION['logist'] = $STAFF->logist;
        $_SESSION['logistcity'] = $STAFF->logistcity;
        $_SESSION['operator'] = $STAFF->operator;
        $_SESSION['adminlogist'] = $STAFF->adminlogist;
        $_SESSION['adminlogistpost'] = $STAFF->adminlogistpost;
        $_SESSION['admincity'] = $STAFF->admincity;
        $_SESSION['postlogist'] = $STAFF->postlogist;
        $_SESSION['incomeoper'] = $STAFF->incomeoper;
        $_SESSION['operatorcold'] = $STAFF->operatorcold;
        $_SESSION['operatorrecovery'] = $STAFF->operatorrecovery;
        $_SESSION['whatsappoperator'] = $STAFF->whatsappoperator;
        $_SESSION['adminsales'] = $STAFF->adminsales;
        $_SESSION['onexperience'] = $STAFF->onexperience;
        $_SESSION['operator_seeding'] = $STAFF->operator_seeding;
        $_SESSION['operator_bishkek'] = $STAFF->operator_bishkek;
        $_SESSION['bishkek_logist'] = $STAFF->bishkek_logist;
        $_SESSION['bishkek_admin_logist'] = $STAFF->bishkek_admin_logist;
        $_SESSION['offline_island'] = $STAFF->offline_island;
        $_SESSION['logistprepayment'] = $STAFF->logistprepayment;

        if (($_SESSION['webmaster'] = $STAFF->webmaster)) {
            $_SESSION['web_access'] = $STAFF->web_access;
        }

        // START TIMEZONES
        getDBTimeZoneOfsset($STAFF->timeZoneName);
        // END TIMEZONES

        $r_data = array(
            'Logged_StaffId' => $STAFF->Id,
            'Sip' => $STAFF->Sip,
            'session_id' => session_id(),
            'FirstName' => $STAFF->FirstName,
            'country' => $STAFF->Location,
            'admin' => $STAFF->admin,
            'logist' => $STAFF->logist,
            'operator' => $STAFF->operator,
            'ip' => $ip
        );
        $redis = RedisManager::getInstance()->getRedis();
        $redis->hMset('Staff/' . $STAFF->Id, $r_data);
        $redis->setTimeout('Staff/' . $STAFF->Id, 900);

        $actionHistoryObj->save('StaffObj', $STAFF->Id, 'login', 'login', '', 'ok', 'logged in, IP:' . $ip);

        $redirect_to_cabinet = true;
        $result['success'] = 'true';
    } else {
        $actionHistoryObj->save('StaffObj', $STAFF->Id, 'login', 'login', '', 'error', 'incorrect password, IP:' . $ip);
        $redirect_to_cabinet = false;
        $result['error'] = '��?���̧�?��?��?��?���̧�?��?��?��?���� ��?���㧳?��?��?��? ���ק�?���� ��?��?��?���ק�?';
    }
} else {
    $redirect_to_cabinet = false;
}
if ($redirect_to_cabinet) {
    $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'];

    if ($ajax) {
        echo json_encode($result);
        die();
    }

    if (!isset($_SESSION['Logged_StaffId'])) {
        ?>
        <script type="text/javascript">
            var alertMsg = '<?php print isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ''; ?>';
            if (alertMsg.length > 0) {
                alert(alertMsg);
            }
            window.location = "http://<?php echo $_SERVER['HTTP_HOST']; ?>/login.html#" + alertMsg;

        </script> <?php
        die();
    }
    header("location: /account.php");
} else {
    //var_dump($ajax); die;
    if ($ajax) {
        echo json_encode($result);
        die();
    }
    if (isset($_SESSION['Logged_StaffId'])) {
        $redis = RedisManager::getInstance()->getRedis();
    }
    ?>
    <script type="text/javascript">
        var alertMsg = '<?php print isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ''; ?>';
        if (alertMsg.length > 0) {
            alert(alertMsg);
        }
        window.location = "http://<?php echo $_SERVER['HTTP_HOST']; ?>/login.html#" + alertMsg;
    </script> <?php
    die();
}
