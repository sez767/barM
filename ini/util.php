<?php

if (!isset($_SESSION['offline_island'])) {
    $_SESSION['offline_island'] = 0;
}

if (empty($_SERVER['SERVER_NAME'])) {
    //define('GLOBAL_STORE_BASE_URL', 'http://89.218.86.178:8081');
    define('GLOBAL_STORE_BASE_URL', 'http://baribarda.com');
} else {
//    define('GLOBAL_STORE_BASE_URL', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . ('://') . (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '')));
    define('GLOBAL_STORE_BASE_URL', 'http://baribarda.com');
}

// Массив включающий механизм логирования
//$logerArr = array(11111111, 11112222, 11113333, 11119999, 25937686, 66049820, 24511553);
$logerArr = array(11111111, 11112222, 11113333, 11119999, 25937686, 24511553, 14175067);

// Массив для списка "Админ" в "анкете доставки" и кнопки "массовые изменения"
$GLOBAL_ALL_COLD_STAFF_ARR = array(
    58417648, 0137635, 97979449, 93991201, 93375132, 90871721, 91318760, 95873538, 99171796, 47369504,
    57369831, 33333333, 32818339, 78017798, 49152384, 48514518, 71171003, 48061934, 45033811, 42655111,
    20217943, 47063460, 36481874, 31769332, 22222222, 22224444, 55555555, 55557777, 55556666
);

// Просчет "HOT filter :)" в Доставке
$GLOBAL_FILTER_CALC_ARR = array(98986784);

// Выводить итого по всем страницам в Доставке
//$GLOBAL_TOTAL_CALC_DATA = array(24511553, 25937686, 66629642, 44917943, 63077972);
$GLOBAL_TOTAL_CALC_DATA = array(24511553, 25937686, 66629642, 44917943, 88189675, 72758398, 66049820, 60457955, 36710186);
//$GLOBAL_TOTAL_CALC_DATA = array();
//
$GLOBAL_QUEUE_CLOSE_PROTECTED_ARR = array(
    'otkaz1_P'
);

$GLOBAL_ORIGINAL_PARFUME_ARR = array('Carolina_Herrera_212_VIP', 'Chanel_5', 'Chanel_Allure_Homme_Sport', 'Chanel_bleu_de_Chanel', 'Chanel_Chance_Eau_Fraiche', 'Chanel_Chance_Eau_Tendre', 'Chanel_Chance_VIVE', 'Chanel_Coco_Mademoiselle', 'Christian_Dior_Sauvage', 'Creed_Aventus', 'Creed_Viking', 'creed_viking_new', 'Cristian_Dior_Jadore', 'DG_3_LImperatrice', 'DolceGabanna_Light_Blue_Pour_Homme', 'Giorgio_Armani_Acqua_di_Gio_Pour_Homme', 'Givenchy_Ange_ou_Demon', 'Givenchy_Ange_ou_Demon_Le_Secret', 'Givenchy_Pour_Homme_Blue_Label', 'GOOD_GIRL_CH_BLACK', 'GOOD_GIRL_CH_RED', 'GOOD_GIRL_CH_WHITE', 'Guerlan_Robe_Noir', 'Lancome_LA_VIE_EST_BELLE', 'Lanvin_Eclat_Darpege', 'Miss_Dior_Cherie_Blooming_Bouquet', 'Montale_Honey_AOUD', 'Montale_Pure_Gold', 'sauvage', 'TOM_FORD_ORCHID_SOLEIL', 'TOM_FORD_VELVET_ORCHID', 'Versace_BRIGHT_CRYSTAL', 'Yves_Saint_Laurent_Black_Opium', 'Парфюм original', 'chanel-present набор', 'original_parfume_1rub', 'desire_m', 'desire_w', 'original_parfume', 'top-parfym');

$_SESSION['api_log_enable'] = empty($_SESSION['Logged_StaffId']) ? false : in_array($_SESSION['Logged_StaffId'], $logerArr);

function getClientsSettings($staffId = null) {
    global $GLOBAL_CURATOR_CLIENT_GROUP_SETTINGS;
    $ret = array();
    if (($curatorId = getStaffCurator($staffId)) && array_key_exists($curatorId, $GLOBAL_CURATOR_CLIENT_GROUP_SETTINGS)) {
        $ret = $GLOBAL_CURATOR_CLIENT_GROUP_SETTINGS[$curatorId];
    }
    return $ret;
}

function getStaffCurator($staffId = null) {
    global $GLOBAL_STAFF_CURATOR;
    $ret = 0;
    if (empty($staffId) && !empty($_SESSION['Logged_StaffId'])) {
        $staffId = $_SESSION['Logged_StaffId'];
    }

    if (!empty($staffId)) {
        if (array_key_exists($staffId, $GLOBAL_STAFF_CURATOR)) {
            $ret = $GLOBAL_STAFF_CURATOR[$staffId];
        }
    }
    return $ret;
}

function getOtpravitelStr($data) {
    global $GLOBAL_ORIGINAL_PARFUME_ARR;

    $zarovshbekovaArr = array(
        'Араван Почта', 'Баткен курьер', 'Исфана курьер', 'Кадамжай почта', 'Каракуль почта', 'Карасу курьер', 'Кызыл-кия курьер',
        'Ноокат Почта', 'Ош курьер', 'Таш-Кумыр курьер', 'Токтогул почта', 'Узген', 'Джалал-Абад курьер', 'Базаркоргон Почта', 'Массы почта',
        'Кочкор ата Почта', 'Майлуу-Суу почта', 'Сузак почта', 'Сулюкта почта', 'Алабука Почта', 'Кербен Почта'
    );
    $stanbekArr = array(
        'Балыкчи курьер', 'Беловодское Почта', 'Бишкек курьер', 'Бостари Почта', 'Ивановка Почта', 'Кант Почта', 'Карабалта курьер', 'Каракол курьер',
        'Кемин Почта', 'Кочкорка почта', 'Нарын курьер', 'Новопавловка курьер', 'Новопокровка Почта', 'Сокулык Почта', 'Талас курьер', 'Токмок курьер', 'Чолпон Почта'
    );

    $ret = 'ТОО «KAZECOTRANSIT»';
    if (in_array($data['kz_delivery'], $zarovshbekovaArr)) {
        $ret = 'ОСОО КБТ';
    } elseif (in_array($data['kz_delivery'], $stanbekArr) && false) {
        $ret = 'ИП Дайырбекова Д';
    } elseif ($data['country'] == 'kz') {
        $ret = 'TOO KBT group';

        if (in_array($data['offer'], $GLOBAL_ORIGINAL_PARFUME_ARR)) {
            $ret = 'TOO KBT group';
        }
    } elseif ($data['country'] == 'am') {
        $ret = 'ЧП «Саргсян»';
    } elseif ($data['country'] == 'kzg') {
        $ret = 'ОСОО КБТ';
    } elseif ($data['country'] == 'uz') {
        $ret = 'ИП ТОКТАССЫНОВ И.';
    }
    return $ret;
}

/**
 *
 * @global array $GLOBAL_ORIGINAL_PARFUME_ARR
 * @param type $data
 * @return string
 */
function getOurAddressStr($data) {
    global $GLOBAL_ORIGINAL_PARFUME_ARR;

    $ret = '';
    if ($data['country'] == 'kz' || $data['country'] == 'KZ') {
        $ret = 'г. Алматы ,
ул Мынбаева 151
Бизнес-центр "VERUM"
4 этаж, 46 каб.';
        $ret = 'Нур-Султан (Астана)
Аманат 2';
        if (in_array($data['offer'], $GLOBAL_ORIGINAL_PARFUME_ARR)) {
            $ret = 'г. Алматы ,
ул Мынбаева 151
Бизнес-центр "VERUM"
4 этаж, 46 каб.';
            $ret = 'Нур-Султан (Астана)
Аманат 2';
        }
    }
    return $ret;
}

/**
 *
 * @global type $GLOBAL_STAFF_CURATOR
 * @param type $staffId
 * @param type $defaultClientGroup
 * @return type
 */
function getClientGroupByStaffId($staffId, $defaultClientGroup = null) {
    global $GLOBAL_STAFF_CURATOR, $GLOBAL_CURATOR_STAFF;

    $ret = false;

    if (
            in_array($staffId, array(44917943, 11594646, 67427284, 72758398, 60457955, 36710186, 13717713, 35258284, 64395288)) ||
            (!empty($GLOBAL_STAFF_CURATOR[$staffId]) && in_array($GLOBAL_STAFF_CURATOR[$staffId], array(44917943, 11594646, 67427284, 72758398, 60457955, 36710186, 13717713, 35258284, 64395288))) ||
            array_key_exists($staffId, $GLOBAL_CURATOR_STAFF)
    ) {
        if (in_array($staffId, array(36710186)) || in_array($GLOBAL_STAFF_CURATOR[$staffId], array(36710186))) {
            $ret = '1, 2';
        } elseif (in_array($staffId, array(44917943)) || in_array($GLOBAL_STAFF_CURATOR[$staffId], array(44917943))) {
            $ret = 1;
        } elseif (in_array($staffId, array(13717713)) || in_array($GLOBAL_STAFF_CURATOR[$staffId], array(13717713))) {
            $ret = '1, 2';
        } elseif (in_array($staffId, array(11594646)) || in_array($GLOBAL_STAFF_CURATOR[$staffId], array(11594646))) {
            $ret = 2;
        } elseif ($staffId == 67427284 || $GLOBAL_STAFF_CURATOR[$staffId] == 67427284) {
            $ret = 3;
//        } elseif ($staffId == 86208368 || $GLOBAL_STAFF_CURATOR[$staffId] == 86208368) {
//            $ret = 4;
        } elseif ($staffId == 72758398 || $GLOBAL_STAFF_CURATOR[$staffId] == 72758398) {
            $ret = 5;
        } elseif ($staffId == 60457955 || $GLOBAL_STAFF_CURATOR[$staffId] == 60457955) {
            $ret = 6;
        } elseif ($staffId == 35258284 || $GLOBAL_STAFF_CURATOR[$staffId] == 35258284) {
            $ret = 7;
        } elseif (array_key_exists($staffId, $GLOBAL_CURATOR_STAFF)) {
            $ret = '1, 2, 3, 4, 5, 6, 7';
        }
    }

    if (empty($ret) && !empty($defaultClientGroup)) {
        $ret = $defaultClientGroup;
    }

    return $ret;
}

/**
 *
 * @param type $ordersArr
 * @param type $prognoz
 * @return type
 */
function getKonkusBiletCount($ordersArr, $prognoz = false) {
    $ret = array();

    $uuIds = array();
    foreach ($ordersArr as $orderItem) {
        $uuIds[$orderItem['id']] = $orderItem['uuid'];
    }

    $qs = " SELECT COUNT(`id`) AS payedCount, `uuid`
                FROM `staff_order`
                    WHERE   `status` = 'Подтвержден' AND
                            (
                            `return_date` > '2019-01-01 00:00:00' AND `send_status` = 'Оплачен'
                            OR
                            `give_date` > '2019-01-01 00:00:00' AND `send_status` = 'Полная предоплата'
                            ) AND uuid IN %ls
                    GROUP BY uuid";
    $payedOrders = DB::queryAssData('uuid', 'payedCount', $qs, $uuIds);

    foreach ($ordersArr as $orderItem) {
        $currentKoef = 0;
        if (array_key_exists($orderItem['uuid'], $payedOrders)) {
            $currentKoef = $payedOrders[$orderItem['uuid']];
        }
        if ($prognoz) {
            $currentKoef++;
        }

        $orderItem['dopTovarArr'] = json_decode($orderItem['dop_tovar'], true);
        $offersCount = (int) $orderItem['package'];
        if (!empty($orderItem['dopTovarArr']['dop_tovar_count'])) {
            $offersCount += (int) array_sum($orderItem['dopTovarArr']['dop_tovar_count']);
        }

        $ret[$orderItem['id']] = $currentKoef * $offersCount;
    }

    return $ret;
}

function getSupportPhoneStr($data) {
    global $GLOBAL_OFFER_GROUP;

    $ret = '* Служба заботы о клиентах +7(705)924 03 70';
    if ($data['country'] == 'kz' || $data['country'] == 'KZ') {
        if ($GLOBAL_OFFER_GROUP[$data['offer']] != 'Парфюмерия') {
            $ret = '* Служба заботы о клиентах тел 2442';
        } else {
            $ret = '* Служба заботы о клиентах тел 2442';
        }
    } elseif ($data['country'] == 'kzg' || $data['country'] == 'KZG') {
        $ret = '* Служба заботы о клиентах +996770008168, +996770008162, +996770008160';
    }

    return $ret;
}

function clearGlobalCache($cacheKeyName) {
    $memcache = MemcacheManager::getInstance()->getMemcache();
    $memcache->delete(md5($cacheKeyName));
}

/**
 * Returns the number of available CPU cores
 *
 *  Should work for Linux, Windows, Mac & BSD
 *
 * @return integer
 */
function num_cpus() {
    static $numCpus = null;

    if ($numCpus === null) {
        $numCpus = 1;
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $numCpus = count($matches[0]);
        } else if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');
            if (false !== $process) {
                fgets($process);
                $numCpus = intval(fgets($process));
                pclose($process);
            }
        } else {
            $process = @popen('sysctl -a', 'rb');
            if (false !== $process) {
                $output = stream_get_contents($process);
                preg_match('/hw.ncpu: (\d+)/', $output, $matches);
                if ($matches) {
                    $numCpus = intval($matches[1][0]);
                }
                pclose($process);
            }
        }
    }

    return $numCpus;
}

function initGlobalVars() {

    $redis = RedisManager::getInstance()->getRedis();
    global $GLOBAL_CALCULATE_TOTAL_OBZVON, $GLOBAL_CALCULATE_TOTAL_DELIVERY;
    global $GLOBAL_CLOSED_CLIENT_GROUPS;

    $status = $redis->get('calculateTotalObzvon');
    $GLOBAL_CALCULATE_TOTAL_OBZVON = ($status || $status === false);
    $status = $redis->get('calculateTotalDelivery');
    $GLOBAL_CALCULATE_TOTAL_DELIVERY = ($status || $status === false);
    $status = $redis->get('closedClientGroups');
    $GLOBAL_CLOSED_CLIENT_GROUPS = ($status || $status === false);

    if (
            $_SESSION['logist'] ||
            $_SESSION['operator'] ||
            $_SESSION['postlogist'] ||
            $_SESSION['incomeoper'] ||
            $_SESSION['operatorrecovery'] ||
            $_SESSION['operator_bishkek'] ||
            $_SESSION['bishkek_logist'] ||
            $_SESSION['operatorcold']
    ) {
        $GLOBAL_CALCULATE_TOTAL_DELIVERY = false;
    }


    $memcache = MemcacheManager::getInstance()->getMemcache();

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_CURATOR_CLIENT_GROUP_SETTINGS;
    $GLOBAL_CURATOR_CLIENT_GROUP_SETTINGS = array(
        44917943 => array('client_group' => 1, 'country' => 'kz'),
        36710186 => array('client_group' => 1, 'country' => 'kzg'),
        11594646 => array('client_group' => 2, 'country' => 'kz'),
        13717713 => array('client_group' => 2, 'country' => 'kzg'),
        67427284 => array('client_group' => 3, 'country' => 'kz'),
        86208368 => array('client_group' => 4, 'country' => 'kz'),
        72758398 => array('client_group' => 5, 'country' => 'kz'),
        60457955 => array('client_group' => 6, 'country' => 'kz'),
    );
/////////////////////////////////////////
global $GLOBAL_OFFERS_COST_PRICE_ASS; 

    if (($result = $memcache->get(md5('GLOBAL_OFFERS_COST_PRICE_ASS'))) && true) {
        $GLOBAL_OFFERS_COST_PRICE_ASS = $result;
    } else {
        $qs = 'SELECT * FROM coffee.offers WHERE 1';
        $tmpArr = array('actiferil', 'active_dry', 'alphavit', 'androcaps', 'barberry_eye_patch', 'BB_Blanc_cream', 'bella', 'bellona', 'BIO_DERM', 'black_latte', 'BLACK_SENSATION', 'britva', 'Bronkhiset', 'Cardiovit', 'clear_mask', 'clear_skin', 'coco_scrub', 'coffee_varka', 'complited_diet', 'cytoforte', 'Detoxol', 'detskie_razvivashki', 'dog_teacher', 'domik_palatka', 'double_effect', 'duhovaya_pech', 'D_norm', 'Eco_Slim', 'embryo', 'farsali', 'fertile', 'fer_parfume', 'frash_peeling', 'fruit_dryer', 'gelminot', 'gepakaps', 'giant', 'Gipertanol', 'grillnica', 'immunex', 'inno-gialuron', 'keto_guru', 'kilian_parfume', 'kostyum_dzhinsovy', 'laboratoriya', 'laminary', 'laminary_cream', 'lanbena', 'lantana', 'LipsPomada', 'lorevit', 'lucem', 'lucem_bio', 'lucem_plus', 'lucem_vacci', 'maral_gel', 'maslo_chernogo_tmina', 'mikocel', 'minoxidil_woman', 'motodron', 'multislicer', 'multivarka', 'myasorubka', 'nabor_kastryul', 'noodle_machine', 'Oftal_plus', 'operaciya_prishelec', 'oral_spray', 'Papillock', 'pirog_v_lico', 'pomada_qibest', 'princess_hair', 'Proctotel', 'Psoristin', 'Ren_Tea', 'Risingstar', 'Salon_hair_mask', 'sandwich_machine', 'sanoflex', 'saumal', 'shvabra', 'smokeout', 'sweets_machine', 'trimmer', 'tvorchesky_nabor', 'Univit', 'Urethra', 'utyug_besprovodnoi', 'utyuzhok', 'Varicozan', 'varikozan', 'virgin_star', 'virgin_suppository', 'vitalex', 'vodonagrevatel', 'xtrazex', 'zhidkiy_yod', 'abakus_1', 'abakus_2', 'abakus_full', 'alconol', 'allergyum', 'aquablue', 'Bizuteria_KOLE', 'Bizuteria_Nabor', 'CARNIX_coffe', 'case', 'case_ornament', 'chekhol_na_rul', 'drawlight', 'Fitox', 'GLOBAL_PRO', 'Heavy_full', 'Improstin', 'Kostyum_dzhentlmen', 'minoxidil', 'money_clip', 'Nosovit', 'Notebook_i5', 'organaizer', 'Organello', 'original_parfume', 'Pacreon', 'Perfect_lashes', 'pillow', 'RELAXIN', 'Sonolux', 'Sustaflan', 'TAVANA', 'titan_gold');
        $GLOBAL_OFFERS_COST_PRICE_ASS = DB::queryAssData('offer_name', 'offer_clientprice', $qs);
        $memcache->set(md5('GLOBAL_OFFERS_COST_PRICE_ASS'), $GLOBAL_OFFERS_COST_PRICE_ASS, false, 600);
    } 
    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_TARIFF_SETTINGS;
    $qs = 'SELECT *, UNIX_TIMESTAMP(date_start) AS date_start, percent / 100 AS percent FROM tariff_settings WHERE percent > 0 AND deleted_at IS NULL ORDER BY date_start';
    if (($result = $memcache->get(md5($qs)))) {
        $GLOBAL_TARIFF_SETTINGS = $result;
    } else {
        $rowData = DB::query($qs);
        $GLOBAL_TARIFF_SETTINGS = array();
        foreach ($rowData as $rowVal) {
            $GLOBAL_TARIFF_SETTINGS[$rowVal['column']][$rowVal['deliv_type']][$rowVal['country']][$rowVal['staff_id']][$rowVal['date_start']] = $rowVal['percent'];
        }
        $memcache->set(md5($qs), $GLOBAL_TARIFF_SETTINGS, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_GROUP_OFFER, $GLOBAL_OFFER_GROUP, $GLOBAL_OFFER_DESC;
    $qs = "SELECT offer_id, offer_group, TRIM(offer_name) AS st, TRIM(offer_logname) AS offer_logname, TRIM(offer_desc) AS offer_desc, offer_clientprice AS ofprice FROM offers WHERE offer_name != '' OR offer_logname != ''  ORDER BY st";
    if (($result = $memcache->get(md5($qs)))) {
        $GLOBAL_GROUP_OFFER = $result;
        $GLOBAL_OFFER_GROUP = $memcache->get(md5("{$qs}_ASS"));
        $GLOBAL_OFFER_DESC = $memcache->get(md5("{$qs}_DESC"));
    } else {
        $GLOBAL_GROUP_OFFER = $GLOBAL_OFFER_GROUP = array();
        $dbData = DB::query($qs);
        foreach ($dbData as $dbItem) {
            $GLOBAL_GROUP_OFFER[$dbItem['offer_group']][] = $dbItem['st'];
            $GLOBAL_OFFER_GROUP[$dbItem['st']] = $dbItem['offer_group'];
            $GLOBAL_OFFER_DESC[$dbItem['st']] = empty($dbItem['offer_logname']) ? $dbItem['offer_desc'] : $dbItem['offer_logname'];
        }
        $memcache->set(md5($qs), $GLOBAL_GROUP_OFFER, false, 300);
        $memcache->set(md5("{$qs}_ASS"), $GLOBAL_OFFER_GROUP, false, 600);
        $memcache->set(md5("{$qs}_DESC"), $GLOBAL_OFFER_DESC, false, 600);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // $GLOBAL_PARTNERS
    global $GLOBAL_PARTNERS;
    $qs = 'SELECT * FROM partners';
    if (($result = $memcache->get(md5($qs)))) {
        $GLOBAL_PARTNERS = $result;
    } else {
        $GLOBAL_PARTNERS = DB::queryAssData('id', 'partner_name', $qs);
        $memcache->set(md5($qs), $GLOBAL_PARTNERS, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // $GLOBAL_PARTNERS
    global $GLOBAL_DELIVERY_REGIONS, $GLOBAL_DELIVERY_REGIONS_ARR;
    $qs = 'GLOBAL_DELIVERY_REGIONS';
    if (($result = $memcache->get(md5($qs)))) {
        $GLOBAL_DELIVERY_REGIONS = $result;
        $GLOBAL_DELIVERY_REGIONS_ARR = $memcache->get(md5("{$qs}_ARR"));
    } else {
        $GLOBAL_DELIVERY_REGIONS = $GLOBAL_DELIVERY_REGIONS_ARR = array();

        $country_regions = $redis->hGetAll('country_regions');
        $country_deliv = $redis->hGetAll('DelivRegions');
        foreach ($country_regions as $rk => $rv) {
            foreach ($country_deliv as $dk => $dv) {
                $tmp = explode('_', $dk);
                if ($tmp[0] == $rk) {
                    $GLOBAL_DELIVERY_REGIONS[trim($dv)] = $rv;
                    $GLOBAL_DELIVERY_REGIONS_ARR[$rv][] = trim($dv);
                }
            }
        }

        $memcache->set(md5($qs), $GLOBAL_DELIVERY_REGIONS, false, 300);
        $memcache->set(md5("{$qs}_ARR"), $GLOBAL_DELIVERY_REGIONS_ARR, false, 600);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // $GLOBAL_OFFER_PRICES
    global $GLOBAL_OFFER_PRICES;
    $qs = 'GLOBAL_OFFER_PRICES';
    if (($result = $memcache->get(md5($qs))) && true) {
        $GLOBAL_OFFER_PRICES = $result;
    } else {
        $GLOBAL_OFFER_PRICES = array();
        $pricesDBData = DB::query("SELECT offer_name, property_location, property_name, property_value FROM offers LEFT JOIN offer_property ON offer_id = property_offer WHERE offers_active AND property_name LIKE 'price%' AND CHAR_LENGTH(property_name)=6");
        foreach ($pricesDBData AS $priceItem) {
//            print_r($priceItem);
            $GLOBAL_OFFER_PRICES['common'][$priceItem['property_location']][$priceItem['offer_name']][substr($priceItem['property_name'], 5, 1)] = $priceItem['property_value'];
        }
        // COLD PRICES
        $pricesDBData = DB::query("SELECT offer_name, property_location, property_name, property_value FROM offers LEFT JOIN offer_property ON offer_id = property_offer WHERE offers_active AND property_name LIKE 'pricecold%' AND CHAR_LENGTH(property_name) = 10");
        foreach ($pricesDBData AS $priceItem) {
            $GLOBAL_OFFER_PRICES['cold'][$priceItem['property_location']][$priceItem['offer_name']][substr($priceItem['property_name'], 9, 1)] = $priceItem['property_value'];
        }

        $memcache->set(md5($qs), $GLOBAL_OFFER_PRICES, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // $GLOBAL_OFFER_PRICES
    global $GLOBAL_OFFER_PROPERTIES;
    $qs = 'GLOBAL_OFFER_PROPERTIES';
    if (($result = $memcache->get(md5($qs))) && true) {
        $GLOBAL_OFFER_PROPERTIES = $result;
    } else {
        $data = DB::query("SELECT `property_id`, `property_offer`, `property_location`, `property_name`, `property_value` FROM `offer_property` WHERE `property_name` IN ('color' , 'size', 'type', 'vendor', 'name', 'description') AND `property_active` = 1");
        $GLOBAL_OFFER_PROPERTIES = array();
        foreach ($data AS $dataItem) {
            $GLOBAL_OFFER_PROPERTIES[$dataItem['property_location']][$dataItem['property_offer']][$dataItem['property_id']] = $dataItem;
        }

        $memcache->set(md5($qs), $GLOBAL_OFFER_PROPERTIES, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // $GLOBAL_ACTIVE_OFFERS
    global $GLOBAL_ACTIVE_OFFERS, $GLOBAL_ACTIVE_OFFERS_ASS;
    $qs = 'GLOBAL_ACTIVE_OFFER';
    if (($result = $memcache->get(md5($qs))) && true) {
        $GLOBAL_ACTIVE_OFFERS = $result;
        $GLOBAL_ACTIVE_OFFERS_ASS = $memcache->get(md5($qs . '_ASS'));
    } else {
        $GLOBAL_ACTIVE_OFFERS = DB::queryAssArray('offer_name', "SELECT `offer_id`, `offer_name`, `offer_desc`, `offer_weight`, `offers_active` FROM `offers` ORDER BY offer_name ASC");
        $GLOBAL_ACTIVE_OFFERS_ASS = array();
        foreach ($GLOBAL_ACTIVE_OFFERS as $dataItem) {
            $GLOBAL_ACTIVE_OFFERS_ASS[$dataItem['offer_id']] = $dataItem;
        }
        $memcache->set(md5($qs), $GLOBAL_ACTIVE_OFFERS, false, 300);
        $memcache->set(md5($qs . '_ASS'), $GLOBAL_ACTIVE_OFFERS_ASS, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_STAFF_CACHE_NAME;
    $GLOBAL_STAFF_CACHE_NAME = '$GLOBAL_STAFF_CACHE_NAME';

    global $GLOBAL_STAFF_TEAM, $GLOBAL_TEAM_STAFF;
    global $GLOBAL_RESPONSIBLE_STAFF, $GLOBAL_STAFF_RESPONSIBLE;
    global $GLOBAL_CURATOR_RESPONSIBLE, $GLOBAL_RESPONSIBLE_CURATOR;
    global $GLOBAL_CURATOR_STAFF, $GLOBAL_STAFF_CURATOR;
    global $GLOBAL_SIP_STAFF;
    global $GLOBAL_STAFF_SIP;
    global $GLOBAL_STAFF_FIO;


    if (($result = $memcache->get(md5($GLOBAL_STAFF_CACHE_NAME))) && true) {
        $GLOBAL_STAFF_TEAM = $result;
        $GLOBAL_TEAM_STAFF = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF"));

        $GLOBAL_STAFF_RESPONSIBLE = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF_RESPONSIBLE_ASS"));
        $GLOBAL_RESPONSIBLE_STAFF = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_RESPONSIBLE_STAFF_ARR"));

        $GLOBAL_RESPONSIBLE_CURATOR = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_RESPONSIBLE_CURATOR_ASS"));
        $GLOBAL_CURATOR_RESPONSIBLE = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_CURATOR_RESPONSIBLE_ARR"));

        $GLOBAL_STAFF_CURATOR = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF_CURATOR_ASS"));
        $GLOBAL_CURATOR_STAFF = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_CURATOR_STAFF_ARR"));

        $GLOBAL_SIP_STAFF = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_SIP_STAFF"));
        $GLOBAL_STAFF_SIP = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF_SIP"));
        $GLOBAL_STAFF_FIO = $memcache->get(md5("{$GLOBAL_STAFF_CACHE_NAME}_FIO"));
    } else {
        $GLOBAL_STAFF_TEAM = $GLOBAL_TEAM_STAFF = $GLOBAL_STAFF_RESPONSIBLE = $GLOBAL_RESPONSIBLE_STAFF = $GLOBAL_CURATOR_RESPONSIBLE = $GLOBAL_RESPONSIBLE_CURATOR = array();

        $qs = 'SELECT *, TRIM(CONCAT(FirstName, " ", LastName)) AS full_name FROM Staff ORDER BY full_name';
        $dbData = DB::queryAssArray('id', $qs);

        foreach ($dbData as $staffId => $dbItem) {
            $GLOBAL_STAFF_TEAM[$staffId] = $dbItem['team'];
            $GLOBAL_TEAM_STAFF[$dbItem['team']][] = $staffId;
            if ($dbItem['Sip']) {
                $GLOBAL_SIP_STAFF["SIP/{$dbItem['Sip']}"] = $staffId;
            }
            $GLOBAL_STAFF_SIP[$dbItem['id']] = $dbItem['Sip'];
            $GLOBAL_STAFF_FIO[$dbItem['id']] = $dbItem['full_name'];

            if ($dbItem['Responsible'] && $dbData[$dbItem['Responsible']]['IsResponsible']) {
                $GLOBAL_STAFF_RESPONSIBLE[$staffId] = $dbItem['Responsible'];
                $GLOBAL_RESPONSIBLE_STAFF[$dbItem['Responsible']][] = $staffId;
            }
            if ($dbItem['Curator'] && $dbData[$dbItem['Curator']]['IsCurator']) {
                $GLOBAL_RESPONSIBLE_CURATOR[$staffId] = $dbItem['Curator'];
                $GLOBAL_CURATOR_RESPONSIBLE[$dbItem['Curator']][] = $staffId;
            }
        }

        $GLOBAL_CURATOR_STAFF = array();
        foreach ($GLOBAL_CURATOR_RESPONSIBLE as $curatorId => $responsibleArr) {
            foreach ($responsibleArr as $responsibpleId) {
                if (empty($GLOBAL_CURATOR_STAFF[$curatorId])) {
                    $GLOBAL_CURATOR_STAFF[$curatorId] = array($curatorId);
                }
                if (!empty($GLOBAL_RESPONSIBLE_STAFF[$responsibpleId])) {
                    $GLOBAL_CURATOR_STAFF[$curatorId] = array_merge($GLOBAL_CURATOR_STAFF[$curatorId], $GLOBAL_RESPONSIBLE_STAFF[$responsibpleId]);
                }
            }
        }
        $GLOBAL_STAFF_CURATOR = array();
        foreach ($GLOBAL_CURATOR_STAFF as $curatorId => $staffArr) {
            $GLOBAL_STAFF_CURATOR += array_fill_keys($staffArr, $curatorId);
        }

        $memcache->set(md5($GLOBAL_STAFF_CACHE_NAME), $GLOBAL_STAFF_TEAM, false, 600);
        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF"), $GLOBAL_TEAM_STAFF, false, 600);
        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF_RESPONSIBLE_ASS"), $GLOBAL_STAFF_RESPONSIBLE, false, 600);
        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_RESPONSIBLE_STAFF_ARR"), $GLOBAL_RESPONSIBLE_STAFF, false, 600);

        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_RESPONSIBLE_CURATOR_ASS"), $GLOBAL_RESPONSIBLE_CURATOR, false, 600);
        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_CURATOR_RESPONSIBLE_ARR"), $GLOBAL_CURATOR_RESPONSIBLE, false, 600);

        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF_CURATOR_ASS"), $GLOBAL_STAFF_CURATOR, false, 600);
        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_CURATOR_STAFF_ARR"), $GLOBAL_CURATOR_STAFF, false, 600);

        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_SIP_STAFF"), $GLOBAL_SIP_STAFF, false, 600);
        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_STAFF_SIP"), $GLOBAL_STAFF_SIP, false, 600);

        $memcache->set(md5("{$GLOBAL_STAFF_CACHE_NAME}_FIO"), $GLOBAL_STAFF_FIO, false, 600);
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_OFFER_PAY;
    $qs = "SELECT offer_payment.web_payment,date_payment,offer_payment.country_payment,offer_payment.cpa_payment,offers.offer_name
        FROM offer_payment
            LEFT JOIN offers ON offer_payment.offer_id = offers.offer_id
        GROUP BY cpa_payment,offer_payment.offer_id,date_payment";
    if (($result = $memcache->get(md5($qs)))) {
        $GLOBAL_OFFER_PAY = $result;
    } else {
        $GLOBAL_OFFER_PAY = array();
        $dbData = DB::query($qs);
        foreach ($dbData as $dbItem) {
            $GLOBAL_OFFER_PAY['pay' . $dbItem['offer_name']][$dbItem['cpa_payment']][$dbItem['country_payment']][$dbItem['date_payment']] = $dbItem['web_payment'];
        }
        $memcache->set(md5($qs), $GLOBAL_OFFER_PAY, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_SIP_QUEUES_ASS;
    $qs = 'SELECT RIGHT(membername, 4) AS Sip, membername, GROUP_CONCAT(queue_name) AS queues FROM asterisk.queue_member_table GROUP BY membername';
    if (($result = $memcache->get(md5('GLOBAL_SIP_QUEUES_ASS'))) && true) {
        $GLOBAL_SIP_QUEUES_ASS = $result;
    } else {
        asterisk_base();
        $GLOBAL_SIP_QUEUES_ASS = DB::queryAssData('Sip', 'queues', $qs);
        $memcache->set(md5('GLOBAL_SIP_QUEUES_ASS'), $GLOBAL_SIP_QUEUES_ASS, false, 300);
        bari_base();
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_STATUS_CUR_OPLACHEN;
    $GLOBAL_STATUS_CUR_OPLACHEN = array(
        'ОПЛ тс Карта',
        'ОПЛ Карта',
        'ОПЛ',
        'Продажа карта распол',
        'ОПЛ дальний',
        'выезд дважды',
        'ОПЛ располовинен',
        'ОПЛ дальний располовинен',
        'Опл транспортировка',
        'Продажа курьер распол',
        'Выезд дважды Распол',
        'ОПЛ скидка',
        'ОПЛ ДОП',
        'Продажа карта',
        'Продажа курьер',
        'ОПЛ карта Располовинен',
        'Продажа карта выезд дважды',
        'Продажа карта выезд дважды распол',
        'Опл нал/без нал'
    );
    global $GLOBAL_COUNTRIES_ARR;
    $GLOBAL_COUNTRIES_ARR = array('kz', 'kzg', 'uz', 'am', 'az', 'md', 'ru', 'ae');

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_CURIER_PAYMENT, $GLOBAL_CURIER_PAYMENT_ASSOC, $GLOBAL_KZ_COURIERS, $GLOBAL_KZ_COURIERS_ASS;
    global $GLOBAL_KETKZ_OTPRAVITEL;

    // $GLOBAL_CURIER_PAYMENT_ASSOC
    $GLOBAL_CURIER_PAYMENT_ASSOC = array("" => "", "ОД" => "od", "ОД оплатить" => "odp", "НД" => "nd", "ОПЛ" => "opl", "нет товара" => "net", "ОТКАЗ" => "otk", "ПЕРЕНОС" => "per", "отказ проплатить" => "otp", "УТОЧНИТЬ" => "ut", "ВЫЕХАЛ" => "vu", "выезд дважды" => "v2", "замена сделана" => "sz", "сделан возврат денег" => "vd", "сделать замену" => "zs", "СЛЕДУЮЩИЙ" => "sms", "сделать возврат денег" => "sv", "Перенос оплатить" => "po", "Перезвонить" => "pr", "ОПЛ располовинен" => "opr", "Замена сделана клиент оплотил(0)" => "zam0", "перенос сделать возврат денег(0)" => "per0", "Нд сделать возврат денег(0)" => "voz0", "ОПЛ дальний" => "opld", "ОПЛ дальний располовинен" => "opdr", "ОЖИДАЕТ" => "wait", "Опл транспортировка" => "opl_transport", "Выезд дважды Распол" => "two_times_raspol", "ОПЛ скидка" => "opl_skidka", "ПЕРЕЗВОНИТ" => "recall");

    $qs = "SELECT * FROM coffee.curier_payment ORDER BY `delivery_type` ASC";
    if (($result = $memcache->get(md5($qs))) && true) {
        $GLOBAL_CURIER_PAYMENT = $result;
        $GLOBAL_KZ_COURIERS = $memcache->get(md5("{$qs}_KZ_COURIERS"));
        $GLOBAL_KZ_COURIERS_ASS = $memcache->get(md5("{$qs}_KZ_COURIERS_ASS"));
        $GLOBAL_KETKZ_OTPRAVITEL = $memcache->get(md5("{$qs}_KETKZ_OTPRAVITEL"));
    } else {

        if (ket_asterisk_base()) {
            // $GLOBAL_CURIER_PAYMENT
            $GLOBAL_CURIER_PAYMENT = $GLOBAL_KZ_COURIERS = $GLOBAL_KZ_COURIERS_ASS = array();

            $GLOBAL_CURIER_PAYMENT = DB::queryAssArray('delivery_type', $qs);

            // START $GLOBAL_KZ_COURIERS
            $qsKzCouriers = "SELECT id AS staff_id, courier_id AS id,
                                CONCAT(`courier_id`, ' ', `courier_city`) AS name,
                                Sip AS `sip`,
                                CONCAT('8', SUBSTRING(courier_phone, 3, 11)) AS phone
                            FROM coffee.`Staff`
                            WHERE `is_courier` = 1 AND Type=1
                            ORDER BY `courier_id`";
            $GLOBAL_KZ_COURIERS = DB::queryAssArray('staff_id', $qsKzCouriers);
            foreach ($GLOBAL_KZ_COURIERS as $curVal) {
                $GLOBAL_KZ_COURIERS_ASS[$curVal['id']] = $curVal;
            }

            $memcache->set(md5($qs), $GLOBAL_CURIER_PAYMENT, false, 600);
            $memcache->set(md5("{$qs}_KZ_COURIERS"), $GLOBAL_KZ_COURIERS, false, 600);
            $memcache->set(md5("{$qs}_KZ_COURIERS_ASS"), $GLOBAL_KZ_COURIERS_ASS, false, 600);

            // END $GLOBAL_KZ_COURIERS
            // START $GLOBAL_KETKZ_OTPRAVITEL
            $qsKetKzOptravitel = "SELECT * FROM coffee.`sender`";
            $GLOBAL_KETKZ_OTPRAVITEL = DB::queryAssArray('id', $qsKetKzOptravitel);

            $memcache->set(md5("{$qs}_KETKZ_OTPRAVITEL"), $GLOBAL_KETKZ_OTPRAVITEL, false, 600);

            // END $GLOBAL_KETKZ_OTPRAVITEL

            bari_base();
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_RESPONSIBLE_PLANS_BONUSES;
    $responsiblePlansCommonObj = new ResponsiblePlansCommonObj('responsible');

    if (($result = $memcache->get($responsiblePlansCommonObj->getCacheName()))) {
        $GLOBAL_RESPONSIBLE_PLANS_BONUSES = $result;
    } else {
        $GLOBAL_RESPONSIBLE_PLANS_BONUSES = array();
        $dbData = DB::query("SELECT * FROM {$responsiblePlansCommonObj->cGetTableName()} WHERE {$responsiblePlansCommonObj->prepWhereStr()} ORDER BY responsible_id, plan");
        foreach ($dbData as $dbItem) {
            $GLOBAL_RESPONSIBLE_PLANS_BONUSES[$dbItem['responsible_id']][$dbItem['plan']] = $dbItem['bonuse'];
        }
        $memcache->set($responsiblePlansCommonObj->getCacheName(), $GLOBAL_RESPONSIBLE_PLANS_BONUSES, false, 300);
    }

    ///////////////////////////////////////////////////////////////////////////////
    global $GLOBAL_COLD_PLANS_BONUSES;
    $responsiblePlansCommonObj = new ResponsiblePlansCommonObj('cold');

    if (($result = $memcache->get($responsiblePlansCommonObj->getCacheName()))) {
        $GLOBAL_COLD_PLANS_BONUSES = $result;
    } else {
        $GLOBAL_COLD_PLANS_BONUSES = array();
        $dbData = DB::query("SELECT * FROM {$responsiblePlansCommonObj->cGetTableName()} WHERE {$responsiblePlansCommonObj->prepWhereStr()} ORDER BY responsible_id, plan");
        foreach ($dbData as $dbItem) {
            $GLOBAL_COLD_PLANS_BONUSES[$dbItem['country']][$dbItem['responsible_id']][$dbItem['plan']] = $dbItem['bonuse'];
        }
        $memcache->set($responsiblePlansCommonObj->getCacheName(), $GLOBAL_COLD_PLANS_BONUSES, false, 300);
    }

    /**
     *
     * @param type $callIds
     * @return type
     */
    function incCallIds($callIds) {
        foreach ($callIds as &$value) {
            $value = incCallId($value);
        }
        return $callIds;
    }

    /**
     *
     * @param type $callId
     * @return type
     */
    function incCallId($callId) {
        $incLastChar = mb_substr($callId, -6) + 1;
        $callId = substr_replace($callId, $incLastChar, -6);
        return $callId;
    }

    /**
     *
     * @param type $callIds
     * @return type
     */
    function decrCallIds($callIds) {
        foreach ($callIds as &$value) {
            $value = decrCallId($value);
        }
        return $callIds;
    }

    /**
     *
     * @param type $callId
     * @return type
     */
    function decrCallId($callId) {
        $incLastChar = mb_substr($callId, -6) - 1;
        $callId = substr_replace($callId, $incLastChar, -6);
        return $callId;
    }

    $redis->close();

}

function getDBTimeZoneOfsset($timeZoneName = false) {

    $workZoneName = empty($timeZoneName) ? (empty($_SESSION['timeZoneName']) ? 'Asia/Almaty' : $_SESSION['timeZoneName']) : $timeZoneName;

    if (empty($_SESSION['timeZoneName']) || empty($_SESSION['db_time_zone']) || $_SESSION['timeZoneName'] != $workZoneName) {
        date_default_timezone_set($workZoneName);
        ini_set('date.timezone', $workZoneName);
        $tz = new DateTimeZone($workZoneName);
        $tzOffset = $tz->getOffset(new DateTime('now', new DateTimeZone('UTC')));
        $sign = $tzOffset < 0 ? '' : '+';
        $ret = $_SESSION['db_time_zone'] = $sign . str_pad(floor($tzOffset / 3600), 2, '0', STR_PAD_LEFT) . ':' . str_pad(floor($tzOffset % 3600 / 60), 2, '0', STR_PAD_LEFT);
        $_SESSION['timeZoneName'] = $workZoneName;
//        ApiLogger::addLogVarExport("getDBTimeZoneOfsset SET: $workZoneName");
    } else {
        $ret = $_SESSION['db_time_zone'];
//        ApiLogger::addLogVarExport("getDBTimeZoneOfsset RET: $ret");
    }
    return $ret;
}

/**
 * @global array $GLOBAL_TARIFF_SETTINGS
 * @param string $column
 * @param string $delivType
 * @param string $country
 * @param mixed $date
 * @param integer $total
 * @return integer
 */
function calcTariffSettings($column, $delivType, $country, $staffId, $date, $total) {
    global $GLOBAL_TARIFF_SETTINGS;

    $staffId *= 1;

    $ret = round($total);
    $workArr = array(0 => 0);
    if (strpos($date, '-')) {
        $date = date('U', strtotime($date));
    } else {
        $date = $date * 1;
    }
    $deliv_type = $delivType == 'Почта' ? 'post' : 'courier';

    if (!empty($date) && !empty($GLOBAL_TARIFF_SETTINGS[$column][$deliv_type][$country])) {

        if (empty($GLOBAL_TARIFF_SETTINGS[$column][$deliv_type][$country][$staffId])) {
            if (!empty($GLOBAL_TARIFF_SETTINGS[$column][$deliv_type][$country][0])) {
                $workArr = $GLOBAL_TARIFF_SETTINGS[$column][$deliv_type][$country][0];
            }
        } else {
            $workArr = $GLOBAL_TARIFF_SETTINGS[$column][$deliv_type][$country][$staffId];
        }
    }

    $koef = reset($workArr);
    foreach ($workArr as $timeStamp => $value) {
        if ($timeStamp > $date) {
            break;
        } else {
            $koef = $value;
        }
    }

    return round($ret * $koef);
}

function gerOfferPrice($orderData, $count = 1) {
    global $GLOBAL_OFFER_PRICES, $GLOBAL_ALL_COLD_STAFF_ARR;
    $ret = 0;

    $pricesData = in_array($orderData['staff_id'], $GLOBAL_ALL_COLD_STAFF_ARR) ? $GLOBAL_OFFER_PRICES['cold'] : $GLOBAL_OFFER_PRICES['common'];
    if (!empty($pricesData[$orderData['country']][$orderData['offer']]) && ($offerPriceArr = $pricesData[$orderData['country']][$orderData['offer']])) {
        $ret = empty($offerPriceArr[$count]) ? empty($offerPriceArr[0]) ? 0 : $offerPriceArr[0] * $count : $offerPriceArr[$count];
    }
    return $ret;
}

function file_get_contents_curl($url, $cache = false) {
//    $cache = false;

    $exist = false;
    if ($cache) {
        $redis = RedisManager::getInstance()->getRedis();

        $redisKey = 'curlCache_' . md5($url);
        if (($exist = $redis->hExists($redisKey, 'data'))) {
            return $redis->hGet($redisKey, 'data');
        }
    }

    $result = sendCurlRequest($url);

    if ($cache && !$exist && !empty($result['data'])) {
        $redis->hMset($redisKey, array('url' => $url, 'data' => json_encode($result)));
        $redis->setTimeout($redisKey, (int) $cache);
    }

    return json_encode($result);
}

/**
 *
 * @param int $staffId
 * @param array $diapazonsSettings
 * @param float $value
 * @return type
 */
function getStaffResponsibleDiapazonKoef($staffId, $diapazonsSettings, $value) {
    global $GLOBAL_STAFF_RESPONSIBLE;
    $defSett = empty($diapazonsSettings['']) ? array() : $diapazonsSettings[''];

    $searchDiapazon = array();
    if (!empty($GLOBAL_STAFF_RESPONSIBLE[$staffId])) {

        $staffResponsibleId = $GLOBAL_STAFF_RESPONSIBLE[$staffId];
        if (array_key_exists($staffResponsibleId, $diapazonsSettings)) {
            $searchDiapazon = $diapazonsSettings[$staffResponsibleId];
        } elseif (!empty($defSett)) {
            $searchDiapazon = $defSett;
        }
    }
    return getDiapazonKoef($searchDiapazon, $value);
}

function getDiapazonKoef($diapazons, $value) {
    $ret = 0;
    foreach ($diapazons as $maxVal => $koef) {
        if ($value >= $maxVal) {
            $ret = $koef;
        } else {
            break;
        }
    }
    return $ret;
}

function sendCurlRequest($url, $postData = null) {

    // инициализируем сеанс
    $curl = curl_init();

    ApiLogger::addLogVarExport($url);

    curl_setopt($curl, CURLOPT_URL, $url);
    // максимальное время выполнения скрипта
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    // теперь curl вернет нам ответ, а не выведет
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // Отключаем ssl проверку
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_VERBOSE, true);

    if ($postData !== null) {
        // передаем данные по методу post
        curl_setopt($curl, CURLOPT_POST, 1);
        // переменные, которые будут переданные по методу post
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    // отправка запроса
    $result = curl_exec($curl);
    $info = curl_getinfo($curl);
    ApiLogger::addLogVarExport('--- INFO --');
    ApiLogger::addLogVarExport($info);
    ApiLogger::addLogVarExport('--- RESULT --');
    ApiLogger::addLogVarExport($result);
    // закрываем соединение
    curl_close($curl);

    if ($result) {
        if (isJson($result)) {
            $result = json_decode($result, true);
        }
    } else {
        $result = array("statuscode" => 503, "error" => "Server is not responding");
    }
    return $result;
}

/**
 * Convert phone number whith template
 * @param string $field_value - original phone number
 * @param string $postTempParams - phone template
 * @return string
 */
function setPhoneTemplate($field_value, $postTempParams = '') {

    $symbol = substr(preg_replace("([0-9])", '', $postTempParams), - 1); // ищем последний символ, кроме цифр
    $countSymbolsInTemplate = substr_count($postTempParams, $symbol); // количество символов в шаблоне, кроме цифр
    $countTemplate = strlen($postTempParams); // количество всех символов в шаблоне
    $phoneCode = isset($postTempParams) ? preg_replace("/\D/", '', $postTempParams) : ''; // шаблон телефона
//    echo "|countSymbolsInTemplate = $countSymbolsInTemplate|";
//    echo "|countTemplate = $countTemplate|";
//    echo "|phoneTemplate = $phoneCode|";

    $field_value = preg_replace("/\D/", '', $field_value);
//    echo "|field_value = $field_value|";

    $field_value = $phoneCode . substr(preg_replace("/\D/", '', $field_value), -1 * $countSymbolsInTemplate);


//    if (isset($phoneCode)) {
//        if (strlen($field_value) >= $countSymbolsInTemplate) { // если количество символов в документе, больше символов в шаблоне
//            $different = $countTemplate - strlen($field_value); // вычесляем сколько цифр с шаблона нужно
//            $newTemplate = substr($phoneCode, 0, $different); // новый шаблон
//            if ($different < 0) { // если в поле больше цифр чем полный телефон, убираем шаблон
//                $newTemplate = substr($postTempParams, 0, strpos($postTempParams, 'X'));
//            }
//            if ((strlen($field_value) == $countTemplate) && (substr($field_value, strlen($phoneCode)) != $phoneCode)) { // если поле содержит полний номер, но код не совпадает, заменяем его '80ХХХХХХХ' 771234567   =>  801234567
//                $field_value = $phoneCode . substr($field_value, - $countSymbolsInTemplate);
//            } else {
//                $field_value = $newTemplate . substr($field_value, - $countSymbolsInTemplate);
//            }
//        } else {
//            $field_value = $phoneCode . substr($field_value, - $countSymbolsInTemplate);
//        }
//    }

    return $field_value;
}

function getCurrency($date, $vars) {
    $redis = RedisManager::getInstance()->getRedis();
    $ResultRow = $redis->hGetAll($vars);
    return (array) json_decode($ResultRow[$date]);
}

function translit($str) {
    $trans_table_ru = array(
        'А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е', 'Ё', 'ё',
        'Ж', 'ж', 'З', 'з', 'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м',
        'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р', 'С', 'с', 'Т', 'т', 'У', 'у',
        'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ы', 'ы', 'Э', 'э', 'Ч', 'ч', 'Ш', 'ш',
        'Щ', 'щ', 'Ю', 'ю', 'Я', 'я', 'ї', 'Ї', 'є', 'Є', 'ь', 'Ь', 'ъ', 'Ъ',
        'І', 'і', 'Ґ', 'ґ'
    );
    $trans_table_lat = array(
        'A', 'a', 'B', 'b', 'V', 'v', 'G', 'g', 'D', 'd', 'E', 'e', 'E', 'e',
        'J', 'j', 'Z', 'z', 'I', 'i', 'Y', 'y', 'K', 'k', 'L', 'l', 'M', 'm',
        'N', 'n', 'O', 'o', 'P', 'p', 'R', 'r', 'S', 's', 'T', 't', 'U', 'u',
        'F', 'f', 'H', 'h', 'C', 'c', 'I', 'i', 'E', 'e',
        'Ch', 'ch', 'Sh', 'sh', 'Sh', 'sh', 'Yu', 'yu', 'Ya', 'ya', 'i', 'Yi', 'ie', 'Ye', "'", "'", '', '',
        'I', 'i', 'G', 'g'
    );

    return str_replace($trans_table_ru, $trans_table_lat, $str);
}

/**
 *
 * @global type $GLOBAL_STAFF_FIO_ID
 * @param type $searchArr
 * @return type
 */
function searchStaffIdsByFio($searchArr = array()) {
    global $GLOBAL_STAFF_FIO;

    $ret = array();
    $staffFioIdAAss = array();
    foreach ($GLOBAL_STAFF_FIO as $id => $fio) {
        if (($clearFio = mb_strtolower($fio))) {
            $staffFioIdAAss[$clearFio] = $id;
        }
    }

    $searchArr = is_array($searchArr) ? $searchArr : array($searchArr);
    foreach ($searchArr as $searchItem) {
        $searchItem = trim(mb_strtolower($searchItem));
        if (!empty($staffFioIdAAss[$searchItem])) {
            $ret[] = $staffFioIdAAss[$searchItem];
        }
    }
    return array_unique($ret);
}

/**
 * @param string $path
 * @param int $maxdepth : how deep to browse (-1=unlimited)
 * @param string $mode  : "FULL"|"DIRS"|"FILES"
 * @param type $d  : must not be defined
 * @return array
 */
function searchDir($path, $maxdepth = -1, $mode = "FULL", $d = 0) {
    if (substr($path, strlen($path) - 1) != '/') {
        $path .= '/';
    }
    $dirlist = array();
    if ($mode != "FILES") {
        $dirlist[] = $path;
    }
    if (($handle = opendir($path))) {
        while (false !== ( $file = readdir($handle) )) {
            if ($file != '.' && $file != '..') {
                $file = $path . $file;
                if (!is_dir($file)) {
                    if ($mode != "DIRS") {
                        $dirlist[] = $file;
                    }
                } elseif ($d >= 0 && ($d < $maxdepth || $maxdepth < 0)) {
                    $result = searchdir($file . '/', $maxdepth, $mode, $d + 1);
                    $dirlist = array_merge($dirlist, $result);
                }
            }
        }
        closedir($handle);
    }
    if ($d == 0) {
        natcasesort($dirlist);
    }
    return ( $dirlist );
}

/**
 * @param mixed $input
 * @return mixed
 */
function recursiveClearArr($input) {
    if (is_array($input)) {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = recursiveClearArr($value);
            } else {
                $value = preg_replace('/\s+/', ' ', trim($value));
            }
        }
        return array_filter($input);
    } else {
        return empty($input) ? null : $input;
    }
}

function sendLogos($data) {
    $uid = '688';
    $key = 'wxk9d2k9mcape7xh7uvhnkhb684fnwtt';
    $hash = md5($data['id'] . $key);

    $order_data = array(array(
            'KodBan' => $data['id'],
            'PostInd' => $data['index'],
            'Oblast' => $data['region'],
            'Rayon' => $data['district'],
            'Gorod' => $data['city'],
            'Adres' => $data['addr'],
            'RecName' => $data['fio'],
            'SumNP' => $data['price'],
            'SumOC' => $data['price'],
            'RecTel' => $data['phone'],
            'Tcomplect' => array(array('Articul' => $data['offer'], 'TovName' => @$data['offer_name'], 'Kolvo' => $data['package'], 'TmcCost' => round($data['price'] / $data['package'])))
    ));

    $Curl = curl_init();
    $CurlOptions = array(
        CURLOPT_URL => 'http://logoskor.ru/index.php?option=com_lkapi&task=neworder',
        CURLOPT_POST => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROXY => 'ketkz.com:3128',
        CURLOPT_HTTPPROXYTUNNEL => 1,
        CURLOPT_USERAGENT => 'Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4',
        CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => "user_id=" . $uid . "&carrier_type=1&np=1&key=" . $hash . "&order_data=" . json_encode($order_data, JSON_UNESCAPED_UNICODE),
            //CURLOPT_POSTFIELDS=>array('user_id'=>651,'testmode'=>true,'key'=>$hash,'order_data'=>json_encode($order_data)),
    );
    curl_setopt_array($Curl, $CurlOptions);
    /* if(false === ($Result = curl_exec($Curl))) {
      throw new Exception('Http request failed');
      } */
    $Result = curl_exec($Curl);
    curl_close($Curl);
    return preg_replace(array('/"\{/', '/\}"/'), array('{', '}'), $Result);
}

function getLogosNew($id) {
    $uid = '688';
    $key = 'wxk9d2k9mcape7xh7uvhnkhb684fnwtt';
    $hash = md5(date('d') . $key);
    $Curl = curl_init();

    $CurlOptions = array(
        CURLOPT_URL => 'http://logoskor.ru/index.php?option=com_lkapi&task=rpo',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROXY => 'ketkz.com:3128',
        CURLOPT_HTTPPROXYTUNNEL => 1,
        CURLOPT_USERAGENT => 'Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4',
        CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => "user_id=" . $uid . "&token=" . $hash . "&kodbans=" . $id,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    curl_close($Curl);
    return $Result;
}

function setMassBarCode($idsData = array()) {
    $ret = array();

//////////////////////////////////////
// KZ
//////////////////////////////////////
    $qs = "SELECT id FROM staff_order
        WHERE   country IN ('kz', 'ru')
                AND kz_code = ''
                AND status = 'Подтвержден'
                AND kz_delivery = 'Почта'
                AND id IN %li
        ORDER BY offer, package";

    if (($idsArr = DB::queryFirstColumn($qs, $idsData))) {

        $qs = " SELECT kz_code AS maxcode
                FROM staff_order
                WHERE country IN ('kz','ru') AND kz_code <> '' AND status = 'Подтвержден' AND kz_delivery = 'Почта' AND substring(kz_code, 1 ,4) = 'RJ01'
                ORDER BY kz_code DESC LIMIT 1";
        $maxcodeStr = DB::queryFirstField($qs);
        $maxcode = (int) substr($maxcodeStr, 4, strlen($maxcodeStr) - 7);

        $codeShablon = "86423597";

        $codeArr = str_split($codeShablon);
        foreach ($idsArr as $index => $ourId) {
            $id = $maxcode + $index + 1;
            $idStr = '01' . str_pad($id, 6, '0', STR_PAD_LEFT);
            $idArr = str_split($idStr);

            $summ = 0;
            foreach ($idArr as $key => $value) {
                $summ += $codeArr[$key] * $value;
            }
            $ost = $summ % 11;
            switch ($ost) {
                case 0:
                    $check = 5;
                    break;
                case 1:
                    $check = 0;
                    break;
                default :
                    $check = 11 - $ost;
                    break;
            }
            $codeStr = $idStr . $check;

            $staffOrderObj = new StaffOrderObj($ourId);
            $staffOrderObj->cSave(array('kz_code' => "RJ{$codeStr}KZ"));
            $ret[$ourId] = $staffOrderObj->cGetValues('kz_code');
        }
    }

//////////////////////////////////////
// KGZ
//////////////////////////////////////
    $qs = " SELECT id
            FROM staff_order
            WHERE country = 'kzg'
                AND kz_code = '' AND status = 'Подтвержден' AND kz_delivery = 'Почта'
                AND id IN %li
            ORDER BY offer, package";
    if (($idsArr = DB::queryFirstColumn($qs, $idsData))) {
        $qs = " SELECT MAX(substring(kz_code, 11, 5)) AS maxcode
                FROM staff_order
                WHERE country = 'kzg' AND kz_code <> '' AND status = 'Подтвержден' AND kz_delivery = 'Почта' AND substring(kz_code, 1, 8) = 'BS721016'";
        $maxcodeStr = DB::queryFirstField($qs);
        if (empty($maxcodeStr)) {
            $maxcodeStr = '00000';
        }
        $maxcode = (int) $maxcodeStr;

        foreach ($idsArr as $index => $ourId) {
            $tmp = $maxcode + $index + 1;
            //var_dump($tmp); die;
            switch (strlen((string) $tmp)) {
                case 1: $vuvu = '0000' . (string) $tmp;
                    break;
                case 2: $vuvu = '000' . (string) $tmp;
                    break;
                case 3: $vuvu = '00' . (string) $tmp;
                    break;
                case 4: $vuvu = '0' . (string) $tmp;
                    break;
                case 5: $vuvu = (string) $tmp;
                    break;
            }
            $ch_str = "072101613" . $vuvu;

            $ch_str = str_split($ch_str);
            $chet = array();
            $nechet = array();
            foreach ($ch_str as $k => $v) {
                if ($k % 2 == 0) {
                    $chet[] = (int) $v;
                } else {
                    $nechet[] = (int) $v;
                }
            }
            $sums = 10 - ((array_sum($nechet) * 3 + array_sum($chet)) % 10);
            if ($sums == 10) {
                $sums = 0;
            }

            $staffOrderObj = new StaffOrderObj($ourId);
            $staffOrderObj->cSave(array('kz_code' => "BS72101613{$vuvu}{$sums}"));
            $ret[$ourId] = $staffOrderObj->cGetValues('kz_code');
        }
    }

//////////////////////////////////////
// AZ
//////////////////////////////////////
    $qs = " SELECT id
        FROM staff_order WHERE country = 'az'
            AND kz_code = '' AND status = 'Подтвержден' AND kz_delivery = 'Почта'
            AND id IN %li
        ORDER BY offer,package";
    if (($idsArr = DB::queryFirstColumn($qs, $idsData))) {
        $qs = " SELECT MAX(kz_code) AS maxcode
                FROM staff_order
                WHERE `date` > NOW() - INTERVAL 1 WEEK AND country = 'az' AND kz_code <> '' AND status = 'Подтвержден'
                        AND kz_delivery = 'Почта' AND substring(kz_code, 1, 8) = 'AZ721000' ";
        $maxcodeStr = DB::queryFirstField($qs);
        if (empty($maxcodeStr)) {
            $maxcodeStr = 'AZ72100000000000';
        }
        $maxcode = (int) substr($maxcodeStr, 10, 6);

        foreach ($idsArr as $index => $ourId) {
            $tmp = $maxcode + $index + 1;
            switch (strlen((string) $tmp)) {
                case 1: $vuvu = '00000' . (string) $tmp;
                    break;
                case 2: $vuvu = '0000' . (string) $tmp;
                    break;
                case 3: $vuvu = '000' . (string) $tmp;
                    break;
                case 4: $vuvu = '00' . (string) $tmp;
                    break;
                case 5: $vuvu = '0' . (string) $tmp;
                    break;
                case 6: $vuvu = (string) $tmp;
                    break;
            }
            $staffOrderObj = new StaffOrderObj($ourId);
            $staffOrderObj->cSave(array('kz_code' => 'AZ721000' . date('m') . $vuvu));
            $ret[$ourId] = $staffOrderObj->cGetValues('kz_code');
        }
    }

    return $ret;
}

/**
 *
 * @param type $type
 * @param type $status
 * @param type $idsArr
 * @param type $ogr
 * @return boolean
 */
function setMassKz($type, $status, $idsArr = array(), $ogr = 1) {
    $ret = array(
        'success' => false
    );

    if (!empty($idsArr)) {

        $addArr = $whereArr = array();

        if (($limit = (int) $ogr)) {
            $limitArr = array_chunk($idsArr, $limit);
            $idsArr = $limitArr[0];
        }

        if ($type == 'send_status' && $status == 'Оплачен') {
            DB::update('staff_order', array('status' => 'Подтвержден'), "`status` = 'Предварительно подтвержден' AND id IN %li", $idsArr);
        }

        if ($type == 'cold_in') {
            // Назначить холодного оператора
            if ($status > 0) {
//            $addArr['oper_use'] = $status;
//            $status = 1;
                $type = 'oper_use';
            }
        }
        if ($type == 'cold_out') {
            // Убрать из холодных
            $addArr['is_cold_staff_id'] = $_SESSION['Logged_StaffId'];
            $type = 'is_cold';
        }
        if ($type == 'cold_group') {
            // Назначить холодную группу
            if ($status > 0) {
                $addArr['is_cold'] = 1;
                $addArr['oper_use'] = 0;
                $type = 'Group_cold';
            }
        }
        if ($type == 'cold_group_new') {
            // Назначить холодную группу NEW
            if ($status > 0) {
                $addArr['is_cold_new'] = 1;
                $addArr['oper_use'] = 0;
                $type = 'Group_cold_new';
            }

            if (($dataCh = DB::queryFirstColumn('SELECT uuid FROM staff_order WHERE is_cold_new > 0 AND id IN %li', $idsArr))) {
                $whereArr[] = 'uuid NOT IN ("' . implode('","', $dataCh) . '")';
            }
        }

        if ($type == 'status_kz' && $status == 'Хранение') {
            if (($dataCh = DB::query("SELECT * FROM staff_order WHERE country = 'kz' AND kz_delivery = 'Почта' AND send_status <> 'Оплачен' AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
                    sendKcellSMS($objCh['phone'], "Уведомление! Посылка " . $objCh['kz_code'] . " прибыла в ваше почтовое отделение", $objCh['id']);
                }
            }
        }
        if ($type == 'status_kz' && $status == 'Груз в дороге') {
            if (($dataCh = DB::query("SELECT * FROM staff_order WHERE country = 'kz' AND status_kz <> 'Груз в дороге' AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
                    sendKcellSMS($objCh['phone'], "Отслеживайте заказ на https://post.kz/{$objCh['kz_code']}
По вопросам номер 2442. Ваш KBT-Store.com");
                }
            }
        }
        if ($type == 'status_kz' && in_array($status, array('Обработка', 'Отложенная доставка', 'На доставку', 'Вручить подарок'))) {
            if (($dataCh = DB::query("SELECT * FROM staff_order WHERE country = 'kz' AND status = 'Подтвержден' AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
                    new Storage($objCh['id'], $objCh['send_status'], $objCh['send_status'], $status, $objCh['status_kz'], 0);
                }
            }
        }
        if ($type == 'send_status') {
            if (in_array($status, array('Отказ')) && ($dataCh = DB::query("SELECT * FROM staff_order WHERE status_kz = 'Нет товара' AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
                    $ret['success'] = false;
                    die(json_encode($ret));
                }
            } else if (false && in_array($status, array('Оплачен')) && ($dataCh = DB::queryFirstColumn("SELECT id FROM staff_order WHERE pay_type > 0 AND send_status != 'Оплачен' AND staff_id != 77777777 AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
                    cloneRassrochka($objCh);
                }
            }
        }
        if ($type == 'description' && !$_SESSION['admin']) {
            die;
        }
        if ($type == 'status_kz' && $status == 'Упакован принят') {
            if (($dataCh = DB::query("SELECT * FROM staff_order WHERE country = 'ru' AND kz_delivery = 'Почта' AND send_status<> 'Оплачен' AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
                    $updateArr = array(
                        'delivery_date' => DB::sqlEval('NOW()'),
                        'status_kz' => 'Упакован принят',
                        'log_data' => '',
                    );
                    if (ACTIVE_DELIVERY_ID == 1) {
                        $updateArr['log_data'] = sendLogos($objCh);
                        if ($updateArr['log_data'] != '{"status":"OK"}') {
                            $updateArr['status_kz'] = 'На контроль';
                        }
                    } elseif (ACTIVE_DELIVERY_ID == 2) {
                        $apiBetaPro = new ApiBetaPro();
                        $updateArr['log_data'] = $apiBetaPro->sendBetaPro($objCh, false);
                        if (!empty($updateArr['log_data']['error'])) {
                            $updateArr['status_kz'] = 'На контроль';
                            $updateArr['logos_desc'] = $updateArr['log_data']['error']['msg'];
                            $updateArr['log_data'] = $updateArr['log_data']['error'];
                        }
                        $updateArr['log_data'] = json_encode($updateArr['log_data']);
                    }
                    $delivResp = json_decode($updateArr['log_data'], true);
                    $delivResp['deliv_id'] = ACTIVE_DELIVERY_ID;
                    $updateArr['log_data'] = json_encode($delivResp);
                    DB::update('staff_order', $updateArr, 'id = %i', $objCh['id']);
                }
                ApiLogger::addLogJson('eto kakoy-to pizdec');
                ApiLogger::addLogJson("-----------------------------END");
                $ret['success'] = true;
                die(json_encode($ret));
            }
        }
        if ($type == 'status_kz_dpd' && $status == 'Упакован принят') {
            if (($dataCh = DB::query("SELECT * FROM staff_order WHERE send_status != 'Оплачен' AND id IN %li", $idsArr))) {
                foreach ($dataCh as $objCh) {
//                $updateArr = array(
//                    'delivery_date' => DB::sqlEval('NOW()'),
//                    'status_kz' => 'Упакован принят',
//                    'log_data' => '',
//                );
//                if (ACTIVE_DELIVERY_ID == 1) {
//                    $updateArr['log_data'] = sendLogos($objCh);
//                    if ($updateArr['log_data'] != '{"status":"OK"}') {
//                        $updateArr['status_kz'] = 'На контроль';
//                    }
//                } elseif (ACTIVE_DELIVERY_ID == 2) {
//                    $updateArr['log_data'] = $apiBetaPro->sendBetaPro($objCh, false);
//                    if (!empty($updateArr['log_data']['error'])) {
//                        $updateArr['status_kz'] = 'На контроль';
//                        $updateArr['logos_desc'] = $updateArr['log_data']['error']['msg'];
//                        $updateArr['log_data'] = $updateArr['log_data']['error'];
//                    }
//                    $updateArr['log_data'] = json_encode($updateArr['log_data']);
//                }
//                $delivResp = json_decode($updateArr['log_data'], true);
//                $delivResp['deliv_id'] = ACTIVE_DELIVERY_ID;
//                $updateArr['log_data'] = json_encode($delivResp);
//                DB::update('staff_order', $updateArr, 'id = %i', $objCh['id']);
                }
                ApiLogger::addLogJson('eto kakoy-to pizdec');
                ApiLogger::addLogJson("-----------------------------END");
                $ret['success'] = true;
                die(json_encode($ret));
            }
        }
        if ($status == 'Оплачен' && $type == 'send_status') {
            $addArr['return_date'] = DB::sqlEval('NOW()');
        } elseif ($status == 'Отправлен' && $type == 'send_status') {
            $addArr['delivery_date'] = DB::sqlEval('NOW()');
        }

        if ($type == 'send_status' && $status == 'Отказ') {
            $addArr['cancel_date'] = DB::sqlEval('NOW()');
        }

        if (in_array($type, array('status_kz', 'send_status', 'status_cur', 'kz_curier'))) {
            $addArr['log_data'] = CommonObject::getAdminId();
        }

        $whereStr = '';
        if (!empty($whereArr)) {
            $whereStr = ' AND ' . implode(' AND ', $whereArr);
        }

        $addArr[$type] = $status;

        // DOBRIK
        if (in_array($_SESSION['Logged_StaffId'], array(11111111, 11113333))) {
//            print_r($addArr);
//            print_r($whereArr);
//            echo $whereStr;
//            die;
        }

        ApiLogger::addLogJson('pre update');
        ApiLogger::addLogJson($addArr);
        ApiLogger::addLogJson($idsArr);

        ApiLogger::addLogJson('$origAssArr = DB::queryAssArray');
        $origAssArr = DB::queryAssArray('id', "SELECT * FROM staff_order WHERE id IN %li $whereStr", $idsArr);
        if (DB::update('staff_order', $addArr, "id IN %li $whereStr", $idsArr)) {
            ApiLogger::addLogJson('DB::update staff_order');

            $historyArr = array();
            foreach ($origAssArr as $id => $origData) {
                foreach ($addArr as $key => $val) {
                    $historyArr[] = array(
                        'object_id' => $id,
                        'type' => 'update',
                        'property' => $key,
                        'was' => $origData[$key],
                        'set' => $val
                    );
                }
            }
            $actionHistoryObj = new ActionHistoryObj();
            $actionHistoryObj->saveAll('StaffOrderObj', $historyArr, null, 'set_MasKz');
            ApiLogger::addLogJson('save history ok');
            $ret['success'] = true;
        }
    }
    return $ret;
}

function getLogosTrack($codes) {
    $uid = '688';
    $key = 'wxk9d2k9mcape7xh7uvhnkhb684fnwtt';
    $hash = md5(date('d') . $key);
    $Curl = curl_init();

    $CurlOptions = array(
        CURLOPT_URL => 'http://logoskor.ru/index.php?option=com_lkapi&task=ruspost_tracking',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROXY => 'ketkz.com:3128',
        CURLOPT_HTTPPROXYTUNNEL => 1,
        CURLOPT_USERAGENT => 'Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4',
        CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => "user_id=" . $uid . "&token=" . $hash . "&barkods=" . $codes,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    curl_close($Curl);
    return $Result;
}

function splitCountSum($count, $sum) {
    $ret = array();

    if ($count > 0) {
        $floorPrice = floor($sum / $count);
        if ($count * $floorPrice == $sum) {
            $ret = array(
                array('price' => $floorPrice, 'count' => $count)
            );
        } else {
            $tempSum = ($count - 1) * $floorPrice;
            $ret = array(
                array('price' => $floorPrice, 'count' => $count - 1),
                array('price' => $sum - $tempSum, 'count' => 1)
            );
        }
    }
    return $ret;
}

function getLogos($id) {
    $Curl = curl_init();

    $CurlOptions = array(
        CURLOPT_URL => 'http://logoskor.ru/index.php?option=com_lkapi&task=getrpo&rp_status=1&kodban=' . $id,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROXY => 'ketkz.com:3128',
        CURLOPT_HTTPPROXYTUNNEL => 1,
        CURLOPT_USERAGENT => 'Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4',
        CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    curl_close($Curl);
    return $Result;
}

if (!function_exists('checkHostPort')) {

    function checkHostPort($host, $port) {
        $ret = true;
        $connection = @fsockopen($host, $port, $errCode, $errStr, 1);
        if (is_resource($connection)) {
            fclose($connection);
        } else {
            $ret = array(
                'host' => $host,
                'port' => $port,
                'ErrCode' => $errCode,
                'ErrStr' => $errStr
            );
        }
        return $ret;
    }

}

if (!function_exists('isJson')) {

    function isJson($string) {
        return (!empty($string) &&
                is_string($string) &&
                (is_object(json_decode($string)) || is_array(json_decode($string)))
                ) ? true : false;
    }

}

if (!function_exists('num2som')) {

    /**
     * Возвращает сумму прописью
     */
    function num2som($num) {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array(// Units
            array('тыйин', 'тыйин', 'тыйин', 1),
            array('сом', 'сом', 'сом', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                } else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                }
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

}

if (!function_exists('num2dram')) {

    /**
     * Возвращает сумму прописью
     */
    function num2dram($num) {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array(// Units
            array('лум', 'лумов', 'лум', 1),
            array('драм', 'драм', 'драм', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                } else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                }
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

}

if (!function_exists('num2str')) {

    /**
     * Возвращает сумму прописью
     * @param type $num
     * @return type
     */
    function num2str($num) {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array(// Units
            array('тыин', 'тыин', 'тыин', 1),
            array('тенге', 'тенге', 'тенге', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                } else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                }
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

}

if (!function_exists('num2sum')) {

    /**
     * Возвращает сумму прописью
     * @param type $num
     * @return type
     */
    function num2sum($num) {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array(// Units
            array('тийн', 'тийн', 'тийн', 1),
            array('сум', 'сум', 'сум', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                } else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                }
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

}

if (!function_exists('morph')) {

    /**
     * Склоняем словоформу
     */
    function morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) {
            return $f5;
        }
        $n = $n % 10;
        if ($n > 1 && $n < 5) {
            return $f2;
        }
        if ($n == 1) {
            return $f1;
        }
        return $f5;
    }

}

if (!function_exists('currentFullDate')) {

    /**
     * Вывод текущей даты на русском
     */
    function currentFullDate() {
        $monthes = array(
            1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
            5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
            9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
        );

        return (date('d') . " " . $monthes[(date('n'))] . " " . date('Y'));
    }

}

if (!function_exists('generateString')) {

    /**
     * Случайная строка
     */
    function generateString($length = 6) {
        $chars = 'abc67defghi012jklmno45pqr69st15uvwxyz389';
        $numChars = strlen($chars);
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }

        return $string;
    }

}

function cloneRassrochka($id) {
    ApiLogger::addLogVarDump($id);
    if (!empty($id) && ($insertData = DB::queryFirstRow('SELECT * FROM staff_order WHERE id = %i', $id))) {
        unset($insertData['id']);
        $insertData['staff_id_orig'] = $insertData['staff_id'];
        $insertData['staff_id'] = 77777777;
        $insertData['web_id'] = $_SESSION['Logged_StaffId'];
        $insertData['ext_id'] = "{$id}_new";

        $insertData['fill_date'] = DB::sqlEval("NOW()");
        $insertData['last_edit'] = $_SESSION['Logged_StaffId'];

        $insertData['status'] = 'Подтвержден';
        $insertData['pay_type'] = 0;
        $insertData['send_status'] = 'Отправлен';
        $insertData['status_kz'] = 'Отложенная доставка';

        $insertData['package'] = 0;
        $insertData['price'] = $insertData['post_price'];
        $insertData['total_price'] = $insertData['post_price'];
        $insertData['pre_price'] = 0;
        $insertData['post_price'] = 0;
        $insertData['date_delivery'] = date('Y-m-d', strtotime('+13 day'));



        DB::insert('staff_order', $insertData);
        if (($newId = DB::insertId())) {
            ApiLogger::addLogVarDump($id);
            return $newId;
        }
    }
    return false;
}

/**
 * @param type $phone
 * @param type $text
 * @return type
 */
function sendSMS($phone, $text) {
    $msgid = 'msg' . rand(10000000, 99999999);
    $src = '<?xml version="1.0" encoding="UTF-8"?>
    <SMS>
        <operations>
        <operation>SEND</operation>
        </operations>
        <authentification>
        <username>offroad@ua.fm</username>
        <password>159753sS</password>
        </authentification>
        <message>
        <sender>SMS</sender>
        <text>' . $text . '</text>
        </message>
        <numbers>
        <number messageID="' . $msgid . '">' . $phone . '</number>
        </numbers>
    </SMS>';
    $post = array(
        "XML" => $src
    );

    $p['post'] = serialize($post);
    $p['url'] = 'http://api.atompark.com/members/sms/xml.php';
    $Curl = curl_init();
    $CurlOptions = array(
        CURLOPT_URL => 'http://api.atompark.com/members/sms/xml.php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_POSTFIELDS => http_build_query($post),
    );
    curl_setopt_array($Curl, $CurlOptions);
    /* if(false === ($Result = curl_exec($Curl))) {
      throw new Exception('Http request failed');
      } */
    $Result['EXE'] = curl_exec($Curl);
    $Result['INF'] = curl_getinfo($Curl);
    $Result['ERR'] = curl_error($Curl);
    //var_dump($Result);
    //$rez = new SimpleXMLElement($Result);
    //$rez = (array) $rez;
    //file_get_contents('http://ketkz.com/services/get_ip.php?id='.$msgid.'&cost='.((float)$rez['amount']*1.1).'&status='.$rez['status'].'&text='.$text.'&phone='.$phone.'&sender_id='.$_SESSION['Logged_StaffId'].'&system=baribarda.com');
    curl_close($Curl);
    return $Result;
}

/**
 *
 * @param type $phone
 * @param type $text
 * @return type
 */
function sendKcell($phone, $text) {
    $username = '	nurly_rest';
    $password = 'JyvB7Kbk';
    $post = array(
        "sender" => '2442',
        "client_message_id" => rand(10000000, 99999999),
        "recipient" => $_GET['phone'],
        "message_text" => $_GET['text'],
        "time_bounds" => "ad99"
    );

    $Curl = curl_init();
    $CurlOptions = array(
        CURLOPT_URL => 'https://api.kcell.kz/app/smsgw/rest/v2/messages',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        //CURLOPT_HTTPAUTH=>CURLAUTH_ANY,
        CURLOPT_USERPWD => "$username:$password",
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POSTFIELDS => json_encode($post),
        CURLOPT_HTTPHEADER => array("Content-Type: application/json; charset=utf-8"),
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    if (0) {
        $redis = new Redis();
        $redis->connect('localhost', '6379');
        $ans = json_decode($Result, true);
        //if(!isset($ans['message_id'])) $ans['message_id'] = rand(11111111,99999999);
        $redis->hmset('msgKcell', array(@$_GET['id'] => $ans['message_id']));
        file_get_contents('http://ketkz.com/services/get_ip.php?id=' . $ans['message_id'] . '&cost=6&status=1&text=' . $_GET['text'] . '&phone=' . $_GET['phone'] . '&sender_id=' . @$_SESSION['Logged_StaffId'] . '&system=call.baribarda.com');
    } else {
        file_get_contents('http://ketkz.com/services/get_ip.php?id=0&cost=6&status=0&text=' . $_GET['text'] . '&phone=' . $_GET['phone'] . '&sender_id=' . @$_SESSION['Logged_StaffId'] . '&system=call.baribarda.com');
    }

    curl_close($Curl);
    return $Result;
}

/**
 *
 * @param type $phone
 * @param type $text
 * @param type $sender
 * @return type
 */
function sendKZSMS($phone, $text, $sender = '') {
    $text = str_replace('http://', 'http&#58;&#47;&#47;', $text);
    $post = array(
        "login" => 'offroad',
        "psw" => 'offroad159753',
        "phones" => $phone,
        "sender" => $sender,
        "mes" => iconv("utf8", "cp1251", $text),
    );
    $p['post'] = serialize($post);
    $p['url'] = 'http://smsc.kz/sys/send.php';
    $Curl = curl_init();
    $CurlOptions = array(
        CURLOPT_URL => 'https://ketkz.com/resend.php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_POSTFIELDS => $p,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);

    $actionHistoryObj = new ActionHistoryObj();
    //$actionHistoryObj->save('SMSObj', '', 'insert', 'SMS text', $post['mes'], json_encode($Result), hide_phone($phone));

    curl_close($Curl);
    return $Result;
}

/**
 *
 * @param type $phone
 * @param type $text
 * @param type $sender
 * @return type
 */
function sendKetSMS($phone, $text, $sender = '') {
    $text = str_replace('http://', 'http&#58;&#47;&#47;', $text);
    $post = array(
        "login" => 'offroad',
        "psw" => 'offroad159753',
        "phones" => $phone,
        "sender" => $sender,
        "mes" => iconv("utf8", "cp1251", $text),
    );
    $p['post'] = serialize($post);
    $p['url'] = 'http://smsc.kz/sys/send.php';
    $Curl = curl_init();
    $CurlOptions = array(
        CURLOPT_URL => 'https://ketkz.com/resend.php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_POSTFIELDS => $p,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    curl_close($Curl);
    return $Result;
}

/**
 *
 * @param type $phone
 * @param type $smsText
 * @param type $id
 * @param type $extendedLogic
 * @return type
 */
function sendKcellSMS($phone = '', $smsText = '', $id = 0, $extendedLogic = false) {

    if (!empty($id)) {
        $info = getOrderOffersInfo($id);

        if (empty($phone) && !empty($info['phone'])) {
            $phone = $info['phone'];
        }
//        print_r($info);
    }

    if ($extendedLogic) {

        if ($info['total_price'] <= 7980) {
            $predoplata = 2000;
        } elseif ($info['total_price'] > 7980 && $info['total_price'] <= 15960) {
            $predoplata = 3500;
        } elseif ($info['total_price'] > 15960 && $info['total_price'] <= 20980) {
            $predoplata = 4500;
        } elseif ($info['total_price'] > 20980 && $info['total_price'] <= 25590) {
            $predoplata = 5000;
        } elseif ($info['total_price'] > 25590 && $info['total_price'] <= 30990) {
            $predoplata = 5500;
        } elseif ($info['total_price'] > 30990 && $info['total_price'] <= 35990) {
            $predoplata = 6500;
        } elseif ($info['total_price'] > 35990 && $info['total_price'] <= 40990) {
            $predoplata = 7000;
        } elseif ($info['total_price'] > 40990 && $info['total_price'] <= 45990) {
            $predoplata = 7500;
        } elseif ($info['total_price'] > 45990 && $info['total_price'] <= 50000) {
            $predoplata = 8500;
        } elseif ($info['total_price'] > 50000) {
            $predoplata = round($info['total_price'] * 0.2);
        }

//        echo "|predoplata = $predoplata|";
//        die;

        $smsText = '#post_predoplata_adaptive#';
//////////////////////////////////////////////////
//        if (array_key_exists('Парфюмерия', $info['groups'])) {
//            $smsText = '#post_predoplata_30#';
//        } elseif (($minoxidilCount = $info['count']['minoxidil'] + $info['count']['minoxidil_woman']) && $minoxidilCount >= 3) {
//            $smsText = '#post_predoplata_30#';
//        } else {
//            $smsText = '#post_predoplata_20#';
//        }
//////////////////////////////////////////////////
    }

    $smsTextArr = array(
        '#approve_info_sms#' => "Уважаемый клиент!
Компания KBT Store, ни с кем договоренности о ПОБЕДЕ в розыгрыше не имеет.
Компания KBT - store хочет поздравить с тем, после выкупа заказа автоматически, Вы становитесь участником розыгрыша 1 миллиона тенге и 50 ценных призов! Желаем вам удачи в розыгрыше!

-В случае обещаний 100% выигрыша
-В случае появления каких-либо вопросов насчет розыгрыша
Просим обратиться по номеру 2442! Обращаясь по этому номеру вы получаете всю достоверную информацию насчет наших розыгрышей !
С уважением ваш магазин KBT store.",
        '#post_predoplata_adaptive#' => "Nomer zakaza: {$id}
Vidy predoplat:
-Авторизуйтесь в Kaspi.kz, выберите
Платежи - Красота и здоровье - KBT-store
-Киви-терминал \"KBT store\" выбор магазина
-Pochtovoe otdelenie:kod platezha 12077 ИП \"Газиз\"
-Авторизуйтесь в Homebank.kz, выберите
Платежи - Покупки - KBT Store
Введите номер заказа и оплатите сумму предоплаты
Info: +77781232629
Общая цена заказа {$info['total_price']} тг.
Vash KBT STORE ❤",
        '#post_predoplata_20#' => "Zakaz {$id}
Predoplata: 20% ot zakaza
Vidy predoplat:
-Pochtovoe otdelenie:kod platezha 12012 ИП \"ABC Group\"
-Kaspi Bank nomer karty 4400 4301 2510 6499  IIN 921 230 450 513 Muratbekkyzy Zhanagul
Posle oplaty chek na whatsapp: +77781232629
Info: +77781232629
Vash KBT STORE ❤",
        '#post_predoplata_30#' => "Zakaz {$id}
Predoplata: 30% ot zakaza
Vidy predoplat:
-Pochtovoe otdelenie:kod platezha 12012 ИП \"ABC Group\"
-Kaspi Bank nomer karty 4400 4301 2510 6499  IIN 921 230 450 513 Muratbekkyzy Zhanagul
Posle oplaty chek na whatsapp: +77781232629
Info: +77781232629
Vash KBT STORE ❤",
        '#oplata_rozigrash#' => "Уважаемый клиент благодарим Вас за покупку ❤
Теперь Вы становитесь автоматически участником розыгрыша Toyota Camry 70
С любовью ваш KBT Group"
    );

    if (!empty($smsTextArr[$smsText])) {
        $smsText = $smsTextArr[$smsText];
    }

    $phoneClear = setPhoneTemplate($phone, '7XXXXXXXXXX');
    $params = array(
        'Kcell' => 1,
        'text' => $smsText,
        'phone' => $phoneClear,
        'id' => $id
    );

//    print_r($params);
//    die;

    $url = 'http://ketkz.com/resend.php?' . http_build_query($params);
    ApiLogger::addLogVarExport($url);

    $rez = sendCurlRequest($url);
    $actionHistoryObj = new ActionHistoryObj();
    if (!empty($rez)) {
        ApiLogger::addLogVarExport($rez);
        if (mb_stripos($rez, 'error') !== false) {
            //$actionHistoryObj->save('SMSObj', $id, 'insert', 'SMS text', $smsText, "ERROR: {$rez}", hide_phone($phoneClear));
        } elseif (mb_stripos($rez, 'message_id') !== false) {
            //$actionHistoryObj->save('SMSObj', $id, 'insert', 'SMS text', $smsText, "SENDED: {$rez}", hide_phone($phoneClear));
        }
    } else {
        //$actionHistoryObj->save('SMSObj', $id, 'insert', 'SMS text', $smsText, "ERROR SERVICE", hide_phone($phoneClear));
    }

    return $rez;
}

/**
 *
 * @param type $text
 * @return type
 */
function mb_ucfirst($text) {
    return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

/**
 *
 * @param type $orderId
 * @return type
 */
function getOrderOffersInfo($orderId) {
    global $GLOBAL_OFFER_GROUP;
    $ret = array();

    if (($staffOrder = DB::queryOneRow('SELECT id, phone, offer, package, price, dop_tovar, fio FROM staff_order WHERE id = %i', $orderId))) {
        if (!empty($staffOrder['offer']) && !empty($staffOrder['package'])) {
            $ret['count'][$staffOrder['offer']] += (int) $staffOrder['package'];
            $ret['price'][$staffOrder['offer']] += (int) $staffOrder['price'];
            $ret['groups'][$GLOBAL_OFFER_GROUP[$staffOrder['offer']]] += (int) $staffOrder['package'];
        }

        if (isJson($staffOrder['dop_tovar'])) {
            $staffOrder['dopTovarArr'] = json_decode($staffOrder['dop_tovar'], true);
            foreach ($staffOrder['dopTovarArr']['dop_tovar'] as $key => $tmp) {
                if (!empty($staffOrder['dopTovarArr']['dop_tovar'][$key]) && !empty($staffOrder['dopTovarArr']['dop_tovar_count'][$key])) {
                    $ret['count'][$staffOrder['dopTovarArr']['dop_tovar'][$key]] += (int) $staffOrder['dopTovarArr']['dop_tovar_count'][$key];
                    $ret['price'][$staffOrder['dopTovarArr']['dop_tovar'][$key]] += (int) $staffOrder['dopTovarArr']['dop_tovar_price'][$key];
                    $ret['groups'][$GLOBAL_OFFER_GROUP[$staffOrder['dopTovarArr']['dop_tovar'][$key]]] += (int) $staffOrder['dopTovarArr']['dop_tovar_count'][$key];
                }
            }
        }

        $ret['total_price'] = array_sum(array_values($ret['price']));
        $ret['total_count'] = array_sum(array_values($ret['count']));

        $ret['fio'] = mb_ucfirst($staffOrder['fio']);
        $ret['phone'] = $staffOrder['phone'];
    }
    return $ret;
}

function getKZSMStat($phone) {
    $post = array(
        "login" => 'offroad',
        "psw" => 'offroad159753',
        "cnt" => 100,
        "phone" => trim($phone)
    );
    //var_dump($post);
    $Curl = curl_init();
    $p['post'] = serialize($post);
    $p['url'] = 'https://smsc.ru/sys/get.php?get_messages=1';
    $CurlOptions = array(
        CURLOPT_URL => 'http://ketkz.com/resend.php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_POSTFIELDS => $p,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    //var_dump($Result); die;
    curl_close($Curl);
    return $Result;
}

function sendKZSMSNew($phone, $text) {

//    $text = 'Vash zakaz uspeshno otpravlen mnogo raz';
    $text = 'Vash zakaz uspeshno otpravlen mnogo raz';
//    $phone = '77477807355';
    $post = array(
        "login" => 'offroad',
        "psw" => 'offroad159753',
        "phones" => $phone,
        "sender" => 'KAZTRANSIT',
        "mes" => $text,
//        "mes" => iconv('utf8', 'cp1251', $text),
    );

    $Curl = curl_init();
    $CurlOptions = array(
        CURLOPT_URL => 'http://smsc.kz/sys/send.php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_POSTFIELDS => $post,
    );
    curl_setopt_array($Curl, $CurlOptions);
    $Result = curl_exec($Curl);
    curl_close($Curl);
    return $Result;
}

function sendGate($phone, $text) {

    include_once("/var/www/baribarda.com/api/phpagi-asmanager.php");
    $agi = new AGI_AsteriskManager;

    $vst_host = "89.218.84.74"; #YOUR VOXSTACK GSM GATEWAY IP ADDRESS
    $vst_user = "admin"; #Corresponding to your GSM gateway API settings
    $vst_pwd = "159753sS"; #Corresponding to your GSM gateway API settings
    $vst_port = "5038"; #Corresponding to your GSM gateway API settings

    $agi_status = $agi->connect($vst_host . ":" . $vst_port, $vst_user, $vst_pwd);
    if (!$agi_status) {
        $msg = "Failed to connected Asterisk,exit..";
        exit(0);
    }
    $type = "gsm";
    $method = "send";
    $sync = "sms";
    $span = rand(1, 4); #YOUR SIMcard for sending sms
    $destination = $phone; #YOUR DESTINATION NUMBER
    $message = mb_convert_encoding($text, "utf-8", mb_detect_encoding($text)); #if text in russian
    $timeout = "30";
    $id = rand(100000, 999999);

    $ret = $agi->Command("$type $method $sync $span $destination \"$message\" $timeout $id");
    return $ret;
}

function pr_time($s) {
    $m = '00';
    $h = 0;
    if ($s > 3600) {
        $h = floor($s / 3600);
        $s = fmod($s, 3600);
    }
    if ($s > 60) {
        $res = floor($s / 60);
        if ($res > 9) {
            $m = $res . ':';
        } else {
            $m = '0' . $res . ':';
        }
        $s = fmod($s, 60);
        if ($s < 10) {
            $m .= '0' . $s;
        } else {
            $m .= $s;
        }
    } else {
        if ($s < 10) {
            $m .= ':0' . $s;
        } else {
            $m .= ':' . $s;
        }
    }
    if ($h) {
        $res = $h . ':' . $m;
    } else {
        $res = $m;
    }
    return($res);
}

function hide_phone($phone, $obr = false) {
    $pre = substr($phone, -7, 6);
    $method = substr($phone, -1, 1);
    switch ($method) {
        case '0':
            $rez = strrev($pre);
            break;

        case '1':
            $pre1 = substr($phone, -7, 3);
            $pre2 = substr($phone, -4, 3);
            $rez = strrev($pre1) . strrev($pre2);
            break;

        case '2':
            $pre1 = substr($phone, -7, 2);
            $pre2 = substr($phone, -5, 2);
            $pre3 = substr($phone, -3, 2);
            $rez = strrev($pre1) . strrev($pre2) . strrev($pre3);
            break;

        case '3':
            $pre1 = substr($phone, -7, 1);
            $pre2 = substr($phone, -6, 4);
            $pre3 = substr($phone, -2, 1);
            $rez = strrev($pre1) . strrev($pre2) . strrev($pre3);
            break;

        case '4':
            $pre1 = substr($phone, -7, 4);
            $pre2 = substr($phone, -3, 2);
            $rez = strrev($pre1) . strrev($pre2);
            break;

        case '5':
            $rez = substr(substr($phone, -7, 1) + $method, -1, 1) . substr(substr($phone, -6, 1) + $method, -1, 1) . substr(substr($phone, -5, 1) + $method, -1, 1) . substr(substr($phone, -4, 1) + $method, -1, 1) . substr(substr($phone, -3, 1) + $method, -1, 1) . substr(substr($phone, -2, 1) + $method, -1, 1);
            break;

        case '6':
        case '7':
        case '8':
        case '9':
            if ($obr) {
                $rez = $pre - $method;
                if ($rez < 0) {
                    $rez = 999999;
                }
            } else {
                $rez = $pre + $method;
            }
            break;
    }
    if (strlen($rez) == 5) {
        $rez = '0' . $rez;
    }
    if (strlen($rez) == 4) {
        $rez = '00' . $rez;
    }
    if (strlen($rez) == 3) {
        $rez = '000' . $rez;
    }
    if (strlen($rez) == 2) {
        $rez = '0000' . $rez;
    }
    if (strlen($rez) == 1) {
        $rez = '00000' . $rez;
    }
    if (strlen($rez) == 0) {
        $rez = '000000' . $rez;
    }
    $new_phone = substr($phone, 0, 4) . $rez . $method;
    return $new_phone;
}

function hide_phone_old($phone) {
    $pre1 = strrev(substr($phone, -7, 3));
    $pre2 = strrev(substr($phone, -4, 2));
    $pre3 = strrev(substr($phone, -2, 2));
    $new_phone = substr($phone, 0, 4) . $pre1 . $pre2 . $pre3;
    //$new_phone = substr($phone,0,4).hexbin($pre1.$pre2.$pre3);
    return $new_phone;
}

function getStaffListByRole($roleName, $assoc = true) {

    $roleBit = 0;
    switch ($roleName) {
        case 'admin':
            $roleBit = 1;
            break;
        case 'logist':
            $roleBit = 2;
            break;
        case 'operator':
            $roleBit = 4;
            break;
        case 'adminlogist':
            $roleBit = 8;
            break;
        case 'adminlogistpost':
            $roleBit = 16;
            break;
        case 'logistcity':
            $roleBit = 32;
            break;
        case 'admincity':
            $roleBit = 64;
            break;
        case 'postlogist':
            $roleBit = 128;
            break;
        case 'incomeoper':
            $roleBit = 256;
            break;
        case 'operatorcold':
            $roleBit = 512;
            break;
        case 'operatorrecovery':
            $roleBit = 1024;
            break;
        case 'whatsappoperator':
            $roleBit = 2048;
            break;
        case 'adminsales':
            $roleBit = 4096;
            break;
        case 'webmaster':
            $roleBit = 8192;
            break;
        case 'onexperience':
            $roleBit = 16384;
            break;
        case 'operator_seeding':
            $roleBit = 32768;
            break;
        case 'operator_bishkek':
            $roleBit = 65536;
            break;
        case 'bishkek_logist':
            $roleBit = 131072;
            break;
        case 'bishkek_admin_logist':
            $roleBit = 262144;
            break;
        case 'offline_island':
            $roleBit = 524288;
            break;
        case 'logistprepayment':
            $roleBit = 1048576;
            break;
    }

    $qs = " SELECT  id,
                    Level,
                    FirstName,
                    LastName,
                    CONCAT(FirstName, ' ', LastName) AS fio,
                    CONCAT(FirstName, ' ', LastName) AS `value`,
                    created_at,
                    Location,
                    Sip,
                    time_zone_id
            FROM    Staff
            WHERE   type = 1 AND Level & $roleBit
            ORDER BY fio";
    return $assoc ? DB::queryAssData('id', 'fio', $qs) : DB::queryAssArray('id', $qs);
}

function orderBonus($phone, $order_id, $price, $count) {
    /**
     * api urls
     * http://crm.brdmarket.loc/api/bonuses/add
     * http://crm.brdmarket.loc/api/bonuses/remove
     */
    $key = '7JMt3kdIS1hhhubE';
    $hash_str = md5($phone . $key);
    $hash = hash('sha256', $hash_str);

    $url = 'http://89.218.86.178:9081/api/bonuses/add';

    $post = [
        'phone' => $phone,
        'order_id' => $order_id,
        'price' => $price,
        'count' => $count,
        'hash' => $hash
    ];

    $s = curl_init();

    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_POST, 1);
//curl_setopt($s, CURLOPT_PROXY,'ketkz.com:3128');
//curl_setopt($s, CURLOPT_HTTPPROXYTUNNEL,1);
    curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($s, CURLOPT_TIMEOUT, 10);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    $exec = curl_exec($s);
    curl_close($s);
    return $exec;
//echo PHP_EOL; print_r($exec); echo PHP_EOL;
    /*
      {"result":5,"message":"ADD :: must be ok"}
      {"result":6,"message":"REMOVE :: must be ok"}
      {"result":false,"message":"wrong hash"}
      {"result":false,"message":"user does`t exist"}
      {"result":false,"message":"you not allowed direct access!"}
     */
}

function getBonus($phone) {
    /**
     * api urls
     * http://crm.brdmarket.loc/api/bonuses/add
     * http://crm.brdmarket.loc/api/bonuses/remove
     */
    $key = '7JMt3kdIS1hhhubE';
    $hash_str = md5($phone . $key);
    $hash = hash('sha256', $hash_str);

    $url = 'http://89.218.86.178:9081/api/bonuses/getByPhone';

    $post = [
        'phone' => $phone,
        'hash' => $hash
    ];

    $s = curl_init();

    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_POST, 1);
//curl_setopt($s, CURLOPT_PROXY,'ketkz.com:3128');
//curl_setopt($s, CURLOPT_HTTPPROXYTUNNEL,1);
    curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($s, CURLOPT_TIMEOUT, 20);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    $exec = curl_exec($s);
    curl_close($s);
    return $exec;
}

function removeBonus($phone, $id, $value) {
    /**
     * api urls
     * http://crm.brdmarket.loc/api/bonuses/add
     * http://crm.brdmarket.loc/api/bonuses/remove
     */
    $key = '7JMt3kdIS1hhhubE';
    $hash_str = md5($phone . $key);
    $hash = hash('sha256', $hash_str);

    $url = 'http://89.218.86.178:9081/api/bonuses/remove';

    $post = [
        'phone' => $phone,
        'order_id' => $id,
        'bonus' => (int) $value,
        'hash' => $hash
    ];

    $s = curl_init();

    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_POST, 1);
//curl_setopt($s, CURLOPT_PROXY,'ketkz.com:3128');
//curl_setopt($s, CURLOPT_HTTPPROXYTUNNEL,1);
    curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($s, CURLOPT_TIMEOUT, 20);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    $exec = curl_exec($s);
    curl_close($s);
    return $exec;
}

/**
 *
 * @param type $partCount
 * @param type $direction
 * @return type
 */
function getHalfMonthPeriod($partCount = 1, $direction = 'desc') {
    $currDeltaDay = 0;
    $ret = array();
    while (count($ret) < $partCount) {

        $currTime = strtotime("$currDeltaDay day");
        $currDay = date('j', $currTime);
        $currYearMonth = date('Y-m-', $currTime);

        $checkDate = $currYearMonth . ($currDay > 15 ? '16' : '01');
        if (!array_key_exists($checkDate, $ret)) {
            $ret[$checkDate]['title'] = "$currYearMonth" . ($currDay > 15 ? '16' : '01') . ' .. ' . $currYearMonth . ($currDay > 15 ? date('t', strtotime($checkDate)) . '' : '15');
            $ret[$checkDate]['sql_between'] = "'$currYearMonth" . ($currDay > 15 ? '16' : '01') . "' AND '" . $currYearMonth . ($currDay > 15 ? date('t', strtotime($checkDate)) . ' 23:59:59' : '15 23:59:59') . "'";
            $ret[$checkDate]['start'] = "$currYearMonth" . ($currDay > 15 ? '16' : '01');
            $ret[$checkDate]['end'] = $currYearMonth . ($currDay > 15 ? date('t', strtotime($checkDate)) . ' 23:59:59' : '15 23:59:59');
        }

//        print_r($ret);die;
        $currDeltaDay -= 10;
    }
    return array_values($direction == 'desc' ? $ret : array_reverse($ret));
}

/**
 *
 * @param type $document
 * @param type $chatId
 * @param type $tokenID
 * @return type
 */
function sendTelegramFile($document, $chatId, $tokenID) {
    $url = "https://api.telegram.org/bot{$tokenID}/sendDocument";
    $document = new CURLFile($document);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id' => $chatId, 'document' => $document]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:multipart/form-data']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $out = curl_exec($ch);

    curl_close($ch);
    return $out;
}
