<?php
    if (is_array($node_list))
    {
        foreach ($node_list as $panel)
        {
            $cat_names="";
            if (count($panel['other_cats']))
            {
                foreach ($panel['other_cats'] as $ocat)
                {
                    $cat_names.=$ocat['name'];
                }
            }

            // filter id
                $filter_id=preg_replace("/[^\da-z]/i", "",strtolower($panel['name']." ".$panel['tags']." ".$panel['user_name']." ".$cat_names));

            // open panels
                echo "<div id='".$filter_id."' class='".$panel['type']."_panel panel' itemprop='itemListElement' itemscope itemtype='http://schema.org/CreativeWork'>";

            // common
                echo "<div class='details_left'>";
                if (0===strpos($panel['url'], "http") ? $url=$panel['url'] : $url="http://".$panel['url'] );
                echo "<h2 itemprop='name'><a itemprop='url' href='".$url."'>".$panel['name']."</a></h2>";
                if (isset($user['id']))
                {
                    if ($user['id']==$panel['user_id'] || 'super_admin' == $user['type'])
                    {
                        echo "<a class='posted_by' href='/article_link/edit/".$panel['id']."'>EDIT</a>";
                    }
                }
                echo "<a class='posted_by' href='/".$panel['user_id']."/links'>".$panel['user_name']."</a>";

                if (count($panel['other_cats']))
                {
                    echo "<span class='other_cats'>";
                    echo    "see also:&nbsp;";
                    foreach ($panel['other_cats'] as $ocat)
                    {
                        echo "<a class='other_cat' href='/".$ocat['url']."'>".$ocat['name']."</a>";
                    }
                    echo "</span>";
                }
                echo "</div>";
                //echo "<div class='list_strap'>".$panel['short_desc']."</div>";
                //echo "<a itemprop='url' href='/".$panel['id']."'>Comments &amp; Summary</a>";
                if (isset($user['id']))
                {
                    if ($user['id']!=$panel['user_id'])
                    {
                        echo "<a class='visit sprite' itemprop='url' href='".$url."'>Visit</a>";
                        echo "<span class='score".$panel['id']." score' style='color:rgba(".$panel['score_data']['font']['red'].",".$panel['score_data']['font']['green'].",".$panel['score_data']['font']['blue'].",1);border:1px solid rgba(".$panel['score_data']['bg']['red'].",".$panel['score_data']['bg']['green'].",".$panel['score_data']['bg']['blue'].",1);background-color:rgba(".$panel['score_data']['bg']['red'].",".$panel['score_data']['bg']['green'].",".$panel['score_data']['bg']['blue'].",0.25)'>".$panel['score_data']['points']."</span>";
                        echo "<div class='votes".$panel['id']." votes'>";
                        echo $panel['vote_buttons'];
                        echo "</div>";
                    }
                    else
                    {
                        echo "<span class='score".$panel['id']." score' style='color:rgba(".$panel['score_data']['font']['red'].",".$panel['score_data']['font']['green'].",".$panel['score_data']['font']['blue'].",1);border:1px solid rgba(".$panel['score_data']['bg']['red'].",".$panel['score_data']['bg']['green'].",".$panel['score_data']['bg']['blue'].",1);background-color:rgba(".$panel['score_data']['bg']['red'].",".$panel['score_data']['bg']['green'].",".$panel['score_data']['bg']['blue'].",0.25)'>".$panel['score_data']['points']."</span>";
                    }
                }
                else
                {
                    echo "<span class='score".$panel['id']." score' style='color:rgba(".$panel['score_data']['font']['red'].",".$panel['score_data']['font']['green'].",".$panel['score_data']['font']['blue'].",1);border:1px solid rgba(".$panel['score_data']['bg']['red'].",".$panel['score_data']['bg']['green'].",".$panel['score_data']['bg']['blue'].",1);background-color:rgba(".$panel['score_data']['bg']['red'].",".$panel['score_data']['bg']['green'].",".$panel['score_data']['bg']['blue'].",0.25)'>".$panel['score_data']['points']."</span>";
                }

                if ('blog'==$panel['type'])
                {
                    // author and date
                        echo "<div class='author' itemprop='author'></div>";
                }
                if (isset($panel['node_html']))
                {
                    echo "<div class='list_body' itemprop='text'>".$panel['node_html']."</div>";
                }
                if (isset($panel['images']))
                {
                    echo "<div class='list_images' itemprop='image' itemscope itemtype='http://schema.org/ImageGallery'>".$panel['images']."</div>";
                }

            // close panel
                echo "</div>";
        }

    }
?>
