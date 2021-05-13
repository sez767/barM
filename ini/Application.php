<?php
ini_set("display_errors", 1);
error_reporting(E_ERROR);

if (!session_id()) {
    session_start();
}

define('LOCAL_APPPATH', substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), '/')));

if (!defined('PATH_SEPARATOR')) {
    if (strpos($_ENV['OS'], 'Win') !== false) {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}

set_include_path(get_include_path() . PATH_SEPARATOR . LOCAL_APPPATH . '/lib' . PATH_SEPARATOR . LOCAL_APPPATH . '/ini' . PATH_SEPARATOR . LOCAL_APPPATH . '/lib/Classes');

if (!defined('SITE_NAME')) {
    if (array_key_exists('HTTP_SITE_NAME', $_SERVER)) {
        define('SITE_NAME', $_SERVER['HTTP_SITE_NAME']);
    } elseif (array_key_exists('HTTP_HOST', $_SERVER)) {
        $hostArr = parse_url($_SERVER['HTTP_HOST']);
        define('SITE_NAME', preg_replace('/^net[\.|-](.*)/', '$1', $hostArr['path']));
    } else {
        define('SITE_NAME', 'BariBarda');
    }
}

// Define constant APPLICATION_ENV
if (!defined('APPLICATION_ENV') && array_key_exists('HTTP_APPLICATION_ENV', $_SERVER)) {
    define('APPLICATION_ENV', $_SERVER['HTTP_APPLICATION_ENV']);
} elseif (!defined('APPLICATION_ENV') && array_key_exists('APPLICATION_ENV', $_SERVER)) {
    define('APPLICATION_ENV', $_SERVER['APPLICATION_ENV']);
} elseif (!defined('APPLICATION_ENV') && isset($_SERVER['argv']) && is_array($_SERVER['argv']) && ($index = array_search('HTTP_APPLICATION_ENV', $_SERVER['argv']))) {
    define('APPLICATION_ENV', mb_strtolower($_SERVER['argv'][$index + 1]));
} elseif (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', 'production');
}

require_once 'Zend/Config/Ini.php';
require_once 'Zend/Debug.php';
require_once 'ApiLogger.php';
require_once dirname(__FILE__) . '/../lib/db/meekrodb.2.3.class.php';
require_once 'configs.php';
require_once 'CommonManagers.php';
require_once 'ObjectFactory.php';
require_once dirname(__FILE__) . '/../lib/Handlers/CrmHandlerBase.php';
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once 'util.php';


DB::$host = Application::getAppConfig()->db->hostname;
DB::$dbName = Application::getAppConfig()->db->database;
DB::$user = Application::getAppConfig()->db->username;
DB::$password = Application::getAppConfig()->db->password;
DB::$port = Application::getAppConfig()->db->port ? Application::getAppConfig()->db->port : 3306;
DB::$encoding = Application::getAppConfig()->db->encoding;

class Application {

    private static $_config;
    private static $_settings = array();

    public static function set($key, $value) {
        self::$_settings[$key] = $value;
    }

    public static function get($key, $defaultValue = null) {
        return array_key_exists($key, self::$_settings) ? self::$_settings[$key] : $defaultValue;
    }

    /**
     * @return string
     */
    public static function env() {
        return APPLICATION_ENV;
    }

    /**
     * @return Zend_Config
     */
    public static function getAppConfig() {
        if (self::$_config === null) {
            try {
                self::$_config = new Zend_Config_Ini(LOCAL_APPPATH . '/config/' . SITE_NAME . '.ini', self::env(), array('allowModifications' => true));
            } catch (Exception $e) {
                self::$_config = new Zend_Config_Ini(LOCAL_APPPATH . '/config/config.ini', self::env(), array('allowModifications' => true));
            }
        }
        return self::$_config;
    }

    public static function setAppConfig($item, $value, $section = false) {
        $config = self::getAppConfig();
        if ($section) {
            $sectionArr = array_diff(explode('->', $section), array(''));
            foreach ($sectionArr as $sectionItem) {
                if (isset($config->$sectionItem)) {
                    $config = $config->$sectionItem;
                } else {
                    $config->__set($sectionItem, array());
                    $config = $config->$sectionItem;
                }
            }
        }
        $config->__set($item, $value);
    }

    static private $_resourcesArr = array();
    static private $cssArr = null;
    static private $cssRulesLimit = 3500;

    static public function addResource($file) {

        if (!file_exists($file) && file_exists(LOCAL_APPPATH . '/' . $file)) {
            $file = LOCAL_APPPATH . '/' . $file;
        }

        if (file_exists($file)) {
            $pathParts = pathinfo($file);
            if ($pathParts['extension'] === 'js') {
                self::$_resourcesArr['js'][$file] = $pathParts;
            } else if ($pathParts['extension'] === 'css') {
                self::$_resourcesArr['css'][$file] = $pathParts;
            }
        }
    }

    static public function resourcesRenderer() {
        // Compile resources
        $resourcesHash = false;

        $ret = false;

        if (!empty(self::$_resourcesArr)) {

            // Get resources
            $cssLink = self::_getResourceUrl('css');
            $jsLink = self::_getResourceUrl('js');

            // Only in debug mode
            if (self::getAppConfig()->debug->mode) {
                // Check is files present
                $mediaDir = LOCAL_APPPATH . self::getAppConfig()->media->dir;

                if (!file_exists($mediaDir . str_replace('.css', '0.css', $cssLink)) || !file_exists($mediaDir . $jsLink)) {
                    $recompile = true;
                } else {
                    $recompile = false;
                }

                // Compile resources
                $resourcesHash = self::_compileResources($cssLink, $jsLink, $recompile);
            }

            // Set css and js
            $cssStr = '';
            $cfgDomainMedia = self::getAppConfig()->media->dir;
            if (!empty(self::$cssArr)) {
                foreach (self::$cssArr as $cssIndex => $cssData) {
                    $cssStr .= '<link rel="stylesheet" href="' . $cfgDomainMedia . str_replace('.css', $cssIndex . '.css', $cssLink) . ($resourcesHash ? "?v=$resourcesHash" : '') . '" type="text/css"/>' . "\n";
                }
                $ret['css'] = $cssStr;
            }

            if (!empty(self::$_resourcesArr['js'])) {
                $ret['js'] = '<script language="javascript" src="' . $cfgDomainMedia . $jsLink . ($resourcesHash ? "?v=$resourcesHash" : '') . '" type="text/javascript"></script>';
            }
        }

        return $ret;
    }

    static private function _getResourceUrl($type, $name = 'complex') {
        $ret = false;
        // Check for type
        switch ($type) {
            // CSS
            case "css":
                $ret = "css/{$name}_" . (defined("DOMAIN_SPECIFIC_RESOURCES") ? self::_crc32fix($_SERVER['HTTP_HOST']) : '') . '.css';
                break;
            // JS
            case "js":
                $ret = "js/{$name}" . (defined("DOMAIN_SPECIFIC_RESOURCES") ? self::_crc32fix($_SERVER['HTTP_HOST']) : '') . '.js';
                break;
        }
        return $ret;
    }

    static private function _compileResources($complexCss, $complexJS, $recompile = false) {

        // Css and JS files list
        $resList = array();

        // Resources hash
        $resHash = '';

        // Process core css resource
        if (!empty(self::$_resourcesArr['css'])) {

            // Иициализируем первый элемент
            $cssIndex = 0;
            self::$cssArr = array($cssIndex => array('count' => 0, 'data' => ''));

            foreach (self::$_resourcesArr['css'] as $file => $cssItem) {
//                print_r($cssItem);
                // Add to hash
                $resHash .= $file . filemtime($file);

                // Проверяем на лимит правил
                $data = file_get_contents($file);
                $dataRulesCount = substr_count($data, '{');
                if ((self::$cssArr[$cssIndex]['count'] + $dataRulesCount) > self::$cssRulesLimit) {
                    $cssIndex++;
                    self::$cssArr[$cssIndex]['count'] = $dataRulesCount;
                    self::$cssArr[$cssIndex]['data'] = $data . PHP_EOL . PHP_EOL;
                } else {
                    self::$cssArr[$cssIndex]['count'] += $dataRulesCount;
                    self::$cssArr[$cssIndex]['data'] .= $data . PHP_EOL . PHP_EOL;
                }
            }
        }

        // Collect info about js files
        if (!empty(self::$_resourcesArr['js'])) {
            foreach (self::$_resourcesArr['js'] as $file => $jsItem) {
                // Store path
                $resList['js'][$file]['time'] = filemtime($file);

                // Add to hash
                $resHash .= $file . filemtime($file);
            }
        }

        // Get statistics data
        // Load cache data
        $oldHash = @file_get_contents(LOCAL_APPPATH . self::getAppConfig()->media->dir . 'cache.txt');
//        echo "$oldHash|".md5($resHash);
        // No files changes flag
        if (!$recompile && $oldHash && md5($resHash) == $oldHash) {
            return $oldHash;
        }

        // Store statistics data
        file_put_contents(LOCAL_APPPATH . self::getAppConfig()->media->dir . 'cache.txt', md5($resHash));

        // Check for css collection
        if (isset(self::$cssArr)) {

            // Create single css file
            foreach (self::$cssArr as $cssIndex => &$cssData) {
                if (self::getAppConfig()->css->compress) {
                    require_once LOCAL_APPPATH . '/lib/CSSMin.php';
                    $cssData['data'] = CssMin::minify($cssData['data']);
                }

                file_put_contents(LOCAL_APPPATH . self::getAppConfig()->media->dir . str_replace('.css', $cssIndex . '.css', $complexCss), $cssData['data']);
            }
        }

        // Check for js collection
        if (isset($resList['js'])) {

            // Reset file
            $JSData = '';
            // Create single js file
            foreach ($resList['js'] as $file => $tmp) {

                $data = file_get_contents($file);
                if (self::getAppConfig()->js->compress) {
                    require_once LOCAL_APPPATH . '/lib/JSMin.php';
                    $data = JSMin::minify($data);
                }

                // Add to common file
                $JSData .= $data . PHP_EOL . PHP_EOL;
            }



            file_put_contents(LOCAL_APPPATH . self::getAppConfig()->media->dir . $complexJS, $JSData);
        }
        return md5($resHash);
    }

    static private function _crc32fix($str) {
        return crc32($str) & 0x0FFFFFF;
    }

}
