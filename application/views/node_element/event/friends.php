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
    // friends as yet to be made
        foreach ($user['pending_friends'] as $pf)
        {
            ?>
                <div id='<?php echo $pf['id']; ?>'>
                    <div class='strpanel notify rounded'>
                        <span class='frimg'><img class='rounded' src='<?php echo $pf['image']; ?>'/></span>
                        <span class='frname'><a href='/<?php echo $pf['url']; ?>'><?php echo $pf['name']; ?></a></span>
                        <span class='frdesc'><?php echo $pf['short_desc']; ?></span>
                        <span class='frbutton' onclick='accept(<?php echo $user['user_id']; ?>,<?php echo $pf['id']; ?>,"<?php echo $pf['name']; ?>","user")'>accept</span>
                        <span class='decline' onclick='decline(<?php echo $user['user_id']; ?>,<?php echo $pf['id']; ?>,"<?php echo $pf['name']; ?>")'>decline</span>
                    </div>
                </div>
            <?php
        }

    // current friendships
        foreach ($node['friends'] as $fr)
        {
            if ('user'==$fr['type'])
            {
                ?>
                    <div id='<?php echo $fr['id']; ?>'>
                        <div class='strpanel viewed rounded'>
                            <span class='frimg'><img class='rounded' src='<?php echo $fr['image']; ?>'/></span>
                            <span class='frname'><a href='/<?php echo $fr['url']; ?>'><?php echo $fr['name']; ?></a></span>
                            <span class='frdesc'><?php echo $fr['short_desc']; ?></span>
                        </div>
                    </div>
                <?php
            }
        }
?>
