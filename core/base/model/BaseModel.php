<?php


namespace core\base\model;

use core\base\exceptions\DbException;
// Все модели настледуются от этого абстрактного класса. 
abstract class BaseModel extends BaseModelMethods{

    protected $db;

    protected function connect(){
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
    //подключаем mysqli_sql_exception для отлавливания проблем с БД
    final public function query($query, $crud ='r', $return_id = false){
        try{
                $result = $this->db->query($query);

        }catch(\mysqli_sql_exception $e){
            throw new DbException('Ошибка в SQL запросе: ' 
            . $query .  ' - ' . $this->db->errno . ' ' . $this->db->error
            );
        }
       // сформировав звапрос с помощью crud определяем что с ним делать дальше

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
                if(isset($return_id)&& ($return_id)) return $this->db->insert_id;
                echo($query);
                return true;
                break;
                
            case 'u':
                echo($query);
                return true;
                break;

            default:
                return true;
                break;

            

            

        }
        
        
    }
    //      @param array $set
    //     $res = $db->read($table,[
        // 'fields' => ['id', 'name'],
        // 'operand' => ['%LIKE%','<>'],
        // // 'condition' => ['OR', 'AND'],
        // 'order' => ['name'],
        // 'order_direction' => ['DESC'],
        // 'limit' => '1',
        // 'join'=> [
        //     'join_table1'=>[
        //         'table'=> 'join_table1',
        //         'fields'=> ['id AS j_id', 'name AS j_name'],
        //         'type' => 'left',

        //         'operand' => ['='],
        //         'condition' => ['OR'],
        //         //признак присоеднинения
        //         'on' =>[
        //             'table' => 'teachers',
        //             'fields' => ['id', 'parent_id']
        //         ]
        //         ],
        //         [
        //         'table'=> 'join_table2',
        //         'fields'=> ['id AS j2_id', 'name2 AS j2_name'],
        //         'type' => 'left',
        //         'where' => ['name'=>'Sasha'],
        //             'operand' => ['='],
        //             'condition' => ['OR'],
        //         'on' =>['id', 'parent_id']
        //         ]
        //     ]
        // ]);
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

        $limit = isset($set['limit']) && $set['limit'] ? 'LIMIT ' .$set['limit'] : '';
        //Формирование запроса
        $query = "SELECT $fields FROM $table $join $where $order $limit";
        return $this->query($query);
    }

    /**
     
     * @param array $set - массив параметров:
     * fields => [поле => значение]; - если не указан, то обрабатывается $_POST[поле => значение]
     * разрешена передача NOW() в качестве MySql функции обычной строкой
     * files => [поле => значение]; - можно подать массив вида [поле => [массив значений]]. Для хранения файлов изображений
     * except => ['исключение 1', 'исключение 2'] - исключает данные элементы массива из добавленных в запрос(работает только с необязательными полями)
     * return_id => true | false - возвращать или нет идентификатор вставленной записи
     * 
     * 
     *@return mixed
     */
    final public function create($table, $set = []){
        //проверяем какие поля пришли, если поля нет выставляем false
        $set['fields'] = isset($set['fields'])&&is_array($set['fields']) && !empty($set['fields'])
        ? $set['fields']: $_POST;
        $set['files'] = isset($set['files'])&&is_array($set['files']) && !empty($set['files'])
        ? $set['files']: false;

        if(!$set['fields'] && !$set['files']) return false;

        $set['return_id'] = isset($set['return_id'])&& $set['return_id'] ? true : false;
        $set['except'] = isset($set['except']) && is_array($set['except']) && !empty($set['except'])? $set['except']:false;

        //передаем параметры методу createInsert(лежащему в BaseModelMethods), для правильного формирования полей и значений 
        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);
        if($insert_arr){
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES ({$insert_arr['values']})";
            // exit($this->query($query,'c',$set['return_id']));
            return $this->query($query,'c',$set['return_id']);
        }
        return false;
        
    }
    // @param array $set
    // all_rows - использкется в значении true для перезаписи всехстолбцов в таблице
    // fields => [поле => значение]; - если не указан, то обрабатывается $_POST[поле => значение]
    // files => [поле => значение]; - можно подать массив вида [поле => [массив значений]]. Для хранения файлов изображений
    // except => ['исключение 1', 'исключение 2'] - исключает данные элементы массива из добавленных в запрос(работает только с необязательными полями)
    // 'where' => ['id'=> 43] - для изменения конкретных строк

    final public function update($table, $set = []){
        //если не передаем значение в $set['fields] то берем значение из $_POST
        $set['fields'] = isset($set['fields'])&&is_array($set['fields']) && !empty($set['fields'])
        ? $set['fields']: $_POST;
        $set['files'] = isset($set['files'])&&is_array($set['files']) && !empty($set['files'])
        ? $set['files']: false;

        //если пришло хоть что то $set['fields'] или $set['fields'] или значение в $_POST продолжаем 
        if(!$set['fields'] && !$set['files']) return false;

        
        $set['except'] = isset($set['except']) && is_array($set['except']) && !empty($set['except'])? $set['except']:false;

       
        // Изменение записи в определенных строках можно записать в двух видах. Пример:
        // 'fields' =>'id'=>44 или 'where' => ['id'=> 44]
        // для нескольких id использеем массив типа 'where' => ['id'=> [43, 44]]
        if(!isset($set['all_rows']) || empty($set['all_rows'])){
            //
            if(isset($set['where']) && $set['where']){
                $where = $this->createWhere($set);
            }else{
                $columns = $this->showColumns($table);

                if(!$columns) return false;
                //Проверяем что передали - Первичный ключ
                if($columns['id_row'] && $set['fields'][$columns['id_row']]){
                    //создаем строку вида "WHERE id=47"
                    $where = 'WHERE ' . $columns['id_row'] . '=' . $set['fields'][$columns['id_row']];
                    // Удаляем переменную так как уже добавили ее в $where
                    // Если мы этого не сделаем id попадет в список изменений. Пример
                    // "UPDATE teachers SET gallery_img='Masha.jpg',id='47',id='47' WHERE id=47"
                    unset($set['fields'][$columns['id_row']]);
                }
            }
        }
        //$update - изменения: Пример:
        //$update ="name='Dima',content='Coddddddntent_Dima',gallery_img='[\"dima.jpg\"]',img='[\"Main_Dima.jpg\"]',"
        $update = $this->createUpdate($set['fields'], $set['files'], $set['except']);
        $query = "UPDATE $table SET $update $where";
        return $this->query($query, 'u');
    }
    
    //      @param array $set
    //     'fields' => ['id', 'name'],
    //     'where' => ['name'=> 'Olya, Sveta', 'surname'=> 'Sergeevna','secondname'=> 'Rodionova', 'car'=>'Porshe'],
    //     'operand' => ['IN','LIKE%','<>', '='],
    //     'condition' => ['OR', 'AND'],
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
    final public function delete($table, $set){
        $table = trim($table);
        $where = $this->createWhere($set, $table);
        $columns = $this->showColumns($table);
        if(!$columns) return false;
        //если передаем поле fields то удаляем только столбцы из значения fields
        if(isset($set['fields'])&&is_array($set['fields'])&& !empty($set['fields'])){
            if($columns['id_row']){
                $key = array_search($columns['id_row'], $set['fields']);
                if ($key !== false) unset($set['fields'][$key]);
        };
        $fields = [];

        foreach($set['fields'] as $field){
            $fields[$field] = $columns[$field]['Default'];
        }
        $update = $this->createUpdate($fields, false, false);
        $query = "UPDATE $table SET $update $where";

    //fields отсутствует, значит полностью удаляем запись из таблицы
    }else{
        $join_arr = $this->createJoin($set, $table);
        $join = $join_arr['join'];
        $join_tables = $join_arr['tables'];

        //если в join пришлае  where то создаем либо новую where либо добавляем к существующей(если такая есть)
        if(isset($join_arr['where'])&&!empty($join_arr['where'])){
            $where = isset($where) ? $where .= $join_arr['where'] : $join_arr['where'];
        }
        //формируем строку вида 
        //DELETE teachers1, students1 FROM teachers1 LEFT JOIN students1 ON teachers1.student_id=students1.id WHERE teachers1.id ='15'
        $query = 'DELETE ' . $table . $join_tables . ' FROM ' . $table . ' ' . $join . ' ' . $where;

    }
    return $this->query($query,'u'); 
    
}
    //$columns => id_row = "id" хранится наименования первичного ключа "id" в нашей таблице
    final public function showColumns($table){
        //запрашиваем колонки у нашей БД
        $query = "SHOW COLUMNS FROM $table";
        $res =$this->query($query);

        $columns = [];

        //Переводим из нумерованного массива к виду асоциативного массива и записываем все в $columns 
        if($res){
        foreach($res as $row){
            $columns[$row['Field']] = $row;
            //если это первичный ключ то записываем его в id_row
            if($row['Key']=== 'PRI') $columns['id_row'] = $row['Field'];
            }
        }
        return $columns;
    }
}
