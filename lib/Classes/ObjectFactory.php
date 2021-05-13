<?php

require_once 'CommonObject.php';
///////////////////
require_once 'StaffOrderCommonObj.php';
require_once 'StaffOrderObj.php';
require_once 'StaffOrderArchObj.php';
require_once 'StaffOrderSnapshotObj.php';
///////////////////
require_once 'ActionHistoryObj.php';
require_once 'ClientsObj.php';
require_once 'StaffObj.php';
require_once 'OprosnikObj.php';
require_once 'IncomeCallsObj.php';
require_once 'ExchangeObj.php';
require_once 'OfferPropertyObj.php';
require_once 'OprosnikAnswersObj.php';
////////////
require_once 'QueueTableObj.php';
require_once 'PredictiveMultiplierObj.php';
////////////
require_once 'DeliveryWeekDaysObj.php';
/////////////
require_once 'ResponsiblePlansCommonObj.php';
require_once 'CallObjectHistoryObj.php';
/////////////
require_once 'ServicesObj.php';
require_once 'ClientsServicesObj.php';


require_once dirname(__FILE__) . '/../class.staff.php';
require_once dirname(__FILE__) . '/../class.storage.php';

class ObjectFactory {

    /**
     * @param mixed $prototype
     * @param integer $id
     * @return CommonObject
     */
    public static function getObj($prototype, $id = null) {
        if (($typeName = self::detectTypeName($prototype))) {
            $className = ucfirst($typeName) . 'Obj';
            $retObject = new $className($id);
            return self::_setCategoryId($retObject, $prototype);
        }
        return null;
    }

    /**
     * @param mixed $prototype
     * @param integer $id
     * @return CategoryObj
     */
    public static function getInventoryObj($prototype, $id = null) {
        $className = 'InventoryObj';
        $retObject = new $className($id);
        return self::_setCategoryId($retObject, $prototype);
    }

    /**
     * @param mixed $prototype
     * @param integer $id
     * @return CategoryObj
     */
    public static function getCategoryObj($prototype, $id = null) {
        $className = 'CategoryObj';
        $retObject = new $className($id);
        return self::_setCategoryId($retObject, $prototype);
    }

    /**
     * @param mixed $prototype
     * @param integer $id
     * @return CharTreeObj
     */
    public static function getCharTreeObj($prototype, $id = null) {
        if (($typeName = self::detectTypeName($prototype))) {
            $className = 'CharTree' . ucfirst($typeName) . 'Obj';
            $retObject = new $className($id);
            return self::_setCategoryId($retObject, $prototype);
        }
        return null;
    }

    /**
     * @param mixed $prototype
     * @param integer $id
     * @return CharOptionsObj
     */
    public static function getCharOptionsObj($prototype, $id = null) {
        if (($typeName = self::detectTypeName($prototype))) {
            $className = 'CharOptions' . ucfirst($typeName) . 'Obj';
            return new $className($id);
        }
        return null;
    }

    /**
     * @param CommonObject $object
     * @param mixed $prototype
     * @return CommonObject
     */
    private static function _setCategoryId($object, $prototype = null, $categoryFieldName = 'category_id') {
        $categoryId = false;

        if (
                method_exists($object, 'getCaterogyId') && $object->getCaterogyId() > 0 ||
                method_exists($object, 'cGetValues') && $object->cGetValues($categoryFieldName) > 0
        ) {

        } else {
            if (is_object($prototype) && method_exists($prototype, 'getCaterogyId') && ($categoryId = $prototype->getCaterogyId())) {

            } elseif (is_object($prototype) && method_exists($prototype, 'cGetValues') && ($categoryId = $prototype->cGetValues($categoryFieldName))) {

            } elseif (!empty($_REQUEST[$categoryFieldName])) {
                $categoryId = $_REQUEST[$categoryFieldName];
            }

            if ($categoryId) {
//            echo ":=$categoryId|";
                if (method_exists($object, 'setCaterogyId')) {
                    $object->setCaterogyId($categoryId);
                } elseif (method_exists($object, 'cSetValues')) {
                    $object->cSetValues($categoryFieldName, $categoryId);
                }
            }
        }

        return $object;
    }

    public static function detectTypeName($prototype) {
        $typeName = false;

//        if (is_string($prototype) && preg_match('/(material|nomenclature)/i', $prototype, $matches)) {
        if (is_string($prototype) && preg_match('/(material|nomenclature|category|order)/i', $prototype, $matches)) {
//            print_r($matches);
            $typeName = $matches[1];
        } elseif (is_object($prototype) && method_exists($prototype, 'cGetValues') && ($typeName = $prototype->cGetValues('type'))) {

//        } elseif (is_object($prototype) && ($caller = get_class($prototype)) && preg_match('/(material|nomenclature)/i', $caller, $matches)) {
        } elseif (is_object($prototype) && ($caller = get_class($prototype)) && preg_match('/(material|nomenclature|category|order)Obj/i', $caller, $matches)) {
//            echo '=>'. get_class($prototype).'|';
            $typeName = $matches[1];
        } elseif (is_object($prototype) && method_exists($prototype, 'getModuleName') && ($typeName = $prototype->getModuleName())) {

        }

        if (empty($typeName)) {
            $exc = new Exception();
            ApiLogger::addLogPrintR('== No Type ==');
            ApiLogger::addLogPrintR($exc->getTraceAsString());
            exit();
        }
        return strtolower($typeName);
    }

}
