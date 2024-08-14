<?php

namespace core\user\controller;

use core\base\controller\BaseController;

class IndexController extends BaseController
{   


    protected $name;
   


    protected function InputData(){
        $this->init();
        exit('i am User');
    }




    protected function OutputData(){
        $items = func_get_arg(0);
        return $this->render('', $items);
        
    }










     

   
}