<?php

defined('VG_ACCESS') or die('Access deny');

const TEMPLATE = 'templates/default/';
const ADMIN_TEMPLATE = 'core/admin/views/';




date_default_timezone_set("Europe/Moscow");
// Для возможности принудительного перелогирования(Сбрасывания куки)
const COOKIE_VERSION = '1.0.0';
// Ключ шифрования для coocie файлов
const CRYPT_KEY = '';
// Время для сброса куки для админов 
const COOKIE_TIME = '60';
// Для защиты от подбора пароля 3 попытки на ввод правильного пароля
const BLOCK_TIME = 3;

// Кол-во товаров отображаемых на странице
const QTY = 8;
// колличество ссылок постраничной навигации
const QTY_LIST = 3;

// подключение css и js
const ADMIN_CSS_JS = [
    'styles' => [],
    'scripts' => []
];

const USER_CSS_JS = [
    'styles' => ['/css/style.css/'],
    'scripts' => []
];

use core\base\exceptions\RouteException;
// 1. Функция служит для загрузки в функцию spl_autoload_register. Выполняет поиск классов в пространстве имен
// 2. str_replace ищет обратный слеш, заменяет его на обычный слеш в переменной $class_name ()
function autoloadMainClasses($class_name){
    $class_name = str_replace('\\', '/', $class_name);
// проводится проверка существует ли такой класс. Выбрасывает исключение если такого класса нет
    if(!@include_once $class_name . '.php'){
        throw new RouteException('Нет такого файла для подключения - ' .$class_name );
    }
}

// Регистрирует функции загрузчики класса и ставит их в очередь 
// (Проще говоря сама подгружает классы)
spl_autoload_register('autoloadMainClasses');