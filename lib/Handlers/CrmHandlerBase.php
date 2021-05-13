<?php

require_once 'CrmHandlerInterface.php';

/**
 * @author dob
 */
abstract class CrmHandlerBase implements CrmHandlerInterface {

    /**
     * @var CommonObject
     */
    protected $mainObj;
    // Request Variables
    protected $scriptStartMicroTime = 0;
    protected $start = 0;
    protected $count = 25;
    protected $sort = 'id';
    protected $dir = 'ASC';
    protected $filters = array();
    protected $encoded = false;
////////////////////////////
    private $_featureName = 'Module access';
    private $_methodName = '';
    private $_staff;

    /**
     * @var Module
     */
//    private $_module;
    private $_bodyData = array();

    ////////////////////////////////////////

    /**
     * @param type $featureName
     */
    function __construct($featureName = null) {
        global $STAFF, $ALL_MODULES;

        $this->scriptStartMicroTime = microtime(true);

        if ($featureName !== null) {
            $this->_featureName = $featureName;
        }

        $dirName = $_SERVER['SCRIPT_NAME'];
        if (preg_match('/\/modules\/(.+?)\//', $dirName, $matches)) {
            $moduleName = $matches[1];
        } else {
            $parentDirs = explode('/', $dirName);
            while (($currDir = array_pop($parentDirs)) != 'modules' && !empty($parentDirs)) {
                $moduleName = $currDir;
            }
        }

        $this->_staff = $STAFF;
//        $this->_staff->getStaffModules();
//        $this->_module = $ALL_MODULES->getByName($moduleName);

        $this->_parseRequest();
    }

    public function doResponse() {
        header('Content-type:text/html; charset=utf-8');

        if (!empty($this->mainObj) && !in_array($this->sort, $this->mainObj->cGetDbFieldsArr())) {
            $this->sort = $this->mainObj->cGetPrimaryFieldName();
        }

        $respond = array(
            'success' => false
        );

        if ($this->_checkAccess()) {

            try {
                if (!empty($this->_bodyData)) {
//                    $origRequest = $_REQUEST;
                    foreach ($this->_bodyData as $requestItem) {
//                        $_REQUEST = array_merge($requestItem, $origRequest);
                        $_REQUEST = array_merge($requestItem, $_REQUEST);
                        $dbResp = $this->_checkAccess() ? $this->{$this->_methodName}((array) $_REQUEST) : false;
                    }
                } else {
                    $dbResp = $this->_checkAccess() ? $this->{$this->_methodName}((array) $_REQUEST) : false;
                }
            } catch (Exception $e) {
                $respond = array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }

            // Формирование ответа
            if ($dbResp === true) {
                $respond = array(
                    'success' => true,
                    'message' => 'Все четенько'
                );
            } elseif (is_array($dbResp)) {
                $respond = array_merge(array(
                    'success' => true,
                    'message' => 'Все четенько'
                        ), $dbResp);
            } else {
                $respond = array(
                    'success' => false,
                    'message' => 'Ошибка обработки данных'
                );
            }
        } else {
            $respond = array(
                'success' => false,
                'message' => 'Доступ запрещен'
            );
        }

        echo json_encode($respond, JSON_NUMERIC_CHECK);
    }

    public function read() {
        return array();
    }

    public function insert($attributes) {
        return empty($this->mainObj) ? false : $this->mainObj->insert($attributes);
    }

    public function update($attributes) {
        return empty($this->mainObj) ? false : $this->mainObj->update($attributes);
    }

    public function delete($attributes) {
        return empty($this->mainObj) ? false : $this->mainObj->delete($attributes);
    }

//    public function getModule() {
//        return $this->_module;
//    }

    /**
     * @return string
     */
//    public function getModuleName() {
//        return $this->_module ? $this->_module->name : null;
//    }

    /**
     * Convert all integer values in $data to Integer
     * @param array $data
     */
    static public function int2Int($data) {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_array($value)) {
                    $value = self::int2Int($value);
                } else {
                    if (is_numeric($value)) {
                        $originalLen = mb_strlen($value);
                        $value *= 1;
                        if (mb_strlen($value) < $originalLen) {
                            $value = str_pad($value, $originalLen, '0', STR_PAD_LEFT);
                        }
                    }
                }
            }
        }
        return $data;
    }

    private function _parseRequest() {

        if (isset($_REQUEST['method'])) {
            $this->_methodName = (string) $_REQUEST['method'];
        }
        if (isset($_REQUEST['start'])) {
            $this->start = (int) $_REQUEST['start'];
        }
        if (isset($_REQUEST['limit'])) {
            $this->count = (int) $_REQUEST['limit'];
        }
        if (isset($_REQUEST['sort'])) {
            if (($sortData = json_decode($_REQUEST['sort'], true)) && is_array($sortData)) {
                $this->sort = mysql_real_escape_string($sortData[0]['property']);
                $this->dir = mysql_real_escape_string($sortData[0]['direction']);
            } else {
                $this->sort = mysql_real_escape_string($_REQUEST['sort']);
                if (isset($_REQUEST['dir'])) {
                    $this->dir = mysql_real_escape_string($_REQUEST['dir']);
                }
            }
        }

        if (isset($_REQUEST['filter'])) {
            $this->filters = $_REQUEST['filter'];
            if (!is_array($this->filters)) {
                $this->encoded = true;
                $this->filters = json_decode($this->filters);
            }
        }

        // _bodyData
        $this->_bodyData = file_get_contents('php://input');
        $this->_bodyData = $this->_bodyData ? json_decode($this->_bodyData) : array();
        if (is_object($this->_bodyData)) {
            $bodyVars = get_object_vars($this->_bodyData);
            $this->_bodyData = array($bodyVars);
            $_REQUEST = array_merge($bodyVars, $_REQUEST);
        }
    }

    /**
     * @return bool
     */
    private function _checkAccess() {

        if (preg_match('/^(read|update|insert|delete)_?(.*)/', $this->_methodName, $matches)) {
            // /SomeModule/handlers/handler.php?method=read_product_tree => feature is 'product_tree'
            $readWrite = ($matches[1] === 'read') ? 'read' : 'write';
            $this->_featureName = empty($this->_featureName) ? $matches[2] : $this->_featureName;
            unset($matches);
        }

        if (empty($this->_featureName) && preg_match('/\/handler_(.+)\.php/', $_SERVER['SCRIPT_NAME'], $matches)) {
            // /SomeModule/handlers/handler_product_tree.php?method=read => feature is 'product_tree'
            $this->_featureName = empty($this->_featureName) ? $matches[1] : $this->_featureName;
            unset($matches);
        }

//        ApiLogger::addLog($this->_module->id);
//        ApiLogger::addLog($this->_featureName);
//        ApiLogger::addLog($this->_staff->ModuleFeature);
//        ApiLogger::addLog("module_id: {$this->_module->id},  module_name: {$this->_module->name}, featureName: {$this->_featureName}, readWrite: {$readWrite}");
//        ApiLogger::addLog(print_r($this->_staff->ModuleFeature[$this->_module->id], true));
//        ApiLogger::addLog('');
//        return false;
        return true;
//        return $this->_staff->ModuleFeature[$this->_module->id][$this->_featureName][$readWrite];
    }

//    public function __call($method, $arguments) {
//        ApiLogger::addLog($method);
//        ApiLogger::addLog(print_r($arguments, true));
//
//        $request = new CrmHandlerRequest();
//
//        print_r($request);
//        $requestBody = file_get_contents('php://input');
//        $postData = (array) json_decode($requestBody);
//        $methodName = (string) $_REQUEST['method'];
//    }
}
