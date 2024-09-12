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


    
    protected function createData($arr = [], $add = true){
    $fields = [];
    $order = [];
    $order_direction = [];
    
    if($add){
        if(!$this->columns['id_row'])return $this->data = [];
        // Как бы не назывался наш приватный ключ, записываем его как id
        $fields[] = $this->columns['id_row'] . ' as id';
        if(isset($this->columns['name'])) $fields['name'] = 'name';
        if(isset($this->columns['img'])) $fields['img'] = 'img';

        //если name или img не пришли. Проверяем есть ли столбец содержащий name или столбец начинающийся с img. Если есть записываем в fields как name или img
                if(count($fields)<3){
            foreach($this->columns as $key => $item){
                if(!isset($fields['name']) && strpos($key, 'name')!==false){
                    $fields['name'] = $key . ' as name';
                }
                if(!isset($fields['img']) && strpos($key, 'img')===0){
                    $fields['img'] = $key . ' as img';
                }
            }
        }
        //если пришел $arr то мы должны склеить его с текущим массивом
        if(isset($arr['fields']) && $arr['fields']){
            $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
        }
        //если в таблице есть table_id а в $fields нету, то добавляем его туда 
        if(isset($this->columns['parent_id']) && $this->columns['parent_id']){
            if(!in_array('parent_id', $fields)) $fields[] = 'parent_id';
            $order[] = 'parent_id';
        }
        if(isset($this->columns['menu_position'])&&$this->columns['menu_position'])$order[] = 'menu_position';
        elseif(isset($this->columns['date'])&& $this->columns['date']){
            if($order) $order_direction = ['ASC','DESC'];
            else $order_direction[]=['DESC'];

            $order[] = 'date';
        }
        if(isset($arr['order'])&& $arr['order']){
            $order = Settings::instace()->arrayMergeRecutsive($order, $arr['order']);
        }
        if(isset($arr['order_dirrection'])&& $arr['order_dirrection']){
            $order_dirrection = Settings::instace()->arrayMergeRecutsive($order_dirrection, $arr['order_dirrection']);
        }
    }else{
        //если мы не передали массив и $add =false то возвращаем пустую data
        if(!$arr)return $this->data = [];
        // если не добавление а получение нового результата
        $fields = isset($arr['fields'])? $arr['fields']: null;
        $order = isset($arr['order'])? $arr['order'] : null;
        $order_direction = isset($arr['order_direction'])?$arr['order_direction'] : null;
    }


    $this->data = $this->model->read($this->table,[
        'fields'=> $fields,
        'order'=> $order,
        'order_direction'=>$order_direction
    ]);

    }

}