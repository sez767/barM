<?php

class ApiWarehouse {

    private $_apiUrl = 'https://warehouse-api-test.azurewebsites.net/api';
    private $_uid = 'aeba694d-f932-4a10-9c17-3de00825e032';
    private $_secret = 'be11bbf1-6108-4486-b51b-2f0e9f2e8580';

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

        $url = "$this->_apiUrl/$method";
        // уcтанавливаем урл, к которому обратимся
        curl_setopt($curl, CURLOPT_URL, $url);
        // максимальное время выполнения скрипта
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        // передаем данные по методу post
        curl_setopt($curl, CURLOPT_POST, 1);
        // теперь curl вернет нам ответ, а не выведет
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // отключаем проверку сертификата
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "accept: application/json",
            "content-type: application/json",
            "x-client-id: $this->_uid",
            "x-signature: {$request['sign']}"
                )
        );
        // переменные, которые будут переданные по методу post
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request['data']);
        // отправка запроса
        $result = curl_exec($curl);

        $info = curl_getinfo($curl);

        echo "- Request Url: {$info['url']}" . PHP_EOL;
        echo "- Request body:\n" . var_export($params, true) . PHP_EOL;
        echo "- Response http_code: {$info['http_code']}" . PHP_EOL;
        echo "- Response:\n" . var_export($result, true) . PHP_EOL;

        // закрываем соединение
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
        // Возврат данных подготовленных данных
        return array(
            'data' => $requestJson,
            'sign' => hash_hmac('sha1', $requestJson, $this->_secret)
        );
    }

}
