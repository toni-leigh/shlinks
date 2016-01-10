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

// get some things for comparisons with node types
    if (isset($edit_node['type']))
    {
        $type=$edit_node['type'];
    }
    else
    {
        $find_type=explode('_',$node['url']);
        if (isset($find_type[1]) ? $type=$find_type[1] : $type='' );
    }

// general links
    // links
        if (in_array($this->user['user_type'],$perms['links']))
        {
            if ('links'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/links'>links</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/links'>links</a>";
            }
        }

    // leaderboard
        if (in_array($this->user['user_type'],$perms['scores']))
        {
            if ('scores'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/scores'>scores</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/scores'>scores</a>";
            }
        }

    // about
        if (in_array($this->user['user_type'],$perms['about']))
        {
            if ('about'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/about'>about</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/about'>about</a>";
            }
        }

    // page
        if (in_array($this->user['user_type'],$perms['page']))
        {
            if ('page'==$type)
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/page/list'>pages</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/page/list'>pages</a>";
            }
        }

    // blog
        if (in_array($this->user['user_type'],$perms['blog']))
        {
            if ('blog'==$type)
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/blog/list'>blog</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/blog/list'>blog</a>";
            }
        }

    // category
        if (in_array($this->user['user_type'],$perms['category']))
        {
            if ('category'==$type)
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/category/list'>category</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/category/list'>category</a>";
            }
        }

    // users
        if (in_array($this->user['user_type'],$perms['user']))
        {
            if (in_array($this->user['user_type'],array('admin_user','super_admin')) ? $text='users' : $text='me' );
            if ('user'==$type)
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/user/list'>".$text."</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/user/list'>".$text."</a>";
            }
        }

    // groups
        if (in_array($this->user['user_type'],$perms['group']))
        {
            if ('node_group'==$type)
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/node_group/list'>groups</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/node_group/list'>groups</a>";
            }
        }

    // images
        if (in_array($this->user['user_type'],$perms['all_images']))
        {
            if ('all-images'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adgen_sel' href='/all-images'>images</a>";
            }
            else
            {
                echo "<a class='adnav adgen' href='/all-images'>images</a>";
            }
        }

// e-commerce links
    // order list
        if (in_array($this->user['user_type'],$perms['order_list']))
        {
            if ('order-list'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adecomm_sel' href='/order-list'>orders</a>";
            }
            else
            {
                echo "<a class='adnav adecomm' href='/order-list'>orders</a>";
            }
        }

    // products
        if (in_array($this->user['user_type'],$perms['product']))
        {
            if ('product'==$type)
            {
                echo "<a class='adnav adnav_sel adecomm_sel' href='/product/list'>products</a>";
            }
            else
            {
                echo "<a class='adnav adecomm' href='/product/list'>products</a>";
            }
        }

    // variation types
        if (in_array($this->user['user_type'],$perms['variation']))
        {
            if ('variation-types-definition'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adecomm_sel' href='/variation-types-definition'>variation types</a>";
            }
            else
            {
                echo "<a class='adnav adecomm' href='/variation-types-definition'>variation types</a>";
            }
        }

    // voucher
        if (in_array($this->user['user_type'],$perms['voucher']))
        {
            if ('voucher-definition'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adecomm_sel' href='/voucher-definition'>vouchers</a>";
            }
            else
            {
                echo "<a class='adnav adecomm' href='/voucher-definition'>vouchers</a>";
            }
        }

    //postage
        if (in_array($this->user['user_type'],$perms['postage']))
        {
            if ('postage-calculation-definition'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adecomm_sel' href='/postage-calculation-definition'>postage</a>";
            }
            else
            {
                echo "<a class='adnav adecomm' href='/postage-calculation-definition'>postage</a>";
            }
        }

// calendar
    // calendars
        if (in_array($this->user['user_type'],$perms['calendar']))
        {
            if ('calendar'==$type or
                isset($admin_calendar))
            {
                echo "<a class='adnav adnav_sel adcal_sel' href='/calendar/list'>calendar</a>";
            }
            else
            {
                echo "<a class='adnav adcal' href='/calendar/list'>calendar</a>";
            }
        }

    // events
        if (in_array($this->user['user_type'],$perms['event']))
        {
            if ('event'==$type or
                isset($event_edit_form))
            {
                echo "<a class='adnav adnav_sel adcal_sel' href='/event/list'>events</a>";
            }
            else
            {
                echo "<a class='adnav adcal' href='/event/list'>events</a>";
            }
        }

// others
    // newsletter
        if (in_array($this->user['user_type'],$perms['newsletter']))
        {
            if ('newsletter-signups'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adother_sel' href='/newsletter-signups'>sign ups</a>";
            }
            else
            {
                echo "<a class='adnav adother' href='/newsletter-signups'>sign ups</a>";
            }
        }

    // miscellaneous
        if (in_array($this->user['user_type'],$perms['misc']))
        {
            if ('miscellaneous-admin'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adother_sel' href='/miscellaneous-admin'>misc.</a>";
            }
            else
            {
                echo "<a class='adnav adother' href='/miscellaneous-admin'>misc.</a>";
            }
        }

    // credentials
        if (in_array($this->user['user_type'],$perms['credentials']))
        {
            if ('change-login-credentials'==$node['url'])
            {
                echo "<a class='adnav adnav_sel adother_sel' href='/change-login-credentials'>credentials</a>";
            }
            else
            {
                echo "<a class='adnav adother' href='/change-login-credentials'>credentials</a>";
            }
        }

    echo $logout_form;

echo "</div>";
echo "</div>";
