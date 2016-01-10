<div class='image_panel_list'>
	<?php
		$this->session->set_userdata('image_admin_reload_source','all_images');
		echo form_open("/images/save_all");
	    echo $image_panels;
	?>
    <div class='saves_mains_and_delete'><input class='submit' type='submit' name='submit' value='save all'/></div>
</form>
</div>