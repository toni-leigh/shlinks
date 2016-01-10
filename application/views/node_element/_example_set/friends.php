<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<?=$this->load->view("template/node/details_heading"); ?>
<div id='friends' class='node_panel friends'>
<?php
    // friends as yet to be made
        ?>
        <div class='friend_list_panel new_requests'>
        <h2>Friend requests</h2>
        <div class='friend_list'>
        <?php
        foreach ($user['pending_friends'] as $pf)
        {
            ?>
                <div id='<?=$pf['id']; ?>'>
                    <div class='friend_panel notify'>
                        <span class='frimg'><img src='<?=str_replace("t300.", "t200.", $pf['image']); ?>' width='156' height='156'/></span>
                        <span class='frname'><a href='/<?=$pf['url']; ?>'><?=$pf['name']; ?></a></span>
                        <span class='js_connect action accept' onclick='accept(<?=$user['user_id']; ?>,<?=$pf['id']; ?>,"<?=$pf['name']; ?>","user")'>accept</span>
                        <span class='js_connect action decline' onclick='decline(<?=$user['user_id']; ?>,<?=$pf['id']; ?>,"<?=$pf['name']; ?>")'>decline</span>
                    </div>
                </div>
            <?php
        }
        ?>
        </div>
        </div>
        <?php

    // current friendships
        ?>
        <div class='friend_list_panel current_friends'>
        <h2>Current friends</h2>
        <div class='friend_list'>
        <?php
        foreach ($node['friends'] as $fr)
        {
            if ('user'==$fr['type'])
            {
                ?>
                    <div id='<?=$fr['id']; ?>'>
                        <div class='friend_panel viewed'>
                            <span class='frimg'><img src='<?=str_replace("t300.", "t200.", $fr['image']); ?>' width='156' height='156'/></span>
                            <span class='frname'><a href='/<?=$fr['url']; ?>'><?=$fr['name']; ?></a></span>
                        </div>
                    </div>
                <?php
            }
        }
        ?>
        </div>
        </div>
        <?php
?>
</div>
