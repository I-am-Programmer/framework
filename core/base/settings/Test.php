<?php


namespace core\base\settings;

class Test{
    public $_instance;



    static public function gets ($property){
        return self::$_instance->myArgs();

    }

// Объявление свойств и методов класса статическими позволяет обращаться к ним без создания экземпляра класса. 
    static public function instance(){
        // self:: - ключевое свойство для обращения к статическим свойствам и методам внутри себя самого или к константам
        if(self::$_instance instanceof self){
            return self::$_instance;
        }
        // new self() или new self - создание экземпляра класса внутри себя самого
        return self::$_instance = new self;
    }

    
    public function myArgs(){
    $gArgs =[];
    $gArgs['name'] = $this->getArgs('sss');
    }



    public function getArgs(){
        $arrays = func_get_args();

    }
}