<?php


namespace core\base\model;
use core\base\controller\Singletone;
use core\base\exceptions\DbException;

class BaseModel extends BaseModelMethods{

    use Singletone;

    protected $db;

    private function __construct(){
        //выполняем проверку что подключение подключение к БД успешно, иначе заканчиваем выполнение 
        try{
            $this->db = @new \mysqli(HOST, USER, PASSWD, DB_NAME);
            $this->db->query("SET NAMES UTF8");
        }catch (\mysqli_sql_exception $e) {
            throw new DbException('Ошибка подключения к базе данных: ' . $e->getMessage());
        }
        $this->db->query("SET NAMES UTF8");
    }


    // string $crud= c - CREATE/ r - SELECT / u - UPDATE / d - DELETE  
    final public function query($query, $crud ='r', $retutn_id = false){
        try{
                $result = $this->db->query($query);

        }catch(\mysqli_sql_exception $e){
            throw new DbException('Ошибка в SQL запросе: ' 
            . $query .  ' - ' . $this->db->errno . ' ' . $this->db->error
            );
        }
       
        switch ($crud){
            case 'r':
                //если количество строк ответа SQL не равно 0
                //num_rows - передает колличество записей
                if($result->num_rows){
                    $res = [];
                    //проходимся по всем запрашиваемым записям и возвращаем их
                    //fetch_assoc - вырезает 0-ую запись из ответа БД (передавая ее в $res)
                    for($i=0; $i<$result->num_rows; $i++){
                        $res[] = $result->fetch_assoc();
                    }

                    return $res;
                }
                return false;

                break;
            case 'c':

                if($return_id) return $this->db->insert_id;

                return true;
                break;

            default:
                return true;
                break;

        }
        
    }
    // $res = $db->read($table,[
    //     'fields' => ['id', 'name'],
    //     'where' => ['name'=> 'Olya, Sveta', 'surname'=> 'Sergeevna','secondname'=> 'Rodionova', 'car'=>'Porshe'],
    //     'operand' => ['IN','LIKE%','<>', '='],
    //     'condition' => ['OR', 'AND'],
    //     'order' => ['fio', 'name'],
    //     'order_direction' => ['DESC'],
    //     'limit' => '1',
    //     'join'=> [
    //         'join_table1'=>[
    //             'table'=> 'join_table',
    //             'fields'=> ['id as j_id', 'name as j_name'],
    //             'type' => 'left',
    //             'where' => ['name'=>'Sasha'],
    //             'operand' => ['='],
    //             'condition' => ['OR'],
    //             'on' =>['id', 'parent_id'],
    //             'group_condition => 'AND'
    //             ],

    //             ['join_table2'=>[
    //             'table'=> 'join_table2',
    //             'fields'=> ['id as j2_id', 'name2 as j2_name'],
    //             'type' => 'left',
    //             'where' => ['name'=>'Sasha'],
    //             'operand' => ['='],
    //             'condition' => ['OR'],
    //             'on' =>[
    //                 'table' => 'teachers',
    //                 'fields' => ['id', 'parent_id']
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ]);
    // final` означает, что метод нельзя переопределить в наследниках этого класса
    final public function read($table, $set=[]){
        $fields = $this->createFields($set,$table);
        $order = $this->createOrder($set, $table);
        $where = $this->createWhere($set, $table);

        if(!$where) $new_where = true;
        else $new_where = false;
        $join_arr = $this->createJoin($set, $table, $new_where);
        //формируем поля. Возвращает строку вида "teachers.id,teachers.name," 
        $fields .= $join_arr['fields'];
        
        $join = $join_arr['join'];
        $where .= $join_arr['where'];

        $fields = rtrim($fields, ',');

        $limit = $set['limit'] ? 'LIMIT ' .$set['limit'] : '';
        //Формирование запроса
        $query = "SELECT $fields FROM $table $join $where $order $limit";
        return $this->query($query);
    }

    
  
}
