<?php

class ApiKetKz {

    private $_apiUrl = 'http://ketkz.com/api/';
    private $_uid = '85935432';
    private $_secret = 'zPGMr1Sy';

    /**
     * @param string $uid
     */
    function __construct($uid = null, $secret = null) {
        $this->_uid = $uid ? $uid : $this->_uid;
        $this->_secret = $secret ? $secret : $this->_secret;
    }

    /**
     * Отправка запроса на API
     * @param string $method - метод API который будем дергать. Пример: addOrder
     * @param array $params - массив параметров передаваемый в метод
     * @return array - ответ от сервера
     */
    public function sendRequest($method, $params = array(), $decode = true) {

        // подготовка данных к отправке на API
        $request = $this->prepareRequest($params);

//        print_r($request);
        // инициализируем сеанс
        $curl = curl_init();

        $url = $this->_apiUrl . "$method.php?uid=$this->_uid&s=$this->_secret&hash={$request['sign']}";
        if ($this->_uid == 97931685) {
            $url .= '&wrumwrum=5';
        }
        // уcтанавливаем урл, к которому обратимся
        curl_setopt($curl, CURLOPT_URL, $url);
        // максимальное время выполнения скрипта
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        // передаем данные по методу post
        curl_setopt($curl, CURLOPT_POST, 1);
        // теперь curl вернет нам ответ, а не выведет
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // отключаем проверку сертификата
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Pragma: no-cache"));
        // переменные, которые будут переданные по методу post
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('data' => $request['data']));
        // отправка запроса
        $result = curl_exec($curl);
//        print_r($result);
        // закрываем соединение
//        echo "{$this->_apiUrl}?token={$this->_apiToken}" . PHP_EOL;
//        print_r($request);
//        print_r($result);
//        die('suka');
        curl_close($curl);

        if ($result) {
            $result = $decode ? json_decode($result, true) : $result;
        } else {
            $result = array("statuscode" => 503, "error" => "Server is not responding");
        }

        return $result;
    }

    /**
     * Подготовка данных перед отправкой
     * @param string $method - вызываемый метод API
     * @param array $params - данные передаваемые в метод
     * @return array - массив подготовленых данных
     */
    public function prepareRequest($params) {
//        $params['secret'] = $this->_secret;
        $requestJson = json_encode($params);

        $hashStr = strlen($requestJson) . md5($this->_uid);
        // Возврат данных подготовленных данных
        return array(
            'data' => $requestJson,
            'sign' => hash('sha256', $hashStr)
        );
    }

}
