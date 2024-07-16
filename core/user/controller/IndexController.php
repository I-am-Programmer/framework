<?php

namespace core\user\controller;

use core\base\controller\BaseController;

class IndexController extends BaseController
{   


    protected $name;
   

    protected function InputData(){
        // подключение стилей css
        $this->init();
        exit();

    }










     

    public function outputdata(){
            $vars = func_get_arg(0);
            //выводим информацию из буфера обмена 
            $this->page = $this->render(TEMPLATE . 'template', $vars);
    }
}