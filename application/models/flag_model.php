<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
    /*
     class Flag_model

     * @package		Template
     * @subpackage	Template Libraries
     * @category	Template Libraries
     * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
     *
     *  Flag_model
    */
    class Flag_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        save_flag() - saves a flag click to the db
        @param $flag_key - the key that references the flag
    */
    function save_flag($flag_key)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_flag_start');

        $flag_details=explode("_", $flag_key);

        $insert_data=array(
            'flag_key'=>$flag_key,
            'ref_id'=>$flag_details[1],
            'ref_type'=>$flag_details[0],
            'user_id'=>$this->user['id']
        );
        $this->db->insert('flag',$insert_data);

        /* BENCHMARK */ $this->benchmark->mark('func_save_flag_end');

    }
}
