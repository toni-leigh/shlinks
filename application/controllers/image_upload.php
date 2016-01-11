<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    ini_set("memory_limit","300M");
	require_once (APPPATH.'controllers/node.php');
/*
 class Image_upload

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
    class Image_upload extends Node {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('image_model');
		$this->load->model('image_upload_model');
		$this->load->model('node_model');
		$this->load->library('input');
		$this->load->helper('form');
		$this->load->helper('url');

		// hardcoded the width, which is used several places for image size etc.
			if (is_numeric($this->config->item('base_image_width')))
			{
				$this->width=$this->config->item('base_image_width');
			}
			else
			{
				$this->width=940;
			}

		// common additions to the 'data' array
			$this->data['upload']=array(); // holds data about the upload
			$this->data['message']='';
			$this->data['image_upload_js']="<script type='text/javascript' src='/js/jquery.imgareaselect.pack.js'></script>";
			$input_data=array(
				'id'=>'image_name',
				'name'=>'image_name',
				'value'=>set_value('image_name')
			);
			$this->data['form']['image_name']=form_input($input_data);

		// create a new directory if this is the first time the user is uploading images
			$this->path=$this->image_upload_model->create_image_directory($this->user['user_id']);
	}

	/* *************************************************************************
	 images() - builds the upload file form and image list
	 @param int $id - the node to which these images belong, if null this is loose image upload
	 return void
	*/
	public function images($id=null)
	{
		// set the edit node to which these images apply
			$this->data['edit_node']=$this->node_model->get_node($id);

		// set an owns edit node variable to show the form if the signed in user owns the node
			$this->data['owns_edit_node']=($this->data['edit_node']['user_id']==$this->user['id']) ? 1 : 0;

		// get the node and images
			$this->data['node']=$this->node_model->get_node($id);
			$this->data['admin_images']=$this->image_model->get_images($this->data['node']['id']);

		// set the form action attributes
			if ('back'==$this->config->item('image_upload_link'))
			{
				$this->data['form']['upload_open']=form_open_multipart('/'.$this->data['node']['type'].'/'.$this->data['node']['id'].'/images/upload');
				$this->data['form']['set_open']=form_open_multipart('/'.$this->data['node']['type'].'/'.$this->data['node']['id'].'/images/set');
			}
			else
			{
				$this->data['form']['upload_open']=form_open_multipart('/'.uri_string().'/upload');
				$this->data['form']['set_open']=form_open_multipart('/'.uri_string().'/set');
			}

		// get all the images for output so images from other nodes can be associated with this node
			$this->data['all_images']=$this->image_model->all_images();

		// get the image panels
			$this->data['image_panels']=$this->image_upload_model->image_panels($this->data['admin_images'],$this->data['edit_node']);

		// call the images node
			$this->display_node('ad_images');
    }

	/* *************************************************************************
		 set() - sets the main image for this node or loose image set, and deletes too
		 @param int $id - retrieves images for this node id or all users loose images if null
	*/
	public function set($id=null)
	{
		// get post
			$post=$this->input->post();

		// our image helper is also needed
			$this->load->helper('image');

		// update the file list for where the image file names are required
			$this->image_upload_model->update_filelist();

		// get the image panels
			if (null==$id)
			{
				$images=$this->image_model->all_images();

				$message=$this->image_upload_model->save_images($post,$images);

				$this->_log_action("image_upload","mass image set all images","success");

			    // success
			        $this->_reload("all-images",$message,"success");
			}
			else
			{
				$images=$this->image_model->get_images($id);

				$message=$this->image_upload_model->save_images($post,$images,$id);

				$this->_log_action("image_upload","mass image set ".$id,"success");

			    // success
					$node=$this->node_model->get_node($id);
			        $this->_reload($node['type']."/".$node['id']."/images",$message,"success");
			}
	}

	/* *************************************************************************
		 upload() - uploads an image
		 @param int $id - uploaded to this $id or to loose images if null
	*/
	public function upload($id=null)
	{
		// set the edit node to which these images apply
			$this->data['edit_node']=$this->node_model->get_node($id);

		$this->data['node']=$this->node_model->get_node($id);
		$this->data['admin_images']=$this->image_model->get_images($this->data['node']['id']);

		// set the upload restraints for upload library
			$config['upload_path'] = $this->path;
			$config['max_size']	= 24*1048;
			$config['max_width'] = '0';
			$config['max_height'] = '0';

			// allowed types come from config
				if (strlen($this->config->item('image_allowed_types')) ? $types=$this->config->item('image_allowed_types') : $types='gif|jpg|png' );
				$config['allowed_types'] = $types;

		// we need the upload library
			$this->load->library('upload', $config);

		// do the upload
		if ( ! $this->upload->do_upload())
		{
			// message
				$this->data['message'].="<span class='fail message'>The upload has failed:<br/>";
				$this->data['message'].=$this->upload->display_errors()."<br/>";
				$this->data['message'].="</span>";

			// set the form action attributes - on fail we reload the set and upload forms
				$this->data['form']['upload_open']=form_open_multipart('/'.uri_string());
				$this->data['form']['set_open']=form_open_multipart('/'.str_replace('upload','set',uri_string()));

			// log and display
				$this->_log_action("image_upload","upload image","fail - ".$this->data['message']);
				$this->display_node($id,'images');
		}
		else
		{
			$upload_data=$this->upload->data();

			// retrieve the aspect ratio array
				$aspects=$this->image_model->get_aspects($this->data['node']);

			// message
				$this->data['message'].="<span class='success message'>The image was successfully uploaded, please select your thumbnail area</span>";

			// save the scaled versions of the image (this returns the image ratio too)
				$ratio=$this->image_upload_model->save_scales($upload_data);
				$this->session->set_userdata('ratio',$ratio);

			// session for the image post data to be saved as a db record
				$this->session->set_userdata($this->input->post());
				$this->session->set_userdata($this->upload->data());

			// some things for the thumbnail selection form
				$crop_img_src='/user_img/'.$this->session->userdata('user_id').'/'.$this->session->userdata('image_file_id').'s'.$this->width.$upload_data['file_ext'];
				$crop_img_height=$this->image_model->get_height($upload_data['file_path'].$this->session->userdata('image_file_id').'s'.$this->width.$upload_data['file_ext']);

				// a crop image for each aspect
					foreach ($aspects as $as)
					{
						$this->data['crop_image'][$as['name']]=img(array(
							'src' => $crop_img_src,
							'alt' => 'image prepared, ready to be cropped',
							'id' => $as['name'],
							'width' => $this->width,
							'height' => $crop_img_height,
							'title' => 'image prepared, ready to be cropped'
						));
					}

				$this->data['img_width']=$this->width;
				$this->data['img_height']=$crop_img_height;
				$ratio=$this->width/$crop_img_height;
				$this->data['upload']=$upload_data;
				$this->data['form']['thumbnail_open']=form_open('/'.str_replace('upload','thumbnail',uri_string()));
				$this->data['wrong_image']='/'.str_replace('/upload','',uri_string());
				$this->data['form']['node_id']=form_hidden('node_id',0);
				$this->data['form']['owning_node_id']=form_hidden('owning_node_id',$this->data['node']['id']);
				$this->data['default_image_name']=$upload_data['raw_name'];

			// get the deafult space around the crop area which defines how close to the edge
			// each default crop area goes, common across all
				if (is_numeric($this->config->item('default_thumb_space')) ? $dcsp=$this->config->item('default_thumb_space') : $dcsp=0.8 );

			// set crop areas on load
				$dc=array();

				for($x=0;$x<count($aspects);$x++)
				{
					$aspect=$aspects[$x];

					$split=explode(':',$aspect['ratio']);

					$crop_ratio_decimal=$split[0]/$split[1];

					$crop=array();

					$crop['ratio']=$aspect['ratio'];

					// set the position of this crop
						// the dimensions of the thumbnail area
						if ($ratio>=$crop_ratio_decimal)
						{
							$crop['h']=ceil($crop_img_height*$dcsp);
							$crop['w']=ceil($crop['h']*$crop_ratio_decimal);
						}
						else
						{
							$crop['w']=ceil($this->width*$dcsp);
							$crop['h']=ceil($crop['w']/$crop_ratio_decimal);
						}

						// the top left position, floored to give slight bias with odds to top left
						$crop['x1']=floor(($this->width-$crop['w'])/2);
						$crop['y1']=floor(($crop_img_height-$crop['h'])/2);

						// bottom right, add on the dimensions
						$crop['x2']=$crop['x1']+$crop['w'];
						$crop['y2']=$crop['y1']+$crop['h'];

					$crop['user_message']=$aspect['user_message'];

					$dc[$aspect['name']]=$crop;
				}

				$this->data['ratio']=$ratio;
				$this->data['crops']=$dc;

			$this->_log_action("image_upload","upload image","the image file was correctly uploaded","success");

			$this->display_node('ad_images');
		}
	}

	/* *************************************************************************
		 thumbnail() - saves the thumbnail for this image based on user selection
		 @param int $id - used to retrieve the images for this node or all loose if null
	*/
	public function thumbnail($id=null)
	{
		// set the edit node to which these images apply
			$this->data['edit_node']=$this->node_model->get_node($id);

		// message
			$this->data['message'].="<span class='success message'>The thumbnail has been successfully created - you should now be able to see your new image below</span>";

		// create a database record for the image
			$insert_data=array(
				'owning_node_id'=>$id,
				'image_name'=>$this->db->escape($this->session->userdata('image_name')),
				'image_filename'=>$this->session->userdata('image_file_id'),
				'image_ext'=>$this->session->userdata('file_ext'),
				'user_id'=>$this->user['user_id'],
				'ratio'=>$this->session->userdata('ratio')
			);

			$image_id=$this->image_model->save_image($insert_data);

        // get co-ordinates
			$post=$this->input->post();
			$post['node_id']=$image_id;

        //create the thumbnails
			$this->image_upload_model->save_thumbnails($post);

		// set the image name
			$this->image_upload_model->set_image_name($post);

		// if this node image is still an empty string then get the first image and save that as the node image
		// this stops missing images
			$this->load->helper('image_helper');
			$node=$this->node_model->get_node($id);
			if (isset($node['image']) &&
				''==$node['image'])
			{
				$this->data['admin_images']=$this->image_model->get_images($node['id']);
				$this->image_model->set_image_in_node($id,$this->data['admin_images'][0]);

				// update the action table to change images retrospectively
					$this->stream_model->set_image_in_stream($id,$this->data['admin_images'][0]);
			}

			$this->image_upload_model->update_filelist();

		// save user action
			$this->image_upload_model->save_image_action($id,$insert_data);

		    // success
		        $this->_log_action("thumbnail created","thumbnail created","success");
		        $this->_reload($node['type']."/".$node['id']."/images","the thumbnail was created","success");
	}

	/* *************************************************************************
	    show_edit_form() - function to edit an individual image thumbnail(s)
	    @param $image_id - the ID of the image to work on
	*/
	function show_edit_form($image_id)
	{
		$image=$this->node_model->get_node($image_id,'image');
		$node=$this->node_model->get_node($image['owning_node_id']);

		// form open
			$this->data['form']['thumbnail_open']=form_open("/images/save_edit/".$image['node_id']);

			// retrieve the aspect ratio array
				$aspects=$this->image_model->get_aspects($node);

			// message
				$this->data['message'].="<span class='success message'>The image was successfully uploaded, please select your thumbnail area</span>";

			// some things for the thumbnail selection form
				$crop_img_src='/user_img/'.$image['user_id'].'/'.$image['image_filename'].'s'.$this->width.$image['image_ext'];
				$crop_img_height=floor($this->width/$image['ratio']);

				// a crop image for each aspect
					foreach ($aspects as $as)
					{
						$this->data['crop_image'][$as['name']]=img(array(
							'src' => $crop_img_src,
							'alt' => 'image prepared, ready to be cropped',
							'id' => $as['name'],
							'width' => $this->width,
							'height' => $crop_img_height,
							'title' => 'image prepared, ready to be cropped'
						));
					}

				$this->data['img_width']=$this->width;
				$this->data['img_height']=$crop_img_height;
				$ratio=$this->width/$crop_img_height;
				$this->data['wrong_image']='/'.str_replace('/upload','',uri_string());
				$this->data['form']['node_id']=form_hidden('node_id',$image_id);
				$this->data['form']['owning_node_id']=form_hidden('owning_node_id',$node['id']);
				$this->data['default_image_name']=$image['image_name'];

		// set the default size and position for the thumbnail selection
			$dc=json_decode($image['thumbnail_coordinates'],true);

			foreach ($aspects as $as)
			{
				$dc[$as['name']]['ratio']=$as['ratio'];
				$dc[$as['name']]['user_message']=$as['user_message'];
			}

			$this->data['ratio']=$ratio;
			$this->data['crops']=$dc;

		$this->display_node('ad_images');
	}

	/* *************************************************************************
	    save_edit() - saves the edited thumbnail for an image
	    @param $image_id - the image to have it's thumbnail edited
	    @reload all-images - URL to reload once the operation is completed
	*/
	function save_edit($image_id)
	{
	    // get the input vals from form or URL, whether hard load or AJAX
	        $post=$this->get_input_vals();

	    // add the image id so it can be found and saved
	        $post['image_id']=$image_id;

	    // save the thumbnails, over-writing the old ones
	        $this->image_upload_model->save_thumbnails($post);

	    // reload URL
	        if ('all_images'==$this->session->userdata('image_admin_reload_source'))
	        {
	        	$reload_url="all-images";
	        }
	        else
	        {
	        	$image=$this->node_model->get_node($image_id,'image');
	        	$node=$this->node_model->get_node($image['owning_node_id']);
	        	$reload_url=$node['type']."/".$node['id']."/images";
	        }

	    // success
	        $this->_log_action("edited thumbnail saved","edited thumbnail saved","success");
	        $this->_reload($reload_url,"thumbnail successfully edited","success");
	}
}
