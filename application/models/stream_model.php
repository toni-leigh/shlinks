<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Stream_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
 *  Stream_model
 *
 *      provides functions for recording activities performed by nodes on other nodes
 *      the acting node in this case is very likely to be a human user, though the
 *      flexibility is there for circumstances
 *
 *      such as: adding images to themselves or others
 *               creating nodes of other types
 *               commenting on nodes
 *               forming connections (becoming friends or joining groups)
 *
 *
*/
    class Stream_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /*
     store_action() - saves an event to the correct stream, incrementing the last stream entry or appending a new one
     @param int $action_code - refers to a config file of action values
     @param int $actor_id - id of the acting user
     @param int $target_id - id of the target node
     @param int $target_owner_id - id of the owner of the target node
     @param string $image - image, if set this is an add image action and the image will be used instead of the
        node main image
     @param array $vals - the new stream event to append
     @return
    */
    public function store_action($action_code,$actor,$target,$target_owner_id=null,$image=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_store_event_start');

        $this->load->model('score_model');

        // target and actor are always present
            $target_owner=$this->get_target_owner($action_code,$actor,$target,$target_owner_id);

            $this->score_model->update_score($target_owner,$target_owner['score_adjust']);

        // if an image is provided this is an add image to node, so that image is used in place of the
        // the node image, streams more real and colourful
            if ($image!=null)
            {
                $target['image']=$image;
            }

        // sort default images
            $default_stream_image=$this->config->item('default_image_stream');
            $actor['image']=(''==$actor['image']) ? $default_stream_image : $actor['image'];

            if (!isset($target['image']))
            {
                $target['image']=$default_stream_image;
            }
            else
            {
                if (''==$target['image'])
                {
                    $target['image']=$default_stream_image;
                }
            }
            if (!isset($target['visible']))
            {
                $target['visible']=0;
            }

            $target_owner['image']=(''==$target_owner['image']) ? $default_stream_image : $target_owner['image'];

        // get the correct score for this actor
            $actor['score_adjust']=$this->score_model->get_score('actor_score',$action_code,$target);

            $this->score_model->update_score($actor,$actor['score_adjust']);

        /* add the event into a table called action */
            $insert_data=array(

                'actor_viewed'=>0,
                'actor_id'=>$actor['id'],
                'actor_name'=>$actor['name'],
                'actor_url'=>$actor['url'],
                'actor_image'=>$actor['image'],
                'actor_type'=>$actor['type'],
                'actor_visible'=>$actor['visible'],
                'actor_score'=>$actor['score_adjust'],

                'target_id'=>$target['id'],
                'target_name'=>$target['name'],
                'target_url'=>$target['url'],
                'target_image'=>$target['image'],
                'target_type'=>$target['type'],
                'target_visible'=>$target['visible'],

                'target_owner_viewed'=>0,
                'target_owner_id'=>$target_owner['id'],
                'target_owner_name'=>$target_owner['name'],
                'target_owner_url'=>$target_owner['url'],
                'target_owner_image'=>$target_owner['image'],
                'target_owner_visible'=>$target_owner['visible'],
                'target_owner_score'=>$target_owner['score_adjust'],

                'action_code'=>$action_code

            );

            $this->db->insert('action',$insert_data);

        /* BENCHMARK */ $this->benchmark->mark('func_store_event_end');
    }

    /* *************************************************************************
        get_target_owner() - gets the target owning node based on the id handed in
        @param $target_owner_id - the id to find the node from
        @param $actor_id - the id of the actor, for checking this isn't your node
        @param $target - the target node, for scores
        @return $target_owner - target owner with the action score for adjustment
    */
    function get_target_owner($action_code,$actor,$target,$target_owner_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_target_owner_start');

        $this->load->model('node_model');

        // target owner is not always set, not on create, can be left null and not set if the
        // actor owns the node, this conditional also sets the score for target owner
            if ($actor['id']==$target_owner_id or
                null==$target_owner_id)
            {
                $target_owner=array(
                    'id'=>null,
                    'name'=>'',
                    'url'=>'',
                    'image'=>'',
                    'visible'=>null,
                    'score_adjust'=>0
                );
            }
            else
            {
                $target_owner=$this->node_model->get_node($target_owner_id);

                $target_owner['score_adjust']=$this->score_model->get_score('target_owner_score',$action_code,$target);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_get_target_owner_end');

        return $target_owner;
    }

    /* *************************************************************************
        undo_action() - undoes an action performed, keeps streams tidier and uncluttered
            with user errors and undone actions
        @param $actor_id - the id of the user doing the undoing
        @param $target_id - the id of the node on which the action is being undone
        @param $target_owner - may need score changing too
        @param $action_code - the numeric reference code for the action
    */
    function undo_action($action_code,$actor,$target,$target_owner_id=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_undo_action_start');

        // standard remove
            $this->db->delete(
                'action',array(
                    'actor_id'=>$actor['id'],
                    'target_id'=>$target['id'],
                    'action_code'=>$action_code
                )
            );

        $deleted_count=$this->db->affected_rows();

        if ($deleted_count>0)
        {
            // undo the node score by adding a negative score
                $this->load->model('score_model');
                $target_owner=$this->get_target_owner($action_code,$actor,$target,$target_owner_id);
                if ($target_owner!=null)
                {
                    $score=0-$this->score_model->get_score('target_owner_score',$action_code,$target);

                    $this->score_model->update_score($target_owner,$score);
                }

                $score=0-$this->score_model->get_score('actor_score',$action_code,$target);

                $this->score_model->update_score($actor,$score);
        }


        if (in_array($action_code, array(8,9,10)))
        {
            // special behaviour for friends and group joins as these work both ways (swap
            // actor and target ids over for a second remove)
                $this->db->delete(
                    'action',array(
                        'actor_id'=>$target['id'],
                        'target_id'=>$actor['id'],
                        'action_code'=>$action_code
                    )
                );
        }

        /* BENCHMARK */ $this->benchmark->mark('func_undo_action_end');
    }

    /* *************************************************************************
        undo_image_action() - removes an image action
        @param $image_url - needed to find the image action
        @param $target - the target node
    */
    function undo_image_action($image_url,$node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_undo_image_action_start');

        $this->db->delete(
            'action',array(
                'target_id'=>$node['id'],
                'target_image'=>$image_url
            )
        );

        /* BENCHMARK */ $this->benchmark->mark('func_undo_image_action_end');
    }

    /* *************************************************************************
        update_visible() - sets the visible state for a node throughout the action
            table
        @param $node - array containing the node
        @param $visible - Boolean containing the visible status to set
    */
    function update_visible($node,$visible)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_update_visible_start');

        // actor first
            $update_data = array(
                'actor_visible'=>$visible
            );

            $this->db->where('actor_id', $node['id']);
            $this->db->update('action', $update_data);

        // then target
            $update_data = array(
                'target_visible'=>$visible
            );

            $this->db->where('target_id', $node['id']);
            $this->db->update('action', $update_data);

        // then target owner
            $update_data = array(
                'target_owner_visible'=>$visible
            );

            $this->db->where('target_owner_id', $node['id']);
            $this->db->update('action', $update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_update_visible_end');

    }

    /* *************************************************************************
        set_image_in_stream() - sets the image in the stream on main update
        @param $node_id - the id of the node to update
        @param $img - the image data to use for the new image value
    */
    function set_image_in_stream($node_id,$img)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_stream_start');

        $image_path=thumbnail_url($img,'300');

        // actor first
            $update_data = array(
                'actor_image'=>$image_path
            );

            $this->db->where('actor_id', $node_id);
            $this->db->update('action', $update_data);

        // then target
            $update_data = array(
                'target_image'=>$image_path
            );

            $this->db->where(
                array(
                    'target_id'=>$node_id,
                    'action_code !='=>2 // don't over-ride image added to
                )
            );
            $this->db->update('action', $update_data);

        // then target owner
            $update_data = array(
                'target_owner_image'=>$image_path
            );

            $this->db->where('target_owner_id', $node_id);
            $this->db->update('action', $update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_stream_end');

    }
}
