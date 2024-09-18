<?php 

function print_arr($arrs){
    $my_arr = [];
    
        // foreach ($arrs as $arr=>$value){
        foreach ($arrs as $arr){
            echo '<pre>';
            print_r($arr);
            // echo '\'' . $arr .'\''. ' => ' .'\''. $value .'\''. ',';
            echo '</pre>';
            
    }

    
    
    
}


