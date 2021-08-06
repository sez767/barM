<?php

require_once dirname(__FILE__) . "/../lib/db.php";

/**
 * @author dob
 */
class HandlerControlAdmins extends CrmHandlerBase {

    private $_redis = null;

    function __construct() {

        parent::__construct();

        $this->_redis = RedisManager::getInstance()->getRedis();

        $this->doResponse();
    }

    public function read() {


        if (!$this->_redis->exists('control_admins')) {
            die(json_encode(array(
                'success' => true,
                'msg' => 'KEY does not exist',
                'data' => array(),
                'total' => 0
            )));
        }

        $aData = $this->_redis->hgetall('control_admins');
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
        $this->_redis->hSet('control_admins', $newId, '0');
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
        $this->_redis->hSet('control_admins', $attributes['id'], $attributes['value']);
        $ret = array(
            'success' => true,
            'message' => 'Все четенько'
        );
        return $ret;
    }

    public function delete($attributes) {
        $this->_redis->hDel('control_admins', $attributes['id']);
        $ret = array(
            'success' => true,
            'message' => 'Все четенько'
        );
        return $ret;
    }

}

new HandlerControlAdmins();
