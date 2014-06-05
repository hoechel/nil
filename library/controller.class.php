<?php
abstract class Controller
{
    protected $model;
    protected $view;
    
    public function __construct($model)
    {
        $this->model = new $model();
        $this->view = new Template();        
    }        
}