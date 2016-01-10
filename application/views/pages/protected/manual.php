<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
?>

<div id='manual'>
    <div class='panel'>
        <h2>
            <span id='mantda_heading' class='ad_heading_text noselect' onclick='open_height("mantda")'>The Data Array</span>
            <span id='mantda_show' class='sprite panel_open noselect' onclick='open_height("mantda")'></span>
        </h2>
        <div id='mantda_panel' class='panel_details panel_closed'>
            <p>this is an over-view of the various common elements of the data array</p>
            <?php
                foreach ($man_data_array as $k=>$v)
                {
                    echo $k."<br/>";
                    if (is_array($v) &&
                        !in_array($k,array('default_nodes','head_views','footer_views')))
                    {
                        foreach ($v as $k2=>$v2)
                        {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$k2."</br>";
                            if (is_array($v2))
                            {
                                foreach ($v2 as $k3=>$v3)
                                {
                                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$k3."</br>";
                                }
                            }
                        }
                    }
                    echo "<br/>";
                }
            ?>
        </div>
    </div>


    <div class='panel'>
        <h2>
            <span id='mantcn_heading' class='ad_heading_text noselect' onclick='open_height("mantcn")'>The Core Nodes</span>
            <span id='mantcn_show' class='sprite panel_open noselect' onclick='open_height("mantcn")'></span>
        </h2>
        <div id='mantcn_panel' class='panel_details panel_closed'>
            <p>
                these nodes are unchangable and stay as they are in all installs. they represent default
                pages, CMS pages and test stuff - it is still a good idea to refer to them by url rather
                than id if you need to do specific things in their cases
            </p>
            <?php
                $last_heading='';
                foreach ($default_nodes as $dn)
                {
                    $id=$dn['id'];

                    // make the node admin urls
                        if (strpos($dn['url'],'_')>0 &&
                            (0===strpos($dn['url'],'create') or 0===strpos($dn['url'],'list')))
                        {
                            $bits=explode('_',$dn['url']);
                            $url=$bits[1].'/'.$bits[0];
                        }
                        else
                        {
                            $url=$dn['url'];
                        }

                    // output a heading
                        if (1==$id) { $heading='Master User [1]'; }
                        elseif ($id>=2 && $id<=5) { $heading='Test Users [2-5]'; }
                        elseif ($id>=6 && $id<=15) { $heading='Front Stage: Basic Pages [6-15]'; }
                        elseif ($id>=16 && $id<=30) { $heading='Front Stage: E-Commerce Pages [16-30]'; }
                        elseif ($id>=31 && $id<=50) { $heading='Front Stage: General Pages [31-50]'; }
                        elseif ($id>=51 && $id<=99) { $heading='Front Stage: Node Lists etc. [51-100]'; }
                        elseif (100==$id) { $heading='Development Manual [100]'; }
                        elseif ($id>=101 && $id<=170) { $heading='Admin: Basic Nodes [101-170]'; }
                        elseif ($id>=171 && $id<=200) { $heading='Admin: Users [171-200]'; }
                        elseif ($id>=201 && $id<=300) { $heading='Admin: E-Commerce [201-300]'; }
                        elseif ($id>=301 && $id<=350) { $heading='Admin: Calendar and Events [301-350]'; }
                        elseif ($id>=351 && $id<=360) { $heading='Admin: Images [351-360]'; }
                        elseif ($id>=361 && $id<=400) { $heading='Admin: Others [360-400]'; }
                        elseif (404==$id){ $heading='404 Page Not Found [404]'; }
                        elseif ($id>=501 && $id<=550){ $heading='Categories [501-550]'; }
                        elseif ($id>=551 && $id<=1000){ $heading='Others [551-1000]'; }

                        if ($heading!=$last_heading)
                        {
                            echo "<h3>".$heading."</h3>";
                        }

                    // output the details
                        echo "<a href='/".$url."'>".$dn['name']." [".$id."]</a>";
                        echo "<span>".$dn['short_desc']."</span>";

                    $last_heading=$heading;
                }
            ?>
        </div>
    </div>


    <div class='panel'>
        <h2>
            <span id='manvs_heading' class='ad_heading_text noselect' onclick='close_height("manvs")'>View Structure</span>
            <span id='manvs_show' class='sprite panel_close noselect' onclick='close_height("manvs")'></span>
        </h2>
        <div id='manvs_panel' class='panel_details'>
            <p>
                where the views are and how they are extracted to create the page, in what order and
                sensible places to insert any specific ones you need to to add
            </p>
            <p>
                views are stored in the database, in a more and more specific fashion and pulled out
                depending on the node being viewed
            </p>
            <h3>Template Views</h3>
            <p>
                these are the views that provide the common content which opens and closes the page
                and should be considered common to all site pages. you can see a list of these views
                here:
            </p>
            <p>opening views:</p>
            <?php
                foreach ($head_views as $hv)
                {
                    $view_name=str_replace('template/','',$hv['view']);
                    echo "<code id='".$view_name."' class='man_view'>";

                    echo "<h3>".$hv['view']."</h3>";

                    echo "<pre rel='html'>";
                    $f=file_get_contents($_SERVER['DOCUMENT_ROOT']."/application/views/".$hv['view'].".php");

                    $f=str_replace(array('<?php echo $','echo $'),'<_',$f);
                    $f=str_replace('; ?>','_>',$f);

                    $f=htmlentities($f);

                    if ('template/admin_nav'==$hv['view'])
                    {
                        echo "admin nav items condensed";
                    }
                    else
                    {
                        echo $f;
                    }

                    echo "</pre>";

                    echo "</code>";
                }

                echo "<div id='central_panels'>";
                echo "<div class='node_element width_135'>connections</div>";
                echo "<div class='node_element width_135'>details</div>";
                echo "<div class='node_element width_136'>images</div>";
                echo "<div class='node_element width_136'>list</div>";
                echo "<div class='node_element width_136'>members</div>";
                echo "<div class='node_element width_135'>messages</div>";
                echo "<div class='node_element width_135'>stream</div>";
                echo "</div>";

                foreach ($footer_views as $fv)
                {
                    $view_name=str_replace('template/','',$fv['view']);
                    echo "<div id='".$view_name."' class='man_view'>";

                    echo "<h3>".$fv['view']."</h3>";

                    echo "<pre rel='html'>";
                    $f=file_get_contents($_SERVER['DOCUMENT_ROOT']."/application/views/".$fv['view'].".php");

                    $f=str_replace(array('<?php echo $','echo $'),'<_',$f);
                    $f=str_replace('; ?>','_>',$f);

                    $f=htmlentities($f);

                    echo $f;

                    echo "</pre>";

                    echo "</div>";
                }
            ?>

            <p><strong>actually output the view files as plain text in structure, with colour to mark the breaks</strong></p>


            <h3>Database Type Defaults</h3>
            <p>
                each node type has its own default that can be set to save and load the same views for each
                type that is created. this mechanism is all set up to load a node panel view which in turn
                defaults to details. it can be set to any view that you like
            </p>

            <h3>Node Specific Views</h3>
            <p>
                each node can have any number of views set to load on a case by case basis
            </p>
        </div>
    </div>


    <div class='panel'>
        <h2>
            <span id='mantdam_heading' class='ad_heading_text noselect' onclick='close_height("mantdam")'>Template Data Array Model</span>
            <span id='mantdam_show' class='sprite panel_close noselect' onclick='close_height("mantdam")'></span>
        </h2>
        <div id='mantdam_panel' class='panel_details'>
            <p>
                the template data array model is where you over-ride any data array values or add to the
                values with your own logic and data extraction in response to conditions such as node type,
                id etc. and is where you do all the coding to do specific things on the site
            </p>
            <p>
                this file includes an empty method for you to fill and also includes a function that is
                called by the save node function allowing you to have the site act in a specific way
                without having to edit the core, for example, if you want to categorise saved nodes based
                on site specific conditions, or if you needed to populate a (possibly site specific)
                field in the node table based on site specific type conditions
            </p>
        </div>
    </div>


    <div class='panel'>
        <h2>
            <span id='manct_heading' class='ad_heading_text noselect' onclick='close_height("manct")'>Creating Types</span>
            <span id='manct_show' class='sprite panel_close noselect' onclick='close_height("manct")'></span>
        </h2>
        <div id='manct_panel' class='panel_details'>
            1. use the sql
            2. set up the admin config to do things correctly for your new type
            3. set up admin nav by cutting and pasting
        </div>
    </div>


    <div class='panel'>
        <h2>
            <span id='manaftt_heading' class='ad_heading_text noselect' onclick='close_height("manaftt")'>Adding Fields to Types</span>
            <span id='manaftt_show' class='sprite panel_close noselect' onclick='close_height("manaftt")'></span>
        </h2>
        <div id='manaftt_panel' class='panel_details'>
        </div>
    </div>


</div>
