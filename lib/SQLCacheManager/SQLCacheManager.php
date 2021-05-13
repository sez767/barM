<?php

/**
 * Description of SQLCacheManager
 *
 * @author dob
 */
interface SQLCacheDriverInterface {

    public function setCachePrefix($prefix);

    public function getCachePrefix();

    /**
     * @param string $sql
     * @return string
     */
    public function getCacheKey($sql = '');

    public function getSQLData($sql, $cacheTime = null);
}

abstract class SQLCacheDriverBase implements SQLCacheDriverInterface {

    public function __construct() {
        $this->setCachePrefix($this->getCalledFileName());
    }

    private $_cachePrefix = null;

    public function setCachePrefix($prefix) {
        $this->_cachePrefix = $prefix;
    }

    public function getCachePrefix() {
        return $this->_cachePrefix;
    }

    public function getCacheKey($sql = '') {
        $ret = $this->getCachePrefix();
        return empty($ret) ? md5($sql) : $ret . '_' . md5($sql);
    }

    protected function decode($data) {
        return json_decode($data, true);
    }

    /**
     *
     * @return String
     */
    static public function getCalledFileName() {
        $baseName = pathinfo(__FILE__, PATHINFO_BASENAME);

        $backTraceArr = array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        $ret = '';
        while (($trItem = array_shift($backTraceArr)) && empty($ret)) {
            if (empty(self::$calledFile)) {
                $info = pathinfo($trItem['file']);
                if ($info['basename'] !== $baseName) {
                    $ret = $info['filename'];
                }
            }
        }

        if (empty($ret)) {
            $ret = pathinfo(__FILE__, PATHINFO_FILENAME);
        }
        return $ret;
    }

}

class SQLCacheFileDriver extends SQLCacheDriverBase {

    public function getSQLData($sql, $cacheTime = null) {
        $key = $this->getCacheKey($sql);
        $this->decode('');
        return true;
    }

}

class SQLCacheManager {

    /**
     * @var SQLCacheDriverInterface
     */
    private $_driver;
    private static $instance = array();

    /**
     *
     * @param String $driver
     * @return SQLCacheDriverBase
     */
    public static function getInstance($driver = 'file') {
        $driverKey = strtolower($driver);
        if (!isset(self::$instance[strtolower($driver)])) {
            self::$instance[$driverKey] = new self($driver);
        }
        return self::$instance[$driverKey];
    }

    private function __clone() {

    }

    public function __construct($driver) {
        echo '-construct-' . PHP_EOL;
        $className = 'SQLCache' . ucfirst($driver) . 'Driver';
        $this->_driver = new $className;
    }

    /**
     * @return SQLCacheDriverInterface
     */
    public function getManager() {
        return $this->_driver;
    }

}
