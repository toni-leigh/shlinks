<?php
/*
    EVENTS
*/     
    // admin calendar height
        $config['admin_calendar_height']=770;
        
    // admin calendar marked colour
        $config['admin_marked_colour']='#c6eeff';
        
    // undo steps
        $config['undo_steps']=10;

    // years ahead
        $config['years_ahead']=2;
        
/*
    IMAGES
*/
    // default ratio for thumbnails
        $config['default_thumb_space']=0.9;

    // default crop widths, are used for the below settings and can be over-ridden specifically if needed
        $crop_widths=array(
            '81'=>array(
                720,
                940,
                1280
            ),
            '51'=>array(),
            '41'=>array(
                460,
                720,
                940,
                1280,
                3000
            ),
            '31'=>array(),
            '21'=>array(
                200,
                300,
                460,
                720
            ),
            '11'=>array(
                40,
                100,
                200,
                300,
                460
            ),
            '12'=>array(
                40,
                100,
                200,
                300,
                460
            ),
            '13'=>array(
                ),
            '14'=>array(
                40,
                100,
                200,
                300,
                460
            ),
            '15'=>array(),
            '18'=>array()
        );

    // a set of aspect ratio definitions for saving different crops for different types
    // NB filename_prefix MUST be unique within a type array
        $config['aspects']=array(
            'page'=>array(
                0=>array(
                    'name'=>'thumbnail',
                    'ratio'=>'1:1',
                    'filename_prefix'=>'t',
                    'crop_widths'=>$crop_widths['12'],
                    'user_message'=>'this crop is used for lists of items and for facebook shares',
                    'auto_crop'=>1
                ),
                1=>array(
                    'name'=>'banner',
                    'ratio'=>'4:1',
                    'filename_prefix'=>'ba',
                    'crop_widths'=>$crop_widths['41'],
                    'user_message'=>'this crop is used as a heading image',
                    'auto_crop'=>1
                )
            ),
            'blog'=>array(
                0=>array(
                    'name'=>'thumbnail',
                    'ratio'=>'1:1',
                    'filename_prefix'=>'t',
                    'crop_widths'=>$crop_widths['11'],
                    'user_message'=>'this crop is used for lists of items and for facebook shares',
                    'auto_crop'=>1
                )  ,
                1=>array(
                    'name'=>'banner',
                    'ratio'=>'4:2.5',
                    'filename_prefix'=>'ba',
                    'crop_widths'=>$crop_widths['41'],
                    'user_message'=>'this crop is used for the image carousel',
                    'auto_crop'=>1
                )
            ),
            'default'=>array(
                0=>array(
                    'name'=>'thumbnail',
                    'ratio'=>'1:1',
                    'filename_prefix'=>'t',
                    'crop_widths'=>$crop_widths['11'],
                    'user_message'=>'this crop is used for lists of items and for facebook shares',
                    'auto_crop'=>1
                )  ,
                1=>array(
                    'name'=>'banner',
                    'ratio'=>'8:1',
                    'filename_prefix'=>'ba',
                    'crop_widths'=>$crop_widths['81'],
                    'user_message'=>'this crop is used as a background heading image',
                    'auto_crop'=>1
                )
            )
        );

/*
    NAVIGATION
*/

/*
  NODE CONFIG
*/            
    // from category add types - see node_admin controller edit() function
        $config['add_with_category']=array();
        
    // link list
        $config['linklist_nodes']=array('activitygroup','blog','committee','event','page');  

    // node list options
        $config['admin_node_list_options']=array(
            'admin_tags'=>array('product'),
            'create'=>array('blog','calendar','category','page','product','user'),
            'exclude_edit'=>array('audio','image_set','video'),
            'exclude_images'=>array('audio','image_set','video'),
            'exclude_view'=>array('audio','image_set','video'),
            'exclude_visible'=>array('audio','image_set','video'),
            'filter'=>array('blog','calendar','category','event','page','product','user'),
            'sortable'=>array()
        );

    // ordering
        $config['ordering']=array(
            '<TYPE>'=>array()
        );

    // required node fields
        $config['node_admin_required']=array(
            'page'=>array(
                        array(
                            'field'=>'name',
                            'label'=>'Name',
                            'rules'=>'required'
                        ),
                        array(
                            'field'=>'short_desc',
                            'label'=>'Short Description',
                            'rules'=>'required'
                        )
                    ),
            'default'=>array(
                        array(
                            'field'=>'name',
                            'label'=>'Name',
                            'rules'=>'required'
                        )
                    )
        );

    // show comments
        $config['product_show_comments']=0;
        $config['category_show_comments']=0;
        $config['user_show_comments']=0;
        $config['blog_show_comments']=1;
        $config['page_show_comments']=0;
        $config['group_show_comments']=0;
        $config['mediaset_show_comments']=0;
        
    // show edit or not for nodes - these will be set to off in the more simple cases
        $config['product_show_edit']=1;
        $config['category_show_edit']=0;
        $config['user_show_edit']=0;
        $config['blog_show_edit']=0;
        $config['page_show_edit']=1;
        $config['group_show_edit']=1;
        $config['mediaset_show_edit']=0;
        
    // show tabs
        $config['product_show_tabs']=0;
        $config['category_show_tabs']=0;
        $config['user_show_tabs']=1;
        $config['blog_show_tabs']=0;
        $config['page_show_tabs']=0;
        $config['group_show_tabs']=0;
        
    // static order - means a developer set database node_order will persist on save rather than being set based on list order
        $config['static_order']=array('');
        
    // text area width for admin
        $config['admin_text_width']=680;  