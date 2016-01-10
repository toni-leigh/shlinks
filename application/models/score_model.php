<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Score_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
 *
 *  Score_model
*/
    class Score_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        $this->load->config('action');

        $this->actions=$this->config->item('stream_actions');
    }

    /* *************************************************************************
        get_score() - gets a score from the score config
        @param $node_key - either actor, noe or target_owner
        @param $action_code - the code for reference for the score
        @return $score - the score for adjusting node score
    */
    function get_score($node_key,$action_code,$node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_score_start');

        $score=0;

        if (isset($this->actions[$action_code][$node_key][$node['type']]))
        {
            $score=$this->actions[$action_code][$node_key][$node['type']];
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_score_end');

        return $score;
    }

    /* *************************************************************************
        update_score() - updates the score value in the node table
        @param $node_id - the node whose score has been affected by the vote
        @param $score - the score to add (can be negative, then add subtracts)
    */
    function update_score($node,$score)
    {
        if (isset($node['score']))
        {
            $update_data = array(
                'score'=>$node['score']+$score
            );

            $this->db->where('id', $node['id']);
            $this->db->update('node', $update_data);
        }
    }
}
