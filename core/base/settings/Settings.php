<?php

namespace core\base\settings;

use core\base\controller\Singletone;

class Settings
{
    use Singletone;
    
    
    private $templateArr = [
        'text'=>['name1'=>1]
    ];
    // Массив маршрутов 
    private $routes = [
        'admin'=> [
            'alias' => 'admin',
            'path' => 'core/admin/controller/',
            'hrUrl' => false,
            'routes' => [
                'product'=> 'goods/getGoods/sale'
            ]
        ],

        'settings' => [ 
            'path'=> 'core/base/settings/'
        ],
        'plugins' => [
            'path' =>'core/plugins/',
            'hrUrl' => false,
            'dir' => false
        ],
        'user' => [
            'path' => 'core/user/controller/',
            'hrUrl' => true,
            'routes' => [
                'site' => 'index/hello'
            ]
        ],
        'default' => [
            'controller' => 'indexController',
            'inputMethod' => 'InputData',
            'outputMethod' => 'OutputData'
        ],

        'p' => [1,2,3] 
    ];


    

    private $projectTables =[
        'key'=>'value'
    ];

    private $defaultTable = 'teacher';



    // Констуктор и клон помогают с приватным доступом позволяют избежать обращений к данному классу и создания копии из вне
    

    static public function get ($property){

        return self::instance()->$property;

    }
//     public function __get($property){
//         return $this->$property = null;
//  }

// Объявление свойств и методов класса статическими позволяет обращаться к ним без создания экземпляра класса. 
    










     


    public function clueProprties($class){

        $baseProperties = [];

        foreach($this as  $name => $item){
            $property = $class::get($name);

            if(is_array($property) && is_array($item)){
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                
            }

            if(!$property) $baseProperties[$name] = $this->$name;
        }

            return $baseProperties;
    }

    public function arrayMergeRecursive(){
        $arrays = func_get_args();
        $base = array_shift($arrays);
        foreach ($arrays as $array){
            foreach($array as $key => $value){
//идем по массиву ShopSettings рекурсивно 
            if (is_array($value) && isset($base[$key]) &&is_array($base[$key])){ 
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                }else{   
                    if(is_int($key)){
// при отсутсвии в базовом массиве нашего значения, добавляем его
                        if(!in_array($value, $base)) array_push($base, $value);
                        continue;
                    }
    // в случае если строка, заменяем базовое значение на значение плагина
    // при отсутствии значения так же добавляем его в плагин 
                    $base[$key] = $value;
                }

            }
        }
 
        return $base;
    }
    
}




