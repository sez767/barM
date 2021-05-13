<?php

class DPD_class {

    public $arMSG = array();
    private $IS_ACTIVE = 1;
    private $IS_TEST = 0;
    private $SOAP_CLIENT;
    private $MY_NUMBER = '1092001165';
    private $MY_KEY = 'F2813DBF6B21CEA50240ED1BE8F52EBE951D999E';
    private $arDPD_HOST = array(
        0 => 'ws.dpd.ru/services/',
        1 => 'wstest.dpd.ru/services/'
    );
    private $arSERVICE = array(
        'getServiceCostByParcels' => 'calculator2',
        'getServiceCost' => 'calculator2',
        'getTerminalsSelfDelivery' => 'geography',
        'getCitiesCashPay' => 'geography',
        'createOrder' => 'order2', // Создание заявки в DPD
        'getOrderStatus' => 'order2', // Получение статуса заказа
        'createLabelFile' => 'label-print' // Получить наклейки
    );

    public function __construct($isTest = false) {
        $this->IS_TEST = $isTest ? 1 : 0;
    }

    public function createOrder($arData) {
        $obj = $this->_getDpdData('createOrder', $arData, 'orders');
        $res = $this->_parceObj2Arr($obj->return, 0);
        return $res;
    }

    public function getCityList() {
        $obj = $this->_getDpdData('getCitiesCashPay');
        $res = $this->_parceObj2Arr($obj->return);
        return $res;
    }

    public function getTerminalsSelfDelivery($arData) {
        $obj = $this->_getDpdData('getTerminalsSelfDelivery');
        $res = $this->_parceObj2Arr($obj->return, 0);
        return $res;
    }

    public function getServiceCostByParcels($arData) {
        $obj = $this->_getDpdData('getServiceCostByParcels');
        return $obj;
    }

    public function getServiceCost($arData) {
        $obj = $this->_getDpdData('getServiceCost', $arData, 'request');
        $res = $this->_parceObj2Arr($obj->return);

        return $res;
    }

    public function createLabelFile($arData) {
        $obj = $this->_getDpdData('createLabelFile', $arData, 'getLabelFile');
        return $obj;
    }

    public function getOrderStatus($arData) {
        $obj = $this->_getDpdData('getOrderStatus', $arData, 'orderStatus');
        return $obj;
    }

    private function _connect2Dpd($method_name) {
        if (!$this->IS_ACTIVE) {
            return false;
        }

        if (!$service = $this->arSERVICE[$method_name]) {
            $this->arMSG['str'] = 'В свойствах класса нет сервиса "' . $method_name . '"';
            return false;
        }
        $host = $this->arDPD_HOST[$this->IS_TEST] . $service . '?WSDL';

        try {
            // Soap-подключение к сервису
            $this->SOAP_CLIENT = new SoapClient('http://' . $host);
            //var_dump($this->SOAP_CLIENT); die;
            if (!$this->SOAP_CLIENT) {
                throw new Exception('Error');
            }
        } catch (Exception $ex) {
            $this->arMSG['str'] = 'Не удалось подключиться к сервисам DPD ' . $service;
            return false;
        }

        return true;
    }

    private function _getDpdData($method_name, $arData = array(), $is_request = 0) {
        if (!$this->_connect2Dpd($method_name)) {
            return false;
        }
        $arData['auth'] = array(
            'clientNumber' => $this->MY_NUMBER,
            'clientKey' => $this->MY_KEY,
        );

        if ($is_request) {
            $arRequest[$is_request] = $arData;
        } else {
            $arRequest = $arData;
        }

        try {

            $obj = $this->SOAP_CLIENT->$method_name($arRequest);
            if (!$obj) {
                throw new Exception('Error');
            }
        } catch (Exception $ex) {
            $this->arMSG['str'] = 'Не удалось вызвать метод ' . $method_name . ' / ' . $ex;
        }

        return $obj ? $obj : false;
    }

    private function _parceObj2Arr($obj, $isUTF = 1, $arr = array()) {
        $isUTF *= 1;

        if (is_object($obj) || is_array($obj)) {
            $arr = array();
            for (reset($obj); list($k, $v) = each($obj);) {
                if ($k === "GLOBALS") {
                    continue;
                }
                $arr[$k] = $this->_parceObj2Arr($v, $isUTF, $arr);
            }
            return $arr;
        } elseif (gettype($obj) == 'boolean') {
            return $obj ? 'true' : 'false';
        } else {
            if ($isUTF && gettype($obj) == 'string') {
                $obj = iconv('utf-8', 'windows-1251', $obj);
            }
            return $obj;
        }
    }

}
