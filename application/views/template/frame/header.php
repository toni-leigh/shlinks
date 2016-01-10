                    <?php if (1==$admin_page): ?>
                    	<div style='height:91px;'>
                    <?php else: ?>
                    	<div style='height:46px;'>
                	<?php endif; ?>
                    </div>
                    <div class='htop'>
                        <div class='frame_centre'>
	                        <?=$login_form ?>
	                        <?=$welcome_message ?>
	                        <a class='action back_stage_toggle' href='<?=$backstage_toggle['link'] ?>'><?=$backstage_toggle['label'] ?></a>
	                        <?=$search_form ?>
        					<?=$logout_form ?>
	                    </div>
	                    <?=$this->load->view("template/frame/admin_nav") ?>
                    </div>

                    <? 
	                    if (0==$admin_page)
	                    {
	                    	?>
		                    <header>
		                        <div class='frame_centre'>
			                    	<img src='/img/logo.png'/>
			                    </div>
		                    </header> 
		                    <nav>  
		                        <div class='frame_centre'>
		                        	<?=$nav ?>  
		                        </div>   
		                    </nav>
		                    <?php
	                    }
	                ?>