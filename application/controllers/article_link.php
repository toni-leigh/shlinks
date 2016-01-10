<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Article_link

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
*/
    class Article_link extends Node {

    public function __construct()
    {
        parent::__construct();
    }

    public function edit($edit_link_id)
    {
        //dev_dump($edit_link_id);
        $link = $this->node_model->get_node($edit_link_id,'link');
        //dev_dump($link);
        if ($link['user_id'] == $this->user['user_id'])
        {
            $this->data['link_vals'] = $link;
            $this->data['link_vals']['url']=$link['url'];
            $this->data['link_vals']['title']=$link['name'];
            $this->data['link_vals']['description']=$link['node_html'];
            $this->data['link_vals']['cats']=$this->db->select('*')->from('node_category')->where(array('node_id' => $link['id']))->get()->result_array();
            $this->display_node('link-details');
        }
    }

    /* *************************************************************************
     add() -
     @param string
     @param numeric
     @param array
     @return
    */
    public function add()
    {

        /* BENCHMARK */ $this->benchmark->mark('func_add_start');

        // get
            $url=$this->get_input_vals();

            if (!isset($url['new_url']))
            {
                $url=$this->session->userdata('post');

                if (isset($url['url_store']))
                {
                    $url['new_url']=$url['url_store'];
                }
                else
                {
                    $url['new_url']="";
                }
            }

        // form vals
            $this->data['link_vals']['id']='';
            $this->data['link_vals']['url']=$url['new_url'];
            $this->data['link_vals']['title']="";
            $this->data['link_vals']['description']="";

        // message for reload
            $success='fail';
            $message='scrape failed - probably not a link';

        // scrape
            $title='';
            $description='';
            if (strlen($url['new_url'])>0)
            {
                $html=$this->file_get_contents_curl($url['new_url']);
                $doc = new DOMDocument();
                @$doc->loadHTML($html);
                $nodes = $doc->getElementsByTagName('title');

                // values
                    if (isset($nodes->item(0)->nodeValue))
                    {
                        $title = $nodes->item(0)->nodeValue;
                        $description='';

                        $metas = $doc->getElementsByTagName('meta');

                        for ($i = 0; $i < $metas->length; $i++)
                        {
                            $meta = $metas->item($i);

                            $name=strtolower($meta->getAttribute('name'));

                            if(strpos(strtolower($name),'description')>0 or
                               'description'==$name)
                            {
                                $description = $meta->getAttribute('content');
                            }
                        }

                    // form vals
                        $this->data['link_vals']['url']=$url['new_url'];
                        $this->data['link_vals']['title']=$title;
                        $this->data['link_vals']['description']=$description;

                    // message for reload
                        $success='success';
                        $message='link scraped successfully';
                    }
            }

        // display
            $bounce_back=$this->session->userdata('bounce_back');
            if ($url['new_url']!="" &&
                $bounce_back!=1)
            {
                // only display message if not empty
                    $this->session->set_userdata("message","<span class='".$success." message'>".$message."</span>");
            }
            $this->session->set_userdata("bounce_back",0);
            $this->display_node('link-details');

        /* BENCHMARK */ $this->benchmark->mark('func_add_end');
    }

    /* *************************************************************************
         save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function save()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_start');

        $vals=$this->get_input_vals();

        $this->load->model('node_admin_model');

        // find duplicate links
            $duplicate=0;

            if (!is_numeric($vals['id'])) // only check if an add rather than an edit
            {
                $check_url=str_replace(array('http://','https://','www.'), "", $vals['url']);

                $query=$this->db->select('*')->from('node')->where(array('url'=>$check_url));
                $res=$query->get();
                $links=$res->row_array();

                $duplicate=count($links);
            }

        if ($vals['url']!="" &&
            $vals['category_id']!="" &&
            $vals['name']!="" &&
            0==$duplicate)
        {
            $categories=$vals['category_id'];
            unset($vals['category_id']);

            $vals['url']=str_replace(array('http://','https://','www.'), "", $vals['url']);

            $link_id=$this->node_admin_model->node_save($vals,'link');

            $link=$this->node_model->get_node($link_id);

            $time=time();

                $user=$this->node_model->get_node($this->user['user_id']);

            $cat_ids=explode(",", $categories);
            unset($cat_ids[count($cat_ids)-1]);

            if (is_numeric($vals['id']))
            {
                $this->db->delete('node_category', array('node_id'=>$link['id']));
            }

            foreach ($cat_ids as $cat_id)
            {
                $category=$this->node_model->get_node($cat_id);

                $insert_data=array(
                    'node_id'=>$link['id'],
                    'category_id'=>$cat_id
                );
                $this->db->insert('node_category',$insert_data);


                // the link going through the action of being added to a category
                    $this->stream_model->store_action(11,$this->user,$category);

                // the category going through the action of receiving a link
                    $this->stream_model->store_action(11,$this->user,$link);
            }

            // display
                if (is_numeric($vals['id']))
                {
                    $this->_log_action('article_link/edit/'.$vals['id'],"The link has been successfully edited","success");
                    $this->_reload('article_link/edit/'.$vals['id'],"The link has been successfully edited","success");
                }
                else
                {
                    $this->_log_action('links',"The link has been successfully added","success");
                    $this->_reload('links',"The link has been successfully added","success");
                }
        }
        else
        {
            $this->session->set_userdata('bounce_back',1);
            // get around the fact the user in the session has a URL too
            $vals['url_store']=$vals['url'];

            $this->_store_post($vals);

            $slug_element = (is_numeric($vals['id'])) ? "edit/".$vals['id'] : "add";

            // display
                $this->_log_action('links',"The link has not been added","fail");
                if (0==$duplicate)
                {
                    $this->_reload("article_link/".$slug_element,"You need a URL, with a name and at least one category to add a link","fail");
                }
                else
                {
                    $this->_reload("article_link/".$slug_element,"This link has already been added","fail");
                }

        }

        /* BENCHMARK */ $this->benchmark->mark('func_save_end');
    }

    /* *************************************************************************
         () -
         @param string
         @param numeric
         @param array
         @return
    */
    public function file_get_contents_curl($url)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_file_get_contents_curl_start');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;

        /* BENCHMARK */ $this->benchmark->mark('func_file_get_contents_curl_end');
    }

    public function categorised_links()
    {
        // get categories
        $this->load->model('hierarchy_model');

        $query=$this->db->select('*')->from('hierarchy')->join('node','hierarchy.node_id=node.id')->join('category','hierarchy.node_id=category.node_id')->order_by('lft');
        $res=$query->get();
        $cats=$res->result_array();

        $stubbed_cats = array();

        foreach ($cats as $c) {
            $stubbed_cats[] = array(
                $c['id'],
                $c['parent_id'],
                $c['lft'],
                $c['name']
            );
        }

        dev_dump($stubbed_cats);

        die();

        // iterate, add links
    }
}
