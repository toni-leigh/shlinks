<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
    /*
     class Comment_model

     * @package		Template
     * @subpackage	Template Libraries
     * @category	Template Libraries
     * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
     *
     *  Comment_model
    */
    class Comment_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        get_comments() - gets the comments for a single node
        @param $node - this node to retrieve comments from
        @return $comments - the comments retrieved
    */
    function get_comments($node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_comments_start');

        $comments=array();

        $query=$this->db->select('*')->from('comment')->where(array('node_id'=>$node['id']))->order_by('comment_time');
        $res=$query->get();
        $comments=$res->result_array();

        /* BENCHMARK */ $this->benchmark->mark('func_get_comments_end');

        return $comments;
    }

    /* *************************************************************************
        comment_form() - gets a bit of html for making a comment
        @param $node - the node on which the comment is being made
        @return $form_html - the html containing the comment form
    */
    function comment_form($node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_comment_form_start');

        $form_html="";

        $form_html.="<form method='post' action='/comment/save'>";
        $form_html.="<input class='js_node_id' type='hidden' name='node_id' value='".$node['id']."'/>";
        $form_html.="<textarea id='js_commentfield' class='js_comment comment_field' name='comment' autofocus='autofocus'></textarea>";
        $form_html.="<input class='js_comment_submit submit' type='submit' name='submit' value='make comment'/>";
        $form_html.="</form>";

        /* BENCHMARK */ $this->benchmark->mark('func_comment_form_end');

        return $form_html;
    }

    /* *************************************************************************
        save_comment() - saves a comment to the database
        @param $node_id - the id of the node of which the comment is being made
        @param $comment - the text of the comment
    */
    function save_comment($node_id,$comment)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_comment_start');

        $this->load->helper('data_helper');

        $comment=convert_urls($comment);

        $insert_data=array(
            'node_id'=>$node_id,
            'user_id'=>$this->user['id'],
            'name'=>$this->user['name'],
            'url'=>$this->user['url'],
            'image'=>$this->user['image'],
            'comment'=>"<p>".str_replace("<br />","</p><p>",nl2br($comment))."</p>"
        );

        $this->db->insert('comment',$insert_data);

        $query=$this->db->select('*')->from('comment')->where(array('comment_id'=>$this->db->insert_id()));
        $res=$query->get();
        $comment=$res->row_array();

        /* BENCHMARK */ $this->benchmark->mark('func_save_comment_end');

        return $comment;

    }

    /* *************************************************************************
        set_image_in_comments() - sets the image in the comment table for all this users
            comments
        @param $node_id - the node id of the user
        @param $img - the array containing the image data
    */
    function set_image_in_comments($node_id,$img)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_comments_start');

        $image_path=thumbnail_url($img,'300');

        // actor first
            $update_data = array(
                'image'=>$image_path
            );

            $this->db->where('user_id', $node_id);
            $this->db->update('comment', $update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_comments_end');

    }
}
