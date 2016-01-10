<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Stream_model

 * @package     Template
 * @subpackage  Template Libraries
 * @category    Template Libraries
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
    class Stream_output_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        get_actions() - retrieve the contents of the action table for this node
        @param $node - array containing the node data
        @return $stream - an array of actions
    */
    function get_actions($node)
    {
        /* BENCHMARK */ $this->benchmark->mark("func_get_".$node['id']."_actions_start");

        /*
            NB - if this is ineffeicient try swicthing back to database object calls
        */

        $stream=array();

        if ('user'==$node['type'])
        {
            // get everything where the users is the node or the owner of the node
            $query=$this->db->select('*')->from('action')->where(array('actor_id'=>$node['id']))->or_where(array('target_id'=>$node['id']))->order_by('time desc');
            $res=$query->get();
            $stream=$res->result_array();

            $stream=$this->db->simple_query(
                "select
                    *
                from
                    action
                where
                    actor_id=".$node['id']." or
                    target_id=".$node['id']." or
                    target_owner_id=".$node['id']."
                order by
                    time desc");
        }
        else
        {
            // just get node stream
            $query=$this->db->select('*')->from('action')->where(array('target_id'=>$node['id']))->order_by('time desc');
            $res=$query->get();
            $stream=$res->result_array();

            $stream=$this->db->simple_query("select * from action where target_id=".$node['id']." order by time desc");
        }

        // compress the streams - this means that sets of similar actions are
        // output once rather than many times
            $cs=array();

            $scount=mysql_num_rows($stream);

            $count=0;
            $val_array=array();

            $x=0;

            $data=array();

            while ($s=mysql_fetch_array($stream))
            {

                if ($s['actor_id']==$this->user['user_id'] && 'user'==$node['type'])
                {

                }
                else
                {

                    // this condition skips any stream element that is not visible
                    if (1==$s['actor_visible'] &&
                        1==$s['target_visible'] &&
                        (1==$s['target_owner_visible'] or null==$s['target_owner_visible']))
                    {
                        if (0==$s['action_code'] && 'image'==$s['target_type'])
                        {
                            // some conditions here avoid clutter by skipping certain things
                            // in order ...
                            //   create image, on output 'added image to' covers this
                        }
                        else
                        {
                            // add some data attributes to be used if necessary when filtering the streams
                            // these three data values allow the signed in user to target their actions; actions
                            // on their stuff; users they followed; articles they subscribe to; and events they
                            // are attending
                            $target_owner_id=(isset($s['target_owner_id'])) ? $s['target_owner_id'] : 0;
                            $data=array(
                                'actor-id'=>$s['actor_id'],
                                'target-type'=>$s['target_type'],
                                'target-owner-id'=>$target_owner_id
                            );

                            if (0==$x)
                            {
                                if (4==$s['action_code'])
                                {
                                    $cs[]=array(
                                        'c'=>1,
                                        'data'=>$data,
                                        'vals'=>array(
                                            0=>$s
                                        )
                                    );

                                    $x=$x+2;
                                }
                                else
                                {
                                    // stream values to be set
                                        $count=1;
                                        $val_array=array(
                                            0=>$s
                                        );
                                }
                            }
                            else
                            {
                                $curr=array(
                                    'actor_id'=>$s['actor_id'],
                                    'target_id'=>$s['target_id'],
                                    'target_owner_id'=>$s['target_owner_id'],
                                    'action_code'=>$s['action_code']
                                );

                                if ($curr==$prev)
                                {
                                    // increment
                                    $count++;
                                    $val_array[]=$s;
                                }
                                elseif (4==$curr['action_code'])
                                {
                                    $cs[]=array(
                                        'c'=>1,
                                        'data'=>$data,
                                        'vals'=>array(
                                            0=>$s
                                        )
                                    );

                                    $x++;
                                }
                                else
                                {
                                    // store
                                    $cs[]=array(
                                        'c'=>$count,
                                        'data'=>$data,
                                        'vals'=>$val_array
                                    );

                                    // reset
                                    $count=1;
                                    $val_array=array(
                                        0=>$s
                                    );
                                }
                            }

                            $prev=array(
                                'actor_id'=>$s['actor_id'],
                                'target_id'=>$s['target_id'],
                                'target_owner_id'=>$s['target_owner_id'],
                                'action_code'=>$s['action_code']
                            );
                        }

                        $x++;
                    }
                }
            }

            // finish by saving the last set
                $cs[]=array(
                    'c'=>$count,
                    'vals'=>$val_array,
                    'data'=>$data
                );


        /* BENCHMARK */ $this->benchmark->mark("func_get_".$node['id']."_actions_end");

        return $cs;
    }

    /* *************************************************************************
        merge_actions() - gets all the stream actions for a given set of connections
        @return $stream - the ordered and merged actions
    */
    public function merge_actions()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_merge_actions_start');

        $this->load->model('connection_model');

        $stream=array();

        $connections=$this->connection_model->get_connections($this->user);

        foreach ($connections as $type => $node_list)
        {
            foreach ($node_list as $n)
            {
                $stream=array_merge($stream,$this->get_actions($n['node']));
            }
        }

        // remove duplicates, occur when a followed user has done something to this users node, or similiar
        $stream=array_unique($stream);

        // sort by timestamp
        usort($stream, array($this,'time_sort'));

        //dev_dump($stream);

        // use simple query to get all

        /* BENCHMARK */ $this->benchmark->mark('func_merge_actions_end');

        return $stream;
    }

    function time_sort($a, $b)
    {
        if (isset($a['vals'][0]) &&
            isset($b['vals'][0]))
        {
            return $a['vals'][0]['time'] < $b['vals'][0]['time'];
        }
        else
        {
            return 0;
        }
    }
}
