<?php 

namespace core\base\controllers;

use core\base\settings\Test;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;



// Модель singleton используется для использование только одного объекта класса.
// При попытке создать объект повторно будет ссылаться на уже созданный объект 
class RouteController{
    
    static private $_instance;
    
    private function __clone()
    {
    }

    


    static public function getInstance(){
        if(self::$_instance instanceof self){
        return self::$_instance;
        }
        return self::$_instance = new self;
    }


    private function __construct()
    {
        $s = Settings::get('templateArr');

        $s1 = ShopSettings::get('templateArr');

        exit();
    }


    
}