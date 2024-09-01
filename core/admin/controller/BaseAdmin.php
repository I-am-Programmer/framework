<?php

namespace core\admin\controller;
use core\base\controller\BaseController;


class BaseAdmin extends BaseController{
    protected $model;
    protected $table;

    protected $columns;
    protected $menu;

    protected function inputData(){
        $this->init(true);
    }


}