<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	require_once (APPPATH.'models/image_model.php');
/*
 class Image_upload_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/

class Image_upload_model extends Node_model {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('image_model');

		// hardcoded the width, which is used several places for image size etc.
			if (is_numeric($this->config->item('base_image_width')))
			{
				$this->width=$this->config->item('base_image_width');
			}
			else
			{
				$this->width=940;
			}
	}

    /* *************************************************************************
        create_image_directory() - create a new directory for user images if one doesn't already exist
        @param int $user_id - node_id that identifies the signed in user
        @return string $dir - the directory path
    */
    public function create_image_directory($user_id)
    {
        $dir=FCPATH.'user_img/'.$user_id;
        if(!is_dir($dir))
        {
            mkdir($dir, 0777);
            chmod($dir, 0777);
        }
        return $dir;
    }

    /* *************************************************************************
        save_scales() - saves a set of scaled images as defined in the set of hardcoded array values in the loop
        @param array $upload_data - the data from the $_FILES array after upload
        @return double $ratio - for the db save
    */
	public function save_scales($upload_data)
	{
		// make a string out the microtime and use this as the image reference
			$time_pieces=explode(' ',microtime());

			$this->session->set_userdata(array('image_file_id'=>$time_pieces[1].str_replace('0.','',$time_pieces[0]))); // this must stay in the session so that the value does not change as time does second by second

		$width = $this->image_model->get_width($upload_data['full_path']);
		$height = $this->image_model->get_height($upload_data['full_path']);
		foreach ($this->config->item('scale_sizes') as $scale)
		{
			// set the new file-name, time makes it unique for this user (unique within this users folder)
			$scale_file=$upload_data['file_path'].$this->session->userdata('image_file_id').'s'.$scale.$upload_data['file_ext'];
			copy($upload_data['full_path'],$scale_file);
			chmod($scale_file, 0777);
			$scale = $scale/$width;
			$uploaded = $this->resize_image($scale_file,$width,$height,$scale);
		}
		return $width/$height;
	}

    /* *************************************************************************
        save_scales() - saves a set of thumbnail images as defined in the set of hardcoded araray values in the loop
        @param array $tdata - the data from the form (hidden fields) that are populated by the user selecting thumbnail
    */
	public function save_thumbnails($tdata)
	{
		// get image details
			$this->load->model('node_model');
			$image=$this->node_model->get_node($tdata['node_id'],'image');

			// the users image directory
				$user_image_directory=FCPATH.'user_img/'.$image['user_id'].'/';

			// common path string, beyond this crop type and width are used to differentiate in loop
			 	$file_path=$user_image_directory.$image['image_filename'];

			// path the the original file, used by cropping functions
			 	$largest_size=$this->config->item('largest');
			 	$largest=is_numeric($largest_size) ? $largest_size : $this->width;
		 		$original_image=$file_path."s".$largest.$image['image_ext'];

		// get aspects
			$node=$this->node_model->get_node($tdata['owning_node_id']);
			$aspects=$this->image_model->get_aspects($node);

		// loop, do co-ordinates and thumbs
			$coordinates=array();
			foreach($aspects as $as)
			{
				if (0==count($as['crop_widths']))
				{
					die("check your crop width settings, make sure all arrays are populated");
				}

				// get the current from the form
					$prefix=($tdata['curr_asp']==$as['name']) ? "" : $as['name'].'_';

				// store the co-ordinate data
					$coordinates[$as['name']]=array(
						'x1'=>$tdata[$prefix.'x1'],
						'y1'=>$tdata[$prefix.'y1'],
						'x2'=>$tdata[$prefix.'x2'],
						'y2'=>$tdata[$prefix.'y2'],
						'w'=>$tdata[$prefix.'w'],
						'h'=>$tdata[$prefix.'h']
					);

				// do each site thumb
			        foreach ($as['crop_widths'] as $thumb)
					{
						$scale=$thumb/$tdata[$prefix.'w'];
						$tfile_name=$file_path.$as['filename_prefix'].$thumb.$image['image_ext'];
						$this->resize_thumbnail_image($tfile_name,$original_image,$tdata[$prefix.'w'],$tdata[$prefix.'h'],$tdata[$prefix.'x1'],$tdata[$prefix.'y1'],$scale);
					}
			}

		// retrieve all images with this filename, there may be more than one if it has been added
		// to multiple nodes and each one will need it's coordinates updating (doesn't sound great
		// here, but makes things simpler elsewhere to avoid a link table)
			$query=$this->db->select('*')->from('image')->where(array('image_filename'=>$image['image_filename']));
			$res=$query->get();
			$images=$res->result_array();

			foreach ($images as $i)
			{
				$update_data = array(
				    'thumbnail_coordinates'=>json_encode($coordinates)
				);

				$this->db->where('node_id', $i['node_id']);
				$this->db->update('image', $update_data);
			}
	}

	/* *************************************************************************
		 save_images() - saves main, names and deletes selected
		 @param array $post - all the checkbox and text details for save
		 @param array $images - all the images to iterate over using the id to query the post array
		 @param string / int $node_id - the node id to update with main
		 @return $message - the message to output
	*/
	public function save_images($post,$images,$node_id=null)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_save_images_start');

		// open message
			$message="";

		// ready for a batch update
			$update_data=array();

		// count removes to adjust the scores
			$removes=array();

		// if there are actually some images
			if (count($images))
			{
				foreach ($images as $img)
				{
					// name the names in the update array
						if (isset($post[$img["node_id"]."name"]))
						{
							if (image_name($img,1000)!=$post[$img["node_id"]."name"])
							{
								$message.=image_name($img)." has been renamed to '".$post[$img["node_id"]."name"]."'<br/>";
							}
							$update_data[]=array('node_id'=>$img["node_id"],'image_name'=>$post[$img["node_id"]."name"]);
						}

					// and if we hit a main then catch that
						if (isset($post["main"]) &&
							$post["main"]==$img["node_id"])
						{
							// set the main
								if ($img['main']!=1)
								{
									// only message if main is actually changed
									$message.=image_name($img).' is now the main image<br/>';
								}
								if (isset($post[$img["node_id"].'gallery']))
								{
									$update_data[]=array('node_id'=>$img["node_id"],'main'=>1,'in_gallery'=>1);
								}
								else
								{
									$update_data[]=array('node_id'=>$img["node_id"],'main'=>1,'in_gallery'=>0);
								}

								if (isset($node_id))
								{
									// update the node table with the new image id
										$this->image_model->set_image_in_node($node_id,$img);

									// update the action table to change images retrospectively
										$this->stream_model->set_image_in_stream($node_id,$img);

									// update the image in the message tables
										$this->load->model('message_model');
										$this->message_model->set_image_in_messages($node_id,$img);

									// update the image in the comment tables
										$this->load->model('comment_model');
										$this->comment_model->set_image_in_comments($node_id,$img);
								}


						}
						else
						{
							if (isset($post[$img["node_id"].'gallery']))
							{
								if (null==$node_id)
								{
									$update_data[]=array('node_id'=>$img["node_id"],'in_gallery'=>1);
								}
								else
								{
									$update_data[]=array('node_id'=>$img["node_id"],'main'=>0,'in_gallery'=>1);
								}
							}
							else
							{
								if (null==$node_id)
								{
									$update_data[]=array('node_id'=>$img["node_id"],'in_gallery'=>0);
								}
								else
								{
									$update_data[]=array('node_id'=>$img["node_id"],'main'=>0,'in_gallery'=>0);
								}
							}
						}

					// then removes
						if (isset($post[$img["node_id"]."remove"]))
						{
							$message.=image_name($img).' has been removed<br/>';
							$this->load->model('node_admin_model');
							$this->node_admin_model->delete_node('image',$img['node_id']);
							$removes[]=thumbnail_url($img,'300');
						}
				}
				// batch processing much more efficient :-)
					$this->db->update_batch('image',$update_data,'node_id');

				// close message
					$message.="";
			}
			else
			{
				$message.="<span class='fail message'>you must upload some images first before setting mains or deleting</span>";
			}

		// adjust the score and actions for the removal of images
			$remove_count=count($removes);
			if ($remove_count>0)
			{
				$this->load->model('score_model');
				$this->load->model('stream_model');

				$node=$this->node_model->get_node($node_id);

				$score=$this->score_model->get_score('actor_score',2,$node);

				$this->score_model->update_score($this->user,(0-($score*$remove_count)));

				foreach ($removes as $r)
				{
					$this->stream_model->undo_image_action($r,$node);
				}
			}

		return $message;

		/* BENCHMARK */ $this->benchmark->mark('func_save_images_end');
	}

    /* *************************************************************************
        resize_image() - resizes an image
        @param string $image - the path to an image file
        @param int $width - the current width of the target image
        @param int $height - the current height of the target image
        @param float $scale - the scale to apply to the width and height to get the new image size
        @return string $image - the path to the image
    */
    public function resize_image($image,$width,$height,$scale)
    {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
        switch($imageType)
        {
            case "image/gif":
                $source=imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source=imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source=imagecreatefrompng($image);
                break;
        }
        imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);

        switch($imageType)
        {
            case "image/gif":
                imagegif($newImage,$image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage,$image,90);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage,$image);
                break;
        }

        chmod($image, 0777);
        imagedestroy($newImage);
        return $image;
    }

    /* *************************************************************************
        resize_thumbnail_image() - resizes a thumbnail image
        @param string $thumb_image_name - the name of the thumbnail image to be created
        @param string $image - the path to an image file
        @param int $width - the width of the selected area
        @param int $height - the height of the selected area
        @param int $startx - the x1 co-ordinate, top left
        @param int $starty - the y1 co-ordinate, top left
        @param float $scale - the scale to apply to the width and height to get the new image size
        @return string $image - the path to the image
    */
    public function resize_thumbnail_image($thumb_image_name, $image, $width, $height, $startx, $starty, $scale)
    {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);

        // get base image width
        	$site_width=$this->width;

        // get the original image width
        	// see imagewidth

        // get the ratio between
        	$ratio=$imagewidth/$site_width;

        // multiply by the ratio to set the cropped area values appropriately
        	$width=floor($width*$ratio);
        	$height=floor($height*$ratio);
        	$startx=floor($startx*$ratio);
        	$starty=floor($starty*$ratio);

        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);

        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
        switch($imageType)
        {
            case "image/gif":
                $source=imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source=imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source=imagecreatefrompng($image);
                break;
        }
        imagecopyresampled($newImage,$source,0,0,$startx,$starty,$newImageWidth,$newImageHeight,$width,$height);
        switch($imageType)
        {
            case "image/gif":
                imagegif($newImage,$thumb_image_name);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage,$thumb_image_name,90);
             break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage,$thumb_image_name);
                break;
        }
        chmod($thumb_image_name, 0777);
        imagedestroy($newImage);
        return $thumb_image_name;
    }

    /* *************************************************************************
        save_image_action() - records the action the user made in creating the
            image
        @param $id - the id of the node to which the image was added
    */
    function save_image_action($id,$image_data)
    {
    	// build the path for this image
    		$image="/user_img/".$image_data['user_id']."/".$image_data['image_filename']."t300".$image_data['image_ext'];

		// set up some data for the action record to be made
			$node=$this->node_model->get_node($id);

		// signed in user adds image to node
			$this->stream_model->store_action(2,$this->user,$node,$node['user_id'],$image);
    }

	/* *************************************************************************
		 set_image_name() -
		 @param string
		 @param numeric
		 @param array
		 @return
	*/
	public function set_image_name($values)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_set_image_name_start');

		$update_data = array(
			'image_name' => $values['image_name']
		);

		$this->db->where('node_id', $values['node_id']);
		$this->db->update('image', $update_data);

		/* BENCHMARK */ $this->benchmark->mark('func_set_image_name_end');
	}

	/*
		update_filelist() - updates a javascript array that contains all the image files for this particular user
	*/
	public function update_filelist()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_update_filelist_start');
			$this->load->helper('image');
			$this->load->helper('url');

		// set the $node_name
			if (isset($node['name']) ? $node_name=$node['name'] : $node_name='loose image' );

		// get images
			$this->load->model('node_model');
			$images=$this->node_model->get_nodes(array('type'=>'image','user_id'=>$this->user['user_id']),1);

		// create string containing js array, and write
			$js_array='var tinyMCEImageList = new Array(';
			foreach ($images as $image)
			{
				if (0==$image['removed'])
				{
					$name=image_name($image,1000);
					$small_path=base_url().'user_img/'.$this->user['user_id'].'/'.$image['image_filename'].'s300'.$image['image_ext'];
					$large_path=base_url().'user_img/'.$this->user['user_id'].'/'.$image['image_filename'].'s460'.$image['image_ext'];
					$thumb_path=base_url().'user_img/'.$this->user['user_id'].'/'.$image['image_filename'].'t140'.$image['image_ext'];
					$js_array.="['".$name." - small', '".$small_path."'],";
					$js_array.="['".$name." - large', '".$large_path."'],";
					$js_array.="['".$name." - thumbnail', '".$thumb_path."'],";
				}
			}
			$js_array=substr($js_array,0,-1);
			$js_array.=');';

		// output file
			$js_filepath='user_files/'.$this->user['user_id'].'_image_list.js';
			$this->load->helper('file');
			delete_files($js_filepath);
			write_file($js_filepath,$js_array,'w');

		/* BENCHMARK */ $this->benchmark->mark('func_update_filelist_end');
	}

	/* *************************************************************************
	    image_panels() - creates an array of image panels based on image list sent in
	    @param $admin_images - an array containing the images to list
	    @param $node - the node these images belong too, defaults to all images
	    @return $phtml - a string of html the represents the panel
	*/
	function image_panels($admin_images,$node=array())
	{
	    $phtml="";

	    // add a text filter like other site pages
            $phtml.="<label for='filter' id='image_filter_name'>filter images by typing (searches name, gallery status and main status):</label>";
            $phtml.="<input id='image_list_filter' class='filter form_field' type='text' autofocus='autofucus'/>";
			$phtml.="<script type='text/javascript'>";
			$phtml.="    $('.filter').on('keyup',filter);";
			$phtml.="    function filter()";
			$phtml.="    {";
			$phtml.="        var value=$('.filter').val();";
			$phtml.="        if (value!='')";
			$phtml.="        {         ";
			$phtml.="            $('.image_panel_list .aim_panel').css('display','none');	";
			$phtml.="            value=value.replace(/\W/g,'').toLowerCase();";
			$phtml.="            $('.image_panel_list [id*='+value+']').css('display','block');";
			$phtml.="        }";
			$phtml.="        else";
			$phtml.="        {";
			$phtml.="            $('.image_panel_list .aim_panel').css('display','block');";
			$phtml.="        }";
			$phtml.="    }";
			$phtml.="</script>";

	    foreach($admin_images as $ai)
	    {
            // a jsid for filtering
            	if (1==$ai['in_gallery'] ? $gal='ingallery' : $gal='' );
            	if (1==$ai['main'] ? $main='main' : $main='' );
                $jsid=strtolower(preg_replace("/[^A-Za-z0-9]/",'',$ai['image_name'].$ai['image_ext'].$gal.$main));

            // set up the variables for this loop
                if ($ai["main"]==1 ? $main=' checked ' : $main='' );
                if ($ai["in_gallery"]==1 ? $gallery=" checked='checked' " : $gallery='' );
                $id=$ai["node_id"];
                $name=$ai["image_name"];
                $display_name=image_name($ai);
                $src="/user_img/".$ai['user_id']."/".$ai['image_filename']."t300".$ai['image_ext'];

            // set the image dimensions for the thumbnail panels
                $thumb_ratio=getimagesize($_SERVER['DOCUMENT_ROOT'].$src);
                if (is_numeric($this->config->item('aim_list_size')) ? $thumb_size=$this->config->item('aim_list_size') : $thumb_size=180 );
                if ($thumb_ratio[0]>=$thumb_ratio[1] ? $image_wh=" width='".$thumb_size."' height='' " : $image_wh=" width='' height='".$thumb_size."' " );

            // image tag
                $thumbnail_tag="<img src='".$src."' ".$image_wh."/>";

            // classes for marking main and gallery
                if ($ai["main"]==1 ? $extra_classes=' main_mark ' : $extra_classes=' ' );
                if ($ai["in_gallery"]==1 ? $extra_classes.=" gallery_mark " : $extra_classes.=' ' );

            // open panel
                $phtml.="<span id='".$jsid."' class='aim_panel ".$extra_classes."'>";

            // remove check, not for main image, or all images list
                if (1==$ai["main"] or
                	0==count($node))
                {
                    $phtml.="<div class='aim_remove'>&nbsp;</div>";
                }
                else
                {
                    $phtml.="<div class='aim_remove'><input id='remove".$id."' type='checkbox' name='".$id."remove' onclick='mark_remove()'/> <label for='remove".$id."'>remove</label></div>";
                }

            // thumbnail image and edit link
                $phtml.="<span class='aim_thumb'><a href='/images/edit/".$ai['node_id']."'>".$thumbnail_tag."</a></span>";

            // name with counter and javascript function for processing count
                $phtml.="<input id='".$id."name' class='aim_name form_field' type='text' name='".$id."name' value='".$name."' maxlength='140' onkeyup='char_count(\"".$id."\",\"name\",140)'/>";
                $phtml.="<div id='".$id."name_count' class='char_counter chars_ok'></div>";
                $phtml.="<script type='text/javascript'>";
                $phtml.="if (window.focus)";
                $phtml.="{";
                $phtml.="$('#".$id."name_count').html(140-$('#".$id."name').val().length);";
                $phtml.="}";
                $phtml.="</script>";

            // main and gallery buttons
                $phtml.="<div class='aim_buttons'>";
                if (count($node))
                {
                	$phtml.="<div class='aim_main'><input id='main".$id."' type='radio' name='main' value='".$id."' ".$main."/> <label for='main".$id."'>main</label></div>";
                }
                $phtml.="<div class='aim_ingallery'><input id='gallery".$id."' type='checkbox' name='".$id."gallery' ".$gallery."/> <label for='gallery".$id."'>in gallery</label></div>";
              	$phtml.="</div>";

            // close panel
              	$phtml.="</span>";
	    }

	    return $phtml;
	}
}
