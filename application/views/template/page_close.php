<?php
                        if (isset($parent_category))
                        {
                            ?>
                                <div id='<?php echo $node['type']; ?>_panel'>
                            <?php
                        }
                        ?>                </div>
            </div>
        <?=$ie['close'] ?>
        <?php echo $javascript; ?>
        <?php echo $image_upload_js; ?>
        <?php echo $background_image; ?>
        <?php echo $admin_js; ?>
        <?php echo $video_player; ?>
    </body>
</html>
