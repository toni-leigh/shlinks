<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

echo "<div id='admin_top_nav'>";
echo "<div id='centre_admin_nav'>";

// get the permissions, with a default set to avoid update problem with old sites
    $perms=$this->config->item('admin_nav_permissions');
// general links
    // links
        if ('links'==$node['url'])
        {
            echo "<a class='adnav adnav_sel adgen_sel' href='/links'>links</a>";
        }
        else
        {
            echo "<a class='adnav adgen' href='/links'>links</a>";
        }

    // leaderboard
        if ('scores'==$node['url'])
        {
            echo "<a class='adnav adnav_sel adgen_sel' href='/scores'>scores</a>";
        }
        else
        {
            echo "<a class='adnav adgen' href='/scores'>scores</a>";
        }

    // about
        if (isset($user['url']))
        {
            if ('me'==$node['url'] or
                (isset($user['id']) && $node['id']==$user['id']))
            {
                echo "<a id='me_link_sel' class='adnav adnav_sel adgen_sel' href='/".$user['url']."/all'>me</a>";
            }
            else
            {
                echo "<a id='me_link' class='adnav adgen' href='/".$user['url']."/all'>me</a>";
            }
        }
        else
        {
            echo "<a id='me_link' class='adnav adgen' href='/me'>me</a>";
        }

    // add link form
        echo form_open('/article_link/add');
        ?>
            <input id='url_submit' class='submit' type='submit' value='add'/>
            <input id='url_field' type='text' name='new_url' value='' autofocus='autofocus' placeholder='url here and click add to scrape'/>

        </form>
        <?php

echo "</div>";
echo "</div>";
