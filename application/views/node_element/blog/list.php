<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<?php
    echo "<div id='filter_panel'>";
    echo "<span id='filter_heading'>filter by name:</span>";
    echo "<input id='filter' class='form_field rounded' type='text' onkeyup='fe_filter()' placeholder='enter text to filter panels'/>";
    echo "</div>";
    ?>
        <script type='text/javascript'>
            if (window.focus)
            {
                function fe_filter()
                {
                    var value=$('#filter').val();
                    if (value!='')
                    {
                        $('.fe_list').css('display','none');
                        value=value.replace(' ','_');
                        value=value.replace("'",'');
                        value=value.toLowerCase();
                        $("[id*="+value+"]").css('display', '');
                    }
                    else
                    {
                        $('#list .fe_list').css('display', '');
                    }
                }
            }
        </script>
    <?php
        // open list
            switch ($node['url'])
            {
                case 'blog':
                    echo "<div id='list' itemscope itemtype='http://schema.org/Blog'>";
                    break;
                case 'products':
                    echo "<div id='list' itemscope itemtype='http://schema.org/SomeProducts'>";
                    break;
                default:
                    echo "<div id='list' itemscope itemtype='http://schema.org/ItemList'>";
                    break;

            }

        // list nodes
            foreach ($node_list as $panel)
            {
                // filter id
                    $filter_id=str_replace("'",'',str_replace(" ","_",strtolower($panel['name']." ".$panel['tags'])));

                // open panels
                    switch ($panel['type'])
                    {
                        case 'blog':
                            echo "<div id='".$filter_id."' class='".$panel['type']."_panel fe_list' itemprop='blogPost' itemscope itemtype='http://schema.org/Article'>";
                            break;
                        case 'product':
                            echo "<div id='".$filter_id."' class='".$panel['type']."_panel fe_list' itemprop='product' itemscope itemtype='http://schema.org/Product'>";
                            break;
                        default:
                            echo "<div id='".$filter_id."' class='".$panel['type']."_panel fe_list' itemprop='itemListElement' itemscope itemtype='http://schema.org/CreativeWork'>";
                    }

                // common
                    echo "<h2 itemprop='name'><a itemprop='url' href='/".$panel['url']."'>".$panel['name']."</a></h2>";
                    echo "<div class='list_strap'>".$panel['short_desc']."</div>";
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

        // close list div
            echo "</div>  ";
    ?>
