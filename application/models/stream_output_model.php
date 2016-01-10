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
        /* BENCHMARK */ $this->benchmark->mark('func_get_actions_start');

        $stream=array();

        if ('user'==$node['type'])
        {
            $query=$this->db->select('*')->from('action')->where(array('actor_id'=>$node['id']))->or_where(array('target_id'=>$node['id']))->order_by('time desc');
            $res=$query->get();
            $stream=$res->result_array();
        }
        else
        {
            $query=$this->db->select('*')->from('action')->where(array('target_id'=>$node['id']))->order_by('time desc');
            $res=$query->get();
            $stream=$res->result_array();
        }



        // compress the streams - this means that sets of similar actions are
        // output once rather than many times
            $cs=array();

            $scount=count($stream);

            $count=0;
            $val_array=array();

            for ($x=0;$x<$scount;$x++)
            {
                $s=$stream[$x];

                if (0==$s['action_code'] && 'image'==$s['target_type'])
                {
                    // some conditions here avoid clutter by skipping certain things
                    // in order ...
                    //   create image, on output 'added image to' covers this
                }
                else
                {
                    if (0==$x)
                    {
                        if (4==$s['action_code'])
                        {
                            $cs[]=array(
                                'c'=>1,
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
            }

            // finish by saving the last set
                $cs[]=array(
                    'c'=>$count,
                    'vals'=>$val_array
                );

        /* BENCHMARK */ $this->benchmark->mark('func_get_actions_end');

        return $cs;
    }
}
