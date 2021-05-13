<?php

require_once dirname(__FILE__) . '/../../lib/XML2Array.php';

/*
 * Get Shipping info via CURL/XML protocol.
 */
define('CFG_NL', PHP_EOL);
define('CFG_REQUEST_POST', 1);
define('CFG_REQUEST_HTTP', 'http://');
define('CFG_REQUEST_HOST', 'api.betapro.ru');
define('CFG_REQUEST_PORT', 8080);
define('CFG_REQUEST_URL', '/bp/hs/wsrv');
define('CFG_REQUEST_FULLURL', CFG_REQUEST_HTTP . CFG_REQUEST_HOST . ':' . CFG_REQUEST_PORT . CFG_REQUEST_URL);
define('CFG_REQUEST_TIMEOUT', 5);
define('CFG_CONTENT_TYPE', 'text/xml');

/**
 * Description of ApiBetaPro
 *
 * @author dob
 */
class ApiBetaPro {

    private $_partner_id = '874';
    private $_password = 'st36twg2';

    function __construct($partner_id = '', $password = '') {
        $this->_partner_id = empty($partner_id) ? $this->_partner_id : $partner_id;
        $this->_password = empty($password) ? $this->_password : $password;
    }

    public function deleteDoc($codes, $jsonResp = true) {
        $codesArr = array();
        if (!empty($codes)) {
            $codesArr = is_array($codes) ? $codes : explode(',', $codes);
            if (!is_array($codesArr) && is_integer($codesArr)) {
                $codesArr = array($codesArr);
            }
        }

        $xml = $this->_getXmlTemplate(102);

        foreach ($codesArr as $orderId) {
            $childDoc = $xml->addChild('doc');
            $childDoc->addAttribute('zdoc_id', $orderId);
        }

        $ret = $this->_sendRequest($xml);
        if (empty($ret['error']) && !empty($ret['parcel'])) {
            $ret = $ret['parcel'];
        }
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getTrack($codes, $jsonResp = true) {
        $codesArr = array();
        if (!empty($codes)) {
            $codesArr = is_array($codes) ? $codes : explode(',', $codes);
            if (!is_array($codesArr) && is_integer($codesArr)) {
                $codesArr = array($codesArr);
            }
        }

        $xml = $this->_getXmlTemplate(550);

        foreach ($codesArr as $parcelCode) {
            $childParcel = $xml->addChild('parcel');
            $childParcel->addAttribute('order_id', $parcelCode);
        }

        $ret = $this->_sendRequest($xml);
        if (empty($ret['error']) && isset($ret['parcel'])) {
            $ret = $ret['parcel'];
            if (isset($ret['@value'])) {
                $ret = array($ret);
            }
        }
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getTrackOrder($codes, $jsonResp = true) {
        $codesArr = array();
        if (!empty($codes)) {
            $codesArr = is_array($codes) ? $codes : explode(',', $codes);
            if (!is_array($codesArr) && is_integer($codesArr)) {
                $codesArr = array($codesArr);
            }
        }

        $xml = $this->_getXmlTemplate(154);

        foreach ($codesArr as $orderId) {
            $childOrder = $xml->addChild('order');
            $childOrder->addAttribute('order_id', $orderId);
        }

        $ret = $this->_sendRequest($xml);
        if (empty($ret['error']) && isset($ret['parcel'])) {
            $ret = $ret['parcel'];
            if (isset($ret['@value'])) {
                $ret = array($ret);
            }
        }
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function sendEchoTest($jsonResp = true) {
        $xml = $this->_getXmlTemplate(3);
        $ret = $this->_sendRequest($xml);
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getDeliveryTypes($jsonResp = true) {
        $xml = $this->_getXmlTemplate(56);
        $ret = $this->_sendRequest($xml);
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getCustomerDocList($params = array(), $jsonResp = true) {
        $xml = $this->_getXmlTemplate(103);
        foreach ($params as $key => $value) {
            $xml->addAttribute($key, $value);
        }

        $ret = $this->_sendRequest($xml);
        if (empty($ret['error'])) {
            $ret = empty($ret['doc']) ? array() : $ret['doc'];
            if (isset($ret['@value'])) {
                $ret = array($ret);
            }
        }
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getExecutorDocList($params = array(), $jsonResp = true) {
        $xml = $this->_getXmlTemplate(104);
        foreach ($params as $key => $value) {
            $xml->addAttribute($key, $value);
        }
        $ret = $this->_sendRequest($xml);
        if (empty($ret['error'])) {
            $ret = empty($ret['doc']) ? array() : $ret['doc'];
            if (isset($ret['@value'])) {
                $ret = array($ret);
            }
        }
        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getExecutorDoc($idocId, $jsonResp = true) {
        $xml = $this->_getXmlTemplate(105);

        $xml->addAttribute('idoc_id', $idocId);
        $ret = $this->_sendRequest($xml);

        if (empty($ret['error']) && isset($ret['doc'])) {
            $ret = $ret['doc'];
            if (!empty($ret['order']['@attributes']['zdoc_id']) && ($pos = strpos($ret['order']['@attributes']['zdoc_id'], '_'))) {
                $ret['order']['@attributes']['zdoc_id'] = substr($ret['order']['@attributes']['zdoc_id'], 0, $pos);
            }
        }

        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function getCustomerDoc($zdocId, $jsonResp = true) {
        $xml = $this->_getXmlTemplate(107);

        $xml->addAttribute('zdoc_id', $zdocId);
        $ret = $this->_sendRequest($xml);

        if (empty($ret['error']) && isset($ret['doc'])) {
            $ret = $ret['doc'];
            if (!empty($ret['@attributes']['zdoc_id']) && ($pos = strpos($ret['@attributes']['zdoc_id'], '_'))) {
                $ret['@attributes']['zdoc_id'] = substr($ret['@attributes']['zdoc_id'], 0, $pos);
            }
        }

        return $jsonResp ? json_encode($ret) : $ret;
    }

    public function setLockExecutorDoc($idocs, $lockStatus = 1, $jsonResp = true) {
        $idocArr = array();
        if (!empty($idocs)) {
            $idocArr = is_array($idocs) ? $idocs : explode(',', $idocs);
            if (!is_array($idocArr) && is_integer($idocArr)) {
                $idocArr = array($idocArr);
            }
        }

        $xml = $this->_getXmlTemplate(106);

        foreach ($idocArr as $idocItem) {
            $docChild = $xml->addChild('doc');
            $docChild->addAttribute('idoc_id', $idocItem);
            $docChild->addAttribute('lock', $lockStatus);
        }

        $ret = $this->_sendRequest($xml);
        return $jsonResp ? json_encode($ret) : $ret;
    }

    function sendBetaPro($data, $jsonResp = true, $isSecond = false) {

        global $GLOBAL_OFFER_DESC;

        static $deliveryType = null;

        if ($deliveryType === null) {
            // START Получаем список доступных служб доставок и берем пока первую из них
            $ret = $this->getDeliveryTypes(false);

            if (empty($ret['error'])) {
                $deliveryItem = reset($ret);
                $deliveryType = $deliveryItem['@attributes']['delivery_type'];
                // END /Получаем список доступных служб доставок и берем пока первую из них
            }
        }

        if ($deliveryType) {

            $xml = $this->_getXmlTemplate(101);

            $childDoc = $xml->addChild('doc');
            $childDoc->addAttribute('doc_type', 5);
            $childDoc->addAttribute('zdoc_id', $data['id'] . ($isSecond ? '_' . time() : ''));
            $childDoc->addAttribute('doc_txt', "{$data['fio']} ({$data['phone']})");

            $childOrder = $childDoc->addChild('order');
            $childOrder->addAttribute('order_id', $data['id']);
            $childOrder->addAttribute('delivery_type', $deliveryType);
            $childOrder->addAttribute('zip', $data['index']);
            $childOrder->addAttribute('clnt_name', $data['fio']);
            if ($deliveryType == 1) {
                $childOrder->addAttribute('dev1mail_type', 16);
//                Тип корреспонденции
//                Используется только для Почты России
//                3= Бандероль
//                4= Посылка
//                16= Бандероль 1 класса
//                23= Посылка-онлайн
            }

            $childOrder->addAttribute('zbarcode', "{$this->_partner_id}+{$data['id']}");

            $childStructAddr = $childOrder->addChild('struct_addr');
            $childStructAddr->addAttribute('region', $data['region']);
            $childStructAddr->addAttribute('area', $data['district']);
            $childStructAddr->addAttribute('city', $data['city']);
            $childStructAddr->addAttribute('street', $data['street']);
            $childStructAddr->addAttribute('house', $data['building'] . (empty($data['flat']) ? '' : "-{$data['flat']}"));

            // order_row
            $tmpDop = array();
            $totalCount = $data['package'] ? $data['package'] : 1;
            if (isJson($data['dop_tovar']) && ($tmpDop = json_decode($data['dop_tovar'], true))) {
                $totalCount += array_sum($tmpDop['dop_tovar_count']);
            }

            $otherData = json_decode($data['other_data'], true);

            $priceItem = round($data['price'] / $totalCount * $data['package'], 2);
            $childOrderRow = $childDoc->addChild('order_row');
            $childOrderRow->addAttribute('order_id', $data['id']);
            $childOrderRow->addAttribute('good_id', $data['offer']);
            $childOrderRow->addAttribute('good_name', $GLOBAL_OFFER_DESC[$data['offer']] . ($otherData ? ' (' . implode(', ', (array) $otherData) . ')' : '') . " - {$data['package']} шт.");
            $childOrderRow->addAttribute('price', $priceItem);
            $childOrderRow->addAttribute('clnt_price', $priceItem);
            $childOrderRow->addAttribute('vat_rate', 18);
            $childOrderRow->addAttribute('vat_amount', round($priceItem / 100 * 18, 2));

            if ($tmpDop) {
                foreach ($tmpDop['dop_tovar'] as $ke => $va) {
                    $priceItem = round($data['price'] / $totalCount * $tmpDop['dop_tovar_count'][$ke], 2);
                    $childOrderRow = $childDoc->addChild('order_row');
                    $childOrderRow->addAttribute('order_id', $data['id']);
                    $childOrderRow->addAttribute('good_id', $va);
                    $childOrderRow->addAttribute('good_name', $GLOBAL_OFFER_DESC[$va] . ' ' . (isset($tmpDop['vendor'][$ke]) ? $tmpDop['vendor'][$ke] : '') . ' ' . (isset($tmpDop['color'][$ke]) ? $tmpDop['color'][$ke] : '') . ' ' . (isset($tmpDop['name'][$ke]) ? $tmpDop['name'][$ke] : '') . ' ' . (isset($tmpDop['type'][$ke]) ? $tmpDop['type'][$ke] : '') . ' ' . (isset($tmpDop['size'][$ke]) ? $tmpDop['size'][$ke] : '') . ' - ' . $tmpDop['dop_tovar_count'][$ke] . 'шт.');
                    $childOrderRow->addAttribute('price', $priceItem);
                    $childOrderRow->addAttribute('clnt_price', $priceItem);
                    $childOrderRow->addAttribute('vat_rate', 18);
                    $childOrderRow->addAttribute('vat_amount', round($priceItem / 100 * 18, 2));
                }
            }

            $ret = $this->_sendRequest($xml);

            if (!empty($ret['error']) && $ret['error']['code'] == -20001 && !$isSecond) {
                $ret = $this->sendBetaPro($data, false, true);
            }
        }

        return $jsonResp ? json_encode($ret) : $ret;
    }

    /**
     * @param int $request_type
     * @return SimpleXMLElement
     */
    private function _getXmlTemplate($request_type) {
        $xmlText = "<request/>";
        $xml = simplexml_load_string($xmlText);
        $xml->addAttribute('partner_id', $this->_partner_id);
        $xml->addAttribute('password', $this->_password);
        $xml->addAttribute('request_type', $request_type);
        return $xml;
    }

    /**
     * @param SimpleXMLElement $xml
     */
    private function _sendRequest($xml) {
        $ret = false;

        $xmlBody = $xml->asXML();

        $o_Curl = curl_init();
        //echo CFG_REQUEST_FULLURL;
        curl_setopt($o_Curl, CURLOPT_URL, CFG_REQUEST_FULLURL);
        curl_setopt($o_Curl, CURLOPT_POST, CFG_REQUEST_POST);
        curl_setopt($o_Curl, CURLOPT_CONNECTTIMEOUT, CFG_REQUEST_TIMEOUT);
        curl_setopt($o_Curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . CFG_CONTENT_TYPE
            , 'Content-Length: ' . strlen($xmlBody)
            , 'Connection: close'
        ));

        curl_setopt($o_Curl, CURLOPT_POSTFIELDS, $xmlBody);
        curl_setopt($o_Curl, CURLOPT_HEADER, 0);
        curl_setopt($o_Curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($o_Curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($o_Curl, CURLOPT_SSL_VERIFYPEER, 0);
        // PROXY
        curl_setopt($o_Curl, CURLOPT_PROXY, 'ketkz.com:3128');
        curl_setopt($o_Curl, CURLOPT_HTTPPROXYTUNNEL, 1);

        $s_Response = curl_exec($o_Curl);
        curl_close($o_Curl);

        if ($s_Response) {
            $domXml = new DOMDocument();
            $domXml->loadXML($s_Response);
            $repsData = XML2Array::createArray($domXml);

//            echo PHP_EOL;
//            print_r($xmlBody);
//            echo PHP_EOL;
//            print_r($s_Response);
//            echo PHP_EOL;
//            print_r($repsData);
//            echo PHP_EOL;

            if (is_array($repsData)) {
                if (!empty($repsData['response'])) {
                    if (!empty($repsData['response'])) {
//                        print_r($repsData);echo PHP_EOL;
                        $ret = $repsData['response'];
                        if (!empty($ret['error'])) {
                            $ret['error'] = $ret['error']['@attributes'];
//                            unset($ret['@attributes']);
                        }
                    }
                } elseif (!empty($repsData['request'])) {
                    $ret = $repsData;
                }
            }
        }
        return $ret;
    }

    public function getExecutorDocListNew($params = array(), $retTag) {
        $xml = $this->_getXmlTemplate(104);
        foreach ($params as $key => $value) {
            $xml->addAttribute($key, $value);
        }
        $ret = $this->_sendRequestNew($xml, $retTag);
        echo "==" . get_class($ret) . "==";
//        if ($ret['error'])) {
//            $ret = empty($ret['doc']) ? array() : $ret['doc'];
//            if (isset($ret['@value'])) {
//                $ret = array($ret);
//            }
//        }
        return $ret;
    }

    private function _sendRequestNew($xml, $retTag) {
        $ret = false;

        $xmlBody = $xml->asXML();

        $o_Curl = curl_init();
        //echo CFG_REQUEST_FULLURL;
        curl_setopt($o_Curl, CURLOPT_URL, CFG_REQUEST_FULLURL);
        curl_setopt($o_Curl, CURLOPT_POST, CFG_REQUEST_POST);
        curl_setopt($o_Curl, CURLOPT_CONNECTTIMEOUT, CFG_REQUEST_TIMEOUT);
        curl_setopt($o_Curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . CFG_CONTENT_TYPE
            , 'Content-Length: ' . strlen($xmlBody)
            , 'Connection: close'
        ));

        curl_setopt($o_Curl, CURLOPT_POSTFIELDS, $xmlBody);
        curl_setopt($o_Curl, CURLOPT_HEADER, 0);
        curl_setopt($o_Curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($o_Curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($o_Curl, CURLOPT_SSL_VERIFYPEER, 0);
        // PROXY
        curl_setopt($o_Curl, CURLOPT_PROXY, 'ketkz.com:3128');
        curl_setopt($o_Curl, CURLOPT_HTTPPROXYTUNNEL, 1);

        $s_Response = curl_exec($o_Curl);
        curl_close($o_Curl);

        if ($s_Response) {
            $domXml = new DOMDocument();

            print_r(XML2Array::createArray($domXml));

            if ($domXml->loadXML($s_Response)) {
                if ($domXml->documentElement->tagName == 'response') {
                    if ($domXml->documentElement->hasAttribute('state') && $domXml->documentElement->getAttribute('state') == 0) {
                        $ret = $retTag ? $domXml->getElementsByTagName($retTag) : $domXml->documentElement;
                    } else {
                        $nodeList = $domXml->getElementsByTagName('error');
                        $ret = $nodeList->item(0);
                    }
                } elseif ($domXml->documentElement == 'request') {
                    $domXml->documentElement;
                }
            }
        }
        return $ret;
    }

}
