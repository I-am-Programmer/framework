<?php

// Константа используется для запрета прямомого доступа к файлам все доступы идут через index.php
// Тем самым создается единая точка входа на сайт
define('VG_ACCESS', true);


// Заголовки всегда отправляются в начале (до вывода информации пользователю)
header ('Content-Type:text/html;charset=utf-8');

// Временные файлы создающиеся на стороне сервера
// 1  Сессия начинается когда мы открыли ссайт и заканчивается, когда мы закрыли браузер(не вкладку, а именно браузер)
session_start();

// В config.php будут храниться базовые настройки для быстрого развертывания сайта на хостинге
// (В случае чего можно поменять настроки и развернуть на другом хостинге)
require_once 'config.php';

// В internal_settings.php будут храниться фундаментальные настройки. Пути к шаблонам, настройки безопасности и т.п.
require_once 'core/base/settings/internal_settings.php';
require_once 'libraries/functions.php';



// подключающий пространство имен, с помощью функции autoloadMainClasses 
// в internal_settings 
use core\base\exceptions\RouteException;  
use core\base\exceptions\DbException;  
use core\base\controller\RouteController;  
use core\base\controller\Singletone;  


// Для отлавливания исключения, нам необходимо создать соответствующий класс дочерний от \Exception
try{
    //запуск всего кода создание выполняется код из RoutController после BaseController(родителя) посредством вызова у него route
   RouteController::instance()->route();
   
}
catch(RouteException $e){
    //выводит переменную message из объекта RouteException, наследника Exception
    exit($e->getMessage());
}
catch(DbException $e){
    //выводит переменную message из объекта RouteException, наследника Exception
    exit($e->getMessage());
}
