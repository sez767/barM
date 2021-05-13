<?php

class ApiTezTez {

    private $_apiUrl = 'https://jeydee.ru/web-api/';
    private $_apiToken = 'contractor_kbt';
    private $_apiKey = 'g63mFw1HlDld6gtSkU9ijlzr9PnSQMov5WCK7Zr3';
////////////////////////////
//id артикул тех брд
//=====================
    public static $shopMap = array(
        231 => array('apiToken' => 'contractor_kbt', 'apiKey' => 'g63mFw1HlDld6gtSkU9ijlzr9PnSQMov5WCK7Zr3'),
        281 => array('apiToken' => 'contractor_kbt_parfum', 'apiKey' => 'dZweioDWxznynudOmeHiR76gTtFvzUv3yG5FtFdK')
    );
    public static $productMap = array(
        'black_latte' => array('shop_id' => 231, 'product_id' => 547),
        'black_latte_bigroi' => array('shop_id' => 231, 'product_id' => 547),
        'active_dry_sprey' => array('shop_id' => 231, 'product_id' => 514),
        'lucem' => array('shop_id' => 231, 'product_id' => 509),
        'lucem_tl' => array('shop_id' => 231, 'product_id' => 509),
        'lucem_first' => array('shop_id' => 231, 'product_id' => 509),
        'lucem_bb_topleads' => array('shop_id' => 231, 'product_id' => 509),
        'lucem' => array('shop_id' => 231, 'product_id' => 509),
        'laminary' => array('shop_id' => 231, 'product_id' => 526),
        'lucem_vacci' => array('shop_id' => 231, 'product_id' => 510),
        'active_dry' => array('shop_id' => 231, 'product_id' => 512),
        'androcaps' => array('shop_id' => 231, 'product_id' => 590),
        'sustaflan' => array('shop_id' => 231, 'product_id' => 589),
        'ssanofleks' => array('shop_id' => 231, 'product_id' => 591),
        'sanofleks' => array('shop_id' => 231, 'product_id' => 591),
        'sanoflex' => array('shop_id' => 231, 'product_id' => 591),
        'varikozan' => array('shop_id' => 231, 'product_id' => 592),
        'vitalex_kids' => array('shop_id' => 231, 'product_id' => 598),
        'vitalex_men' => array('shop_id' => 231, 'product_id' => 597),
        'vitalex_women' => array('shop_id' => 231, 'product_id' => 596),
        'mikocel' => array('shop_id' => 231, 'product_id' => 595),
        'sauvage' => array('shop_id' => 281, 'product_id' => 921),
    );
///////////////////////////
//Список возможных значений статусов заказа
//new - дефолтовый статус
//assembling-complete - В обработке. Ожидает передачи в доставку
//send-to-assembling - Заказ ожидает товар на складе заказа.
//complectated - Упакован. Ожидает передачи в доставку
//send-to-delivery - Выехал курьером
//redirect - Доставка перенесена на другую дату
//complete - Выполнен
//cancel-after-delivery - Отменен
//refused-quality - Отказ клиента
//cancel-error - Ошибка для дублей и тд
//cancel-after-aprove - Отмена после подтверждения. Когда заказ отваливается на стадии предварительного звонка
    public static $statusMap = array(
        'new' => array('send_status' => 'Отправлен', 'status_kz' => 'Свежий'),
        'assembling-complete' => array('send_status' => 'Отправлен', 'status_kz' => 'Обработка'),
        'send-to-assembling' => array('send_status' => 'Отправлен', 'status_kz' => 'Обработка'),
        'complectated' => array('send_status' => 'Отправлен', 'status_kz' => 'Обработка'),
        'send-to-delivery' => array('send_status' => 'Отправлен', 'status_kz' => 'Обработка'),
        'redirect' => array('send_status' => 'Отправлен', 'status_kz' => 'Обработка'),
        'complete' => array('send_status' => 'Оплачен', 'status_kz' => 'Получен'),
        'cancel-after-delivery' => array('send_status' => 'Отказ', 'status_kz' => 'Груз в дороге'),
        'refused-quality' => array('send_status' => 'Отказ', 'status_kz' => 'Груз в дороге'),
        'cancel-error' => array('send_status' => 'Отказ', 'status_kz' => 'Груз в дороге'),
        'cancel-after-aprove' => array('send_status' => 'Отказ', 'status_kz' => 'Груз в дороге')
    );

    /**
     * @param String $apiToken
     * @param String $apiKey
     */
    function __construct($apiToken = null, $apiKey = null) {
        if ($apiToken !== null) {
            $this->_apiToken = $apiToken;
        }
        if ($apiKey !== null) {
            $this->_apiKey = $apiKey;
        }
    }

    /**
     * Отправка запроса на API
     * @param string $method - метод API который будем дергать. Пример: getPayForm
     * @param array $postParams - массив параметров передаваемый в метод
     * @return array - ответ от сервера
     */
    public function sendRequest($method, $getParams = array(), $postParams = array()) {

        if (empty($this->_apiUrl)) {
            return 'error';
        }

        $getParams['api_key'] = empty($postParams['shop_id'] && !empty($this->_apiKey)) ? $this->_apiKey : self::$shopMap[$postParams['shop_id']]['apiKey'];

        // инициализируем сеанс
        $curl = curl_init();
        // уcтанавливаем урл, к которому обратимся
        $url = $this->_apiUrl . $method . '/';
//        ApiLogger::echoLogVarExport($getParams);
        $url .= '?' . http_build_query($getParams);
        ApiLogger::addLogVarExport("URL => $url");
//        echo $url . PHP_EOL;

        curl_setopt($curl, CURLOPT_URL, $url);
        // максимальное время выполнения скрипта
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        // отключаем проверку сертификата
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        // теперь curl вернет нам ответ, а не выведет
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // переменные, которые будут переданные по методу post
        if (!empty($postParams)) {
            // передаем данные по методу post
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postParams));
        }
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
