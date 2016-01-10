<?php
    if (isset($individual_image_tag))
    {
        echo $pin_button;
        echo "<div id='image_node_details'>This is an image from the gallery of the ".$image_node['human_type']." <a href='/".$image_node['url']."'>".$image_node['name']."</a>. Please <a href='/".$image_node['url']."'>click here</a> to see more about this ".$image_node['human_type'].".</div>";
        echo $individual_image_tag;
        echo "<div id='individual_image_name'>".$individual_image['image_name']."</div>";
    }
    else
    {
        echo "<span id='image_not_found'>Image Not Found</span>";
    }
?>