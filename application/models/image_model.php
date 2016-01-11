<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Image_model

 * @package        Template
 * @subpackage    Template Libraries
 * @category    Template Libraries
 *
*/

class Image_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        get_images() - queries image table, on node_id for this nodes images, or creator for this users loose images
        @param int $id - node_id that identifies the signed in user, can be null in the case of a users loose images being retreived
        @param int $single - tells the function to just return the first image from the query (the main image)
        @return - a 2d array of the rsults
    */
    public function get_images($id,$single=0)
    {
        $this->load->model('node_model');
        $images=$this->node_model->get_nodes(array('type'=>'image','owning_node_id'=>$id),1,'main desc');

        return $images;
    }

    /*************************************************************************
        get_image() - gets an individual image based on it's image id
        @param $image_id - the id of the image to retrieve
        @return $image - the individual image array
    */
    function get_image($image_id)
    {
        $image=array();

        $image=$this->node_model->get_node($image_id,'image');

        return $image;
    }

    /* *************************************************************************
         all_images() - retrieves all a users uploaded images whether they are assigned to any node or not
         @param string
         @param numeric
         @param array
         @return
    */
    public function all_images()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_all_images_start');

        $images=array();

        /*$query=$this->db->select('*')->from('image')->group_by(array('user_id','image_filename'));
        $res=$query->get();
        $images=$res->result_array();*/

        return $images;

        /* BENCHMARK */ $this->benchmark->mark('func_all_images_end');
    }

    /* *************************************************************************
         get_individual_image() - retrieves an image on image_id rather than a set on node_id
         @param int $image_id - db id for the image
         @return $image
    */
    public function get_individual_image($image_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_individual_image_start');

        // get image if id is numeric
            if (is_numeric($image_id))
            {
                $query=$this->db->select('*')->from('image')->where(array('image_id'=>$image_id));
                $res=$query->get();
                $image=$res->row_array();
            }
            else
            {
                $image=null;
            }

        // return null if image not present
            if (0==count($image))
            {
                $image=null;
            }

        /* BENCHMARK */ $this->benchmark->mark('func_get_individual_image_end');

        return $image;
    }

    /* *************************************************************************
         save_image() - save a new image to the database
         @param array $insert_data - the data for the new image
         @return
    */
    public function save_image($insert_data)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_image_start');

        // add node values to insert data
            $insert_data['id']=null;
            $insert_data['name']=$insert_data['image_name'];
            $insert_data['type']='image';

            $insert_data['user_id']=$this->user['id'];
            $insert_data['user_name']=$this->user['name'];
            $insert_data['user_image']=$this->user['image'];

        // save
            $this->load->model('node_admin_model');
            $id=$this->node_admin_model->node_save($insert_data,'image');

        return $id;

        /* BENCHMARK */ $this->benchmark->mark('func_save_image_end');
    }

    /* *************************************************************************
        get_aspects() - gets the aspects from config, on type, with defauls
        @param $node - the node to find the type with
        @return $aspects - the array of aspect ratios
    */
    function get_aspects($node)
    {
        $aspects=array();

        $this->config->load('admin');
        if (is_array($this->config->item('aspects')))
        {
            $aspect_config=$this->config->item('aspects');

            if (isset($aspect_config[$node['type']]))
            {
                $aspects=$aspect_config[$node['type']];
            }
            else
            {
                $aspects=$aspect_config['default'];
            }
        }
        else
        {
            $aspects=array(
                'name'=>'thumbnail',
                'ratio'=>'1:1',
                'filename_prefix'=>'t',
                'crop_widths'=>array(
                    40,
                    100,
                    200,
                    300,
                    460
                )
            );
        }

        return $aspects;
    }

    /* *************************************************************************
         set_image_in_node() - sets the image in the node table, for the default view of the node and on lists etc
         @param string / int $node_id
         @param array $img - the array of data about the image
    */
    public function set_image_in_node($node_id,$img)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_node_start');

        $this->load->model('node_admin_model');

        // the image update data
            $node_update_data = array(
                'image' => thumbnail_url($img,'300')
            );

        // update
            $this->node_admin_model->node_update($node_id,$node_update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_node_end');
    }

    /* *************************************************************************
         image_slider() - builds an image slider
         @param string $name
         @param array $images - the images to use for the slider
         @param array $dimensions - the sizes to use to build the slider
         @param array $properties - the bits of the slider to display
            'slider'
            'buttons'
            'sequence'
            'count'
         @return $image_slider - the image slider
    */
    public function image_slider()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_image_slider_start');

        /* BENCHMARK */ $this->benchmark->mark('func_image_slider_end');
    }

    /* *************************************************************************
         make_panel() - make a panel of fixed width and height with an image of any ratio centered, either vertically or horinzontally
         @param array $image - the image details
         @param int $iwidth - the width of saved image to load
         @param int $width - the panel width
         @param int $height - the panel height
         @param Boolean $share - whether to add share buttons to this image
         @return $html - for a panel
    */
    public function make_panel($image,$iwidth,$width,$height,$share=null,$add_name=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_make_panel_start');

        // tag dimensions for resizing the image in the panel
            $tag_width=0;
            $tag_height=0;
            $rp=0;
            $lp=0;
            $tp=0;
            $bp=0;

        // get the actual dimensions of the image
            $actual_width=$this->get_width($_SERVER['DOCUMENT_ROOT'].image_url($image,$iwidth));
            $actual_height=$this->get_height($_SERVER['DOCUMENT_ROOT'].image_url($image,$iwidth));

        if ($actual_width>0 &&
            $actual_height>0)
        {
            if ($actual_width==$width &&
                $actual_height==$height)
            {
                // skip any complicated calculations, this image is the same size as the panel
                    $tag_width=$actual_width;
                    $tag_height=$actual_height;
            }
            else
            {
                // get the ratio between width and height
                    $image_ratio=$actual_width/$actual_height;
                    $panel_ratio=$width/$height;

                if ($image_ratio==$panel_ratio)
                {
                    // again skip calculations, just resize image to the panel size as no padding is needed
                    // the image ratio is the same as the panel ratio
                        $tag_width=$width;
                        $tag_height=$height;
                }
                else
                {
                    // here we need to work out padding sizes
                        if ($image_ratio>$panel_ratio)
                        {
                            // the ratio of width divided by height is greater for the image than it is for the panel
                            // the result will be an image with padding at the top and the bottom

                            // reduce the width down to the panel width
                                $tag_width=$width;

                            // get a ratio image width / panel width
                                $width_ratio=$width/$actual_width;

                            // use this ratio to adjust the height
                                $tag_height=floor($actual_height*$width_ratio);

                            // subtract the new height from the panel height and divide by two to get the padding size
                                $pad_height=floor(($height-$tag_height)/2);
                                $tp=$pad_height;
                                if (0==($height-$tag_height)%2 ? $bp=$tp : $bp=$tp++ );

                            // bulk out padding
                                $lp=$tag_width;
                                $rp=$tag_width;
                        }
                        else
                        {
                            // the ratio of width divided by height is greater for the panel than it is for the image
                            // the result will be an image with padding at the left and the right

                            // reduce the height down to the panel height
                                $tag_height=$height;

                            // get a ratio image height / panel height
                                $height_ratio=$height/$actual_height;

                            // use this ratio to ajudt the width
                                $tag_width=floor($actual_width*$height_ratio);

                            // subtract the new width from the panel width and divide by two to get the padding size
                                $pad_width=floor(($width-$tag_width)/2);
                                $lp=$pad_width;
                                if (0==($width-$tag_width)%2 ? $rp=$lp : $rp=$lp++ );

                            // bulk out padding
                                $tp=$tag_height;
                                $bp=$tag_height;
                        }
                }
            }

            // output image - here we set the onclick functionality
                $fsi="<div class='img_panel' style='width:".$width."px;height:".$height."px'>";
                $fsi.="<div class='img_pad' style='width:".$lp."px;height:".$tp."px;'></div>";
                //"<img itemprop='image' class='fs_image' src='".image_url($image,$iwidth)."' width='".$tag_width."' height='".$tag_height."' alt='".$image['image_name']."'/>";
                $fsi.=image_tag($image,$iwidth,$tag_width);
                $fsi.="<div class='img_pad' style='width:".$rp."px;height:".$bp."px;'></div>";
                $fsi.="</div>";

                // add an image name
                    if (1==$add_name)
                    {
                        $fsi.="<span class='panel_image_name'>".$image['image_name']."</span>";
                    }

                // add in the share buttons
                    if (1==$share)
                    {
                        $fsi.="<div class='image_panel_share'>";

                        // create image and image node
                            $image_node=$this->node_model->get_node($image['node_id']);
                            $node_and_image=array(
                                'individual_image'=>$image,
                                'image_node'=>$image_node
                            );

                        /*$this->load->model('share_model');
                        $this->load->helper('image_helper');

                        // get the sites to share to from this node
                            $sites=$this->config->item('social_sites');

                        // open social_buttons
                            $fsi.="<div id='image_share_buttons'>";

                        // iterate - we can add extra ones in here as and when
                            foreach ($sites as $site=>$config)
                            {
                                switch ($site)
                                {
                                    case 'facebook':
                                        $fsi.=$this->share_model->facebook_like($config,null,$node_and_image);
                                        break;
                                    case 'twitter':
                                        $fsi.=$this->share_model->tweet_button($config,null,$node_and_image);
                                        break;
                                }
                            }

                        // close social_buttons
                            $fsi.="</div>";*/

                        // pinterest
                            $fsi.=pinterest_button($image_node['url'],image_url($image,$this->config->item('base_image_width')),str_replace("'","",$image['image_name']));

                        $fsi.="</div>";
                    }

        }
        else
        {
            $fsi="probable missing image: http://".$this->config->item('full_domain').image_url($image,$iwidth)." - most likely a config path issue or an olf test image saved before the default save image sizes were set for this site";
        }

        /* BENCHMARK */ $this->benchmark->mark('func_make_panel_end');

        return $fsi;
    }

    /* *************************************************************************
        set_config_sizes() - used to go over a set of current images and resize them to a new set of config values
        ### NB !!! this will need changing for new image crop stuff !!! NB ###
        @return
    */
    public function set_config_sizes()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_set_config_sizes_start');
        //$this->load->helper('image_helper');
         /* $this->load->model('image_upload_model');

        $image_sizes=$this->config->item('scale_sizes');

        $base_image_width=$this->config->item('base_image_width');

        $query=$this->db->select('*')->from('image')->where(array('removed'=>0));
        $res=$query->get();
        $images=$res->result_array();

        $c=1;
        foreach ($images as $i)
        {
            echo "Image: ".$c."<br/><br/>";
            foreach ($image_sizes as $is)
            {
                $src=$_sERVER['DOCUMENT_ROOT'].image_url($i,$is);
                echo "src:".$src."<br/>";
                $base_src=$_sERVER['DOCUMENT_ROOT'].image_url($i,$base_image_width);
                echo "bsrc:".$base_src."<br/>";
                $w=$this->get_width($base_src);
                $h=$this->get_height($base_src);
                $scale=$is/$w;
                echo "-W-".$w."-H-".$h."-s-".$scale."<br/>";
                if (is_file($src))
                {
                    echo "yes".$is."</br><br/>";
                }
                else
                {
                    echo "no".$is."</br>";
                    $scale_file=str_replace('s'.$base_image_width,'s'.$is,$base_src);
                    echo "scale file:".$scale_file."<br/>";
                    copy($base_src,$scale_file);
                    chmod($scale_file, 0777);
                    $this->image_upload_model->resize_image($scale_file,$w,$h,$scale);
                    echo "</br><br/>";
                }
            }
            echo "<br/>------------------------------------------------<br/><br/><br/>";
            //break;
        }

        /* BENCHMARK */ $this->benchmark->mark('func_set_config_sizes_end');
    }

    /* *************************************************************************
     returns image height
    */
    public function get_height($image)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_height_start');
        $size = getimagesize($image);
        /* BENCHMARK */ $this->benchmark->mark('func_height_end');
        $height = $size[1];
        return $height;
    }

    /* *************************************************************************
     returns image width
    */
    public function get_width($image)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_width_start');
        $size = getimagesize($image);
        /* BENCHMARK */ $this->benchmark->mark('func_width_end');
        $width = $size[0];
        return $width;
    }
}
