<?php

namespace core\base\exceptions;

// Указываем что родительский класс Exception необходимо искать в глобальном пространстве имен
// т.к выше мы присвоили классу RouteException другое пространство имен
class RouteException extends \Exception
{
        
}
