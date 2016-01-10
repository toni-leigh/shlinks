<?php
    foreach ($connections as $con_type=>$con_list)
    {
        echo "<div class='con_type_panel'>";
        echo "<h2>".$con_type."</h2>";

        foreach ($con_list as $con)
        {
            ?>
            <div class='con_panel'>
                <span class='con_img'>
                    <a href='/<?=$con['node']['url']; ?>'>
                        <img src='<?=str_replace("t300.", "t100.", $con['node']['image']); ?>' width='60' height='60'/>
                    </a>
                </span>
                <span class='con_name'>
                    <a href='/<?=$con['node']['url']; ?>'>
                    <?=$con['node']['name']; ?>
                    </a>
                </span>
                <span class='con_desc'>
                <?=$con['node']['short_desc']; ?>
                </span>
                <div class='con_buttons'>
                <?php 
                    foreach ($con['buttons'] as $button)
                    {
                        if (strlen($button))
                        {
                            echo "<div class='button_wrapper'>";
                            echo $button;
                            echo "</div>";
                        }
                    }
                ?>
                </div>
            </div>
            <?php
        }

        echo "</div>";
    }
?>