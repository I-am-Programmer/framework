<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController
{   

   
    protected function InputData(){
        
        $db = Model::instance();


        $table = 'teachers';

        $files['gallery_img'] = ["olya.jpg"];
        $files['img'] = ["Main_Olya.jpg"];
        
        
        $res = $db->update($table, [
            // 'all_rows' => ['no'],
            'fields' => ['name' =>'ddddd', 'content' => 'CoddddddntentOlya'],
            'files' => $files,
            
            'where' => ['id'=> [43, 44]],
            'operand' => ['IN']
        ]);

        // exit('id=' . $res['id'] . ' Name= ' .$res['name']);


            

        exit('I am admin panel');
    }




    protected function OutputData(){
        exit();
        
    }










     

   
}