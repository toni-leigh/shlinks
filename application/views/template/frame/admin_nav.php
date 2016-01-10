<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

if (1==$admin_page)
{
    echo "<div class='admin_top_nav'>";

    echo "<div class='frame_centre'>";

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
        // page
            if (in_array($this->user['user_type'],$perms['page']))
            {
                if ('page'==$type)
                {
                    echo "<a class='adnav adgen_sel adnav_sel' href='/page/list'>pages</a>";
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
                    echo "<a class='adnav adgen_sel adnav_sel' href='/blog/list'>blog</a>";
                }
                else
                {
                    echo "<a class='adnav adgen' href='/blog/list'>blog</a>";
                }
            }

        // seo articles
            if (in_array($this->user['user_type'],$perms['seoarticle']))
            {
                if ('seoarticle'==$type)
                {
                    echo "<a class='adnav adgen_sel adnav_sel' href='/seoarticle/list'>seo articles</a>";
                }
                else
                {
                    echo "<a class='adnav adgen' href='/seoarticle/list'>seo articles</a>";
                }
            }

        // users
            if (in_array($this->user['user_type'],$perms['user']))
            {
                if (in_array($this->user['user_type'],array('admin_user','super_admin')) ? $text='users' : $text='me' );
                if ('user'==$type)
                {
                    echo "<a class='adnav adgen_sel adnav_sel' href='/user/list'>".$text."</a>";
                }
                else
                {
                    echo "<a class='adnav adgen' href='/user/list'>".$text."</a>";
                }
            }

        // groups
            if (in_array($this->user['user_type'],$perms['groupnode']))
            {
                if ('groupnode'==$type)
                {
                    echo "<a class='adnav adgen_sel adnav_sel' href='/groupnode/list'>groups</a>";
                }
                else
                {
                    echo "<a class='adnav adgen' href='/groupnode/list'>groups</a>";
                }
            }

        // images
            if (in_array($this->user['user_type'],$perms['all_images']))
            {
                if ('all-images'==$node['url'])
                {
                    echo "<a class='adnav adgen_sel adnav_sel' href='/all-images'>images</a>";
                }
                else
                {
                    echo "<a class='adnav adgen' href='/all-images'>images</a>";
                }
            }

            echo "<span class='admin_nav_spacer'>|</span>";

    // e-commerce links
        // order list
            /* if (in_array($this->user['user_type'],$perms['order_list']))
            {
                if ('order-list'==$node['url'])
                {
                    echo "<a class='adnav adecomm_sel adnav_sel' href='/order-list'>orders</a>";
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
                    echo "<a class='adnav adecomm_sel adnav_sel' href='/product/list'>products</a>";
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
                    echo "<a class='adnav adecomm_sel adnav_sel' href='/variation-types-definition'>variation types</a>";
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
                    echo "<a class='adnav adecomm_sel adnav_sel' href='/voucher-definition'>vouchers</a>";
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
                    echo "<a class='adnav adecomm_sel adnav_sel' href='/postage-calculation-definition'>postage</a>";
                }
                else
                {
                    echo "<a class='adnav adecomm' href='/postage-calculation-definition'>postage</a>";
                }
            }

            echo "<span class='admin_nav_spacer'>|</span>"; */

    // calendar
        // calendars
            if (in_array($this->user['user_type'],$perms['calendar']))
            {
                if ('calendar'==$type or
                    isset($admin_calendar))
                {
                    echo "<a class='adnav adcal_sel adnav_sel' href='/calendar/list'>calendar</a>";
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
                    echo "<a class='adnav adcal_sel adnav_sel' href='/event/list'>events</a>";
                }
                else
                {
                    echo "<a class='adnav adcal' href='/event/list'>events</a>";
                }
            }

            echo "<span class='admin_nav_spacer'>|</span>";

    // others
        // newsletter
            if (in_array($this->user['user_type'],$perms['newsletter']))
            {
                if ('newsletter-signups'==$node['url'])
                {
                    echo "<a class='adnav adother_sel adnav_sel' href='/newsletter-signups'>sign ups</a>";
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
                    echo "<a class='adnav adother_sel adnav_sel' href='/miscellaneous-admin'>misc.</a>";
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
                    echo "<a class='adnav adother_sel adnav_sel' href='/change-login-credentials'>credentials</a>";
                }
                else
                {
                    echo "<a class='adnav adother' href='/change-login-credentials'>credentials</a>";
                }
            }

        // contacts
            if (in_array($this->user['user_type'],$perms['contacts']))
            {
                if ('contact-list'==$node['url'])
                {
                    echo "<a class='adnav adother_sel adnav_sel' href='/contact-list'>contacts</a>";
                }
                else
                {
                    echo "<a class='adnav adother' href='/contact-list'>contacts</a>";
                }
            }

    echo "</div>";

    echo "</div>";
}
