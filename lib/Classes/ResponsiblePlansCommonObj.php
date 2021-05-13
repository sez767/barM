<?php

/**
 * @author dob
 */
class ResponsiblePlansCommonObj extends CommonObject {

    protected $_autoWhereFields = array('type');
    public static $CACHE_PREFIX = 'GLOBAL_BONUSE_PLANS_';
    public static $CACHE_NAME = 'GLOBAL_RESPONSIBLE_PLANS_BONUSES';

    function __construct($type, $id = null, $withLoad = false) {
        $this->cSetTableName('ResponsiblePlansCommonNew');
        $this->cSetAutoFieldsValues(array('type' => $type));
        parent::__construct($id, $withLoad);
    }

    public function cSave($newValues = null) {
        $ret = parent::cSave($newValues);

        $memcache = MemcacheManager::getInstance()->getMemcache();
        $memcache->delete($this->getCacheName());

        return $ret;
    }

    public function getCacheName() {
        return self::$CACHE_PREFIX . $this->cGetValues('type') . '_' . $this->cGetValues('country');
    }

}
