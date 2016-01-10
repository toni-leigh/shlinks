<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Flag

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Flag extends Universal {

    public function __construct()
    {
        parent::__construct();

        // models
			$this->load->model('flag_model');
    }

    /* *************************************************************************
        save() - saves a Flag
        @reload  -
    */
    function save()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_start');

        $flag=$this->get_input_vals();

        // save the Flag
        	$this->flag_model->save_flag($flag['flag_key']);

        /* BENCHMARK */ $this->benchmark->mark('func_save_end');

        // success
        	exit(json_encode(""));
    }
}
