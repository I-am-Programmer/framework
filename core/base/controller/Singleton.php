<?php 


namespace core\base\controller;





trait Singleton{


    static private $_instance;

    private function __construct(){

    }
    private function __clone(){

    }
    
   static public function instance(){

        if(self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::$_instance = new self;

        //проверяем существует ли свойство connect в нашем объекте(может быть унаследовано из абстактного класса BaseModel)
        //если существует, то автоматически вызываем его для подключения к БД
        if(method_exists(self::$_instance, 'connect')){
            self::$_instance->connect();
        }
        return self::$_instance;
    }

    

}