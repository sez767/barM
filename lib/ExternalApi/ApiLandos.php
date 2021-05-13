<?php

class ApiLandos {

    private $_apiUrl = 'http://crm.landos.info/api/v2/';
    private $_apiToken = '';
    private $_apiKey = '';

    /**
     * @param type $apiKey
     */
    function __construct($apiToken, $apiKey) {
        $this->_apiToken = $apiToken;
        $this->_apiKey = $apiKey;
    }

    /**
     * Отправка запроса на API
     * @param string $action - метод API который будем дергать. Пример: addOrder
     * @param array $params - массив параметров передаваемый в метод
     * @return array - ответ от сервера
     */
    public function sendRequest($action, $params = array(), $decode = true) {

        // подготовка данных к отправке на API
        $request = $this->prepareRequest($action, $params);

        // инициализируем сеанс
        $curl = curl_init();
        // уcтанавливаем урл, к которому обратимся
        curl_setopt($curl, CURLOPT_URL, $this->_apiUrl . '?token=' . $this->_apiToken);
        // максимальное время выполнения скрипта
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        // передаем данные по методу post
        curl_setopt($curl, CURLOPT_POST, 1);
        // теперь curl вернет нам ответ, а не выведет
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // отключаем проверку сертификата
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // переменные, которые будут переданные по методу post
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        // отправка запроса
        $result = curl_exec($curl);
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
     * @param string $action - вызываемый метод API
     * @param array $params - данные передаваемые в метод
     * @return array - массив подготовленых данных
     */
    public function prepareRequest($action, $params) {
        //  Структурирование данных
        $requestArr = array(
            'action' => $action,
            'params' => $params,
        );

        // Получение JSON представления данных
        $requestJson = json_encode($requestArr);
        //echo $requestJson;
        // Кодирование данных
        $data = base64_encode($requestJson);

        // Генерация подписи
        $sign = base64_encode(md5($this->_apiKey . $requestJson . $this->_apiKey));

        // Возврат данных подготовленных данных
        return array(
            'data' => $data,
            'sign' => $sign
        );
    }

}
