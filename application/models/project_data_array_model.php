<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Project_data_array_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Project_data_array_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
     project_specific_template_data() - gets template data for this project only
     @param string
     @param numeric
     @param array
     @return
    */
    public function project_specific_template_data($panel,$data,$user)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_project_specific_template_data_start');

        $this->data=$data;
        $this->user=$user;
        $node=$this->data['node'];

        // output order, set by user type
            $data['output_order']=array(
                'developer'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1055,
                    1004,
                    1798,
                    1045
                ),
                'signedup_user'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1055,
                    1004,
                    1798,
                    1045
                ),
                'super_admin'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1055,
                    1004,
                    1798,
                    1045
                ),
                'designer'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1004,
                    1798,
                    1055,
                    1045
                ),
                'seo'=>array(
                    1748,
                    1746,
                    1745,
                    1749,
                    1750,
                    1045,
                    1055,
                    1004,
                    1798,
                    1019
                )
            );

        // open ones - the first two
            $open_ones=array(
                'developer'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1004,
                    1798,
                    1055,
                    1045
                ),
                'signedup_user'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1004,
                    1798,
                    1055,
                    1045
                ),
                'super_admin'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1004,
                    1798,
                    1055,
                    1045
                ),
                'designer'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1004,
                    1798,
                    1055,
                    1045
                ),
                'seo'=>array(
                    1748,
                    1746,
                    1745,
                    1019,
                    1749,
                    1750,
                    1004,
                    1798,
                    1055,
                    1045
                )
            );


        // hierarchy of categories
            $this->load->model('hierarchy_model');

            $query=$this->db->select('*')->from('hierarchy')->join('node','hierarchy.node_id=node.id')->join('category','hierarchy.node_id=category.node_id')->order_by('lft');
            $res=$query->get();
            $cats=$res->result_array();

            $cl=array();
            $id=0;
            $lid=0;
            if ('links'==$node['url'])
            {
                foreach ($cats as $c)
                {
                    $count=mysql_fetch_array(mysql_query("select count(node_id) as link_count from node_category where category_id=".$c['id']));

                    if (1002==$c['parent_id'])
                    {
                        $id=$c['id'];

                        $cl[$id]='';

                        // close last ul and li
                            if ($c['id']!=1748)
                            {
                                $cl[$lid].="</ul>";
                                $cl[$lid].="</li>";
                            }

                        $cl[$id].="<li id='cat_".$c['id']."' class='cat_bar'>";
                        $cl[$id].="<div>";
                        $cl[$id].="  <a class='master' href='".$c['url']."'>";
                        //[".$c['id']." -- l".$c['lft']."-- r".$c['rght']."]
                        $cl[$id].="".$c['name'];
                        $cl[$id].="  </a>";
                        if (in_array($id,$open_ones[$user['user_type']]) ? $action='close' : $action='open' );
                        $cl[$id].="  <a id='c_".$c['id']."' class='sprite ".$action."'>";
                        $cl[$id].="  </a>";
                        $cl[$id].="</div>";

                        // open ul
                            if (in_array($id,$open_ones[$user['user_type']]) ? $state="" : $state="style='height:68px;opacity:0.3;'" );
                            $cl[$id].="<ul id='subs_".$c['id']."' ".$state.">";
                    }
                    else
                    {
                        //[".$c['id']." -- l".$c['lft']."-- r".$c['rght']."]
                        $cl[$id].="<li><a href='".$c['url']."'><span class='links_count'>".$count['link_count']."</span>".$c['name']." </a></li>";
                    }

                    $lid=$id;
                }
                $cl[$id].="</ul>";
                $cl[$id].="</li>";
            }

            $data['categories']=$cl;

            $cs=array();
            $id=0;
            $lid=0;
            $set_cats = '';
            $set_cat_count = 0;
            if ('link-details'==$node['url'])
            {
                foreach ($cats as $c)
                {
                    $count=mysql_fetch_array(mysql_query("select count(node_id) as link_count from node_category where category_id=".$c['id']));

                    if (1002==$c['parent_id'])
                    {
                        $id=$c['id'];

                        $cs[$id]='';

                        // close last ul and li
                            if ($c['id']!=1748)
                            {
                                $cs[$lid].="</ul>";
                                $cs[$lid].="</li>";
                            }

                        $cs[$id].="<li id='cat_".$c['id']."' class='select_header'>";
                        $cs[$id].="<div>";
                        $cs[$id].=$c['name'];
                        $cs[$id].="</div>";

                        // open ul
                            $cs[$id].="<ul>";
                    }
                    else
                    {

                        $selected = '';
                        if (isset($data['link_vals']) &&
                            isset($data['link_vals']['cats']))
                        {
                            foreach ($data['link_vals']['cats'] as $categ)
                            {
                                if ($c['id'] == $categ['category_id'])
                                {
                                    $set_cats .= $categ['category_id'].',';
                                    $set_cat_count ++;
                                    $selected = " linkcat_sel ";
                                    break;
                                }
                            }
                        }

                        $cs[$id].="<li id='lcc".$c['id']."' class='linkcat ".$selected."'>";
                        $cs[$id].=strtolower($c['name']);
                        $cs[$id].="</li>";
                    }

                    $lid=$id;
                }
                $cs[$id].="</ul>";
                $cs[$id].="</li>";
            }

            $data['set_cats'] = $set_cats;
            $data['set_cat_count'] = $set_cat_count;
            $data['cat_select']=$cs;

        // category link list
            $nl='';
            if ('category'==$node['type'])
            {
                $query=$this->db->select('*')->from('hierarchy')->where(array('node_id'=>$node['id']));
                $res=$query->get();
                $cat=$res->row_array();

                $query=$this->db->select('*')->from('hierarchy')->where(array('lft >='=>$cat['lft'],'lft <='=>$cat['rght']));
                $res=$query->get();
                $cats=$res->result_array();

                $where_in=array();
                foreach ($cats as $c)
                {
                    $where_in[]=$c['node_id'];
                }

                $query=$this->db->select('*')->from('node_category')->where_in('category_id',$where_in);
                $res=$query->get();
                $nodes=$res->result_array();

                $where_in=array();
                foreach ($nodes as $n)
                {
                    $where_in[]=$n['node_id'];
                }

                if (count($where_in))
                {
                    $query=$this->db->select('*')->from('node')->where_in('id',$where_in)->order_by('name');
                    $res=$query->get();
                    $nl=$res->result_array();

                    $nl=$this->link_list($nl,$node);
                }

                // url tab
                    if ('details'==$data['current_tab'])
                    {
                        $data['current_tab']='links';
                    }
            }
            $data['node_list']=$nl;

        // master category for colouring
            $pcat=array();
            $cat=array();
            if ('link'==$node['type'])
            {
                $query=$this->db->select('*')->from('hierarchy')->where(array('node_id'=>$node['category_id']));
                $res=$query->get();
                $cat=$res->row_array();

                $query=$this->db->select('*')->from('hierarchy')->where(array('node_id'=>$cat['parent_id']));
                $res=$query->get();
                $pcat=$res->row_array();
            }

            if ('category'==$node['type'])
            {
                $query=$this->db->select('*')->from('hierarchy')->where(array('node_id'=>$node['id']));
                $res=$query->get();
                $cat=$res->row_array();

                if (1002==$cat['parent_id'])
                {
                    $pcat=$cat;
                }
                else
                {
                    $query=$this->db->select('*')->from('hierarchy')->where(array('node_id'=>$cat['parent_id']));
                    $res=$query->get();
                    $pcat=$res->row_array();
                }
            }

            $data['category']=$cat;
            $data['parent_category']=$pcat;

        // user
            if ('user'==$node['type'] or
                'category'==$node['type'])
            {
                // stream
                    $this->load->model('stream_output_model');
					$data['stream']=$this->stream_output_model->get_actions($node);

                // images
					$data['images']=$this->image_model->get_images($node['id']);
            }

        // user tabs
            if ('user'==$node['type'])
            {
                $nl=array();
                $data['votes_sel']="";
                $data['activity_sel']="";
                $data['links_sel']="";

                if ('links'==$data['current_tab'])
                {
                    $query=$this->db->select('*')->from('node')->where(array('user_id'=>$node['id'],'type'=>'link'));
                    $res=$query->get();
                    $nl=$res->result_array();
                    $nl=$this->link_list($nl,$node);

                    $data['links_sel']=" sel";
                }
                elseif ('votes'==$data['current_tab'])
                {
                    $query=$this->db->select('*')->from('vote')->where(array('user_id'=>$node['id'],'vote >'=>0));
                    $res=$query->get();
                    $votes=$res->result_array();

                    $where_in=array();
                    foreach ($votes as $v)
                    {
                        $where_in[]=$v['node_id'];
                    }

                    if (count($where_in))
                    {
                        $query=$this->db->select('*')->from('node')->where_in('id',$where_in)->order_by('name');
                        $res=$query->get();
                        $nl=$res->result_array();

                        $nl=$this->link_list($nl,$node);
                    }

                    $data['votes_sel']=" sel";
                }
                elseif ('all'==$data['current_tab'])
                {
                    $query=$this->db->select('*')->from('node')->where(array('user_id'=>$node['id'],'type'=>'link'));
                    $res=$query->get();
                    $nl=$res->result_array();

                    $query=$this->db->select('*')->from('vote')->where(array('user_id'=>$node['id'],'vote >'=>0));
                    $res=$query->get();
                    $votes=$res->result_array();

                    $where_in=array();
                    foreach ($votes as $v)
                    {
                        $where_in[]=$v['node_id'];
                    }

                    if (count($where_in))
                    {
                        $query=$this->db->select('*')->from('node')->where_in('id',$where_in)->order_by('name');
                        $res=$query->get();

                        $nl=array_merge($res->result_array(),$nl);
                    }
                        $nl=$this->link_list($nl,$node);

                    $data['all_sel']=" sel";
                }
                else
                {
                    $data['activity_sel']=" sel";
                }

                $data['node_list']=$nl;
            }

        $this->data=$data;

        return $this->data;

        /* BENCHMARK */ $this->benchmark->mark('func_project_specific_template_data_end');
    }

    /* *************************************************************************
        link_list() - gets a list of links
        @param $where_in - array of nodes for the link list
        @param $node - the node link list is on
        @return $link_list - array containing link output data
    */
    function link_list($nl,$node)
    {
        $this->load->model('voting_model');

        for($x=0;$x<count($nl);$x++)
        {
            // link score
                $points=$this->voting_model->get_score($nl[$x]['id']);

            // other categories
                $query=$this->db->select('*')->from('node_category')->where(array('node_id'=>$nl[$x]['id']));
                $res=$query->get();
                $others=$res->result_array();

                $where_in=array();
                foreach ($others as $o)
                {
                    if ($o['category_id']!=$node['id'])
                    {
                        $where_in[]=$o['category_id'];
                    }
                }

                $nl[$x]['other_cats']=array();
                if (count($where_in))
                {
                    $query=$this->db->select('*')->from('node')->where_in('id',$where_in);
                    $res=$query->get();
                    $nl[$x]['other_cats']=$res->result_array();
                }

            // score colour
                $glow_ratio=200/10;
                if (0==$points)
                {
                    $colours['red']=200;
                    $colours['green']=200;
                    $colours['blue']=200;

                    $font['red']=60;
                    $font['green']=60;
                    $font['blue']=60;
                }
                elseif ($points>0)
                {
                    $red=floor($points*$glow_ratio);

                    $colours['red']=200;
                    $colours['green']=200-$red;
                    $colours['blue']=200-$red;

                    $font['red']=60+(floor($red/10));
                    $font['green']=60;
                    $font['blue']=60;
                }
                else
                {
                    //$blue=floor($points*(0-$glow_ratio));

                    $more_grey=0-$points;

                    $colours['red']=200+$more_grey;
                    $colours['green']=200+$more_grey;
                    $colours['blue']=200+$more_grey;

                    $font['red']=60+($more_grey*8);
                    $font['green']=60+($more_grey*8);
                    $font['blue']=60+($more_grey*8);
                }

                $nl[$x]['score_data']['points']=$points;
                $nl[$x]['score_data']['bg']['red']=$colours['red'];
                $nl[$x]['score_data']['bg']['green']=$colours['green'];
                $nl[$x]['score_data']['bg']['blue']=$colours['blue'];
                $nl[$x]['score_data']['font']['red']=$font['red'];
                $nl[$x]['score_data']['font']['green']=$font['green'];
                $nl[$x]['score_data']['font']['blue']=$font['blue'];

            // button state
                $nl[$x]['vote_buttons']=$this->voting_model->get_vote_buttons($this->user,$nl[$x]);
        }

        return $nl;
    }

    /* *************************************************************************
         save_specific() - a function for doing project specific things to nodes
         @param string
         @param numeric
         @param array
         @return
    */
    public function save_specific($post,$id,$type,$create=0)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_specific_start');

        if ('video'==$type)
        {
            $update_data = array(
                'video' =>1
            );

            $this->db->where('id', $id);
            $this->db->update('node', $update_data);
        }

        if ('link'==$type)
        {
            $update_data = array(
                'visible' =>1
            );

            $this->db->where('id', $id);
            $this->db->update('node', $update_data);

            $update_data = array(
                'node_html' =>$post['node_html']
            );

            $this->db->where('node_id', $id);
            $this->db->update('link', $update_data);
        }

        if ('user'==$type)
        {
            $update_data = array(
                'show_tabs' =>1
            );

            $this->db->where('id', $id);
            $this->db->update('node', $update_data);

            $update_data = array(
                'user_type' =>'developer'
            );

            $this->db->where('user_id', $id);
            $this->db->update('user', $update_data);
        }

        return $id;

        /* BENCHMARK */ $this->benchmark->mark('func_save_specific_end');
    }

    /* *************************************************************************
         set_specific_type_format() - sets human readable type names for output, used to set human readables for
            site specific node types but can also over-ride huamn types for core node types
         @param string $human_type
         @param numeric
         @param array
         @return
    */
    public function set_specific_type_format($type,$human_type)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_set_specific_type_format_start');

        switch ($type)
        {
            /* case 'activitygroup':
                $human_type='activity group';
                break;
            case 'committee':
                $human_type='committee member';
                break;
            case 'presslink':
                $human_type='mwi press article';
                break; */
        }

        /* BENCHMARK */ $this->benchmark->mark('func_set_specific_type_format_end');

        return $human_type;
    }

    /* *************************************************************************
         get_node() - hooked into by node so that the loaded node can be different from the url, useful for dynamic loading
            saves over-riding everything in project data array model
            this function by default just returns the node from $node_model->get_node() and this functionality should be left
            as a default
         @param variable $id - the node id
         @return $node - the returned node
    */
    public function get_node($id,$user)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_node_start');

        if (('me'==$id or 1065==$id) &&
            is_numeric($user['user_id']))
        {
            $node=$this->node_model->get_node($user['user_id']);
        }
        else
        {
            $node=$this->node_model->get_node($id);
        }

        return $node;

        /* BENCHMARK */ $this->benchmark->mark('func_get_node_end');
    }

    /* *************************************************************************
        score_vote() - processes a vote action and returns a score for that vote
            based on site specific parameters
        @param $node - array containing the node which the vote was placed about
        @param $user - array containing the user who placed the vote
        @return $score - numeric score value based on the vote processing
    */
    public function score_vote($node,$user)
    {
        $score=0;

        $this->load->config('voting');
        $scores=$this->config->item('scores');

        // get all categories for this node
            $query=$this->db->select('*')->from('node_category')->where('node_id',$node['id']);
            $res=$query->get();
            $categories=$res->result_array();

        // loop and process, adding to score
            $score=0;

            foreach ($categories as $cat)
            {
                $query=$this->db->select('*')->from('hierarchy')->where(array('node_id'=>$cat['category_id']));
                $res=$query->get();
                $category=$res->row_array();

                $cat_score=$scores['augments'][$user[$scores['levels'][0]]][$category[$scores['levels'][1]]];
                if ($cat_score>$score)
                {
                    $score=$cat_score;
                }
            }

        return $score;
    }
}
