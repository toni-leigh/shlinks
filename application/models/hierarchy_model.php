<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/node_model.php');
/*
 class Node_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Hierarchy_model extends Node_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
     get_hierarchy() -
     @param string
     @param numeric
     @param array
     @return
    */
    public function get_hierarchy($id,$cats)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_hierarchy_start');

        /* BENCHMARK */ $this->benchmark->mark('func_get_hierarchy_end');
    }
}
