<?php

namespace core\admin\controller;


class ShowController extends BaseAdmin{
    protected function inputData(){
        $this->execBase();
        // получаем свойства $table и $columns
        $this->createTableData();
       
        //по умолчанию создет массив $data с id,img,name,parent_id
        //можно добавить еще столбцы ['fields'=> ['content']], и воспользоваться вторым аргументом если нужены только передаваемые параметры 
        $this->createData(['fields'=> ['content','something' ]], false);
        exit();
    }

    protected function outputData(){

    }
}