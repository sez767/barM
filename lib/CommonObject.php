<?php

class CommonObject {

    protected $hiddenLoggedFields = array();
    protected $jsonFields = array();
    protected $_autoWhereFields = array();
    //////////////////////////////
    private $_className = null;
    private $_tableName = null;
    private $_dbId = null;
    private $_loadedState = null;
    private $_currentState = null;
    private $_otherState = array();
    private $_loggingState = true;
    private $_historyTable = 'ActionHistoryNew';
    //////////////////////////////
    static private $_commonLoggedFields = array();

    function __construct($id = null, $withLoad = false) {
        $this->_className = get_called_class();

//        print "|$this->_className|";

        if (!$this->cGetTableName() && ($pos = strripos($this->_className, 'Obj'))) {
            $this->cSetTableName(substr($this->_className, 0, $pos));
        }

        if (!empty($id)) {
            $this->cSetId($id, $withLoad);
        }
    }

    function __clone() {
        $newValues = $this->cGetValues();

        $this->setId(null);

        unset($newValues[$this->cGetPrimaryFieldName()]);
        if (in_array('created_by', $this->cGetDbFieldsArr())) {
            unset($newValues['created_by']);
        }
        if (in_array('created_at', $this->cGetDbFieldsArr())) {
            unset($newValues['created_at']);
        }
        if (in_array('updated_by', $this->cGetDbFieldsArr())) {
            unset($newValues['updated_by']);
        }
        if (in_array('updated_at', $this->cGetDbFieldsArr())) {
            unset($newValues['updated_at']);
        }

        $this->cSetValues($newValues);
    }

    /**
     * @param array $newValues
     * @return CommonObject
     */
    public function cClone($newValues = null, $withSave = false) {
        $newObj = clone $this;
        $newValues = empty($newValues) || !is_array($newValues) ? $newObj->cGetValues() : array_merge($newObj->cGetValues(), $newValues);

        $withSave ? $newObj->cSave($newValues) : $newObj->cSetValues($newValues);
        return $newObj;
    }

    /**
     *
     * @param type $attributes
     * @return type
     */
    public function insert($attributes) {
        if ($this->cSave($attributes)) {
            $ret = array(
                'data' => $this->cGetValues()
            );
        }
        return empty($ret) ? false : $ret;
    }

    /**
     *
     * @param type $attributes
     * @return type
     */
    public function update($attributes) {
        unset($attributes['created_at']);
        $this->cSetId($attributes[$this->cGetPrimaryFieldName()]);
        return $this->cSave($attributes);
    }

    /**
     *
     * @param type $attributes
     * @return type
     */
    public function delete($attributes) {
        $this->cSetId($attributes[$this->cGetPrimaryFieldName()]);
        return $this->cDelete();
    }

    /**
     * Set ID without load from DB
     * @param int $id
     */
    public function setId($id = null) {
        if ($this->cGetId() != $id) {
            $this->_dbId =  $id;
            $this->_loadedState = null;
            $this->_currentState = null;
        }
    }

    /**
     *
     * @param int $id
     * @param boolean $withLoad
     */
    public function cSetId($id, $withLoad = false) {
        if ($this->cGetId() != $id || $withLoad) {
            $this->_dbId =  $id;
            $this->_loadedState = null;

            $this->_loadFromDb();
            if ($withLoad) {
                $this->cLoadClassVars();
            }
        }
    }

    public function cGetId() {
        return $this->_dbId;
    }

    /**
     *
     * @param type $fieldName
     * @return type
     */
    public function cGetLoadedValues($fieldName = null) {
        if ($fieldName === null) {
            return $this->_loadedState === null ? array() : $this->_loadedState;
        } else {
            return array_key_exists($fieldName, $this->_loadedState) ? $this->_loadedState[$fieldName] : null;
        }
    }

    public function translate($name = null, $parent = null) {
        return translate($name, $parent);
    }

    /**
     * Sets the given field to the given value, marks the instance as dirty
     * @param String/Array $fieldName The fields data to set, or an object containing key/value pairs
     * @param mixed $newValue The value to set
     * @return array changedValues
     */
    public function cSetValues($fieldName, $newValue = null) {
        $retChangedArr = array();
        $data = is_null($newValue) && is_array($fieldName) ? $fieldName : array($fieldName => $newValue);

        $fieldsArr = $this->cGetDbFieldsArr();

        $priFieldName = $this->cGetPrimaryFieldName();
        if (array_key_exists($priFieldName, $data)) {
            // устанавливаем _dbId без релоада из базы;
            $this->setId($data[$priFieldName]);
        }

        foreach ($data as $fieldName => $value) {
            if (in_array($fieldName, $fieldsArr)) {

                if (!array_key_exists($fieldName, $this->_currentState) || $this->_currentState[$fieldName] !== $value) {
                    if (empty($value) && in_array($fieldName, $this->_autoWhereFields)) {
                        continue;
                    }
                    $retChangedArr[$fieldName] = $value;
                    $this->_currentState[$fieldName] = $value;
                }

//                if ($fieldName === $priFieldName) {
//                    // устанавливаем _dbId без релоада из базы;
//                    $this->setId($value);
//                }
            }
        }

        return $retChangedArr;
    }

    /**
     * Sets the given field to the given value, marks the instance as dirty
     * @param String/Array $fieldName The fields data to set, or an object containing key/value pairs
     * @param mixed $newValue The value to set
     * @return array changedValues
     */
    public function setValues($fieldName, $newValue = null) {
        $data = is_null($newValue) && is_array($fieldName) ? $fieldName : array($fieldName => $newValue);

        $retChangedArr = $this->cSetValues($fieldName, $newValue);

        $otherFieldsData = array_diff_key($data, $this->cGetValues());
        foreach ($otherFieldsData as $fieldName => $value) {
            if (!array_key_exists($fieldName, $this->_otherState) || $this->_otherState[$fieldName] !== $value) {
                $retChangedArr[$fieldName] = $value;
                $this->_otherState[$fieldName] = $value;
            }
        }

        return $retChangedArr;
    }

    /**
     * @param Array $fieldName The fields data to get, or an object containing key/value pairs
     * @return type
     */
    public function cGetValues($fieldName = null) {
        $ret = null;
        if ($fieldName === null) {
            $ret = $this->_currentState === null ? array() : $this->_currentState;
        } elseif ($fieldName === 'ALL') {
            $ret = $this->cGetValues($this->cGetDbFieldsArr());
        } elseif (is_array($fieldName)) {
            foreach ($fieldName as $fieldItem) {
                $ret[$fieldItem] = $this->cGetValues($fieldItem);
            }
        } elseif (array_key_exists($fieldName, $this->_currentState)) {
            $ret = $this->_currentState[$fieldName];
        }
        return $ret;
    }

    /**
     * @param Array $fieldName The fields data to get, or an object containing key/value pairs
     * @return type
     */
    public function getValues($fieldName = null) {
        if ($fieldName === null) {
            return array_merge($this->_otherState, $this->_currentState === null ? array() : $this->_currentState);
        } elseif (in_array($fieldName, $this->cGetDbFieldsArr())) {
            return array_key_exists($fieldName, $this->_currentState) ? $this->_currentState[$fieldName] : null;
        } else {
            return array_key_exists($fieldName, $this->_otherState) ? $this->_otherState[$fieldName] : null;
        }
    }

    /**
     * Sets the given field to the given value, marks the instance as dirty
     * @param String/Array $fieldName The fields data to set, or an object containing key/value pairs
     * @param mixed $newValue The value to set
     * @return array changedValues
     */
    public function cSetAutoFieldsValues($fieldName, $newValue = null) {
        $retChangedArr = array();
        $data = is_null($newValue) && is_array($fieldName) ? $fieldName : array($fieldName => $newValue);
        foreach ($data as $fieldName => $value) {
            if (!in_array($fieldName, $this->_autoWhereFields)) {
                $this->_autoWhereFields[] = $fieldName;
            }
            if (!empty($value)) {
                $this->cSetValues($fieldName, strtolower($value));
            }
        }

        return $retChangedArr;
    }

    /**
     * Sets the given field to the given value, if last one is specified <br />
     * and save data to DB this logging changed fields
     * @param Array $newValues The fields data to set, or an object containing key/value pairs
     * @return boolean
     */
    public function cSave($newValues = null) {
        $ret = false;

        $this->cSetValues($newValues);

        $fieldsArr = $this->cGetDbFieldsArr();

        $loadedVal = $this->cGetLoadedValues();
        if (empty($loadedVal)) {

            if (in_array('created_at', $fieldsArr)) {
                $this->cSetValues('created_at', DB::sqlEval('NOW()'));
            }
            if (in_array('created_by', $fieldsArr)) {
                $this->cSetValues('created_by', self::getAdminId());
            }

            $insArr = $this->cGetChanges();
            if (($ret = DB::insert($this->cGetTableName(), $insArr))) {
                $this->cSetId(DB::insertId());
                $this->_loadedState = null; // Тут именно так надо - дабы залогировать изменения
                $this->cSaveChangeLog('insert', $insArr);
                $this->_loadFromDb();
            }
        } else {
            $updateArr = $this->cGetChanges();
            if (!array_key_exists('deleted_at', $updateArr) && in_array('updated_at', $fieldsArr)) {
                $updateArr['updated_at'] = DB::sqlEval('NOW()');
            }
            if (!array_key_exists('deleted_by', $updateArr) && in_array('updated_by', $fieldsArr)) {
                $updateArr['updated_by'] = self::getAdminId();
            }

            if (($ret = DB::update($this->cGetTableName(), $updateArr ? $updateArr : array($this->cGetPrimaryFieldName() => $this->cGetId()), $this->prepWhereStr()))) {
                $this->cSaveChangeLog('update', $updateArr);
            }
        }
        return $ret;
    }

    /**
     * @param type $addData
     * @param type $delIdArr
     * @return type
     */
    public function cMarkDeleted() {
        $fieldsArr = $this->cGetDbFieldsArr();

        if (in_array('deleted_at', $fieldsArr)) {
            $this->cSetValues('deleted_at', DB::sqlEval('NOW()'));

            if (in_array('deleted_by', $fieldsArr)) {
                $this->cSetValues('deleted_by', self::getAdminId());
            }
            return true;
        }
        return false;
    }

    /**
     * @param type $addData
     * @param type $delIdArr
     * @return type
     */
    public function cDelete() {
        if ($this->cMarkDeleted()) {
            return $this->cSave();
        } else {
            if ($this->cGetId()) {
                if (DB::delete($this->cGetTableName(), $this->prepWhereStr())) {
                    $this->cSaveChangeLog('delete', $this->cGetLoadedValues());
                    return true;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * @param array $customWhere
     * @param string $tableAlias
     * @return string
     */
    public function prepWhereStr($customWhere = array(), $tableAlias = null) {

        if (empty($customWhere)) {
            if ($this->cGetId()) {
                $whereArr = array("`{$this->cGetPrimaryFieldName()}` = '{$this->cGetId()}'");
            } else {
                $whereArr = array('1 = 1');
            }
        } else {
            $whereArr = is_array($customWhere) ? $customWhere : array($customWhere);
        }
        $fieldsArr = $this->cGetDbFieldsArr();
        foreach ($this->_autoWhereFields as $autoFieldName) {
            if (in_array($autoFieldName, $fieldsArr)) {
                $whereArr[] = (empty($tableAlias) ? '' : "`$tableAlias`.") . "`$autoFieldName` = '{$this->cGetValues($autoFieldName)}'";
            }
        }
        if (in_array('deleted_at', $fieldsArr)) {
            $whereArr[] = (empty($tableAlias) ? '' : "`$tableAlias`.") . '`deleted_at` IS NULL';
        }
        return implode(' AND ', $whereArr);
    }

    /**
     * Gets a data of only the fields that have been modified since this Object was loaded or last time saved
     * @param Array $newValues The fields data to set, or an object containing key/value pairs
     * @return Array containing key/value pairs
     */
    public function cGetChanges($newValues = array()) {
        $ret = array();

        if (!empty($newValues)) {
            $this->cSetValues($newValues);
        }

        $origValues = $this->cGetLoadedValues();
        $currValues = $this->cGetValues();

        $fieldsArr = $this->cGetDbFieldsArr();
        foreach ($fieldsArr as $fieldName) {
            if (
                    (!empty($origValues[$fieldName]) || !empty($currValues[$fieldName])) &&
                    (
                    (empty($origValues[$fieldName]) && !empty($currValues[$fieldName])) ||
                    (!empty($origValues[$fieldName]) && empty($currValues[$fieldName])) ||
                    $origValues[$fieldName] != $currValues[$fieldName]
                    )
            ) {
                $ret[$fieldName] = $currValues[$fieldName];
            }
        }
        return $ret;
    }

    /**
     * @param type $tableName
     */
    public function cSetTableName($tableName) {
        $this->_tableName = $tableName;
    }

    /**
     * @return string
     */
    public function cGetTableName() {
        return $this->_tableName;
    }

    /**
     * set the primary key FieldName
     * @param string $fieldName
     * @return string
     */
    public function cSetPrimaryFieldName($fieldName) {
        $this->cGetDbFieldsArr();
        self::$_commonLoggedFields[$this->_className]['pri'] = $fieldName;
    }

    /**
     * return the primary key FieldName
     * @return type
     */
    public function cGetPrimaryFieldName() {
        return $this->cGetDbFieldsArr(true);
    }

    /**
     * @param array $primary
     * @return type
     */
    public function cGetDbFieldsArr($primary = null) {
        if (empty(self::$_commonLoggedFields[$this->_className]['pri'])) {
            $fieldsData = DB::query('SHOW FIELDS FROM %b', $this->cGetTableName());
//            echo "\n******* 'SHOW FIELDS FROM `{$this->cGetTableName()}`' \n";

            $commonArr = array();
            $priAutoIncrement = null;

            foreach ($fieldsData as $fieldItem) {
                $commonArr['all'][] = $fieldItem['Field'];

                if ($fieldItem['Key'] == 'PRI') {
                    $priAutoIncrement = (empty($priAutoIncrement) && $fieldItem['Extra'] == 'auto_increment') ? $fieldItem['Field'] : $priAutoIncrement;
                    $commonArr['pri'] = $priAutoIncrement ? $priAutoIncrement : $fieldItem['Field'];
                }
            }
            self::$_commonLoggedFields[$this->_className] = $commonArr;
        }

        return $primary ? self::$_commonLoggedFields[$this->_className]['pri'] : self::$_commonLoggedFields[$this->_className]['all'];
    }

    /**
     * @return array
     */
    public function cGetFullDbFieldsArr() {
        return DB::queryAssArray('Field', 'SHOW FIELDS FROM %b', $this->cGetTableName());
    }

    /**
     * @param type $withParent
     * @return type
     */
    protected function cGetClassVars($withParent = false) {

        if (empty(self::$_commonLoggedFields[$this->_className]['vars'])) {
            $this->_className = get_called_class();
            $classVars[$this->_className] = array_keys(get_class_vars($this->_className));

            $parent = get_parent_class($this->_className);
            $prevParentVars = null;
            while ($parent !== false) {
                //echo "parent: $parent \n";
                $classVars[$parent] = array_keys(get_class_vars($parent));
                $classVars[$this->_className] = array_diff($classVars[$this->_className], $classVars[$parent]);
                if (isset($prevParentVars)) {
                    $prevParentVars = array_diff($prevParentVars, $classVars[$parent]);
                }

                $prevParentVars = &$classVars[$parent];
                $parent = get_parent_class($parent);
            }
            self::$_commonLoggedFields[$this->_className]['vars'] = $classVars;
        }
        return $withParent ? self::$_commonLoggedFields[$this->_className]['vars'] : self::$_commonLoggedFields[$this->_className]['vars'][$this->_className];
    }

    /**
     * Заполняет свойства объекта из _currentState
     */
    protected function cLoadClassVars() {
        $ownFieldsArr = $this->cGetClassVars();
        $dbData = $this->cGetValues();
        foreach ($ownFieldsArr as $fieldName) {
            if (array_key_exists($fieldName, $dbData)) {
                $this->$fieldName = in_array($fieldName, $this->jsonFields) ? json_decode($dbData[$fieldName], true) : $dbData[$fieldName];
            }
        }
    }

    /**
     * Заполняет _currentState из свойств объекта
     */
    protected function cWriteClassVars() {
        $ownFieldsArr = $this->cGetClassVars();
        foreach ($ownFieldsArr as $fieldName) {
            $this->cSetValues($fieldName, in_array($fieldName, $this->jsonFields) ? json_encode($this->$fieldName) : $this->$fieldName);
        }
    }

    /**
     * Enable or Disable logging
     * @param boolean $state
     */
    public function cSetLoggingState($state) {
        $this->_loggingState = $state;
    }

    /**
     * Set the name of logging table
     * @param string $tableName
     */
    public function cSetHistoreTable($tableName) {
        $this->_historyTable = $tableName;
    }

    /**
     *
     * @param type $changesArr
     * @return type
     */
    public function cSaveChangeLog($actionType, $changesArr = null) {

        if ($this->_loggingState) {
            if ($changesArr === null) {
                $changesArr = $this->cGetChanges();
            }
            foreach ($changesArr as $fieldName => $fieldValue) {
                if (in_array($fieldName, array('created_at', 'deleted_at'))) {
                    $fieldValue = date('Y-m-d H:i:s', time());
                }
                $this->logSave($actionType, $fieldName, in_array($fieldName, $this->hiddenLoggedFields) ? '_secret_' : $this->cGetLoadedValues($fieldName), in_array($fieldName, $this->hiddenLoggedFields) ? '_secret_' : $fieldValue);
            }
        }

        return $this->_loggingState;
    }

//    public function cCheckExists($id) {
//        $ret = false;
//        if (is_int($id) && $id != $this->_getDbId()) {
//            $obj = /* @var $obj CommonObject */ new $this->_className($id);
//            $ret = $obj->getOrigDbData();
//        }
//        return $ret;
//    }
//
//    ?????
//    public function cCopyFields($someData) {
//        if (is_object($someData)) {
//            $someData = (array) clone $someData;
//        }
//
//        if (!empty($someData)) {
//            foreach ($someData as $key => $value) {
//                $this->$key = $value;
//            }
//        }
//    }
    /////////////////////////////////////////////////////////////
    //         PRIVATE SECTION
    /////////////////////////////////////////////////////////////

    private function _loadFromDb() {
        $ret = null;
        if (($id = $this->cGetId())) {
            $ret = DB::queryFirstRow("SELECT * FROM `{$this->cGetTableName()}` WHERE {$this->prepWhereStr()}", $id);
            if ($this->_loadedState === null) {
                $this->_loadedState = $ret;
                $this->cSetValues($ret);
            }
        }
        return $ret;
    }

    static public function getAdminId() {
        global $STAFF;

        $ret = false;

        // Определяем под кого кидать логи
        if (defined('SITE_API_REQUEST')) {
            // Усли это запрос с сайта, то тока этот пользователь
            $ret = Staff::$_STAFF_API_ADMIN_ID;
        } elseif (!empty($_SESSION['Logged_StaffId'])) {
            $ret = $_SESSION['Logged_StaffId'];
        } elseif (!empty($STAFF) && !empty($STAFF->Id)) {
            $ret = $STAFF->Id;
        } else {
            $ret = Staff::$_STAFF_SYSTEM_ID;
        }

        return $ret;
    }

    /**
     * @param type $actionType - 'update','insert','delete','login','logout','user','view'
     * @param type $property - string
     * @param type $was - old value
     * @param type $set - new value
     * @param type $comment
     * @return boolean
     */
    public function logSave($actionType, $property = null, $was = null, $set = null, $comment = null) {

        $adminId = self::getAdminId();

        if ($actionType == 'update' && (gettype($was) == gettype($set) || is_numeric($was) && is_numeric($set)) && $was == $set) {
            return;
        }
        $insertArr = array(
            'object_name' => ($this->_className === 'CommonObject' ? '' : $this->_className),
            'object_id' => $this->cGetId(),
            'type' => $actionType,
            'worker' => $adminId
        );

        if (!empty($property)) {
            $insertArr['property'] = (string) $property;
        }
        if (!empty($was)) {
            $insertArr['was'] = is_array($was) ? json_encode($was) : $was;
        }
        if (!empty($set)) {
            $insertArr['set'] = is_array($set) ? json_encode($set) : $set;
        }
        if (!empty($comment)) {
            $insertArr['comment'] = (string) $comment;
        } elseif (!empty($_REQUEST['comment'])) {
            $insertArr['comment'] = (string) $_REQUEST['comment'];
        } elseif (($callelFileName = ApiLogger::getCalledFileName())) {
            $insertArr['comment'] = $callelFileName;
        }

        return DB::insert($this->_historyTable, $insertArr);
    }

}
