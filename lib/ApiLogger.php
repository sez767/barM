<?php

define('LOGGER_FORMAT_JSON', 1);
define('LOGGER_FORMAT_VAR_EXPORT', 2);
define('LOGGER_FORMAT_VAR_DUMP', 3);
define('LOGGER_FORMAT_PRINT_R', 4);

/**
 * Description of ApiLogger
 *
 * @author dob
 */
class ApiLogger {

    static private $_lasttime = null;
    static private $_date = null;
    static private $_month = null;
    static private $_dir = null;
    static private $_fileName;
    static private $_isOk;

    /**
     * @param mixed $data
     * @param string $format - use: LOGGER_FORMAT_JSON | LOGGER_FORMAT_VAR_EXPORT | LOGGER_FORMAT_VAR_DUMP | LOGGER_FORMAT_PRINT_R
     * @param boolean $return
     * @return string
     */
    static public function addLog($data, $format = LOGGER_FORMAT_JSON, $return = false, $time = true) {
        if (!session_id() || empty($_SESSION['api_log_enable'])) {
            return;
        }
        if (self::$_date === null) {
            self::setLogFile();
        }

        if (self::$_isOk) {
            $logData = $data;
            if (is_array($data) || is_object($data)) {
                switch ($format) {
                    case LOGGER_FORMAT_JSON:
                        $logData = json_encode($data, JSON_UNESCAPED_UNICODE);
                        break;
                    case LOGGER_FORMAT_VAR_EXPORT:
                        $logData = preg_replace('/ =\> \n\s+/', ' => ', var_export($data, true));
                        break;
                    case LOGGER_FORMAT_VAR_DUMP:
                        ob_start();
                        var_dump($data);
                        $logData = preg_replace('/"\]=\>\n\s+/', '"] => ', ob_get_clean());
                        break;
                    case LOGGER_FORMAT_PRINT_R:
                        $logData = print_r($data, true);
                        break;
                }
            }
            if ($return) {
                return $logData;
            }
            if ($time) {
                $lastTime = self::$_lasttime;
                self::$_lasttime = microtime(true);
                $diffInt = $lastTime ? self::$_lasttime - $lastTime : 0;
                $diffStr = $diffInt ? ' (' . ($diffInt > 0.5 ? '-->' . sprintf('%f', $diffInt) . '<--' : sprintf('%f', $diffInt)) . ')' : '';
            }

            error_log(($time ? self::$_lasttime . $diffStr . ' => ' : '') . $logData);
        }
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function echoLog($data, $time = true) {
        echo self::addLog($data, LOGGER_FORMAT_JSON, true, $time) . PHP_EOL;
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function echoLogJson($data, $time = true) {
        echo self::addLog($data, LOGGER_FORMAT_JSON, true, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function echoLogVarExport($data, $time = true) {
        echo self::addLog($data, LOGGER_FORMAT_VAR_EXPORT, true, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function echoLogVarDump($data, $time = true) {
        echo self::addLog($data, LOGGER_FORMAT_VAR_DUMP, true, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function echoLogPrintR($data, $time = true) {
        echo self::addLog($data, LOGGER_FORMAT_PRINT_R, true, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function addLogJson($data, $time = true) {
        self::addLog($data, LOGGER_FORMAT_JSON, false, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function addLogVarExport($data, $time = true) {
        self::addLog($data, LOGGER_FORMAT_VAR_EXPORT, false, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function addLogVarDump($data, $time = true) {
        self::addLog($data, LOGGER_FORMAT_VAR_DUMP, false, $time);
    }

    /**
     * @param mixed $data
     * @param boolean $time
     */
    static public function addLogPrintR($data, $time = true) {
        self::addLog($data, LOGGER_FORMAT_PRINT_R, false, $time);
    }

    /**
     * @param int $traceMode - FALSE | DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS
     * @param boolean $time
     */
    static public function echoLogBackrTaceaVarExport($traceMode = DEBUG_BACKTRACE_IGNORE_ARGS, $time = true) {
        echo self::addLog('', LOGGER_FORMAT_VAR_EXPORT, true, $time);
        echo self::addLog('', LOGGER_FORMAT_VAR_EXPORT, true, $time);
        echo self::addLog('START ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', LOGGER_FORMAT_VAR_EXPORT, true, $time);
        $e = new \Exception;
        echo self::addLog($e->getTraceAsString(), LOGGER_FORMAT_VAR_EXPORT, true, $time);
        if ($traceMode !== false) {
            echo self::addLog('DETAIL ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', LOGGER_FORMAT_VAR_EXPORT, true, $time);
            echo self::addLog(debug_backtrace($traceMode), LOGGER_FORMAT_VAR_EXPORT, true, $time);
        }
        echo self::addLog('END ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', LOGGER_FORMAT_VAR_EXPORT, true, $time);
        echo self::addLog('', LOGGER_FORMAT_VAR_EXPORT, true, $time);
    }

    /**
     * @param int $traceMode - DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS
     * @param boolean $time
     */
    static public function addLogBackrTaceaVarExport($traceMode = DEBUG_BACKTRACE_IGNORE_ARGS, $time = true) {
        self::addLog('', LOGGER_FORMAT_VAR_EXPORT, false, $time);
        self::addLog('', LOGGER_FORMAT_VAR_EXPORT, false, $time);
        self::addLog('START ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', LOGGER_FORMAT_VAR_EXPORT, false, $time);
        $e = new \Exception;
        self::addLog($e->getTraceAsString(), LOGGER_FORMAT_VAR_EXPORT, false, $time);
        if ($traceMode !== false) {
            self::addLog('DETAIL ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', LOGGER_FORMAT_VAR_EXPORT, false, $time);
            self::addLog(debug_backtrace($traceMode), LOGGER_FORMAT_VAR_EXPORT, false, $time);
        }
        self::addLog('END ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~', LOGGER_FORMAT_VAR_EXPORT, false, $time);
        self::addLog('', LOGGER_FORMAT_VAR_EXPORT, false, $time);
    }

    /**
     * @param type $dirName
     * @param type $fileName
     */
    static public function setLogFile($dirName = null, $fileName = null) {

        if (!session_id() || empty($_SESSION['api_log_enable'])) {
            return;
        }
        if (empty($dirName)) {
            self::$_dir = dirname(__FILE__) . '/../log/';
        } else {
            self::$_dir = $dirName;
        }

        if (!file_exists(self::$_dir)) {
            @mkdir(self::$_dir);
        }
        self::_setFileName($fileName);
        self::_checkDate();
    }

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

    static private function _checkDate() {
        self::$_isOk = false;
        self::$_month = date('Y-m');
        self::$_date = date('Y-m-d');

        $path_month = self::$_dir . self::$_month . '/';
        $path = $path_month . self::$_date;
        if ((file_exists($path_month) || @mkdir($path_month)) && (file_exists($path) || @mkdir($path))) {
            ini_set('log_errors', 1);
            ini_set('error_log', $path . '/' . self::$_fileName);
            self::$_isOk = true;
        }
    }

    static private function _setFileName($fileName = null) {
        self::$_fileName = (empty($fileName) ? self::getCalledFileName() : $fileName) . '-log.txt';
    }

}
