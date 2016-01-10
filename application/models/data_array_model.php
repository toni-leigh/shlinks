<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Data_array_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Data_array_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

		// helpers
			$this->load->helper('database_to_html_helper');
			$this->load->helper('form');
			$this->load->helper('html');
			$this->load->helper('image');
			$this->load->helper('string');

		$this->load->model('share_model');
    }

    /* *************************************************************************
     frame_data() - initialises frame related html objects and similar - stuff that may well go into the frame of the site in many cases
		though the resulting chunks in the data array can be used at any position in the page
     @param array $data - the data array into which we will add new elements
     @param array $user - the signed in user
    */
    public function frame_data($data,$user,$node)
    {
		/* BENCHMARK */ $this->benchmark->mark('func_template_data_start');

		// back stage toggle
		$data['backstage_toggle']=backstage_toggle($data['admin_page'],$user);

		// ANALYTICS - dont output if site admin or super admin logged in, or if in dev mode
			if (in_array($user['user_type'],array('super_admin','admin_user')) or
				1==$this->config->item('dev'))
			{
				$data['ga']='';
			}
			else
			{
				$data['ga']="<script type='text/javascript'>var _gaq = _gaq || [];_gaq.push(['_setAccount', '".$this->config->item('ga')."']);_gaq.push(['_trackPageview']);(function() {var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);})();</script>";
			}

		// NODE CREATOR
			$creator=array();
			if(isset($node['user_id']))
			{
				$creator=$this->node_model->get_node($node['user_id'],'user');
			}
			$data['creator']=$creator;

		// HEAD INCLUDES
			$data['css']=css_files($data['admin_page']);

			$data['fonts']=fonts();

			// no follow and index so Google doesn't crawl the development server
			$data['no_follow']=no_follow();

		// date formats
			$data['date_formats']=$this->config->item('date_format');

		// JAVASCRIPT INCLUDES
			// admin js
				if (!isset($data['admin_js']))
				{
					$data['admin_js']='';
				}

			// global values
				// open script
					$jsgl='';
					$jsgl.="<script type='text/javascript'>\n";
					$jsgl.="if (window.focus)\n";
					$jsgl.="{\n";

				// colours
					foreach ($this->config->item('colours') as $name=>$hex)
					{
						$jsgl.=$name."='".$hex."';\n";
					}

				// responsive image array
					$jsgl.="var rimg_ids=Array();";

				// responsive image load function - needs to be in the head so image can be replaced immediately at the point of load
					$jsgl.="function source_response(id,load_size)";
                    $jsgl.="{";
                    $jsgl.="    var src=$(id).attr('src');";
                    $jsgl.="    $(id).attr('src',src.replace('s300.','s'+load_size+'.'));";
                    $jsgl.="}";

				// close script
					$jsgl.="}\n";
					$jsgl.="</script>\n";

				$data['js_global']=$jsgl;


			// footer includes
				$js="<script src='/js/global.js'></script>";

				// audio player if this is an audio page
					if (1==$node["audio"])
					{
						$js.="<script src='/js/yahoo_player.js' type='text/javascript'></script>";
					}

				// background image - loaded last as they are often large and just for decoration
					$data['background_image']='';
					if (strlen($this->config->item('background')))
					{
						$data['background_image']="<style>html{".$this->config->item('background')."}</style>";
					}

				// facebook
					if (1==$node['social'])
					{
						$js.="<div id='fb-root'></div>";
						$js.="<script>(function(d, s, id) {";
						$js.="  var js, fjs = d.getElementsByTagName(s)[0];";
						$js.="  if (d.getElementById(id)) return;";
						$js.="  js = d.createElement(s); js.id = id;";
						$js.="  js.src = '//connect.facebook.net/en_GB/all.js#xfbml=1';";
						$js.="  fjs.parentNode.insertBefore(js, fjs);";
						$js.="}(document, 'script', 'facebook-jssdk'));</script>";
					}

				// image upload
					// set in the image upload controller
					if (!isset($data['image_upload_js']))
					{
						$data['image_upload_js']='';
					}

				// jquery ui
					if (1==$node['jquery_ui'])
					{
						$js.="<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js'></script>";
					}

				// large files js
					if (isset($node['large_files']) &&
						1==$node['large_files'])
					{
						$js.="<script src='/js/jquery.uploadifive.min.js' type='text/javascript'></script>";
					}

				// map
					$map_types=$this->config->item('map_types');
					if (1==$node['map'] or
						in_array($node['type'],$map_types))
					{
						$map_query_string=$this->config->item('map_qs');
						if (!strlen($map_query_string))
						{
							die("to use a map you need to set a query value in config - add item 'map_qs' with the values for your map");
						}
						$js.="<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js".$map_query_string."'></script>";
					}

				// modernizr
					if ($this->config->item('modernizr'))
					{
						$js.="<script src='/js/modernizr253.js'></script>";
					}

				// node specific js file
					if (1==$node['javascript'])
					{
						$js.="<script src='/js/".$node['url'].".js'></script>";
					}

				// pinterest
					if (1==$node['pinterest'])
					{
						$js.="<script type='text/javascript' src='//assets.pinterest.com/js/pinit.js'></script>";
					}

				// responsive src replace
					$js.="<script type='text/javascript'>";
					$js.="	for (x=0;x<rimg_ids.length;x++)";
					$js.="	{";
					$js.="		source_response('#img'+rimg_ids[x][0],rimg_ids[x][1]);";
					$js.="	}";
					$js.="</script>";

				// shivs
					if ($this->config->item('html5shiv'))
					{
						$js.="<!--[if lt IE 9]> <script src='http://html5shiv.googlecode.com/svn/trunk/html5.js'></script> <![endif]-->";
					}
					if ($this->config->item('html_canvas'))
					{
						$js.="<!--[if lt IE 9]> <script src='/js/excanvas.js'></script> <![endif]-->";
					}

				// video player is the player js and the control values
					$vidjs='';
					$video_div='';
					$video_player='';
					$other_videos=array();
					if ((strlen($node['video_src'])>0) or
						(1==$node["video"] &&
						isset($this->data['node_details']['video_src']) &&
						strlen($this->data['node_details']['video_src'])>0))
					{
						$this->load->model('video_model');

						$video_div.="<div id='vid_player' class='vid_player'>";
						$video_div.="<div id='mp'>the video content doesn't appear to be working - please try again later, or check your javascript (js) settings as video does not work without javascript</div>";
						$video_div.="</div>";

						if (strlen($node['video_src'])>0)
						{
							$video_player=$this->video_model->video_player($node['video_src'],"youtube",$this->config->item('video_config'));
						}
						else
						{
							$video_player=$this->video_model->video_player($this->data['node_details']['video_src'],$this->data['node_details']['video_host'],$this->config->item('video_config'));
						}

						$js.="<script type='text/javascript' src='/js/jwplayer/jwplayer.js'></script>";
						$js.="<script type='text/javascript' src='/js/jwplayer/control.js'></script>";

						$other_videos=$this->video_model->get_other_videos($node);
					}

					// also set the video player stuff here
						$data['video_player']=$video_player;
						$data['video_div']=$video_div;
						$data['other_videos']=$other_videos;

				$data['javascript']=$js;

		// HEADER BASKET
			// header basket - do this first as subsequent calculations are based on things that happen in here
				$data['header_basket']='';
				if (1==$this->config->item('show_header_basket'))
				{
					$this->load->model('basket_model');
					$data['header_basket']="<div id='header_basket'>".$this->basket_model->header_basket()."</div>";
				}

		// LARGE FILES
			if (1==$node['large_files'])
			{
				$this->load->model('file_upload_model');
				$data['file_upload']=$this->file_upload_model->file_upload_field('hello');
			}

		// LOGIN AND LOGOUT FORMS
			if (is_numeric($user['user_id']))
			{
				$data['login_form']='';
				$data['logout_form']=$this->engage_model->logout_form($node['url']);
			}
			else
			{
				$data['login_form']=$this->engage_model->login_form();
				$data['logout_form']='';
			}

		// MESSAGE - for feedback to the user on reload - also clear from session
			$mes="<div id='mback'>";
			$hidden=0;
			if (0==$node['visible'] && (is_numeric($user['user_id']) && $node['user_id']==$user['user_id'] ))
			{
				$hidden=1;
			}
			if ((strlen($this->session->userdata('message'))>0) or
				1==$hidden)
			{
				// get and unset
					if (1==$hidden)
					{
						$mes.="<span id='hidden_message' class='red'>!!! this page is currently invisible - you are able to see it because it is yours or you are admin !!!</span>";
					}
					$mes.=$this->session->userdata('message');
					$this->session->set_userdata('message','');

				// fade in to show that something happened when the same action was performed twice
					$mes.="<script type='text/javascript'>";
					$mes.="if (window.focus)";
					$mes.="{";
					$mes.="$('#mback').css('opacity',0).animate({'opacity':'+=1'},500);";
					$mes.="}";
					$mes.="</script>";
			}
			$mes.="</div>";

			$data['message']=$mes;

		// NAVIGATION
			$nav='';

			$nav_nodes=$this->node_model->get_nodes(array('category_id'=>502));

			foreach ($nav_nodes as $n)
			{
				// list nodes
					$list_nodes=array();
					if ('page'==$n['type'])
					{
						$n_details=$this->node_model->get_node($n['id'],'page');

						$list_nodes=json_decode($n_details['node_list'],true);
					}

				// selected logic, also uses the node type to highlight when looking at specifics
					if ($n['id']==$node['id'] or
						(is_array($list_nodes) && in_array($node['type'], $list_nodes)))
					{
						$extra_class=' nsel ';
					}
					else
					{
						$extra_class='';
					}

				// a bit of tab text for the home page - not it's long title
					if (1==method_exists($this->project_data_array_model,'set_nav_name'))
					{
						$text=$this->project_data_array_model->set_nav_name($n);
						$url="/".$n['url'];
					}
					else
					{
						if (6==$n['id'])
						{
							if (strlen($this->config->item('home_nav')) ? $text=$this->config->item('home_nav') : $text='home' );
							$url='/';
						}
						else
						{
							$text=$n['name'];
							$url="/".$n['url'];
						}
					}

				// the nav link itself
					$nav.="<a href='".$url."'>";
					$nav.="<span class='n ".$extra_class."'>";
					$nav.=$text;
					$nav.="</span>";
					$nav.="</a> ";
			}

			$data['nav']=$nav;

		// NEWSLETTER, sign up form - usually in the frame somewhere
			$data['newsletter']=$this->newsletter_model->get_newsletter_form();

		// NODE LIST
			$data['node_list']=array();
			$nodes_list=json_decode($this->node['node_list'],true);
			if (count($nodes_list)>0)
			{
				// do something special for a blog list as we need the html too, but just if it's blogs alone
					if (1==count($nodes_list) &&
						'blog'==$nodes_list[0])
					{
						$data['node_list']=$this->node_model->get_nodes(array('type'=>'blog','visible'=>1),1);
					}
					else
					{
						$query=$this->db->select('*')->from('node')->where(array('visible'=>1))->where_in('type',$nodes_list)->order_by('name');
						$res=$query->get();
						$data['node_list']=$res->result_array();
					}
			}

			// and for the map
				$data['map_node_list']=array();
				$map_nodes_list=json_decode($this->node['node_list'],true);
				if (count($map_nodes_list)>0)
				{
					$query=$this->db->select('*')->from('node')->where(array('latitude !='=>999,'visible'=>1))->where_in('type',$map_nodes_list)->order_by('name');
					$res=$query->get();
					$data['map_node_list']=$res->result_array();
				}

		// PAGE FORMATTING - body, title, h1 etc
			// body tag
				$data['body_classes']=body_classes($data['admin_page']);

				$data['body_map_onload']=body_map_onload($data['admin_page'],count($data['map_node_list']),$node['map']);

			// facebook metas
				$data['fbog']=$this->share_model->fbogs($node,$this->data);

			// h1 - NB this may well be over-ridden in template project specific
				if (isset($this->data['image_node']) ? $h1=$this->data['image_node']['name'] : $h1=$node['name'] );

				$data['h1']=$h1;

			// live warning
				$data['live_warning']='';
				if (1==$this->config->item('live_warning'))
				{
					$data['live_warning'].="<div id='live_warning'>site now live - please login to the live site for ongoing changes: <a href='".$this->config->item('live_domain')."/login'>login here</a></div>";
				}

			// ie page covering conditionals
				$data['ie']=ie_tags();

			// title
				if (isset($this->data['image_node']) &&
					isset($this->data['individual_image']))
				{
					$data['title']="<title>".$this->data['individual_image']['image_name'].", an image belonging to ".$this->data['image_node']['human_type']." ".$this->data['image_node']['name']." at ".$this->config->item('site_name')."</title>";
				}
				else
				{
					$data['title']="<title>".$node['name']." ".$node['human_type']." at ".$this->config->item('site_name')."</title>";
				}

		// SEARCH
			if (is_numeric($this->config->item('header_search')) ? $header_search=$this->config->item('header_search') : $header_search=1 );

			$sf="";

			if (1==$header_search)
			{
				$sf=form_open('search/search_reload');
				$sf.="<input id='search_input' class='form_field' type='text' name='search_input' value='".get_value(null,'search_input')."' x-webkit-speech />";
				$sf.="<input id='search_submit' class='submit' type='submit' name='submit' value='search'/>";
				$sf.="</form>";
			}

			$data['search_form']=$sf;

		// SKIP TO CONTENT
			$data['skip']['start']="<a id='skip_link' class='hide_text' href='#content_skipper'>skip to content</a>";
			$data['skip']['end']="<a id='content_skipper'></a>";

		// SOCIAL MEDIA - site wide, i.e. links to site social media pages, displays social media widgets
			// facebook face pile / like box

			// twitter widget
				$data['twidget']=$this->share_model->twidget();

		// USER WELCOME MESSAGE - finish profile etc. if required in the config of this site
			$wm='';

			// make a message to welcome the user
				$data['complete']=1;
				if (1==$this->config->item('welcome_encouragement'))
				{
					$data['complete']=$this->engage_model->profile_complete($user);
					if (is_numeric($user['user_id']))
					{
						if (0==$data['complete'])
						{
							$name=$user['user_name'];
							$complete="<a href='/my-profile'>complete your profile</a>";
						}
						else
						{
							//$name=$user['display_name']." [".$user['user_name']."]";
							$name=$user['display_name'];
							$complete="";
						}
						$data['welcome']="Hi, <a href='/".$user['user_name']."'>".$name."</a>".$complete;
					}
					else
					{
						$data['welcome']='';
					}
				}
				else
				{
					$data['welcome']='';
				}

			// build the engage message
				if ($data['welcome']!='')
				{
					$wm="<div id='htop_engage'>";
					$wm.="<span id='welcome_user'>".$welcome."</span>";
					$wm.="</div>";
				}

			$data['welcome_message']=$wm;

		/* BENCHMARK */ $this->benchmark->mark('func_adminspcifics_template_data_end');

		/* BENCHMARK */ $this->benchmark->mark('func_template_data_end');

        return $data;
    }

	/* *************************************************************************
		 node_details_data() - gets the html elements for the details for this node - depends on the type and the panel
		 @param $panel - which node tab we are looking at
		 @param array $data - the data array into which we will add new elements
		 @param array $user - the signed in user
		 @return
	*/
	public function node_details_data($panel,$data,$user,$node)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_node_details_data_start');

		// common to all nodes - things like tabs, images, inplace editing etc.
			// IMAGE - the main image for the node
				$img=$this->image_model->get_images($node['id'],1);
				if (count($img))
				{
					$data['main_image']="/user_img/".$img[0]['user_id']."/".$img[0]['image_filename']."s700".$img[0]['image_ext'];
					$data['main_thumb']=thumbnail_url($img[0],360);
					$data['small_thumb']=thumbnail_url($img[0],120);
				}

			// IMAGES
				// all the images
					$data['images']=$this->image_model->get_images($node['id'],1);

				// a large image slider

			// INPLACE EDITING - set up the in place editing
				if (($user['user_type']=='super_admin' or
					 $user['user_type']=='admin_user' or
					 1==$data['owns_node']) &&
					 0==$data['admin_page'])
				{
					$data['javascript'].="<script type='text/javascript' src='/js/tinymce/tiny_mce.js'></script>";

					$data['admin_user']=1;
					$data['clean_html']=quotes_to_entities(preg_replace('/\s+/',' ',addslashes($data['node_details']['node_html'])));
					$hidden_data=array(
						'node_id'=>$node['id']
					);
					$data['html_form_open']=form_open($node['type'].'/'.$node['id'].'/inplace_save','',$hidden_data);
				}
				else
				{
					$data['admin_user']=0;
					$data['clean_html']='';
					$data['html_form_open']='';
				}

				$data['details_text']='';
				$data['in_page']='';

				// where is it going ?
					if (1==$node['show_in_details'] ? $ref='details_text' : $ref='in_page' );

				// build the deatils text, in a form if needed
					$data[$ref].="<div id='".$node['type']."_text' class='node_text'>";
					if (1==$data['admin_user'] &&
						1==$node['show_edit'])
					{
						$data[$ref].=$data['html_form_open'];
						$data[$ref].="<div id='node_html_edit_button' class='submit' onclick='load_inplace(\"".$data['clean_html']."\",\"".$user['user_id']."\")'>click to edit</div>";
						$data[$ref].="<div id='".$node['type']."_html_display' class='node_html_display'>";
						$data[$ref].="<div class='">$node['type']."_html editable'>";
						$data[$ref].=$data['node_details']['node_html'];
						$data[$ref].="</div>";
						$data[$ref].="</form>";
					}
					else
					{
						$data[$ref].="<div class='".$node['type']."_html_display node_html_display'>";
						$data[$ref].=$data['node_details']['node_html'];
						$data[$ref].="</div>";

					}
					$data[$ref].="</div>";

			// SCROLLERS - next and previous nodes in this set of nodes
				if (in_array($node['type'],$this->config->item('scroller_array')))
				{
					$panel_suffix='';

					if (isset($panel))
					{
						if ($node['type']!='calendar' ||
							$panel!='details')
						{
							$panel_suffix="/".$panel;
						}
						else
						{
							if (strlen($data['url_extra']))
							{
								$panel_suffix="/".$data['url_extra'];
							}
						}
					}

					if ($this->session->userdata('search_term'))
					{
						$this->load->model('search_model');
						$nodes=$this->search_model->search($this->session->userdata('search_term'),$this->config->item('search_nodes'));
						$next_prev_text='search result';
					}
					else
					{
						$nodes=$this->node_model->get_nodes(array('type'=>$node['type'],'visible'=>1));
						$next_prev_text=$node['human_type'];
					}

					$scroll_intro_text="scroll through ".$next_prev_text."s:";

					$nc=count($nodes);

					for ($x=0;$x<$nc;$x++)
					{
						if ($nodes[$x]['url']==$node['url'])
						{
							if (0==$x)
							{
								$pl="<span class='left_scr_deact scr_deact sprite'>previous ".$next_prev_text."</span>";
								$pl.="<span class='scr_divider'>|</span>";
								if (1==$nc)
								{
									$nl="<span class='right_scr_deact scr_deact sprite'>next ".$next_prev_text."</span>";
								}
								else
								{
									$nl="<a href='/".$nodes[$x+1]['url'].$panel_suffix."' title='next ".$next_prev_text.": ".str_replace("'",'',$nodes[$x+1]['name'])."'><span class='right_scr scr sprite'>next ".$next_prev_text."</span></a>";
								}
							}
							elseif ($x==$nc-1)
							{
								$pl="<a href='/".$nodes[$x-1]['url'].$panel_suffix."' title='previous ".$next_prev_text.": ".str_replace("'",'',$nodes[$x-1]['name'])."'><span class='left_scr scr sprite'>previous ".$next_prev_text."</span></a>";
								$pl.="<span class='scr_divider'>|</span>";
								$nl="<span class='right_scr_deact scr_deact sprite'>next ".$next_prev_text."</span>";
							}
							else
							{
								$pl="<a href='/".$nodes[$x-1]['url'].$panel_suffix."' title='previous ".$next_prev_text.": ".str_replace("'",'',$nodes[$x-1]['name'])."'><span class='left_scr scr sprite'>previous ".$next_prev_text."</span></a>";
								$pl.="<span class='scr_divider'>|</span>";
								$nl="<a href='/".$nodes[$x+1]['url'].$panel_suffix."' title='next ".$next_prev_text.": ".str_replace("'",'',$nodes[$x+1]['name'])."'><span class='right_scr scr sprite'>next ".$next_prev_text."</span></a>";
							}
						}
					}
				}

				// set the html snippet
					$scr='';
					if (0==$data['admin_page'])
					{
						if (isset($pl) &&
							isset($nl))
						{
							$scr.="<div id='scrollers'>";
							$scr.="<span id='scroll_intro'>".$scroll_intro_text."</span>";
							$scr.="<div id='scroll_buttons'>";
							$scr.=$pl;
							$scr.=$nl;
							$scr.="</div>";
							$scr.="</div>";
						}
					}

					$data['scrollers']=$scr;

			// SOCIAL - includes share this stuff etc.
				$sb='';

				if (1==$node['social'])
				{
					// !!!!!
					// NB needs to be in a model so it can be used on lists

					// get the sites to share to from this node
						$sites=$this->config->item('social_sites');

					// open social_buttons
						$sb.="<div id='share_buttons'>";

					// iterate - we can add extra ones in here as and when
						foreach ($sites as $site=>$config)
						{
							switch ($site)
							{
								case 'facebook':
									$sb.=$this->share_model->facebook_like($config,$node,$data);
									break;
								case 'twitter':
									$sb.=$this->share_model->tweet_button($config,$node,$data);
									break;
							}
						}

					// close social_buttons
						$sb.="</div>";
				}

				$data['share_buttons']=$sb;

			// TABS - tab menus, different for each type of node, not if admin
				$data['tabs']=array();
				$data['count_unread']='';
				if (1==$node['show_tabs'])
				{
					$tabs_config=$this->config->item('tabs');
					if (is_array($tabs_config))
					{
						// first look for type based tabs
							$data['tabs']=$tabs_config[$node['type']];
					}
					else
					{
						// old sites, only ever used details
							$data['tabs'][]='details';
					}
				}

			// TAB - specific data for each tab type

				$actions=array();
				$articles=array();
				$comments=array();
				$comment_form='';
				$conversations=array();
				$images=array();
				$messages=array();
				$message_form='';
				$stream=array();

				switch ($panel)
				{
					case 'articles':
					case 'blog':
						$articles=$this->node_model->get_nodes(array('type'=>'article','user_id'=>$node['id'],'visible'=>1),1);
						break;
					case 'calendar':
						$calendar=array();
						$calendars=$this->node_model->get_nodes(array('type'=>'calendar','user_id'=>$node['id']));
						if (count($calendars)>0)
						{
							$this->load->model('events_model');
							$calendar=$this->node_model->get_node($calendars[0]['id'],'calendar');
							$calendar=$this->events_model->get_calendar($calendar,$node['url']."/calendar",$data['params']);
						}
						$data['calendar']=$calendar;
					case 'comments':
					case 'details':
					case 'intro':
						$this->load->model('comment_model');
						$comments=$this->comment_model->get_comments($node);
						$comment_form=$this->comment_model->comment_form($node);
						$data['javascript'].="<script src='/js/comments.js'></script>";
						$data['javascript'].="<script type='text/javascript' src='/js/tinymce/tiny_mce.js'></script>";
					case 'images':
					case 'gallery':
						$images=$this->image_model->get_images($node['id']);
						break;
					case 'messages':
						if (isset($this->user['user_id']))
						{
							$this->load->model('conversation_model');
							$this->load->model('message_model');
							if ($node['id']==$user['id'])
							{
								// get the conversations
									$conversations=$this->conversation_model->get_users_conversations($user);

									if (count($conversations)>0)
									{
										$conversation=$conversations[0];

										$messages=$this->message_model->get_conversation_messages($user,$conversation);
									}

								// form if we have a conversation
									if (isset($conversation) &&
										count($conversation)>0)
									{
										$message_form=$this->message_model->message_form($conversation);
									}
							}
							else
							{
								// just get the messages between the user and the node
									$conversation=$this->conversation_model->get_conversation($user,$node);

								// form anyway, to start a conversation
									if (!is_array($conversation))
									{
										$conversation=array(
											'conversation_id'=>0
										);
									}

									if (count($conversation)>0)
									{
										$messages=$this->message_model->get_conversation_messages($user,$conversation);
									}

									$message_form=$this->message_model->message_form($conversation,$node);
							}
							$data['javascript'].="<script src='/js/messages.js'></script>";
							$data['javascript'].="<script type='text/javascript' src='/js/tinymce/tiny_mce.js'></script>";

						}
						break;
					case 'stream':
					case 'activity':
						// stream model is also used below to merge streams for users
							$this->load->model('stream_output_model');

						$stream=$this->stream_output_model->get_actions($node);

						$this->load->config('action');
						$actions=$this->config->item('stream_actions');
						break;
				}

				$data['actions']=$actions;
				$data['articles']=$articles;
				$data['comments']=$comments;
				$data['comment_form']=$comment_form;
				$data['conversations']=$conversations;
				$data['images']=$images;
				$data['messages']=$messages;
				$data['message_form']=$message_form;
				$data['stream']=$stream;

			// TAB HTML
				$ntb='';

				// only if there are tabs (and they are shown as this array will be empty if not)
					if (count($data['tabs'])>0)
					{
						// open the list
							$ntb.="<div id='node_tabs'>";
							$ntb.="<ul>";

						// iterate over the tabs set above
							for($x=0;$x<count($data['tabs']);$x++)
							{
								// class for selected
									if ($data['current_tab']==$data['tabs'][$x] ? $class='tab_sel' : $class='tab' );

								// message count for the message tab
									if ('messages'==$data['tabs'][$x])
									{
										$this->load->model('message_model');

										$count_unread=$this->message_model->count_unread($user);

										$suffix='';
										if (isset($count_unread['unread']))
										{
											$suffix=" [".$count_unread['unread']."]";
										}

										$tab_text=$data['tabs'][$x].$suffix;
									}
									else
									{
										$tab_text=$data['tabs'][$x];
									}

								// the tab itself
									$ntb.="<li>";
									$ntb.="<a href='/".$data['node']['url']."/".$data['tabs'][$x]."'>";
									$ntb.="<span class='".$class."'>";
									$ntb.=$tab_text;
									$ntb.="</span>";
									$ntb.="</a>";
									$ntb.="</li>";
							}

						// close the list
							$ntb.="</ul>";
							$ntb.="</div>";
					}

				$data['node_tabs']=$ntb;

			// VOTE BUTTONS
				$data['vote_buttons']='';
				if (isset($user['id']) &&
					is_numeric($user['id']))
				{
					$this->load->model('voting_model');
					$data['vote_buttons']="<div class='vote_buttons votes".$node['id']."'>".$this->voting_model->get_vote_buttons($user,$node)."</div>";
				}

		// specific to node type, such as add to basket, variations and user connection stuff
			// CALENDAR
				if (!isset($calendar))
				{
					$calendar='';
				}
				if ('calendar'==$node['type'])
				{
					$this->load->model('events_model');
					$calendar=$this->events_model->get_calendar($node,$node['url'],$data['url_extra']);
				}

				if (isset($node['calendar_id']) &&
					$node['calendar_id']>0)
				{
					$this->load->model('events_model');
					$calendar=$this->events_model->get_calendar($this->node_model->get_node($node['calendar_id'],'calendar'),$node['url']."/calendar",$data['params']);
				}

				$data['calendar']=$calendar;

			// EVENT (OR ANY EVENTS ARE FOUND WITH THIS AS A CATEGORY ID)
				$query=$this->db->select('*')->from('node')->where("type = 'event' and (id = ".$node['id']." or category_id = ".$node['id'].")");
				$res=$query->get();
				$events=$res->result_array();

				$el=array(
					'past'=>array(),
					'upcoming'=>array()
				);

				if (count($events)>0)
				{
					$now=time();

					$events_by_id=$this->node_model->nodes_by_id($events);

					// get all the ids into an array
						$nids=array();
						foreach ($events as $e)
						{
							$nids[]=$e['id'];
						}

					// get all the event instances from the nvar table, ordered by time stamp
						$seq=array();
						if (count($nids))
						{
							$query=$this->db->select('*')->from('nvar')->where_in('node_id',$nids)->order_by('event_timestamp');
							$res=$query->get();
							$seq=$res->result_array();
						}

					// iterate over all the event instances
						foreach ($seq as $e)
						{
							// place into the correct array
								if ($now>$e['event_timestamp'] ?  $akey='past' : $akey='upcoming' );

							// get the event for this iteration
								$event=$events_by_id[$e['node_id']];

							// convert the timestamp
								$dt=get_now($e['event_timestamp']);

							// if this is node is a category into which events falls
								if (count($events)>1)
								{
									// just the sequence details, rest on the page
										$edet=$e['event_timestamp'];
								}
								else
								{
									// output heading with link to event, use event query data
										$edet=$event['name'].'-'.$e['event_timestamp'];
								}

							// add to array - include the event and sequence details in case we want to use that in
							// project data model to build something more complex than a basic html output
								$el[$akey][]=array(
									'html'=>$edet,
									'e'=>array_merge($event,$e)
								);
						}

					// reverse the past events so that the list starts with the most recent past event
						$el['past']=array_reverse($el['past']);
				}

				$data['event_list']=$el;

			// PRODUCT
				// add to basket button
					$data['add_to_basket']='';

				// add this product to basket panel
					$add_quantity="<div id='add_quantity_field'><input id='add_quantity' class='form_field' type='text' name='add_quantity' value='1' onkeyup='check_quantity()' tabindex='11'/></div>";

					// only if product
						if ('product'==$node['type'])
						{
							$this->load->model('variation_model');
							$variations=json_decode($data['node_details']['nvar_json'],true);
							$c=0;
							foreach ($variations as $v)
							{
								// select the first variation as default
									if (0==$c)
									{
										$data['main_variation']=$this->variation_model->format_add_panel($v);
										$selected=$v['nvar_id'];
									}

								// if we hit a main variation then update the variation
									if (1==$v['main'])
									{
										$data['main_variation']=$this->variation_model->format_add_panel($v);
										if (isset($data['selected']) ? $selected=$data['selected'] : $selected=$v['nvar_id'] );
										break;
									}
								$c++;
							}
							$data['variation_selector']=$this->variation_model->variation_selector($data['node_details'],$selected);

							$atb='';

							// open add to basket panel
								$atb.="<div id='add_basket_panel'>";

							// add panel text
								$atb.="<div id='add_panel_text'>";
								$atb.=$data['main_variation'];
								$atb.="</div>";

							// add panel - updated by js
								$atb.="<div id='add_panel'>";
								$atb.=form_open('/basket/add');
								$atb.="<input type='hidden' name='product_add' value=''/>";
								$atb.=$data['variation_selector'];
								$atb.=$add_quantity;
								$atb.="<input id='add_submit' class='submit checkout' type='submit' name='submit' value='add to basket'/>";
								// no closing div for add_panel, its somewhere in the variation selector or :-\ must fix this !!
								$atb.="</form>";

							// script builds button
								$atb.='<script type="text/javascript">';
								$atb.='if (window.focus)';
								$atb.='{';
								$atb.='var add_span="<div id=\'variation_selector_field\' class=\'check_this\'>'.$data['variation_selector'].'</div>";';
								$atb.='add_span+="<span id=\'add_button\' class=\'submit checkout\' onclick=\'basket_add()\' tabindex=\'12\'>add to basket</span>";';
								$atb.='add_span+="'.$add_quantity.'<span id=\'not_enough_stock\'></span>";';
								$atb.='$("#add_panel").html(add_span);';
								$atb.='}';
								$atb.='</script>';

							// close add to basket panel
								$atb.="</div>";

							$data['add_to_basket']=$atb;
						}

			// USER AND GROUP
				$data['connection_buttons']=array();
				$followable=$this->config->item('followable');
				if ('user'==$node['type'] or
					'groupnode'==$node['type'] or
					(is_array($followable) && in_array($node['type'], $followable)))
				{
					$this->load->model('connection_model');
					$this->load->model('connection_button_model');

					// user and group connections - users have more connection types
						$data['connections']=$this->connection_model->get_connections($node);

					// connection buttons
						if (isset($this->user['user_id']))
						{
							$data['connection_buttons']=$this->connection_button_model->connection_buttons($user,$node);
						}
				}

				// friends stream data
					if ('user'==$node['type'] && // this is a user
						in_array($panel, array('stream','activity'))) // and we are looking at the panel
					{
					}

		return $data;

		/* BENCHMARK */ $this->benchmark->mark('func_node_details_data_end');
	}

	/* *************************************************************************
		 page_specific_data() - gets some data for specific pages
		 @param array $data - the data array into which we will add new elements
		 @param array $user - the signed in user
		 @return
	*/
	public function page_specific_data($data,$user,$node)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_admin_page_data_start');

        $user=$user;

		// BASKET
			if ('basket'==$node['url'])
			{
				$this->load->model('basket_model');

				// cart library and postage model required
					$this->load->library('cart');
					$this->load->model('postage_model');

				// data
					$data['basket']=$this->cart->contents();
					$data['paypal_form']=$this->basket_model->paypal_form();
					$data['pclasses']=$this->postage_model->get_postage_classes(10);
				    $data['total']=$this->basket_model->total();
				    $data['postage_total']=number_format(0,2);
				    $data['show_voucher']=0;
				    $data['vprice']=0;
			}

		// DISPLAY INDIVIDUAL IMAGE
			if ('individual_image'==$node['url'])
			{
				// the actual image to display
					$img=$this->data['individual_image'];
					$width=$this->config->item('display_individual_width');
					if (count($img)>0)
					{
						$data['individual_image_tag']=image_tag($img,$width);
						$data['pin_button']=pinterest_button("image/".$img['image_id'],image_url($img,$width),str_replace("'","",$img['image_name']));
					}
					else
					{
						$data['individual_image']=null;
					}
			}

		// ORDER FAILED
			if ('order-failed'==$node['url'])
			{
				$this->load->model('basket_model');

				$data['paypal_form']=$this->basket_model->paypal_form();
			}

		return $data;

		/* BENCHMARK */ $this->benchmark->mark('func_admin_page_data_end');
	}
}
