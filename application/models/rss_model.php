<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Rss_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Rss_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('date_convert_helper');
    }

    /* *************************************************************************
         rss_link() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function rss_link($image="")
    {
        /* BENCHMARK */ $this->benchmark->mark('func__start');

        if (strlen($image)>0)
        {
            return "<a href='http://feeds.feedburner.com/".$this->config->item('feedburner_name')."' target='_blank'><span id='rss_link'></span></a>";
        }
        else
        {
            return "<span id='rss_link'>please <a href='http://feeds.feedburner.com/".$this->config->item('feedburner_name')."' target='_blank'>click here</a> to subscribe to the <a href='http://feeds.feedburner.com/".$this->config->item('feedburner_name')."' target='_blank'>".$this->config->item('site_name')." Blog RSS feed</a></span>";
        }

        /* BENCHMARK */ $this->benchmark->mark('func__end');
    }

    /* *************************************************************************
     build_rss_file() -
     @param string
     @param numeric
     @param array
     @return
    */
    public function build_rss_file($user_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_build_rss_file_start');

        // get user
            $query=$this->db->select('*')->from('user')->where(array('user_id'=>$user_id));
            $res=$query->get();
            $user=$res->row_array();

        $blogs=$this->get_blogs($user_id);

        if (count($blogs)>0)
        {
            $rss='<?xml version="1.0" encoding="UTF-8" ?>';
            $rss.='<rss version="2.0">';
            $rss.='<channel>';
            $rss.="<title>Blog by ".$user['display_name']." on ".$this->config->item('site_name')."</title>";
            $rss.="<description>Blog keeping you up to date with activity from ".$user['display_name']." on ".$this->config->item('site_name')."</description>";
            $rss.="<link>http://".$this->config->item('full_domain')."</link>";

            $rss.='<lastBuildDate>'.date("D, d M Y",time()).' +0000 </lastBuildDate>';
            $rss.='<pubDate>'.format_date($blogs[0]["created"],"D, d M Y").' +0000 </pubDate>';
            $rss.='<ttl>0</ttl>';

            $rss.='<item>';
            $rss.='<title>'.$blogs[0]['name'].'</title>';
            $rss.='<description>'.$blogs[0]['short_desc'].'</description>';
            $rss.="<link>http://".$this->config->item('full_domain')."/".$blogs[0]['url']."</link>";
            //$rss.='<guid>'.$item["itemID"].'</guid>';
            $rss.='<pubDate>'.format_date($blogs[0]["created"],"D, d M Y").' +0000 </pubDate>';
            $rss.='</item>';

            for ($x=1; $x<count($blogs); $x++)
            {
                $rss.='<item>';
                $rss.='<title>'.$blogs[$x]['name'].'</title>';
                $rss.='<description>'.$blogs[$x]['short_desc'].'</description>';
                $rss.="<link>http://".$this->config->item('full_domain')."/".$blogs[$x]['url']."</link>";
                //$rss.='<guid>'.$item["itemID"].'</guid>';
                $rss.='<pubDate>'.format_date($blogs[$x]["created"],"D, d M Y").' +0000 </pubDate>';
                $rss.='</item>';
            }

            $rss.='</channel>';
            $rss.='</rss>';
        }
        else
        {
            $rss='<?xml version="1.0" encoding="UTF-8" ?>';
            $rss.='<rss version="2.0">';
            $rss.='<channel>';
            $rss.="<title>Blog by ".$user['display_name']." on ".$this->config->item('site_name')."</title>";
            $rss.="<description>Blog keeping you up to date with activity from ".$user['display_name']." on ".$this->config->item('site_name')."</description>";
            $rss.="<link>http://".$this->config->item('full_domain')."</link>";

            $rss.='<lastBuildDate>'.date("D, d M Y",time()).' +0000 </lastBuildDate>';
            $rss.='<ttl>0</ttl>';
            $rss.='</channel>';
            $rss.='</rss>';
        }

        // write the file
            $fp=fopen("rss/".$user_id."blog.xml","w");
            fwrite($fp,$rss);
            fclose($fp);

        /* BENCHMARK */ $this->benchmark->mark('func_build_rss_file_end');
    }

    /* *************************************************************************
         get_blogs() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function get_blogs($user_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_blogs_start');

        $this->load->model('node_model');

        return $this->node_model->get_nodes(array('type'=>'blog','user_id'=>$user_id,'visible'=>1),null,'created desc');

        /* BENCHMARK */ $this->benchmark->mark('func_get_blogs_end');
    }
}
