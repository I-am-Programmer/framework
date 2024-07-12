<?php

namespace core\base\controller;
use core\base\exceptions\RouteException;

abstract class BaseController
{

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parametrs;
    

    public function route(){
        $controller = str_replace('/', '\\', $this->controller);



// используем расширение reflection для проверки класса 
        try{
            $object = new \ReflectionMethod($controller, 'request');


            $args = [
                'parametrs' => $this->parametrs,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];

            $object -> invoke(new $controller, $args); 
        }
        catch(\ReflectionException $e){
            throw new RouteException($e);
        }
    }

  


    public function request($args){
        $this->parametrs = $args['parametrs'];

        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $this->$inputData();
        $this->page = $this->$outputData();


        if($this->errors){
            $this->vritwLog();
        }

        $this -> getPage();
    }


    protected function render($path = '', $parametrs = []){

        extract($parametrs);


        if(!$path){
                $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0];
        }
        

        ob_start();

        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path);

        return ob_get_clean();

    }

    protected function getPage(){
        exit ($this->page);
    }


}