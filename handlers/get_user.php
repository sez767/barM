<?php

require_once dirname(__FILE__) . "/../lib/db.php";
require_once dirname(__FILE__) . "/../lib/class.staff.php";

// Получение данных пользователя
if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Authorisation required"
    )));
}



$obj = new stdClass();

ApiLogger::addLogVarExport('START');

$query = "
	SELECT
		`id`,
		`FirstName`,
		`LastName`,
		`Position`,
		`Responsible`,
		`Curator`,
		`Birthday`,
		`Level`,
		`Location`,
		`Delivers`,
		`Type`,
		`Predictive`,
		`Sip`,
		`Bonuses`,
		`PaymentOption`,
		`PaymentBase`,
		`team`,
		`staff_oper_use`,
		`IsResponsible`,
		`IsCurator`,
		`IsPost`,
		`Ban`,
		`Secret`,
		`Skype`,
		`Rank`,
		`Email`,
		`Phone`,
		`City`,
        `web_access`,
        `created_by`
	FROM `Staff`
	WHERE `id` = '" . mysql_real_escape_string($_GET['id']) . "'
	LIMIT 1";

$rs = mysql_query($query);

if (mysql_num_rows($rs) > 0) {
    $obj = mysql_fetch_object($rs);

    // Подменяем 0 на 1 и 1 на 0
    $obj->Ban = $obj->Ban == 0 ? 'on' : 'off';

    $obj->Location = str_replace('"', '', $obj->Location);
    $sublocation = explode(',', $obj->Location);
    foreach ($sublocation as $sv) {
        if ($sv) {
            $obj->{$sv} = 1;
        }
    }

    $obj->Delivers = str_replace('"', '', $obj->Delivers);
    $obj->Delivers = explode(',', $obj->Delivers);

    $obj->web_access = str_replace('"', '', $obj->web_access);
    $obj->web_access = explode(',', $obj->web_access);

    $obj->admin = (int) (($obj->Level & 1) > 0);
    $obj->logist = (int) (($obj->Level & 2) > 0);
    $obj->operator = (int) (($obj->Level & 4) > 0);
    $obj->adminlogist = (int) (($obj->Level & 8) > 0);
    $obj->adminlogistpost = (int) (($obj->Level & 16) > 0);
    $obj->logistcity = (int) (($obj->Level & 32) > 0);
    $obj->admincity = (int) (($obj->Level & 64) > 0);
    $obj->postlogist = (int) (($obj->Level & 128) > 0);
    $obj->incomeoper = (int) (($obj->Level & 256) > 0);
    $obj->operatorcold = (int) (($obj->Level & 512) > 0);
    $obj->operatorrecovery = (int) (($obj->Level & 1024) > 0);
    $obj->whatsappoperator = (int) (($obj->Level & 2048) > 0);
    $obj->adminsales = (int) (($obj->Level & 4096) > 0);
    $obj->webmaster = (int) (($obj->Level & 8192) > 0);
    $obj->onexperience = (int) (($obj->Level & 16384) > 0);
    $obj->operator_seeding = (int) (($obj->Level & 32768) > 0);
    $obj->operator_bishkek = (int) (($obj->Level & 65536) > 0);
    $obj->bishkek_logist = (int) (($obj->Level & 131072) > 0);
    $obj->bishkek_admin_logist = (int) (($obj->Level & 262144) > 0);
    $obj->offline_island = (int) (($obj->Level & 524288) > 0);
    $obj->logistprepayment = (int) (($obj->Level & 1048576) > 0);

    $obj->Group = $obj->Bonuses;
    if (!isset($obj->City) || empty($obj->City)) {
        $obj->City = "--- Нет ---";
    }
}

/////////////////////////////////////////////////////////////
//echo GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=delivery_couriers&full=1' . PHP_EOL;

$jd_temp = json_decode(file_get_contents_curl(GLOBAL_STORE_BASE_URL . '/handlers/get_StoreData.php?data=delivery_couriers&full=1', 360), true);
//print_r($jd_temp);
//die('ok');
$delivery_couriers = reset($jd_temp);

$obj->delivery_access = array();
foreach ($delivery_couriers as $value) {
    if (!in_array($value['value'], array('Почта', 'Вся курьерка', 'Работает', 'Не работает'))) {
        $obj->delivery_access[] = array(
            'boxLabel' => $value['value'],
            'inputValue' => $value['value'],
            'checked' => (isset($obj->Delivers) && in_array($value['value'], $obj->Delivers)) ? true : false
        );
    }
}

//die('ololo4');
/////////////////////////////////////////////////////////////
// INIT
$memcache = MemcacheManager::getInstance()->getMemcache();

$WEB_IDS = array();
$qs = "SELECT DISTINCT(`web_id`) as Field FROM `staff_order` WHERE `date` > NOW() - INTERVAL 3 MONTH AND web_id > 0 AND kz_delivery != '' ORDER BY web_id";
if (true || ($result = $memcache->get(md5($qs)))) {
    $WEB_IDS = array(
        0 => '666666',
    );
} else {
    DB::queryFirstColumn($qs);
    $WEB_IDS = DB::queryFirstColumn($qs);
    $memcache->set(md5($qs), $WEB_IDS, false, 43200); // 12 часов
}
ApiLogger::addLogVarExport($WEB_IDS);

// END INIT

$obj->webs = array();
foreach ($WEB_IDS as $value) {
    $obj->webs[] = array(
        "boxLabel" => $value,
        "inputValue" => $value,
        "checked" => (isset($obj->web_access) && in_array($value, $obj->web_access) ) ? true : false
    );
}

// Show result
echo json_encode(array(
    "success" => TRUE,
    "data" => $obj
));
