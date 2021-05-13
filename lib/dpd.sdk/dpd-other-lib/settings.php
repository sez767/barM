<?php

error_reporting(E_ALL | E_ERROR);
ini_set('display_errors', 'On');

$MY_NUMBER = 'учётная запись';
$MY_KEY = 'ключ учётной запиcи';

$server = array(
    1 => 'http://ws.dpd.ru/services/', //обычный сервер
    0 => 'http://wstest.dpd.ru/services/' //тестовый сервер
);
