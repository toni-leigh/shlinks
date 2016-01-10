<a href='/<?=$panel['url']; ?>'>
    <article id='<?=$filter_id; ?>' class='blog_panel fe_list'>
        <?=node_thumb_src($panel,"ba",460,360); ?>
        <div class='details'>
            <h2><?=$panel['name']; ?></h2>
            <span>written by <strong><?=$panel['user_name']; ?></strong></span>
            <?=$panel['node_html']; ?>
            <?=$this->load->view("template/node/flag",$panel); ?>
        </div>
    </article>
</a>