<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Voting

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Voting extends Universal {

    public function __construct()
    {
        parent::__construct();

        // models
			$this->load->model('voting_model');
    }

    /* *************************************************************************
        up() - votes a node up
    */
    function up()
    {
        $vals=$this->get_input_vals();

        $this->voting_model->up($vals['nid'],$this->user['user_id']);

        $new=array(
        	0=>$vals['nid'],
        	1=>$this->voting_model->get_vote_buttons($this->user,$vals['nid']),
            2=>$this->voting_model->get_score($vals['nid'])
        );

        exit(json_encode($new));
    }

    /* *************************************************************************
        down() - votes a node down
    */
    function down()
    {
        $vals=$this->get_input_vals();

        $this->voting_model->down($vals['nid'],$this->user['user_id']);

        $new=array(
        	0=>$vals['nid'],
        	1=>$this->voting_model->get_vote_buttons($this->user,$vals['nid']),
            2=>$this->voting_model->get_score($vals['nid'])
        );

        exit(json_encode($new));
    }
}
