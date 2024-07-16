<?php

namespace core\base\controller;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseController
{
    use \core\base\controller\BaseMethods;

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

            //получаем парпметры, инпут аутпут методы для передачи в соответствующий метод 
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

        $data = $this->$inputData();


        //Если метод outputData существует в нашем классе, то вызывем и передаем inputData
        if(method_exists($this, $outputData)) {
        //если метод возвращает данные, то сохраняем их, иначе берем данные из $yhis->page
            $page = $this->$outputData($data);
            if ($page) $this->page =$page;
        }   
        // Иначе(при условии что есть метод inputData) сохраняем результат работы в $this->page
        elseif ($data){
             $this->page = $data;
        }

        if($this->errors){
            $this->vritwLog();
        }

            $this -> getPage();
    }

// Шаблонизатор, метод собирающий страницу path путь 
// к шаблону parametrs массив данных для передачи в шаблон
    protected function render($path = '', $parametrs = []){
        // extract создаем переменные из массива ключ => Значение
        // переменные не отображаются в Debuger
        extract($parametrs);

        // если путь не пришел, то получаем название класса ищем файл с таким названием(обрезая контроллер) в teplates/default
        if(!$path){
            $class = new \ReflectionClass($this);
            //получаем путь к нашему контроллеру вытаскивая его из namespace 
            // и путь прописанный в настройках сравниваем их 
            // подключаем либо пользовательский либо админский путь 

            $space = str_replace('\\', '/', $class->getNamespaceName() . '\\');
            $routes = Settings::get('routes');

            if($space === $routes['user']['path']) $template =TEMPLATE;
                else $template = ADMIN_TEMPLATE; 

            $path =$template . explode ('controller', strtolower($class->getShortName()))[0]; 
            
            $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0];
        }
        
        //Создание буфера обмена
        ob_start();
        //Если не получается подключить файл выкидываем исключение
        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path . '.php');
        //Возвращаем и закрываем буфер обмена
        return ob_get_clean();

    }

    protected function getPage(){
        if(is_array($this->page)){
            foreach ($this->page as $block) echo $block;
        }else{
            echo $this->page;
        }
        exit();
    }
}