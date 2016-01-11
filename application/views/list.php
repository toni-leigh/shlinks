<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
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
                            echo "<div id='".$filter_id."' class='".$panel['type']."_panel panel' itemprop='blogPost' itemscope itemtype='http://schema.org/Article'>";
                            break;
                        case 'product':
                            echo "<div id='".$filter_id."' class='".$panel['type']."_panel panel' itemprop='product' itemscope itemtype='http://schema.org/Product'>";
                            break;
                        default:
                            echo "<div id='".$filter_id."' class='".$panel['type']."_panel panel' itemprop='itemListElement' itemscope itemtype='http://schema.org/CreativeWork'>";
                    }

                // common
                    echo "<h2 itemprop='name'>".$panel['name']."</h2>";
                    echo "<div class='list_strap'>".$panel['short_desc']."</div>";
                    echo "<a itemprop='url' href='".$panel['url']."'>Visit</a>";
                    echo "<a itemprop='url' href='/".$panel['id']."'>Comments &amp; Summary</a>";
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
