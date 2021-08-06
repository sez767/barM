<?php

require_once dirname(__FILE__) . "/../lib/db.php";
require_once dirname(__FILE__) . "/../lib/class.staff.php";

// Получение данных пользователя

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Authorisation required"
    )));
}

$result = array('success' => false);

foreach ($_REQUEST as &$tmpVal) {
    if (is_string($tmpVal)) {
        $tmpVal = trim($tmpVal);
    }
}

$level = (int) $_REQUEST['logistprepayment'] .
        (int) $_REQUEST['offline_island'] .
        (int) $_REQUEST['bishkek_admin_logist'] .
        (int) $_REQUEST['bishkek_logist'] .
        (int) $_REQUEST['operator_bishkek'] .
        (int) $_REQUEST['operator_seeding'] .
        (int) $_REQUEST['onexperience'] .
        (int) $_REQUEST['webmaster'] .
        (int) $_REQUEST['adminsales'] .
        (int) $_REQUEST['whatsappoperator'] .
        (int) $_REQUEST['operatorrecovery'] .
        (int) $_REQUEST['operatorcold'] .
        (int) $_REQUEST['incomeoper'] .
        (int) $_REQUEST['postlogist'] .
        (int) $_REQUEST['admincity'] .
        (int) $_REQUEST['logistcity'] .
        (int) $_REQUEST['adminlogistpost'] .
        (int) $_REQUEST['adminlogist'] .
        (int) $_REQUEST['operator'] .
        (int) $_REQUEST['logist'] .
        (int) $_REQUEST['admin'];

ApiLogger::addLogJson($_REQUEST);
ApiLogger::addLogJson("=== $level ====");

$level = bindec($level);

ApiLogger::addLogJson($level);


$Location = '';
$Delivs = '"' . implode('","', $_REQUEST['delivers']) . '"';
$web_access = '"' . implode('","', $_REQUEST['webs']) . '"';

if ((int) $_REQUEST['kz']) {
    $Location .= ',"kz"';
}
if ((int) $_REQUEST['kzg']) {
    $Location .= ',"kzg"';
}
if ((int) $_REQUEST['ru']) {
    $Location .= ',"ru"';
}
if ((int) $_REQUEST['am']) {
    $Location .= ',"am"';
}
if ((int) $_REQUEST['az']) {
    $Location .= ',"az"';
}
if ((int) $_REQUEST['uz']) {
    $Location .= ',"uz"';
}
if ((int) $_REQUEST['ae']) {
    $Location .= ',"ae"';
}
if (strlen($Location)) {
    $Location = substr($Location, 1, strlen($Location));
}

$staffObj = new StaffObj();

$actionHistoryObj = new ActionHistoryObj();
if ((int) $_GET['id']) {

    $staffObj->cSetId($_GET['id']);

    $updateArr = array(
        'FirstName' => $_REQUEST['FirstName'],
        'Position' => $_REQUEST['Position'],
        'Responsible' => $_REQUEST['Responsible'],
        'Curator' => $_REQUEST['Curator'],
        'Phone' => (!empty($_REQUEST['Phone']) ? $_REQUEST['Phone'] : DB::sqlEval('NULL')),
        'Skype' => (!empty($_REQUEST['Skype']) ? $_REQUEST['Skype'] : DB::sqlEval('NULL')),
        'Email' => (!empty($_REQUEST['Email']) ? $_REQUEST['Email'] : DB::sqlEval('NULL')),
        'City' => (!empty($_REQUEST['City']) && $_REQUEST['City'] != "--- Нет ---" ? $_REQUEST['City'] : DB::sqlEval('NULL')),
        'Birthday' => $_REQUEST['Birthday'],
        'Sip' => $_REQUEST['Sip'],
        'Predictive' => (int) $_REQUEST['Predictive'],
        'PaymentOption' => (int) $_REQUEST['PaymentOption'],
        'PaymentBase' => (int) $_REQUEST['PaymentBase'],
        'team' => (int) $_REQUEST['team'],
        'staff_oper_use' => (int) $_REQUEST['staff_oper_use'],
        'IsResponsible' => (int) $_REQUEST['IsResponsible'],
        'Bonuses' => (int) $_REQUEST['Group'],
        'staff_oper_use' => (int) $_REQUEST['staff_oper_use'],
        'IsResponsible' => (int) $_REQUEST['IsResponsible'],
        'IsCurator' => (int) $_REQUEST['IsCurator'],
        'IsPost' => (int) $_REQUEST['IsPost'],
        'Ban' => $_REQUEST['Ban'] == 'on' ? 0 : 1,
        'Secret' => $_REQUEST['Secret'],
        'Delivers' => $Delivs == '""' ? '' : $Delivs,
        'web_access' => $web_access,
        'Location' => $Location,
        'Level' => $level
    );

    if (in_array($_SESSION['Logged_StaffId'], array(63077972, 25937686))) {
        $updateArr['Rank'] = (int) ($_REQUEST['Rank']);
    }

    if (strlen($_REQUEST['Password']) > 5) {
        $updateArr['Password'] = md5($_REQUEST['Password']);
    }

    $rez = $staffObj->cSave($updateArr);

    asterisk_base();
    $checkData = DB::queryOneRow('SELECT * FROM asterisk.sip_buddies WHERE name = %s', $_REQUEST['Sip']);
    if (!empty($checkData) && strlen($_REQUEST['Password']) > 5) {
        if (DB::update('sip_buddies', array('md5secret' => md5($_REQUEST['Sip'] . ':asterisk:' . $_REQUEST['Password'])), 'name = %s', $_REQUEST['Sip'])) {
            file_get_contents('http://call.baribarda.com/index.php?prune=' . $_REQUEST['Sip']);
            file_get_contents('http://call2.baribarda.com/index.php?prune=' . $_REQUEST['Sip']);
        }
    } elseif (empty($checkData)) {
        $insArr = array(
            'callerid' => $_REQUEST['Sip'] . ' <' . $_REQUEST['Sip'] . '>',
            'crm_username' => $staffObj->cGetId(),
            'defaultuser' => $_REQUEST['Sip'],
            'name' => $_REQUEST['Sip'],
            'md5secret' => md5($_REQUEST['Sip'] . ':asterisk:' . (strlen($_REQUEST['Password']) > 5 ? $_REQUEST['Password'] : '1q2w3e')),
        );
        DB::insert('sip_buddies', $insArr);
    }
} else {
    $insArr = array(
        'id' => rand(10000000, 99999999),
        'created_by' => $_SESSION['Logged_StaffId']
    );

    if (DB::insert($staffObj->cGetTableName(), $insArr)) {
        $insId = DB::insertId();
        $staffObj->cSetId($insId);

        $updateArr = array(
            'FirstName' => $_REQUEST['FirstName'],
            'Position' => $_REQUEST['Position'],
            'Responsible' => $_REQUEST['Responsible'],
            'Curator' => $_REQUEST['Curator'],
            'Phone' => (!empty($_REQUEST['Phone']) ? $_REQUEST['Phone'] : DB::sqlEval('NULL')),
            'Skype' => (!empty($_REQUEST['Skype']) ? $_REQUEST['Skype'] : DB::sqlEval('NULL')),
            'Rank' => (int) ($_REQUEST['Rank']),
            'Email' => (!empty($_REQUEST['Email']) ? $_REQUEST['Email'] : DB::sqlEval('NULL')),
            'City' => (!empty($_REQUEST['City']) && $_REQUEST['City'] != "--- Нет ---" ? $_REQUEST['City'] : DB::sqlEval('NULL')),
            'Birthday' => $_REQUEST['Birthday'],
            'Sip' => $_REQUEST['Sip'],
            'Predictive' => (int) $_REQUEST['Predictive'],
            'staff_oper_use' => (int) $_REQUEST['staff_oper_use'],
            'IsResponsible' => (int) $_REQUEST['IsResponsible'],
            'Delivers' => $Delivs,
            'web_access' => $web_access,
            'Location' => $Location,
            'Level' => $level
        );

        if (strlen($_REQUEST['Password']) > 5) {
            $updateArr['Password'] = md5($_REQUEST['Password']);
        }
    }

    $rez = $staffObj->cSave($updateArr);

    asterisk_base();
    $checkData = DB::queryOneRow('SELECT * FROM sip_buddies WHERE name = %i', $_REQUEST['Sip']);
    if (empty($checkData) && strlen($_REQUEST['Sip']) == 4) {
        $insArr = array(
            'callerid' => $_REQUEST['Sip'] . ' <' . $_REQUEST['Sip'] . '>',
            'crm_username' => $Login,
            'defaultuser' => $_REQUEST['Sip'],
            'name' => $_REQUEST['Sip'],
            'md5secret' => md5($_REQUEST['Sip'] . ':asterisk:' . $_REQUEST['Password']),
        );
        DB::insert('sip_buddies', $insArr);
    }
}

clearGlobalCache($GLOBAL_STAFF_CACHE_NAME);

if ($rez) {
    $result = array('success' => true);
}

echo json_encode($result);
