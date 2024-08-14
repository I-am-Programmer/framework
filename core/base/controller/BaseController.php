<?php

namespace core\base\controller;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseController
{
    //Добавляем пространство имен трейта(можно обращаться к свойствам и методам через $this->method())
    use \core\base\controller\BaseMethods;

    protected $page;
    protected $errors;
    protected $styles;
    protected $scripts;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parametrs;
    
    // записываем controller из адресной строки
    public function route(){
        $controller = str_replace('/', '\\', $this->controller);

        // используем расширение reflection выброса исключений, создаем объект $controller
        // вызываем метод request
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


        //Если метод outputData существует в нашем классе(и не возвращается пустым), 
        // то вызывем его и передаем в него inputData
        if(method_exists($this, $outputData)) {
        //если метод возвращает данные, то сохраняем их, иначе берем данные из $this->page
            $page = $this->$outputData($data);
            if ($page) $this->page =$page;
        }   
        // Иначе(при условии что inputData что то возвращает) сохраняем результат работы в $this->page
        elseif ($data){
             $this->page = $data;
        }

        if($this->errors){
            $this->writeLog($this->errors);
        }

            $this -> getPage();
    }

    // Шаблонизатор, метод собирающий страницу. передаем 
    //path - путь к шаблону, parametrs - массив данных для передачи в шаблон
    protected function render($path = '', $parametrs = []){
        // extract создаем переменные из массива ключ => Значение

        extract($parametrs);

        // если путь не пришел, то получаем название класса ищем файл с таким названием(обрезая контроллер) в teplates/default
        if(!$path){
            $class = new \ReflectionClass($this);
            //получаем путь к нашему контроллеру вытаскивая его из namespace в переменную $space
            // и путь прописанный в настройках ($path) сравниваем их 
            // подключаем либо пользовательский либо админский путь
            // (в зависимости от того, какой роутинг пришел)

            $space = str_replace('\\', '/', $class->getNamespaceName() . '\\');
            $routes = Settings::get('routes');

            //если мы находимся под админкой, то подключаем дефолтные контроллеры из админки 
            if($space === $routes['user']['path']) $template = TEMPLATE;
                else $template = ADMIN_TEMPLATE; 
            
            $path =$template . explode ('controller', strtolower($class->getShortName()))[0]; 
        }
        
        //Создание буфера обмена. Возвращает шаблон
        ob_start();
        //Если не получается подключить файл выкидываем исключение
        if(!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - '.$path . '.php');
        //Возвращаем и закрываем буфер обмена
        return ob_get_clean();

    }
    //выводим полученный результат на страницу
    protected function getPage(){
        if(is_array($this->page)){
            foreach ($this->page as $block) echo $block;
        }else{
            echo $this->page;
        }
        exit();
    }

    

    // подключаем стили и скрипты исходя из констант в internal_settings(пользовательские или админский)
    protected function init($admin = false){

        if(!$admin){
            if(USER_CSS_JS['styles']){
                foreach(USER_CSS_JS['styles'] as $item) $this->styles[] = PATH . TEMPLATE . trim($item, '/');
            }
            if(USER_CSS_JS['scripts']){
                foreach(USER_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
            }
        }else{
            if(ADMIN_CSS_JS['styles']){
                foreach(USER_CSS_JS['styles'] as $item) $this->styles[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }
            if(ADMIN_CSS_JS['scripts']){
                foreach(USER_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }
        }

    }
}