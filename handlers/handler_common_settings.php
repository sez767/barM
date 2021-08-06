<?php

require_once dirname(__FILE__) . "/../lib/db.php";

/**
 * @author dob
 */
class HandlerCommonSettings extends CrmHandlerBase {

    private $_redis = null;

    function __construct() {

        parent::__construct();

        $this->_redis = RedisManager::getInstance()->getRedis();

        $this->doResponse();
    }

    public function read() {


        if (!$this->_redis->exists($_REQUEST['key'])) {
            die(json_encode(array(
                'success' => true,
                'msg' => 'KEY does not exist',
                'data' => array(),
                'total' => 0
            )));
        }

        $aData = $this->_redis->hgetall($_REQUEST['key']);
        $data = array();

        foreach ($aData AS $id => $value) {
            $data[] = array(
                'id' => $id,
                'value' => $value
            );
        }

        $ret = array(
            'success' => true,
            'data' => $data,
            'total' => count($data)
        );

        return $ret;
    }

    public function insert($attributes) {
        $newId = time();
        $this->_redis->hSet($_REQUEST['key'], $newId, '0');
        $ret = array(
            'success' => true,
            'data' => array(
                'id' => $newId,
                'value' => 0
            ),
            'message' => 'Все четенько'
        );

        return $ret;
    }

    public function update($attributes) {
        $this->_redis->hSet($_REQUEST['key'], $attributes['id'], $attributes['value']);
        $ret = array(
            'success' => true,
            'message' => 'Все четенько'
        );
        return $ret;
    }

    public function delete($attributes) {
        $this->_redis->hDel($_REQUEST['key'], $attributes['id']);
        $ret = array(
            'success' => true,
            'message' => 'Все четенько'
        );
        return $ret;
    }

}

new HandlerCommonSettings();
