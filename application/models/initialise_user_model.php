<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
    class Initialise_user_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

        /* BENCHMARK */ $this->benchmark->mark('Initialise_user_model_start');

        // models
        $this->load->model('node_model');

        // libraries

        // helpers

        // properties

        /* BENCHMARK */ $this->benchmark->mark('Initialise_user_model_end');
    }

    /* *************************************************************************
     initialise_user() -
     @param string
     @param numeric
     @param array
     @return
    */
    public function initialise_user()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_initialise_user_start');

        // get the basic user details

        return $user;

        /* BENCHMARK */ $this->benchmark->mark('func_initialise_user_end');
    }
}
