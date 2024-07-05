<?php


namespace core\base\settings;
use core\base\settings\Settings;

// Файлы плагинов называются с большой буквы, начинается с названия плагина
class ShopSettings{
    

    static private $_instance;
//Ссылка на объект класса Settings
    private $baseSettings;

    // private $routes = [
    //     'admin'=> [
    //         'name' => 'sudo'
    //     ]
    //     ];

    private $templaaateArr = [
        'text1'=>['name1'=>'value1'], 'name2', 'name3', 'name4',
        'textArea'=>['content']
    ];
    
// С помощью метода instance передаем информацию о свойстве 
    static public function get ($property){
        return self::instance()->$property;
    }
// Ловим несуществующие свойства, для избежания ошибки 
    public function __get($property){
        return $this->$property = null;
 }


 

// Возвращает свойство объекта - созданного
// Если объект не создан, создает
    static public function instance(){
        if(self::$_instance instanceof self){
            return self::$_instance;
        }

        self::$_instance = new self;
// В свойство baseSettings помещаем ссылку на объект класса Settings, если объект еще не существует то он создается
        self::$_instance->baseSettings = Settings::instance();
// В переменную baseProperties записываем результат работы меотода
// Из Объекта в baseSettings, вызываем clueProprties и передаем ему ИМЯ текущего класса
        $baseProperties = self::$_instance->baseSettings->clueProprties(get_class());
        self::$_instance->setProperty($baseProperties);
        return self::$_instance;
    }

//распаковывает свойства в объектПлагина из полученного массива 
    protected function setProperty($properties){
        if($properties){
            foreach($properties as $name => $property){
                $this->$name = $property;
            }
        }
    }

    private function __construct(){

    }
    private function __clone(){

    }
}