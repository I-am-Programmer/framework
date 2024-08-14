<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{   

   
    protected function InputData(){
        
        $db = Model::instance();

        $table = 'teachers';
        $res = $db->read($table,[
            'fields' => ['id', 'name'],
            'operand' => ['%LIKE%','<>'],
            // 'condition' => ['OR', 'AND'],
            'order' => ['name'],
            'order_direction' => ['DESC'],
            'limit' => '1',
            'join'=> [
                'join_table1'=>[
                    'table'=> 'join_table1',
                    'fields'=> ['id AS j_id', 'name AS j_name'],
                    'type' => 'left',

                    'operand' => ['='],
                    'condition' => ['OR'],
                    //признак присоеднинения
                    'on' =>[
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                    ],
                    [
                    'table'=> 'join_table2',
                    'fields'=> ['id AS j2_id', 'name2 AS j2_name'],
                    'type' => 'left',
                    'where' => ['name'=>'Sasha'],
                        'operand' => ['='],
                        'condition' => ['OR'],
                    'on' =>['id', 'parent_id']
                    ]
                ]
            ]);

        exit('I am admin panel');
    }




    protected function OutputData(){
        exit();
        
    }










     

   
}