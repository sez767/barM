<?php

header('Content-Type: application/javascript; charset=utf-8');

$parametr = $_REQUEST['data'];

$ret = array();
$type = empty($_REQUEST['type']) ? 'value' : $_REQUEST['type'];
$key = empty($_REQUEST['key']) ? '' : $_REQUEST['key'];
$full = empty($_REQUEST['full']) ? '' : true;
$data = array();

switch ($parametr) {

    case 'cold_statuses':
        $data = array(
            array('id' => 0, 'value' => 'Не определен'),
            array('id' => 1, 'value' => 'Холодный'),
            array('id' => 2, 'value' => 'Надо перезвонить'),
            array('id' => 3, 'value' => 'Недоступен'),
            array('id' => 4, 'value' => 'Не согласен'),
            array('id' => 5, 'value' => 'Внести заказ'),
            array('id' => 6, 'value' => 'Холодный (брак)'),
            array('id' => 7, 'value' => 'Прозвонен нд'),
//            array('id' => 8, 'value' => 'Заинтересован'),
//            array('id' => 9, 'value' => 'Не заинтересован'),
//            array('id' => 8, 'value' => 'ОД')
            array('id' => 10, 'value' => 'Прозвонен ОТКЛ'),
//            array('id' => 11, 'value' => 'Разговор не состоялся'),
            array('id' => 12, 'value' => 'Дубль')
        );
        break;

    case 'queue_list':
        require_once dirname(__FILE__) . "/../lib/db.php";
        asterisk_base();
        $data = DB::query('SELECT `queue_name` AS id, `queue_name` AS `value` FROM `predictive_multiplier` UNION SELECT `name` AS id, `name` AS `value` FROM `queue_table` ORDER BY `value`');
        break;

    case 'offer_groups':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = array_flip($redis->hGetAll('offer_groups'));
        $data = array();
        $data[] = array('group' => '', 'offer' => '-');

        foreach ($GLOBAL_GROUP_OFFER as $group => $offerArr) {
            foreach ($offerArr as $offerItem) {
                $data[] = array('group' => $group, 'offer' => $offerItem);
            }
            unset($redisData[$group]);
        }
        foreach ($redisData as $group => $noUsedVal) {
            $data[] = array('group' => $group, 'offer' => $noUsedVal);
        }

        break;

    case 'manager':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $whereArr = array('1 = 1');
        if (!empty($_REQUEST['real'])) {
            $whereArr[] = 'type = 1';
        } else if (!empty($_REQUEST['is_responsible'])) {
            $whereArr[] = 'id IN (' . implode(', ', $GLOBAL_STAFF_RESPONSIBLE) . ')';
        }
        $data = DB::query("SELECT id, TRIM(CONCAT(FirstName, ' ', LastName)) AS `value`, `Type` AS `type`, IsResponsible, Responsible, IsCurator, Curator FROM `Staff` WHERE " . implode(' AND ', $whereArr) . ' ORDER BY `value`');
        break;

    case 'partners':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $whereArr = array('1=1');
        if (in_array($_SESSION['Logged_StaffId'], array(94448321))) {
            $whereArr[] = 'id NOT IN (11111111, 22222222, 33333333)';
        }

        $data = DB::query('SELECT id, partner_name, partner_name AS `value` FROM partners WHERE ' . implode(' AND ', $whereArr) . ' ORDER BY value');
//        $data[] = array('id' => '', 'partner_name' => '--', 'value' => '--');
        break;

    case 'cold_operator':
        require_once dirname(__FILE__) . "/../lib/db.php";
//        $data = array_merge(getStaffListByRole('operatorcold', false), getStaffListByRole('operator_bishkek', false));
        $data = getStaffListByRole('operatorcold', false);
        break;

    case 'offers':
        require_once dirname(__FILE__) . "/../lib/db.php";
        $data = DB::query('SELECT offer_id AS id, offer_name AS value, offer_group AS `group`, offer_desc AS name, offer_clientprice AS price, offer_show_in_cold_kz, offer_show_in_cold_kgz, offer_show_in_cold_uz, offer_photo FROM offers WHERE offers_active ORDER BY value');
        break;

    case 'incomeoper':
        require_once dirname(__FILE__) . "/../lib/db.php";
        $data = getStaffListByRole('incomeoper', false);
        break;

    case 'call_operator':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $data = getStaffListByRole('operator', false);
        break;

//    case 'operator_logist':
//        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
//
//        $redis = RedisManager::getInstance()->getRedis();
//        $redisData = $redis->hGetAll('operator_logist');
//        $data = array();
//        foreach ($redisData as $ii => $vv) {
//            $data[$vv] = array('id' => $ii, 'value' => $vv);
//        }
//        asort($data);
//        break;

    case 'operator_logist':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('operator_logist');
        $redisDataKC = $redis->hGetAll('operator_logist_kc');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[trim($vv)] = array('id' => $ii, 'value' => trim($vv));
//            $data[trim($vv)]['kc'] = $redisDataKC[$ii];
            if (isset($redisDataKC[$ii])) {
                $data[trim($vv)]['kc'] = $redisDataKC[$ii];
            }
        }
        asort($data);
        $data = array_values($data);
        break;

    case 'kz_admin':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('delivery_mass_admin');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        $data = array_values($data);
        break;

    case 'services_categories':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('services_categories');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        $data = array_values($data);
        break;

    case 'cities_kz':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('Kazaxstan');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        $data = array_values($data);

//        print_r($data);die;

        break;

    case 'control_admin':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('control_admins');
        $data = DB::query("SELECT id, TRIM(CONCAT(FirstName, ' ', LastName)) AS `value` FROM `Staff` WHERE id IN %li ORDER BY `value`", $redisData);
        break;

    case 'position_man':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('position_man');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'obzvon_problems':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('obzvon_problems');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'cancel_types':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('cancel_types');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'defect_types':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('defect_types');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'status_kz':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
        session_start();

        $whereArr = array();
        if (!empty($_SESSION['logist']) && strpos($_SESSION['country'], '"kz"') !== false && empty($_SESSION['admin'])) {
            $whereArr = array(
                'Обработка',
//                'Вручить подарок',
//                'Упакован',
//                'Груз в дороге',
                'Упакован добавочный',
                'На доставку',
                'Отложенная доставка',
                'Хранение',
                'На контроль',
                'Отказ',
                'Автоответчик',
                'Скинут предоплату'
            );
        }

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('status_kz');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            if (empty($whereArr) || in_array($vv, $whereArr)) {
                $data[$vv] = array('id' => $ii, 'value' => $vv);
            }
        }
        ksort($data);
        break;

    case 'status_kz_otkaz':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';
        session_start();

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('status_kz_otkaz_reasons');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'send_status':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('send_statuses');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'status_cur':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('status_cur');

        $finStatuses = array(
            /////////////////// Безопасные
//            'НД',
//            'Не упакован',
//            'ОД',
//            'ОД оплатить',
//            'ОЖИДАЕТ',
//            'ОТКАЗ',
//            'ПЕРЕНОС',
//            'Перезвонить',
//            'Перенос оплатить',
//            'СЛЕДУЮЩИЙ',
//            'УТОЧНИТЬ',
//            'нет товара',
//            'отказ проплатить',
//            'сделан возврат денег',
            /////////////////// Финансовые
            'Выезд дважды Распол',
            'выезд дважды',
            'Замена сделана клиент оплотил(0)',
            'ОПЛ',
            'ОПЛ ДОП',
            'ОПЛ ДОП РАСПОЛ',
            'ОПЛ Карта',
            'ОПЛ дальний',
            'ОПЛ дальний располовинен',
            'ОПЛ карта Располовинен',
            'ОПЛ располовинен',
            'ОПЛ тс Карта',
            'Опл транспортировка',
            'Продажа курьер',
            'Продажа курьер распол',
            'Продажа карта',
            'Продажа карта распол',
            'замена сделана',
            'перенос сделать возврат денег(0)',
            'сделать возврат денег',
            'сделать замену',
        );

        function cmp($a, $b) {
            if ($a['fin_type'] == $b['fin_type']) {
                if ($a['id'] == $b['id']) {
                    return 0;
                }
                return ($a['id'] < $b['id']) ? -1 : 1;
            }
            return ($a['fin_type'] < $b['fin_type']) ? -1 : 1;
        }

        $data = array();
        foreach ($redisData as $ii => $vv) {
            $isFin = in_array($vv, $finStatuses) ? 1 : 0;
            $data[$vv] = array(
                'id' => $vv,
                'value' => $vv,
                'fin_type' => $isFin,
                'class' => $isFin ? 'warning_bg_row' : 'self_bg_row'
            );
        }
        ksort($data);
        usort($data, 'cmp');
        $data = array_values($data);
        break;

    case 'otkaz_types':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('otkaz_types');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'cold_otkaz_reasons':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();
        $redisData = $redis->hGetAll('cold_otkaz_reasons');
        $data = array();
        foreach ($redisData as $ii => $vv) {
            $data[$vv] = array('id' => $ii, 'value' => $vv);
        }
        ksort($data);
        break;

    case 'delivery_couriers':
        require_once dirname(__FILE__) . '/../lib/CommonManagers.php';

        $redis = RedisManager::getInstance()->getRedis();

        $currier_kz = $redis->hGetAll('Currier');
        $currier_kg = $redis->hGetAll('CurrierKGZ');
        $currier_am = $redis->hGetAll('CurrierAM');
        $currier_az = $redis->hGetAll('CurrierAZ');
        $currier_md = $redis->hGetAll('CurrierMD');
        $currier_uz = $redis->hGetAll('CurrierUZ');
        $currier_ae = $redis->hGetAll('CurrierAE');
        $currier_ru = $redis->hGetAll('CurrierRus');

        $data = array();
        $data[] = array('id' => 'Почта', 'index' => '', 'country' => '', 'city' => 'Почта', 'value' => 'Почта');
        $data[] = array('id' => 'Вся курьерка', 'index' => '', 'country' => '', 'city' => 'Вся курьерка', 'value' => 'Вся курьерка');
        ///////////////////////
        $data[] = array('id' => 'Работает', 'index' => '', 'country' => '', 'city' => 'Работает', 'value' => 'Работает');
        $data[] = array('id' => 'Не работает', 'index' => '', 'country' => '', 'city' => 'Не работает', 'value' => 'Не работает');

        foreach (array('kz', 'kg', 'am', 'az', 'md', 'uz', 'ae', 'ru') as $country) {
            $arrName = 'currier_' . $country;
            asort($$arrName);

//            $data[] = array('id' => 'Почта', 'index' => '', 'country' => $country, 'city' => 'Почта', 'value' => 'Почта');

            foreach ($$arrName as $index => $city) {
                $data[] = array('id' => $city, 'index' => $index, 'country' => $country, 'city' => $city, 'value' => $city);
            }
        }

        break;

    case 'kz_couriers':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $data = array();
        if (empty($GLOBAL_KZ_COURIERS)) {
            for ($i = 1; $i <= 200; $i++) {
                $data[] = array(
                    'id' => $i,
                    'name' => "$i: $i",
                    'sip' => 0,
                    'phone' => null
                );
            }
        } else {
            $data = array_values($GLOBAL_KZ_COURIERS);
        }
        break;

    case 'ketkz_otpravitels':
        require_once dirname(__FILE__) . "/../lib/db.php";

        $data = array();
        if (!empty($GLOBAL_KETKZ_OTPRAVITEL)) {



            foreach ($GLOBAL_KETKZ_OTPRAVITEL as $key => $value) {
                $data[$value['sender_name']] = array(
                    'id' => $key,
                    'value' => $value['sender_name']
                );
            }
        }

        ksort($data);

        $data[] = array('id' => 3, 'value' => '-Мукалиев-');
        $data[] = array('id' => 7, 'value' => '-БРДМаркет-');
        $data[] = array('id' => 10, 'value' => '-БЦ групп-');

        $data = array('data' => array_values($data));
        echo json_encode($data);
        die;

        break;
}

// Обрабатываем условия, есили такие были в запросе
if (!empty($_REQUEST['where']) && is_array($_REQUEST['where'])) {
    foreach ($data as $dKey => $dValue) {
        foreach ($_REQUEST['where'] as $wKey => $wValue) {
            if ($dValue[$wKey] != $wValue) {
                unset($data[$dKey]);
                continue;
            }
        }
    }
}

if ($data) {
    foreach ($data as $valKey => $value) {
        if (empty($_REQUEST['store'])) {
            if (empty($key)) {
                if ($full) {
                    $ret[$valKey] = $value;
                } else {
                    if ($type == 'key') {
                        $ret[$valKey] = $valKey;
                    } else {
                        $ret[$valKey] = isset($value[$type]) ? $value[$type] : $value['value'];
                    }
                }
            } else {
                if ($full) {
                    $ret[$value[$key]] = $value;
                } else {
                    if ($type == 'key') {
                        $ret[$value[$key]] = $valKey;
                    } else {
                        $ret[$value[$key]] = isset($value[$type]) ? $value[$type] : $value['value'];
                    }
                }
            }
        } else {
            if (empty($key)) {
                if ($full) {
                    $ret[] = array((string) $valKey, $value);
                } else {
                    if ($type == 'key') {
                        $ret[] = array((string) $valKey, $valKey);
                    } else {
                        $ret[] = array((string) $valKey, isset($value[$type]) ? $value[$type] : $value['value']);
                    }
                }
            } else {
                if ($full) {
                    $ret[] = array((string) $value[$key], $value);
                } else {
                    if ($type == 'key') {
                        $ret[] = array((string) $value[$key], $valKey);
                    } else {
                        $ret[] = array((string) $value[$key], isset($value[$type]) ? $value[$type] : $value['value']);
                    }
                }
            }
        }
    }
}

echo json_encode(array('data' => $ret));
