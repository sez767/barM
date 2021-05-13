<?php

class ApiLeadvertex {

    // Documentation:
    // http://akula-hryash.leadvertex.ru/admin/page/api.html?token=9w587tm
//Добрый день!
//Домен: akula-hryash
//Токен: 9w587tm
//
//Номера статусов:
//Подтвержден - 25
//Принят (почта) - 1
//Фрод - 15
//Фрод (недозвон) - 37
//Фрод (отмена) – 38
//Фрод (разобраться) - 40

    static private $_conf = array(
        'akula-hryash' => array('url' => 'http://akula-hryash.leadvertex.ru/api/admin/', 'token' => '9w587tm'),
        /////////////////
        // LeadPforit
        'mangosteen-es' => array('url' => 'https://mangosteen-es.leadvertex.ru/api/admin/', 'token' => 'B6DC8A2468B2B61AB0ED2CD5C303C629'),
    );

    /*
     */
    private $_apiUrl = '';
    /*
     */
    private $_apiToken = '';

    /**
     * @param string $serviceName
     * @return string
     */
    function __construct($serviceName) {
        if (empty(self::$_conf[$serviceName])) {
            return 'error';
        }
        $this->_apiUrl = self::$_conf[$serviceName]['url'];
        $this->_apiToken = self::$_conf[$serviceName]['token'];
    }

    /**
     * Отправка запроса на API
     * @param string $method - метод API который будем дергать. Пример: getPayForm
     * @param array $getParams - массив параметров передаваемый в метод
     * @param array $postParams - массив параметров передаваемый в метод
     * @return array - ответ от сервера
     */
    public function sendRequest($method, $getParams = array(), $postParams = array()) {

        if (empty($this->_apiUrl)) {
            return 'error';
        }

        $getParams['token'] = $this->_apiToken;

        // инициализируем сеанс
        $curl = curl_init();
        // уcтанавливаем урл, к которому обратимся
        $url = $this->_apiUrl . $method . '.html';
//        ApiLogger::echoLogVarExport($getParams);
        $url .= '?' . http_build_query($getParams);
        ApiLogger::addLogVarExport("URL => $url");

        curl_setopt($curl, CURLOPT_URL, $url);
        // максимальное время выполнения скрипта
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        // передаем данные по методу post
        curl_setopt($curl, CURLOPT_POST, 1);
        // отключаем проверку сертификата
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        // теперь curl вернет нам ответ, а не выведет
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // переменные, которые будут переданные по методу post
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postParams));
        // отправка запроса
        $result = curl_exec($curl);
//        ApiLogger::addLogVarExport($result);

        if ($result) {
            $result = json_decode($result, true);
        } else {
            $info = curl_getinfo($curl);
            ApiLogger::addLogVarExport('$info:');
            ApiLogger::addLogVarExport($info);
            $result = array("statuscode" => 503, "error" => "Server is not responding");
        }

        // закрываем соединение
        curl_close($curl);
        return $result;
    }

}

/* End of file ApiMonsterleads.php */
