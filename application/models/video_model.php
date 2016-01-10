<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Video_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         video_player() - gets a video player
         @param string $video_id - an identifier for the video itself, could be a youtube id, or the file-name if local
         @param string $source - the location of the video - currently local or youtube
         @param string $id - an identifier for this video player - so it can be selected from many (leave blank for single vid players on a page)
         @param array $p - a set of defining parametres for the video player
            ['w'] - integer width in pixels
            ['h'] - integer height in pixels
            ['bc'] - string hex colour back colour
            ['fc'] - string hex colour front colour - colours the control bar
            ['lc'] - string hex colour light colour - colours the control bar
            ['sc'] - string hex colour screen colour
            ['scroll'] - scroll bar position set to none for bespoke styling (most cases)
            ['poster'] - a poster image for a player that is not playing
         @return $video_html - the html for the player
    */
    public function video_player($video_id,$source,$p=array(),$id='')
    {
        /* BENCHMARK */ $this->benchmark->mark('func_video_player_start');

        if (isset($p['w']) ? $w=$p['w'] : $w=940 );
        if (isset($p['h']) ? $h=$p['h'] : $w=529 );

        if (isset($p['bc']) ? $bc=$p['bc'] : $bc='000000' );
        if (isset($p['fc']) ? $fc=$p['fc'] : $fc=$this->config->item('dark_colour') );
        if (isset($p['lc']) ? $lc=$p['lc'] : $lc=$this->config->item('light_colour') );
        if (isset($p['sc']) ? $sc=$p['sc'] : $sc='000000' );

        if (isset($p['scroll']) ? $scroll=$p['scroll'] : $scroll='none' );
        if (isset($p['poster']) ? $poster=$p['poster'] : $poster='/img/poster.png' );

        $id=str_replace(" ","",$id);


        $vid_html="";

        $vid_html.="<script type='text/javascript'>\n";
        $vid_html.="jwplayer('mp".$id."').setup({\n";
        $vid_html.="flashplayer: '/js/jwplayer/player.swf',\n";
        /* $vid_html.="width: '".$w."',\n";
        $vid_html.="height: '".$h."',\n"; */
        $vid_html.="width: $('#vid_player').width(),\n";
        $vid_html.="height: $('#vid_player').height()-".$this->config->item('controlbar_height').",\n";
        $vid_html.="backcolor: '".$bc."',\n";
        $vid_html.="frontcolor: '".$fc."',\n";
        $vid_html.="lightcolor: '".$lc."',\n";
        $vid_html.="screencolor: '".$sc."',\n";
        $vid_html.="controlbar: '".$scroll."',\n";
        $vid_html.="mute: 'false',\n";
        $vid_html.="volume: 90,\n";
        if ('youtube'==$source)
        {
            $vid_html.="file: 'http://www.youtube.com/watch?v=".$video_id."',\n";
        }
        elseif ('local'==$source)
        {
            $vid_html.="file: '/videos/".$video_id."',\n";
        }
        $vid_html.="image: '".$poster."'\n";
        $vid_html.="});\n";
        $vid_html.="$('.jw_controlbar .jw_controlbar').addClass('sprite');";
        $vid_html.="</script>\n";
        $vid_html.="</div>\n";

        return $vid_html;

        /* BENCHMARK */ $this->benchmark->mark('func_video_player_end');
    }

    /* *************************************************************************
        get_other_videos() - gets other videos from the system, for recommendation
            purposes
        @param $node - array containing the node
        @return $videos - array containing other nodes with videos
    */
    function get_other_videos($node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_other_videos_start');

        $videos=array();

        $query=$this->db->select('*')->from('node')->where(array('video_src !='=>'','id !='=>$node['id']));
        $res=$query->get();
        $videos=$res->result_array();

        /* BENCHMARK */ $this->benchmark->mark('func_get_other_videos_end');

        return $videos;
    }
}
