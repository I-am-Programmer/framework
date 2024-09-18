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
    protected $data;

    protected $menu;
    protected $title;

    protected function inputData(){
        //подключаем стили методом init родительского класса
        $this->init(true);

        $this->title = 'VG engine';

        //подключаем базовую модель и меню, если не определено раньше
        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');

        //отключаем кеширование
        $this->sendNocacheHeaders();

    }

    protected function outputData(){

    }

    protected function sendNoCacheHeaders(){
        // отключаем кеширование для разных браузеров
        header("Last-Modified: " . gmdate("D, d m  Y H:i:s") . "GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0,pre-check=0");
    }

    //промежуточный метод нужный для доступа к свойствам inputData
    protected function execBase(){
        self::inputData();
    }


    //Определяет с какой таблицы брать данные. Выбирает колонки из этой таблицы
    // получаем свойства $table и $columns
    protected function createTableData(){
        //если нет $table && то берем из URI полсе admin/controller
        //если и там ничего не пришло то берем дефолтное из настроек
        if(!$this->table){
            if(isset($this->parametrs)&&($this->parametrs)) $this->table = array_keys($this->parameters)[0];
                else $this->table = Settings::get('defaultTable');
        }

        //получаем название колонок и из свойства из таблицы
        $this->columns =$this->model->showColumns($this->table);
        //если $columns пустая делаем запись в сообщениии пользователю или админу и записываем лог и прекращаем исполнение кода
        if(!$this->columns) throw new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
    }


    protected function expansion($args = []){
        // разделяем строку на массв по разделителю _
        $this->table = 'test_my';
        $filename = explode('_',$this->table);
        $className = '';

        //записываем в $className наименование таблицы с больших букв. В формате TestMy
        foreach($filename as $item) $className .= ucfirst($item);
        
        // получаем строку вида "core/admin/expansion/TestMyExpansion"
        $class = Settings::get('expansion') . $className . 'Expansion';
        //если читабелен файл. По полному пути -"/Applications/MAMP/htdocs/framework/core/admin/expansion/TestMyExpansion.php"
        $a = $_SERVER['DOCUMENT_ROOT'] . PATH. $class . '.php';
        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH. $class . '.php')){
            $class = str_replace('/', '\\', $class);
            //формируем доступ к классу через сингллтон(для экономии памяти)
            $exp = $class::instance();
            $res = $exp->expansion($this);
        }else{
            exit('нет такого расширения');
        }

    }

}