<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<div id='members' class='node_panel members'>
<?php
    // users waiting to join
        foreach ($group['pending_joins'] as $pj)
        {
            ?>
                <div id='<?php echo $pj['id']; ?>'>
                    <div class='strpanel notify rounded'>
                        <span class='frimg'><img class='rounded' src='<?php echo $pj['image']; ?>'/></span>
                        <span class='frname'><a href='/<?php echo $pj['url']; ?>'><?php echo $pj['name']; ?></a></span>
                        <span class='frdesc'><?php echo $pj['short_desc']; ?></span>
                        <span class='conn_button accept' onclick='accept(<?php echo $node['id']; ?>,<?php echo $pj['id']; ?>,"<?php echo $pj['name']; ?>","node_group")'>accept</span>
                        <span class='conn_button decline' onclick='decline(<?php echo $node['id']; ?>,<?php echo $pj['id']; ?>,"<?php echo $pj['name']; ?>")'>decline</span>
                    </div>
                </div>
            <?php
        }

    // users already joined
        foreach ($node['members'] as $m)
        {
            ?>
                <div id='<?php echo $m['id']; ?>'>
                    <div class='strpanel viewed rounded'>
                        <span class='frimg'><img class='rounded' src='<?php echo $m['image']; ?>'/></span>
                        <span class='frname'><a href='/<?php echo $m['url']; ?>'><?php echo $m['name']; ?></a></span>
                        <span class='frdesc'><?php echo $m['short_desc']; ?></span>
                    </div>
                </div>
            <?php
        }
?>
</div>
