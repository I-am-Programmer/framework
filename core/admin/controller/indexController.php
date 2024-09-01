<?php

namespace core\admin\controller;
use core\admin\controller\ShowController;

use core\base\controller\BaseController;;
use core\admin\model\Model;
use core\base\settings\Settings;

class IndexController extends BaseController
{   

   
    protected function InputData(){
        
        $redirect = PATH.Settings::get('routes')['admin']['alias'] . '/show';
        $this->redirect($redirect);
    }




    protected function OutputData(){
        exit();
        
    }

    










     

   
}