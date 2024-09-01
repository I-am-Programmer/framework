<?php

namespace core\base\controller;

trait BaseMethods{
    

    protected function clearStr($str){
        if(is_array($str)) {
            foreach ($str as $key => $item) $str[$key] = trim(strip_tags($item));
            return $str;
        }else{
            return trim(strip_tags($str));
        }
    }

    //выполняем перевод из строкового типа в числовой(если пришло число с типом - строка)
    protected function clearNum($num){
         if(is_numeric($num)) return $num*1;
         return $num;
    }

   
    protected function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    
    protected function isAjax(){
        return isset($_SERVER['HTTP_X_REQESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    //если придет 301 код страницы, то при помощи функции header отправляем браузеру нужный заголовок 
    protected function redirect($http =false, $code=false){
        if($code){
            $codes = [
                '301' => 'HTTP/1.1 Move Permanently'
            ];
                if($codes[$code]) header($codes[$code]);
        }
        //если придет http, то при помощи функции header отправляем браузеру нужный заголовок 
        if($http) $redirect = $http;
            
            // если пользователь перешел со странице нашего сайта то редирект, иначе направляем на главную страницу
            else $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;
            // перенаправляем на нужный адрес
            header('Location:' . $redirect);

            exit;
    }
    
    protected function writeLog($message, $file='log.txt', $event = 'Fault'){
        
        $dateTime = new \DateTime();
        // тип событие+дата : время - ошибка 
        $str = $event . ': ' . $dateTime->format('d-m-Y G:i:s') . ' - ' . $message . "\r\n";
        //в файл - 'log/'.$file, передаем $str, 
        //`FILE_APPEND`: Флаг, указывающий, что нужно добавить данные в конец файла, а не перезаписать его.

        file_put_contents('log/' . $file, $str, FILE_APPEND);
    }
}