<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Sitemap_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Sitemap_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
     generate_sitemap() - creates an XML file which is a site map for the site
     @param string
     @param numeric
     @param array
     @return
    */
    public function generate_sitemap()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_generate_sitemap_start');

        $this->load->helper('file_helper');

        $this->load->model('node_model');

        // open sitemap xml
            $xml='<?xml version="1.0" encoding="UTF-8" ?>';
            $xml.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        // get nodes
            $node_types=$this->config->item('node_types');

            $nodes=$this->node_model->get_nodes(array('visible'=>1,'protected'=>0),null,'name');

        // get site map priorities
            if (is_array($this->config->item('site_map_priority')))
            {
                $priorities=$this->config->item('site_map_priority');
            }
            else
            {
                $priorities=array(
                    'blog'=>0.8,
                    'calendar'=>0.8,
                    'category'=>0.5,
                    'event'=>1.0,
                    'groupnode'=>0.3,
                    'page'=>0.7,
                    'product'=>1.0,
                    'user'=>0.1
                );
            }

        // iterate
            foreach ($nodes as $n)
            {
                if (!in_array($n['type'],$this->config->item('nositemap')))
                {
                    $xml.='<url>';
                    $xml.='<loc>http://'.$this->config->item('full_domain').'/'.$n['url'].'</loc>';
                    $xml.='<lastmod>'.date('Y-m-d',$n['updated']).'</lastmod>';
                    $xml.='<changefreq>weekly</changefreq>';
                    if (isset($priorities[$n['type']]) ? $priority=$priorities[$n['type']] : $priority=0.8 );
                    $xml.='<priority>'.$priority.'</priority>';
                    $xml.='</url>';
                }
            }

        // close
            $xml.='</urlset>';

        // save as the sitemap.xml file
            write_file('sitemap.xml',$xml,'w');

        /* BENCHMARK */ $this->benchmark->mark('func_generate_sitemap_end');
    }
}
