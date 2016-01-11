<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<?=$this->load->view("template/node/details_heading"); ?>
<div id='members' class='node_panel members'>
<?php
    // users waiting to join
        foreach ($group['pending_joins'] as $pj)
        {
            ?>
                <div id='<?=$pj['id']; ?>'>
                    <div class='strpanel notify rounded'>
                        <span class='frimg'><img class='rounded' src='<?=$pj['image']; ?>'/></span>
                        <span class='frname'><a href='/<?=$pj['url']; ?>'><?=$pj['name']; ?></a></span>
                        <span class='frdesc'><?=$pj['short_desc']; ?></span>
                        <span class='js_connect action accept' onclick='accept(<?=$node['id']; ?>,<?=$pj['id']; ?>,"<?=$pj['name']; ?>","node_group")'>accept</span>
                        <span class='js_connect action decline' onclick='decline(<?=$node['id']; ?>,<?=$pj['id']; ?>,"<?=$pj['name']; ?>")'>decline</span>
                    </div>
                </div>
            <?php
        }

    // users already joined
        foreach ($node['members'] as $m)
        {
            ?>
                <div id='<?=$m['id']; ?>'>
                    <div class='strpanel viewed rounded'>
                        <span class='frimg'><img class='rounded' src='<?=$m['image']; ?>'/></span>
                        <span class='frname'><a href='/<?=$m['url']; ?>'><?=$m['name']; ?></a></span>
                        <span class='frdesc'><?=$m['short_desc']; ?></span>
                    </div>
                </div>
            <?php
        }
?>
</div>
