<?php  
    function pinterest_button($url,$src,$description)
    {
        $pb="<span class='pinit_link'>";
        $pb.="<a href='http://pinterest.com/pin/create/button/";
        $pb.="?url=".base_url().$url;
        $pb.="&media=".base_url().$src;
        $pb.="&description=".$description."' ";
        $pb.="class='pin-it-button pin_button' count-layout='horizontal'>";
        $pb.="</a>";
        $pb.="</span>";
        
        return $pb;        
    }

    /* *************************************************************************
        image_from_node() - returns a tag for an image or default if it doesn't exist (from the node field)
        @param $image_path - the path to the image (or a node so type can be used)
        @param $target_size - target file size
        @param $resize - the tag resize
        @return $image_tag - the tag
    */
    function image_from_node($image_path,$target_size,$resize)
    {
    
        $image_tag="";

        $type=(is_array($image_path)) ? $image_path['type'] : "";
        $image=(is_array($image_path)) ? $image_path['image'] : $image_path;
    
        if (strlen($image) &&
            $image!="/img/default_image_stream.png")
        {
            $image_tag="<img src='".str_replace("t300.", "t".$target_size.".", $image)."' width='".$resize."' height='".$resize."'/>";
        }
        else
        {
            $type_path=$_SERVER['DOCUMENT_ROOT']."/img/default_".$type.".png";
            $path=(is_file($type_path)) ? "/img/default_".$type.".png" : "/img/default_image_stream.png";
            $image_tag="<img src='".$path."' width='".$resize."' height='".$resize."'/>";
        }
    
        return $image_tag;
        
    }
    
    /* *************************************************************************
        image_tag() - gets an image tag holding an image of the correct size
        @param array $img - the image data
        @param int $size - the size to be used as a choice of file
        @param int $resize - the size to set the image to, will scale
        @return string - the img tag
    */  
    function image_tag($img,$size,$resize=null,$classes='')
    {
        $img=get_image($img);

        // get the dimension values for the image tag
            if (null==$resize)
            {
                $width=$size;
                $height=ceil($size/$img['ratio']);
            }
            else
            {
                $width=$size*($resize/$size);
                $height=ceil($width/$img['ratio']);
            }
        
        // set a classes value
            if (strlen($classes)>0 ? $class="class='".$classes."'" : $class='' );
        
        $img_url=image_url($img,300);
        
        $image_tag="<img id='img".$img['image_id']."' ".$class." itemprop='image' src='".$img_url."' alt='thumbail of ".$img['image_name']."' width='".$width."' height='".$height."'/>";
        
        $image_tag.="<script type='text/javascript'>";
        $image_tag.="   rimg_ids[rimg_ids.length]=new Array(".$img['image_id'].",".$size.");";
        $image_tag.="</script>";
        
        return $image_tag;
    }
    
    /* *************************************************************************
        image_url() - gets just the url of a thumbnail
        @param array $img - the image data
        @param int $size - the size to be used as a choice of file
        @return string - the img url
    */   
    function image_url($img,$size)
    {
        return "/user_img/".$img['user_id']."/".$img['image_filename']."s".$size.$img['image_ext'];
    }
    
    /* *************************************************************************
        thumbnail_tag() - gets an image tag holding a thumbnail of the correct size
        @param array $img - the image data
        @param int $size - the size to be used as a choice of file
        @param int $resize - the size to set the thumbnail to
        @return string - the img tag
    */  
    function thumbnail_tag($img,$size,$resize=null,$classes='')
    {
        $img=get_image($img);

        if (null==$resize)
        {
            $resize=$size;
        }
        $img_url=thumbnail_url($img,$size);
        return "<img class='".$classes."' itemprop='image' src='".$img_url."' alt='thumbail of ".$img['image_name']."' width='".$resize."' height='".$resize."' title='".$img['image_name']."'/>";
    }
    
    /* *************************************************************************
        thumbnail_url() - gets just the url of a thumbnail
        @param array $img - the image data
        @param int $size - the size to be used as a choice of file, can be an array ordered with the best
            choice first to help with loading images after size changes
        @return string - the thumbnail url
    */   
    function thumbnail_url($img,$size,$prefix='t')
    {
        $thumbnail_url='';
        if (is_array($size))
        {
            foreach ($size as $s)
            {
                $path_suffix="/user_img/".$img['user_id']."/".$img['image_filename'].$prefix.$s.$img['image_ext'];
                $path=$_SERVER['DOCUMENT_ROOT'].$path_suffix;

                if (is_file($path))
                {
                    $thumbnail_url=$path_suffix;
                    break;
                }
            }
        }
        else
        {
            $path_suffix="/user_img/".$img['user_id']."/".$img['image_filename'].$prefix.$size.$img['image_ext'];
            $path=$_SERVER['DOCUMENT_ROOT'].$path_suffix;
            if (is_file($path))
            {
                $thumbnail_url=$path_suffix;
            } 
        }
        return $thumbnail_url;
    }
    
    /* *************************************************************************
        image_name() - gets the image name
        @param array $img - the image data
        @param int $size - the size to be used as a choice of file
    */  
    function image_name($img,$trim=25)
    {
        if (strlen($img["image_name"])>0)
            if (strlen($img["image_name"])>$trim)
                return substr($img["image_name"],0,$trim);
            else
                return $img["image_name"];
        else
            return "un-named";
    }

    /* *************************************************************************
        node_src() - gets an image, or the default image, based on just the node image value
        @param $node - the node
        @param $aspect - the aspect ratio to choose
        @param $target - the saved image width target
        @param $size - the size to resize to
        @return $img_tag - a complete image tag
    */
    function node_thumb_src($node,$aspect='t',$target=300,$size=180)
    {
        $img_tag="";
    
        if (strlen($node['image']))
        {
            $src=str_replace("t300.", $aspect.$target.".", $node['image']);
            $img_tag="<img src='".$src."' alt='".$node['name']."' width='".$size."'/>";
        }
        else
        {
            $img_tag="<img src='/img/default_image.png' alt='".$node['name']."' width='".$size."'/>";
        }
    
        return $img_tag;
    }