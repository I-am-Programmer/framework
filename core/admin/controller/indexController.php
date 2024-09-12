<?php

namespace core\admin\controller;
use core\admin\controller\ShowController;

use core\base\controller\BaseController;;
// use core\admin\model\Model;
use core\base\settings\Settings;

class IndexController extends BaseController
{   

   
    protected function InputData(){
        // Папка в которой находится фреймворк/Путь к нашей адинке 
        // Перенаправляем наш запрос на контроллер /show. Получаем строку /admin/show для передачи в метод редиректа 
        $redirect = PATH.Settings::get('routes')['admin']['alias'] . '/show';


        $this->redirect($redirect);
    }






    










     

   
}