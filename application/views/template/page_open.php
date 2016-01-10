    <body class='<?=$body_classes ?>' <?=$body_map_onload ?>>
        <?=$ie['open'] ?>
            <div id='panel_<?php echo $node['id']; ?>'>
                <div id='<?php echo $node['type']; ?>_panel'>
                <?php
                    if (is_array($parent_category) &&
                        count($parent_category))
                    {
                        ?>
                            <div id='catpanel_<?php echo $parent_category['node_id']; ?>'>
                        <?php
                    } ?>
                    <?php echo $skip['start']; ?>
                    <?php echo $live_warning; ?>

