<?php 

namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Test;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;



// Модель singleton используется для использование только одного объекта класса.
// При попытке создать объект повторно будет ссылаться на уже созданный объект 
class RouteController extends BaseController {
    
    static private $_instance;
    protected $routes;

    


    protected $redirect;
    // protected $parametrs;

   




    private function __clone()
    {
    }

    

    // проверяем создан ли объект, если объект создан то 
    // новый не создаем новый объект а обращаемся к существующему
    static public function getInstance(){
        if(self::$_instance instanceof self){
        return self::$_instance;
        }
        return self::$_instance = new self;
    }







    private function __construct()
    {
        
        $address_str = $_SERVER['REQUEST_URI'];
        $server = $_SERVER;
        //если запрос заканчивается на / делаем запись в redirect
        if((strrpos($address_str, '/') === strlen($address_str) -1) && (strrpos($address_str, '/') !== 0)){
                  
            $this->redirect(rtrim($address_str, '/'), 301);
        }
        //проверяем что фреймворк расположен в нужной папке === константе PATH// Иначе вызываем исключение
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));
        if($path === PATH){
            $this->routes = Settings::get('routes');
            if(!$this->routes)throw new RouteException('Сайт находится на техническом обслуживании');
            //разбиваем полученную строку на массив
            $url = explode('/', substr($address_str, strlen(PATH)));

            if($url[0] && $url[0] === $this->routes['admin']['alias']){
                $plugin =  array_shift($url);

                /* Админка */
                ////разбиваем полученную строку на массив вырезая admin
                $url = explode('/', substr($address_str, strlen(PATH . $this->routes['admin']['alias'])+1));
        
                //Подключение плагина
                if($url[0] && is_dir($_SERVER['DOCUMENT_ROOT'] .  PATH . $this-> routes['plugins']['path'].$url[0])){
                    $plugin = array_shift($url);
                    $pluginSettings = $this->routes['settings']['path'] . ucfirst( $plugin . 'Settings');
                    
                    
                    if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')){
                        
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes=$pluginSettings::get('routes');

                        
                    }
                    
                    $dir = $this->routes['plugins']['dir'] ? '/' .$this->routes['plugins']['dir'] . '/' : '/';
                    $dir = str_replace('//', '/', $dir);


                    $this -> controller = $this-> routes['plugins']['path'] . $plugin . $dir;
                    
                    $hrUrl=$this->routes['plugins']['hrUrl'];

                    $route ='plugins';

                    

                }else{

                    $this->controller = $this-> routes['admin']['path'];

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';


                }

                //конец Админки

            }else{              
                
                $hrUrl = $this->routes['user']['hrUrl'];
                
                $this -> controller = $this->routes['user']['path'];

                $route = 'user';
            }




                
            $this->createRoute($route, $url);
            
            
            if(isset($url[1])&& $url[1]){
                $count = count($url);
                $key = '';

                if(!$hrUrl){
                    $i = 1;
                }else{
                    $this->parametrs['alias'] = $url[1];
                    $i =2;
                }

                for( ; $i<$count;$i++){
                    if(!$key){
                        $key =$url[$i];
                        $this->parametrs[$key] = '';

                    }else{
                        $this->parametrs[$key] = $url[$i];
                        $key = '';
                    }
                }
            }

            

        }else{
            try{
                throw new \Exception('Не корректная дирректория ссайта');
            }
            catch(\Exception $e){
                exit ($e->getMessage());
            }
        }   
    }

    private function createRoute($var, $arr){
        $route = [];
        $a = $this->routes[$var]['routes'];
        
        if(!empty($arr[0])){
            if(isset($this->routes[$var]['routes'][$arr[0]])){
               
                
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);

                $this->controller .= ucfirst($route[0] . 'Controller');
            }else{
                $this->controller .= ucfirst($arr[0] . 'Controller');
            }
         }else{
            //
            $this->controller .= $this->routes['default']['controller'];
         }     
         

         $this->inputMethod = isset($route[1]) && $route[1] ? $route[1] : $this->routes['default']['inputMethod'];
         $this->outputMethod =isset($route[2]) && $route[2] ? $route[2] : $this->routes['default']['outputMethod'];

         return;
    }

   

}