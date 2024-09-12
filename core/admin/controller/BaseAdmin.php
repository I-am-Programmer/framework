<?php

namespace core\admin\controller;

use core\admin\model\Model; 
use core\base\controller\BaseController;
use core\base\settings\Settings;
use core\base\exceptions\RouteException;


abstract class BaseAdmin extends BaseController{
    protected $model;
    protected $table;

    protected $columns;
    protected $menu;
    protected $title;

    protected function inputData(){
        //подключаем стили
        $this->init(true);

        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');


        $this->sendNocacheHeaders();

    }

    protected function outputData(){

    }

    protected function sendNoCacheHeaders(){
        // отключаем кеширование для разных браузеров
        header("Last-Modified: " . gmdate("D, d m  Y H:i:s" . "GMT"));
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0,pre-check=0");
    }

    //промежуточный метод нужный для доступа к свойствам inputData
    protected function exectBase(){
        self::inputData();
    }


    protected function createTableData(){
        if(!$this->table){
            if(isset($this->parameters)&&($this->parameters)) $this->table = array_keys($this->parameters)[0];
                else $this->table = Settings::get('defaultTable');
        }
        //если таблица пустая делаем запись в сообщениии пользователю или админу и записываем лог 
        if(!$this->columns) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
        //записываем информацию из таблицы 
        $this->columns =$this->model->showColumns($this->table);

    }


}