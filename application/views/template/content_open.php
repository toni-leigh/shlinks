                    <div id='content'>
                        <?php echo $skip['end']; ?>
                        <?php echo $scrollers; ?>

                        <?php
                            /*
                                <div id='nav'>
                                    <?php echo $nav; ?>
                                </div>
                            */
                        ?>
                        <span id='heading_panel'>
                        <?php
                            if (!in_array($node['id'],array(6)))
                            {
                                echo "<h1>".$h1."</h1>";
                            }
                            if ('user'==$node['type'])
                            {
                                // add three panel links
                                    ?>
                                        <!-- <a class='<?php echo $activity_sel; ?>' href='/<?php echo $node['url']; ?>/activity'>Activity</a> -->
                                        <a class='<?php echo $all_sel; ?>' href='/<?php echo $node['url']; ?>/all'>Collection</a>
                                        <a class='<?php echo $links_sel; ?>' href='/<?php echo $node['url']; ?>/links'>Added</a>
                                        <a class='<?php echo $votes_sel; ?>' href='/<?php echo $node['url']; ?>/votes'>Voted For</a>
                                    <?php
                            }
                            if (is_array($node_list))
                            {
                                echo "<div id='filter_panel'>";
                                echo "<input id='filter' class='form_field rounded' type='text' onkeyup='fe_filter()' placeholder='enter text to filter panels' autofocus='autofocus'/>";
                                echo "</div>";
                            }
                        ?>
                        </span>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                function fe_filter()
                                {
                                    var value=$('#filter').val();

                                    console.log(value);

                                    if (value!='')
                                    {
                                        $('.panel').css('display','none');
                                        value=value.replace(/\W/g, '');
                                        value=value.toLowerCase();
                                        $("[id*="+value+"]").css('display', '');
                                    }
                                    else
                                    {
                                        $('#list .panel').css('display', '');
                                    }
                                }
                            }
                        </script>
                        <?php echo $message; ?>
