<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Voting_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
*/
    class Voting_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        get_vote() - gets an individual vote
        @param $node_id - the id of the node on which the vote has been cast
        @param $user_id - the id of the voter
        @return $vote - array containing the vote data
    */
    function get_vote($node_id,$user_id)
    {
        $vote=array();

        $query=$this->db->select('*')->from('vote')->where(array('node_id'=>$node_id,'user_id'=>$user_id));
        $res=$query->get();
        $vote=$res->row_array();

        return $vote;
    }

    /* *************************************************************************
        save_vote() - saves a vote to the system
        @param $node_id - the id of the node on which the vote has been cast
        @param $user_id - the id of the vote
        @param $vote - +1, -1 or 0 depending on whether the vote was up or down or a cancellation
        @return void
    */
    function save_vote($node_id,$user_id,$vote)
    {
        $insert_data=array(
                'node_id'=>$node_id,
                'user_id'=>$user_id,
                'vote'=>$vote
            );
        $this->db->insert('vote',$insert_data);
    }

    /* *************************************************************************
        update_vote() - updates a vote that already exists if it is changed
        @param $node_id - the id of the node on which the vote has been cast
        @param $user_id - the id of the voter adjusting their vote
        @param $vote - +1, -1 or 0 depending on whether the vote was up or down or a cancellation
        @return void
    */
    function update_vote($node_id,$user_id,$vote)
    {
        $update_data = array(
            'vote'=>$vote
        );

        $this->db->where(array('node_id'=>$node_id,'user_id'=>$user_id));
        $this->db->update('vote', $update_data);
    }

    /* *************************************************************************
        up() - votes a node up
        @param $node_id - the id of the node on which the vote has been cast
        @param $user_id - the id of the vote
        @return void
    */
    public function up($node_id,$user_id)
    {
        // models required
            $this->load->model('node_model');

        // get the current value of the vote
            $vote=$this->get_vote($node_id,$user_id);

        // full nodes for the user and the node
            $node=$this->node_model->get_node($node_id);
            $user=$this->node_model->get_node($user_id,'user');

        // set the vote count for the vote table
            $vote_value=1;

        // process the vote
            if (0==count($vote))
            {
                $this->save_vote($node_id,$user_id,$vote_value);

                $this->store_vote_activity($node,$user,'up');
            }
            else
            {
                // undo vote activity, just get rid of it all to be replaced
                    $this->undo_vote_activity($node,$user,'down');

                // calculate how the new vote affects the current vote, it may change or not
                    if ($vote['vote']<0)
                    {
                        $vote_value=0;
                    }

                // update the vote with the new value
                    $this->update_vote($node_id,$user_id,$vote_value);

                // save the vote action to the stream
                    if (1==$vote_value)
                    {
                        $this->store_vote_activity($node,$user,'up');
                    }
            }
    }

    /* *************************************************************************
        down() - votes a node down
        @param $node_id - the id of the node on which the vote has been cast
        @param $user_id - the id of the vote
        @return void
    */
    public function down($node_id,$user_id)
    {
        // models required
            $this->load->model('node_model');

        // get the current value of the vote
            $vote=$this->get_vote($node_id,$user_id);

        // full nodes for the user and the node
            $node=$this->node_model->get_node($node_id);
            $user=$this->node_model->get_node($user_id,'user');

        // set the vote count for the vote table
            $vote_value=-1;

        // process the vote
            if (0==count($vote))
            {
                $this->save_vote($node_id,$user_id,$vote_value);

                $this->store_vote_activity($node,$user,'down');
            }
            else
            {
                // undo vote activity, just get rid of it all to be replaced
                    $this->undo_vote_activity($node,$user,'up');

                // calculate how the new vote affects the current vote, it may change or not
                    if ($vote['vote']>0)
                    {
                        $vote_value=0;
                    }

                // update the vote with the new value
                    $this->update_vote($node_id,$user_id,$vote_value);

                // save the vote action to the stream
                    if (-1==$vote_value)
                    {
                        $this->store_vote_activity($node,$user,'down');
                    }
            }
    }

    /* *************************************************************************
        get_vote_buttons() - gets the vote buttons with signed in users state
        @param $node_id - the node that has been voted on
        @param $user_id - the signed in user
        @return $vote_buttons - html of the vote buttons with state
    */
    public function get_vote_buttons($user,$node)
    {
        $vote_buttons="";

        // get the node if this is a cll from js then there will be an id in the node variable
            if (is_numeric($node))
            {
                $this->load->model('node_model');

                $node=$this->node_model->get_node($node);
            }

        // get the current value of the vote
            $vote=$this->get_vote($node['id'],$user['id']);

        // vote down config - some nodes cannot be voted down, meaning the voting system can work as a like system
            $this->load->config('voting');
            $vote_downs=$this->config->item('vote_down_types');

        if ($node['user_id']==$user['id'])
        {
            // a user cannot vote for her nodes
                $vote_buttons.="<span class='cant_vote cant_vote_up sprite' title='cant vote, this is your post'>Vote Up</span>";

                if (in_array($node['type'], $vote_downs))
                {
                    $vote_buttons.="<span class='cant_vote cant_vote_down sprite' title='cant vote, this is your post'>Vote Down</span>";
                }

        }
        else
        {
            if (isset($vote['vote']) &&
                $vote['vote']!=0)
            {
                if ($vote['vote']<0)
                {
                    // node is currently voted down
                        $vote_buttons.="<span id='up".$node['id']."' class='not_voted vote_up sprite'>Vote Up</span>";

                        if (in_array($node['type'], $vote_downs))
                        {
                            $vote_buttons.="<span id='down".$node['id']."' class='voted_down vote_down sprite'>Vote Down</span>";
                        }
                }
                else
                {
                    // node is currently voted up
                        $vote_buttons.="<span id='up".$node['id']."' class='voted_up vote_up sprite'>Vote Up</span>";

                        if (in_array($node['type'], $vote_downs))
                        {
                            $vote_buttons.="<span id='down".$node['id']."' class='not_voted vote_down sprite'>Vote Down</span>";
                        }
                }
            }
            else
            {
                // neither are voted so both buttons are ready for vote
                    $vote_buttons.="<span id='up".$node['id']."' class='not_voted vote_up sprite'>Vote Up</span>";

                    if (in_array($node['type'], $vote_downs))
                    {
                        $vote_buttons.="<span id='down".$node['id']."' class='not_voted vote_down sprite'>Vote Down</span>";
                    }
            }
        }

        return $vote_buttons;
    }

    /* *************************************************************************
        get_score() - gets the score for this node
        @param $node_id - the node
        @return $score - numerical value containing the score
    */
    public function get_score($node_id)
    {
        // get the node
            $node=$this->node_model->get_node($node_id);

        return $node['score'];
    }

    /* *************************************************************************
        store_vote_activity() - stores the action of voting
        @param $target - the node which is acted on
        @param $actor - the user who acts
        @return void -
    */
    public function undo_vote_activity($target,$actor,$vote_direction)
    {
        // stream model
            $this->load->model('stream_model');

        // point at the right action array values
            $action=('up'==$vote_direction) ? 6 : 7;

        // the actor acting on the target
            $this->stream_model->undo_action($action,$actor,$target,$target['user_id']);
    }

    /* *************************************************************************
        store_vote_activity() - stores the action of voting
        @param $target - the node which is acted on
        @param $actor - the user who acts
        @return void -
    */
    public function store_vote_activity($target,$actor,$vote_direction)
    {
        // stream model
            $this->load->model('stream_model');

        // point at the right action array values
            $action=('up'==$vote_direction) ? 6 : 7;

        // the actor acting on the target
            $this->stream_model->store_action($action,$actor,$target,$target['user_id']);
    }
}
