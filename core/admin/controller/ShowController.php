<?php

namespace core\admin\controller;

use core\base\settings\Settings;


class ShowController extends BaseAdmin{
    protected function inputData(){
        $this->execBase();
        // получаем свойства $table и $columns
        $this->createTableData();
       
        //по умолчанию создет массив $data с id,img,name,parent_id
        //можно добавить еще столбцы ['fields'=> ['content']]
        $this->createData(['fields'=> 'content']);
        
        return $this->expansion(get_defined_vars());

    }

    protected function outputData(){

    }

    protected function createData($arr = []){
        $fields = [];
        $order = [];
        $order_direction = [];
        
        if(!$this->columns['id_row'])return $this->data = [];
        // Как бы не назывался наш приватный ключ, записываем его как id

        $fields[]=$this->columns['id_row'] . ' as id';
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
        if(isset($arr['fields'])){
            if(is_array($arr['fields'])){
                $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
            }else{
                $fields[] = $arr['fields'];
            }
        }
        //если в таблице есть table_id а в $fields нету, то добавляем его туда 
        if(isset($this->columns['parent_id']) && $this->columns['parent_id']){
            if(!in_array('parent_id', $fields)) $fields[] = 'parent_id';
            $order[] = 'parent_id';
        }
        if(isset($this->columns['menu_position'])&&$this->columns['menu_position'])$order[] = 'menu_position';
        elseif(isset($this->columns['date'])&& $this->columns['date']){
            if($order) $order_direction = ['ASC','DESC'];
            else $order_direction[]='DESC';

            $order[] = 'date';
        }
        if(isset($arr['order'])&& $arr['order']){
            $order = Settings::instace()->arrayMergeRecutsive($order, $arr['order']);
        }
        if(isset($arr['order_dirrection'])&& $arr['order_dirrection']){
            if(is_array($arr['order_dirrection'])){
                $order_dirrection = Settings::instace()->arrayMergeRecutsive($order_dirrection, $arr['order_dirrection']);
            }else{
                $order_direction[]=$arr['order_dirrection'];
            }
        }
        


        $this->data = $this->model->read($this->table,[
            'fields'=> $fields,
            'order'=> $order,
            'order_direction'=>$order_direction
        ]);
    }
}