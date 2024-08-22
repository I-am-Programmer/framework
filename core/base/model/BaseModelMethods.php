<?php


namespace core\base\model;

//формируем поля типа table.id,table.name
abstract class BaseModelMethods{
    protected $sqlFunc = ['NOW()'];
    protected function createFields($set, $table= false){
        $set['fields'] = (isset($set['fields']) && is_array($set['fields']) 
        && !empty($set['fields'])) 
            ? $set['fields'] :['*'];

        $table = $table ? $table . '.' :'';

        $fields = '';

        
        foreach($set['fields'] as $field){
            $fields .= $table . $field . ',';
        }

        return $fields;
    }

    //ORDER BY table.name DESC
    protected function createOrder($set, $table=false,){
        $table = $table ? $table . '.' :'';
        $order_by = '';
        
        if(isset($set['order']) && is_array($set['order'])&& !empty($set['order'])){
            //по умолчанию  ставим ASC  
            $set['order_direction'] = (isset($set['order_direction']) 
            && is_array($set['order_direction']) && !empty($set['order_direction'])) 
                ? $set['order_direction'] : ['ASC'];

            $dirrect_count = 0;
            $order_by = 'ORDER BY ';

            //если у сущетвует значение DESC или ASC, то берем его. Иначе, берем предыдущее
            foreach($set['order'] as $order){
                if(isset($set['order_direction'][$dirrect_count])){
                    $order_direction = strtoupper($set['order_direction'][$dirrect_count]);
                    $dirrect_count++;
                }else{
                    $order_direction = strtoupper($set['order_direction'][$dirrect_count-1]);   
                }

                $order_by .= (is_int($table)) ? $order . ' ' . $order_direction . ', '
                :$table . $order . ' ' . $order_direction . ',';
            }

            $order_by = rtrim($order_by, ',');

        }


        return $order_by;
    }

    protected function createWhere($set, $table=false, $instruction='WHERE'){
        
        $table = $table ? $table . '.': '';

        $where = '';
        //если передано where
        //если передан операнд, иначе он ['='] по умолчанию
        if(isset($set['where'])&& !empty($set['where']) && is_array($set['where'])){ 
            $set['operand'] = isset($set['operand'])&&!empty($set['operand'])&&is_array($set['operand'])
            ?$set['operand']:['='];

            $set['condition'] = isset($set['condition'])&&!empty($set['condition'])&&is_array($set['condition'])
            ?$set['condition']:['AND'];
            //таким образом мы имеем возможность пропустить where, Если оно не пришло. или подставаи нужное знаечение, если пришло
            $where = $instruction;

            //счетчики operand и condition
            $o_count = 0;
            $c_count = 0;

            foreach($set['where'] as $key => $item ){

                $where .= ' ';

                if(isset($set['operand'][$o_count]) && $set['operand'][$o_count]){
                    $operand = $set['operand'][$o_count];
                    $o_count++;
                }else{
                    $condition =$set['operand'][$o_count -1];
                }

                if(isset($set['condition'][$c_count])&&$set['condition'][$c_count]){
                    $condition = $set['condition'][$c_count];
                    $c_count++;
                }else{
                    $condition =$set['condition'][$c_count -1];
                }

                if($operand === 'IN' || $operand === 'NOT IN'){ 

                    // НЕ ОБРАБАТЫВАЕМ вложенные запросов типа "WHERE teachers.name IN (SELECT * IN table_name)"
                    // а передаем в $in_str
                    if(is_string($item) && strpos($item, 'SELECT')===0){
                            $in_str = $item;
                    }else{
                        //иначе мы передали массив вида 'name'=> ['Olya, Sveta'], 
                        //ее нужно разделить, передав значения в $temp_item
                        if(is_array($item)) $temp_item = $item;
                            else $temp_item = explode(',', $item);
                        $in_str = '';
                        //в $in_str записываем все значения $temp_item приводя к нужному виду. Пример 'Olya','Sveta
                        foreach($temp_item as $v){
                            $in_str .= "'" . addslashes(trim($v)) . "',";
                        }
                    }
                    //Приводим к виду: "WHERE teachers.name IN ('Olya','Sveta','Masha') OR"
                    $where .= $table .$key .' '. $operand . ' (' . rtrim($in_str, ',') . ') ' . $condition;
                    
                    
                
                //если LIKE Есть в строке $operand(Может быть не первым символом)
                }elseif(strpos($operand, 'LIKE') !== false){
                    $like_template = explode('%', $operand);
                    //если в 0 элементе пусто, то ставим % перед $item
                    //если пусто не в 0 элементе, то ставим % после %item
                    foreach($like_template as $lt_key => $lt){
                        if(!$lt){
                            if(!$lt_key){
                                $item = '%'.$item;
                            }else{
                                $item .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . addslashes($item) . "' " . $condition;
                }else{
                    //В случае если у нас вложенный запрос то ставим скобки вместо кавычек, для $item
                    if(strpos($item, 'SELECT') ===0){
                        $where .= $table . $key . ' ' . $operand . '(' . $item .') ' . $condition; 
                    }
                    else{
                        $where .= $table . $key . ' ' . $operand . "'" . addslashes($item) ."' " . $condition; 
                    }
                }
            }
            //отрезаем последний $condition (AND, )
            $where =substr($where, 0, strrpos($where, $condition));
        }
        return $where;
    }

    protected function createJoin($set, $table, $new_where =false){
        //пустые поля создаются, чтобы в случае если join не пришел, мы возвращали пустую строку
        $fields = '';
        $join = '';
        $where = '';

        if(isset($set['join'])&& $set['join']){
            $join_table = $table;

            foreach ($set['join'] as $key=>$item){
                //если ключа нет(если передали индексированный массив)
                if(is_int($key)){
                    //если отсутствует $table то переходим в cледующей итерации
                    if(!isset($item['table']))continue;
                    //если есть, то меняем значение ключа на значение в 'table'
                    else $key = $item['table'];
                }
                // если в переменной join что то есть, то добавляем пробел для следующего джоина
                if($join) $join .= ' ';
                //если отсутствует 'on' то не выполняем, цикл прокрутится
                if($item['on']){
                    $join_fields =[];

                    switch(2){
                        //проверяем что в ($item['on']['fields']) лежит 2 элемента если передаем on вида:
                        
                        // 'on' =>[
                        // 'table' => 'teachers',
                        // 'fields' => ['id', 'parent_id']
                        case isset($item['on']['fields']) && count($item['on']['fields']):
                            $join_fields = $item['on']['fields'];
                            break;
                        
                        //проверяем что в ($item['on']) лежит 2 элемента если передаем on вида:
                        //     'on' =>['id', 'parent_id']
                        case isset($item['on']) && count($item['on']):
                            $join_fields = $item['on'];
                            break;

                        default:
                            // если условия не выполнятся то дальнейшее выполнение не имеет смысла
                            // continue 2 для пропуска итерации цикла 2-го уровня(не в switch а в foreach)
                            continue 2;
                            break;
                    }
                    
                    if(!$item['type']) $join .= 'LEFT JOIN ';
                    else $join .= trim(strtoupper($item['type'])) . ' JOIN ';

                    $join .= $key . ' ON ';

                    //если есть 'table' то присоединяемся к ней, если нет, то к предыдущей таблице по умолчанию
                    if(isset($item['on']['table']) && $item['on']['table']) $join .= $item['on']['table'];
                    else $join .= $join_table;

                    // формируем join
                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];

                    $join_table = $key;
                    //проверяем есть ли уже что то в переменной where,
                    //если нет то начинаем строку с WHERE иначе используя group_condition=AND, OR, для соединения WHERE
                    if($new_where){
                        if(isset($item['where'])&&$item['where']){
                            $new_where = false;    
                        } 
                        $group_condition = 'WHERE';
                    }else{
                        $group_condition = isset($item['group_condition'])
                        ? strtoupper($item['group_condition']): 'AND';
                    }
                    $fields .= $this-> createFields($item, $key);
                    $where .= $this->createWhere($item, $key, $group_condition);
                }
                
            }
        }
        return compact('fields', 'join', 'where');
    }
    protected function createInsert($fields, $files, $except){
        
        $insert_arr = [];

        if($fields){
            
            foreach($fields as $row=>$value){
                //если в переданном массиве except есть поле $row то пропускаем его
                if($except && in_array($row, $except))continue;
                $insert_arr['fields'] = isset($insert_arr['fields']) ? $insert_arr['fields'] . $row . ',' : $row . ',';

                //если в $fields - NOW(), то не ставим кавычки и не экранируем символы
                if(in_array($value, $this->sqlFunc)){
                    // isset($insert_arr['values']) ? $insert_arr['values'] .= $value . ',' : $insert_arr['values'] = $value . ',';
                    $insert_arr['values'] = isset($insert_arr['values'])? $insert_arr['values'] .$row . ',': $row . ',';

                }else{
                    $insert_arr['values'] = isset($insert_arr['values'])? $insert_arr['values'] . "'".addslashes($value) . "',": "'".addslashes($value) . "',";
                    // isset($insert_arr['values']) ? $insert_arr['values'] .= "'".addslashes($value) . "'," : $insert_arr['values'] = "'".addslashes($value) . "',";

                }
            }
        }
        
        if(isset($files)&&$files){
            //$row - название поля/ $file - значение
            foreach($files as $row=>$file){
                $insert_arr['fields'] = isset($insert_arr['fields']) ? $insert_arr['fields'] . $row . ',' : $row . ',';
                // $insert_arr['fields'] .= $row . ',';
                
                
                //для галереи изображений используем формат JSON, для хранения массива в базе данных 
                if(is_array($file)) {
                    $insert_arr['values'] = isset($insert_arr['values'])?$insert_arr['values']. "'". addslashes(json_encode($file))."',":"'". addslashes(json_encode($file))."',";
                    // $insert_arr['values'] .= "'". addslashes(json_encode($file))."',";
                }
                // если у нас строка, то записываем в запрос как есть добавляя кавычки, запятую и экранируя символы 
                else{ 
                    $insert_arr['values'] = isset($insert_arr['values'])? $insert_arr['values']."'" . addslashes($file) . "',": "'" . addslashes($file) . "',";;
                    // $insert_arr['values'] .= "'" . addslashes($file) . "',";
                }
            }
           
        }
        // для каждого ключа $insert_arr - обрезаем конечную запятую
        foreach($insert_arr as $key => $arr) $insert_arr[$key]=rtrim($arr, ',');
            
        return $insert_arr;
    }

    protected function createUpdate($fields, $files, $except){
        $update ='';

        if($fields){
            foreach($fields as $row => $value){
                if($except && in_array($row, $except))continue;
                $update .= $row . '=';

                if(in_array($value,$this->sqlFunc)){
                    $update .= $value . ',';
                }else{
                    $update .= "'" . addslashes($value) . "',";
                }

            }
        }
        if(isset($files)&&$files){
            //$row - название поля/ $file - значение
            foreach($files as $row=>$file){
                $update .= $row . '=';
                
                
                //для галереи изображений используем формат JSON, для хранения массива в базе данных 
                if(is_array($file)) $update .= "'". addslashes(json_encode($file))."',";
                
                else $update .="'" . addslashes($file) . "',";
            }
        }
        return rtrim($update, ',');
        }
    
}