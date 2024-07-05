<?php 

function print_arr($arrs){
    $my_arr = [];
    
        foreach ($arrs as $arr=>$value){
            echo '<pre>';
            echo '\'' . $arr .'\''. ' => ' .'\''. $value .'\''. ',';
            echo '</pre>';
        
    }

    
    
    
}