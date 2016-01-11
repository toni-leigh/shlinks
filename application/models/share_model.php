<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Share_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Share_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         fbogs() - builds a set of facebook OG tags
         @param array $node - the node loaded by the system
         @param array $data - the data array, from which we will extract the individual image stuff if required
         @return
    */
    public function fbogs($node,$data)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_fbogs_start');

        /*dev_dump($node);
        dev_dump($image_node);
        dev_dump($image);*/

        // human type for text
            $human_type=$this->node_model->get_human_type($node['type']);

        // get the image data for individual image stuff
            $individual_image=$this->individual_image($data);

        // either the node, or the individual image with the title of the node it belongs to
            if (isset($individual_image['node']))
            {
                $fb="<meta property='og:title' content='".str_replace("'",'',$individual_image['image']['image_name']).", an image belonging to ".str_replace("'",'',$individual_image['node']['name'])." @ ".$this->config->item('site_name')."'/>";
            }
            else
            {
                $fb="<meta property='og:title' content='".str_replace("'",'',$node['name'])." @ ".$this->config->item('site_name')."'/>";
            }

        // some common tags
            $fb.='<meta property="og:type" content="website"/>';
            $fb.='<meta property="og:url" content="'.current_url().'"/>';

        // the image to share with facebook, use the thumbnail
            if (isset($individual_image['image']))
            {
                // show the thumbnail of the individual image
                    $fb.='<meta property="og:image" content="'.base_url().thumbnail_url($individual_image['image'],200).'"/>';
            }
            else
            {
                // get the image based on node and show that
                    $image=$this->image_model->get_images($node['id']);
                    if (count($image)>0)
                    {
                        $fb.='<meta property="og:image" content="'.base_url().thumbnail_url($image[0],200).'"/>';
                    }
                    else
                    {
                        $fb.='<meta property="og:image" content="'.base_url().$this->config->item('default_image').'"/>';
                    }
            }

        // finally close it up - set $node = $image_node so the short desc comes from the node to which the image belongs
            if (isset($individual_image['node']))
            {
                $node=$individual_image['node'];
            }
            $fb.='<meta property="og:site_name" content="'.$this->config->item('site_name').'"/>';
            $fb.='<meta property="og:description" content="'.$node['short_desc'].'"/>';
            $fb.='<meta property="fb:admins" content="'.$this->config->item('fbadmin').'"/>';

        /* BENCHMARK */ $this->benchmark->mark('func_fbogs_end');

        return $fb;
    }

    /* *************************************************************************
         facebook_like() - creates a facebook share button
         @param array $config - some data from the config file for how the facebook button should be formatted
         @param array $node - to share a different node from the one currently being viewed (likely on a list where all nodes can be shared)
         @param array $data - used to see if we are looking at an individual image
         @return
    */
    public function facebook_like($config,$node,$data)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_facebook_like_start');

        // get the image data for individual image stuff, the url is slightly different
            $individual_image=$this->individual_image($data);

        // open div
            $fbl="<div id='facebook_share' class='share_button'>";
            $fbl.="<div class='fb-like' ";

        //url
            if (isset($individual_image['node']))
            {
                $fbl.="data-href='".base_url()."/image/".$individual_image['image']['image_id']."' ";
            }
            else
            {
                $fbl.="data-href='".base_url().$node['url']."' ";
            }

        // build the fb like button config data attributes
            foreach ($config as $attr_name=>$attr_value)
            {
                if (strlen($attr_value))
                {
                    $fbl.="data-".$attr_name."='".$attr_value."' ";
                }
            }

        // close the opening div and the div element
            $fbl.="></div>";
            $fbl.="</div>";

        /* BENCHMARK */ $this->benchmark->mark('func_facebook_like_end');

        return $fbl;
    }

    /* *************************************************************************
         tweet_button() - creates a 'tweet this' button
         @param array $config - some data from the config file for how the tweet button should be formatted
         @param array $node - to share a different node from the one currently being viewed (likely on a list where all nodes can be shared)
         @param array $data - used to see if we are looking at an individual image
         @return $twb - the button
    */
    public function tweet_button($config,$node,$data)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_tweet_button_start');

        // get the image data for individual image stuff
            $individual_image=$this->individual_image($data);

        // open
            $twb="<div id='twitter_share' class='share_button'>";
            $twb.="<a href='https://twitter.com/share' class='twitter-share-button'";

        // always use the node for url and name, over-ride twitter default behaviour
            if (isset($individual_image['node']))
            {
                // human type for text
                    $human_type=$this->node_model->get_human_type($individual_image['node']['type']);

                // node stuff
                    $twb.=" data-url='".base_url()."image/".$individual_image['image']['image_id']."'";
                    $twb.=" data-text='".$config['intro']." image of ".str_replace("'",'',$individual_image['node']['name'])." @ ".$this->config->item('site_name')."'";
            }
            else
            {
                // human type for text
                    $human_type=$this->node_model->get_human_type($node['type']);

                // node stuff
                    $twb.=" data-url='".base_url().$node['url']."'";
                    $twb.=" data-text='".$config['intro']." ".str_replace("'",'',$node['name'])." @ ".$this->config->item('site_name')."'";
            }

        // configure
            if (0==$config['count'])
            {
                $twb.=" data-count='none'";
            }
            if (1==$config['large_button'])
            {
                $twb.=" data-size='large'";
            }
            if (strlen($config['hashtag']))
            {
                $twb.=" data-hashtags='".$config['hashtag']."'";
            }
            if (strlen($config['via']))
            {
                $twb.=" data-via='".$config['via']."'";
            }

        // close opening a tag
            $twb.=">";

        // button text
            $twb.=$config['text'];

        // close and script
            $twb.="</a>";
            $twb.="<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>";
            $twb.="</div>";

        /* BENCHMARK */ $this->benchmark->mark('func_tweet_button_end');

        return $twb;
    }

    /* *************************************************************************
         twidget() - builds a twitter tweet stream widget
            values are all taken from config for site specificity
         @return
    */
    public function twidget()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_twidget_start');

        $tw='';
        $tw.="<script src='http://widgets.twimg.com/j/2/widget.js'></script>";
        $tw.="<script>";
        $tw.=" $('#twitter_nojs').css('display','none');";
        $tw.=" new TWTR.Widget({\n";
        $tw.="     version: 2,\n";
        $tw.="     type: 'profile',\n";
        $tw.="     rpp: 20,\n";
        $tw.="     interval: 30000,\n";
        $tw.="     width: ".$this->config->item('twidth').",\n";
        $tw.="     height: ".$this->config->item('theight').",\n";
        $tw.="     theme: {\n";
        $tw.="         shell: {\n";
        $tw.="             background: '#".$this->config->item('tback_colour')."',\n";
        $tw.="             color: '#".$this->config->item('ttext_colour')."'\n";
        $tw.="         },\n";
        $tw.="         tweets: {\n";
        $tw.="             background: '".$this->config->item('tback_colour')."',\n";
        $tw.="             color: '".$this->config->item('ttext_colour')."',\n";
        $tw.="             links: '".$this->config->item('tlink_colour')."'\n";
        $tw.="         }\n";
        $tw.="     },\n";
        $tw.="     features: {\n";
        $tw.="         scrollbar: false,\n";
        $tw.="         loop: false,\n";
        $tw.="         live: true,\n";
        $tw.="         hashtags: true,\n";
        $tw.="         timestamp: true,\n";
        $tw.="         avatars: false,\n";
        $tw.="         behavior: 'all'\n";
        $tw.="     }\n";
        $tw.=" }).render().setUser('".$this->config->item('twitter_user')."').start();\n";
        $tw.="</script>";

        /* BENCHMARK */ $this->benchmark->mark('func_twidget_end');

        return $tw;
    }

    /* *************************************************************************
         individual_image() - gets the individual image details if this is that page
         @param array $data - to check for the correct values
         @return $individual_image
    */
    public function individual_image($data)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_individual_image_start');

        $individual_image=array();

        // get the image data for individual image stuff
            if (isset($data['image_node']))
            {
                $individual_image['node']=$data['image_node'];
            }
            if (isset($data['individual_image']))
            {
                $individual_image['image']=$data['individual_image'];
            }

        /* BENCHMARK */ $this->benchmark->mark('func_individual_image_end');

        return $individual_image;
    }
}
