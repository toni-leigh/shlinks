
<?=$this->load->view("template/node/details_heading"); ?>
<div id='comments' class='node_panel comments'>
    <div class='inner_content'>
    	<?=$this->load->view("template/node/comments"); ?>
    </div>
    <div class='social_images'>
        <?=$this->load->view("template/node/connect_buttons"); ?>
        <?=$this->load->view("template/node/add_to_basket"); ?>
        <?=$this->load->view("template/node/gallery_small"); ?>
        <?=$this->load->view("template/node/social_media"); ?>
    </div>
</div>
<?=$this->load->view("template/node/recommended_products"); ?>
<?=$this->load->view("template/node/map"); ?>