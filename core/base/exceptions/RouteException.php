<?php

namespace core\base\exceptions;

use core\base\controller\BaseMethods;

// Указываем что родительский класс Exception необходимо искать в глобальном пространстве имен
// т.к выше мы присвоили классу RouteException другое пространство имен
class RouteException extends \Exception
{
    use BaseMethods;

    protected $messages;

public function __construct($message ='',$code = 0)
{
    //вызвать метод родительского класса \Exception, в случа если мы не описали сообщение для
    parent::__construct($message,$code);
    
    //подключаем вывод ошибок пользователям
    $this->messages = include 'messages.php';
    //записываем сообщение если оно есть, если нет берем внутреннее
    $error = $this->getMessage() ? $this->getMessage() : $this->messages[$this->getCode()];

    $error .= "\r\n" . 'file' .$this->getFile() . "\r\n" . 'In Line ' . $this->getLine() ."\r\n";
    //если пользовательское сообщение существует, то вызываем его если его не передали, то 0 по дефолту
    // в переменную message записываем значение messages. В случае если значение существует
    // if(isset($this->messages[$this->getCode()])) $this->message = $this->messages[$this->getCode()];

    $this->writeLog($error);
    $this->writeLog($message, 'message_log');
}   

}
