<?php

require_once dirname(__FILE__) . '/../ini/util.php';
require_once dirname(__FILE__) . '/../lib/ApiLogger.php';

class DobrMailSender {

    /**
     *
     * @param array | string $to
     * @param string $subject
     * @param array $files
     * @param string $body
     * @param string $fromName
     * @param integer $debugMode
     * @return boolean
     */
    static public function sendMailGetaway($to, $subject, $files = array(), $body = null, $fromName = null, $debugMode = 0) {
        $ret = false;

        if (!empty($to)) {
            if (!is_array($to)) {
                $to = array($to);
            }
            if (!empty($files) && !is_array($files)) {
                $files = array($files);
            }
            $postData = array(
                'use' => false || isset($_SESSION['Logged_StaffId']) && in_array($_SESSION['Logged_StaffId'], array(-111111111)) ? json_encode(array('barisender')) : json_encode(array('baribardacall', 'off.blackpearl')),
                'use' => false || isset($_SESSION['Logged_StaffId']) && in_array($_SESSION['Logged_StaffId'], array(-111111111)) ? json_encode(array('barisender')) : json_encode(array('ketkzcom', 'bbardacall')),
                'use' => false || isset($_SESSION['Logged_StaffId']) && in_array($_SESSION['Logged_StaffId'], array(-111111111)) ? json_encode(array('barisender')) : json_encode(array('barisender')),
                'use' => json_encode(array('barisender')),
                'pfgbplfnj' => true,
                'addressees' => json_encode($to),
                'Subject' => $subject,
                'SMTPDebug' => $debugMode
            );
            if ($body) {
                $postData['Body'] = $body;
            }
            if ($fromName) {
                $postData['FromName'] = $fromName;
            }

            foreach ($files as $fileItem) {
                $info = pathinfo($fileItem);

                if (function_exists('curl_file_create')) {
                    $postData[$info['filename']] = curl_file_create($fileItem);
                } else {
                    $info = pathinfo($fileItem);
                    $postData[$info['filename']] = '@' . $fileItem . ';filename=' . $info['basename'] . ';type=' . mime_content_type($fileItem);
                }
            }

//            $ret = self::_sendCurlRequest('http://baribarda.com/mailer/send.php', $postData);
            $ret = self::_sendCurlRequest('http://call.baribarda.com/call.baribarda.com/mailer/send.php', $postData);
//            $ret = self::_sendCurlRequest('http://baribarda.com/mailer/send.php', $postData);
        }

        return $debugMode > 0 ? $ret : !empty($ret['success']);
    }

    static private function _sendCurlRequest($url, $postData = null) {
        // инициализируем сеанс
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        // максимальное время выполнения скрипта
        curl_setopt($curl, CURLOPT_TIMEOUT, 40);
        // теперь curl вернет нам ответ, а не выведет
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // Отключаем ssl проверку
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);

        if ($postData !== null) {
            // передаем данные по методу post
            curl_setopt($curl, CURLOPT_POST, 1);
            // переменные, которые будут переданные по методу post
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }

        // отправка запроса
        $result = curl_exec($curl);

        $info = curl_getinfo($curl);
        if (empty($result)) {
            ApiLogger::addLogJson($info);
            $result = array("http_code" => $info['http_code'], "error" => "Server is not responding");
        } else {
            $result = json_decode($result, true);
            $result['http_code'] = $info['http_code'];
        }
        // закрываем соединение
        curl_close($curl);

//        ApiLogger::addLogJson($result);
        return $result;
    }

}

class MemcacheManager {

    private $_memcache;
    private static $instance = null;

    /**
     * @return MemcacheManager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {

    }

    private function __construct() {
        $this->_memcache = new Memcache();
        $this->_memcache->connect('127.0.0.1', 11211);
    }

    public function getMemcache() {
        return $this->_memcache;
    }

}

class RedisManager {

    private $_redis;
    private static $instance = null;

    /**
     * @return RedisManager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {

    }

    private function __construct() {
        $this->_redis = new Redis();
        $this->_redis->connect('127.0.0.1', 6379);
    }

    public function getRedis() {
        return $this->_redis;
    }

}

class SphinxManager {

    private $_dbLink;
    private static $instance = null;

    /**
     * @return SphinxManager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {

    }

    private function __construct() {
        $this->_dbLink = mysql_connect(sphinx_hostname . ':' . sphinx_port, sphinx_username, sphinx_password);
    }

    public function getDB() {
        return $this->_dbLink;
    }

    /**
     * @param string $qs
     * @param boolean $retId
     * @return array
     */
    public static function prepareQuery($qs, $retId = true) {
        $ret = array('qs_orig' => $qs);

        $ret['qs_spinx'] = preg_replace(array('/SQL_CALC_FOUND_ROWS/i', '/select\s+(.*)\s+from\s+(.+?)\s+/i'), array('', "SELECT $1 FROM $2" . '_index '), $qs);
        $ret['qs_total'] = preg_replace(array('/select\s+.+?\s+from(.+)\s+(order by|limit).+/i'), array("SELECT COUNT(*) FROM $1"), $ret['qs_spinx']);

        if ($retId) {
            $ret['qs_spinx'] = preg_replace(array('/select\s+(.+)\s+from\s+/i'), array("SELECT `id` FROM "), $ret['qs_spinx']);
        }
        $ret['data'] = array();
        return $ret;
    }

    public static function getId($qs, $cache = false) {
        $ret = self::prepareQuery($qs);

        $sphinxRes = mysql_query($ret['qs_spinx'], self::getInstance()->getDB());
        while (($obj = mysql_fetch_assoc($sphinxRes))) {
            $ret['data'][$obj['id']] = $obj['id'];
        }

        return self::addTotal($ret);
    }

    public static function getData($qs, $cache = false) {
        $ret = self::prepareQuery($qs, false);

        if ($cache) {
            $redis = RedisManager::getInstance()->getRedis();

            $redisKey = 'sphinxCache_' . md5($ret['qs_spinx']);
            $exist = $redis->exists($redisKey);

            if ($exist) {
                $ret['data'] = json_decode($redis->get($redisKey), true);
            }
        }

        if (empty($exist)) {

            $sphinxRes = mysql_query($ret['qs_spinx'], self::getInstance()->getDB());
            if (($err = mysql_error())) {
                ApiLogger::addLogPrintR('!!!!!!!!  ERROR !!!!!!!!');
                ApiLogger::addLogPrintR($ret['qs_spinx']);
                ApiLogger::addLogPrintR($err);
            }
            while (($obj = mysql_fetch_assoc($sphinxRes))) {
                if (empty($obj['id'])) {
                    $ret['data'][] = $obj;
                } else {
                    $ret['data'][$obj['id']] = $obj;
                }
            }
        }

        if ($cache && empty($exist) && !empty($ret['data'])) {
            $redis->set($redisKey, json_encode($ret['data']));
            $redis->expire($redisKey, (int) $cache);
        }


        return self::addTotal($ret);
    }

    public static function addTotal($ret) {
        $sphinxResTotal = mysql_query($ret['qs_total'], self::getInstance()->getDB());
        $total = mysql_fetch_row($sphinxResTotal);
        $ret['total'] = empty($total) ? 0 : reset($total);

        $inArr = array_keys($ret['data']);
        $inArr[] = -1;
        $ret['qs_mysql'] = preg_replace(array('/select\s+(.+?)\s+from(.+?)\s+((order by).+)limit.*/i'), array("SELECT $1 FROM $2 AND `id` IN (" . implode(',', $inArr) . ") $3"), $ret['qs_orig']);
        $ret['data'] = array_values($ret['data']);

        return $ret;
    }

}

class OfferPropertiesManager {

    public static function getOfferProperties($offerName) {
        $ret = array();
        $redis = RedisManager::getInstance()->getRedis();

        if ($redis->exists('offer_property')) {
            if (($jsonProp = $redis->hGet('offer_property', $offerName))) {
                $ret = json_decode($jsonProp, true);
            }
        } else {
            //Все свойства товаров
            $qs = "SELECT
                `offer_property`.`property_id` AS `id`,
                `offers`.`offer_name` AS `offer`,
                `offers`.`offer_group`,
                `offer_property`.`property_name` AS `name`,
                `offer_property`.`property_value` AS `value`,
                `offer_property`.`property_location` AS `country`,
                `offer_property`.`property_location` AS `location`
            FROM `offer_property`
                    LEFT JOIN `offers` ON `offers`.`offer_id` = `offer_property`.`property_offer`
            WHERE
                `offer_property`.`property_name` IN ('color' , 'size', 'type', 'vendor', 'name',  'description')
                AND `offer_property`.`property_active` = 1";
            $dbData = DB::query($qs);
            $allProp = array();
            foreach ($dbData as $dbItem) {
                $allProp[$dbItem['offer']][$dbItem['name']][] = $dbItem;
            }
            $ret = empty($allProp[$offerName]) ? $ret : $allProp[$offerName];
            foreach ($allProp as &$value) {
                if (is_array($value)) {
                    $value = is_array($value) ? json_encode($value) : $value;
                }
            }
            $redis->hMset('offer_property', $allProp);
            $redis->setTimeout('offer_property', 600);
        }
        return $ret;
    }

}
