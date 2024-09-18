<?php

namespace core\admin\expansion;

use core\base\controller\Singleton;
use core\admin\controller\BaseAdmin;

class TestMyExpansion extends BaseAdmin
{
    use Singleton;

    public function expansion($args =[]){
        
        $a = $args->data;
        exit();
    }
    
}