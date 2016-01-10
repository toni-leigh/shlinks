<?php

    echo form_open('/article_link/save',array('id'=>'link_form'));
    
    ?>
    <div id='link_form_left'>
        <input type='hidden' name='id' value='<?php echo $link_vals['id']; ?>'/>
        <label for='url'>URL:</label>
        <input title='<?php echo $link_vals['url']; ?>' id='url' type='text' name='url' value='<?php echo $link_vals['url']; ?>'/>
        <label for='title'>title:</label>
        <textarea id='title' name='name'><?php echo $link_vals['title']; ?></textarea>
        <label for='description'>description:</label>
        <textarea id='description' name='short_desc'><?php echo trim($link_vals['description']); ?></textarea>
        <label for='notes'>notes and more details:</label>
        <textarea id='notes' name='node_html'></textarea>
    </div>
    
    <div id='link_form_right' class='noselect'>
        <label for='category'>category:</label>
        <?php if($set_cat_count >= 1) { $cat_counted1 = ' cat_counted'; } ?>
        <div class='cat_count1 cat_count <?php echo $cat_counted1; ?>'>1</div>
        <?php if($set_cat_count >= 2) { $cat_counted2 = ' cat_counted'; } ?>
        <div class='cat_count2 cat_count <?php echo $cat_counted2; ?>'>2</div>
        <?php if($set_cat_count >= 3) { $cat_counted3 = ' cat_counted'; } ?>
        <div class='cat_count3 cat_count <?php echo $cat_counted3; ?>'>MAX</div>
        <ul id='cat_select'>
        <?php
            foreach ($output_order[$user['user_type']] as $o)
            {
                echo $cat_select[$o];
            }
        ?>
        </ul>
    </div>
    
    <input id='linkcat' type='hidden' name='category_id' value='<?php echo $set_cats; ?>'/>
    <input type='hidden' name='node_html' value=''/>
    
    <input id='link_submit' class='submit' type='submit' name='submit' value='save link'/>
    
    </form>
    
    <script type='text/javascript'>
        if (window.focus)
        {
            $('.linkcat').on('click',set_category);
            
            function set_category()
            {
                var id=this.id.replace('lcc','');                
                
                var cats=$('#linkcat').val();
                var count_cats=cats.split(',').length-1;

                /* add the category */
                    if (-1==cats.indexOf(id))
                    {
                        if (count_cats<3)
                        {
                            $('#'+this.id).addClass('linkcat_sel');
                            $('#linkcat').val(cats+id+",");
                        }
                        else
                        {
                            $('.cat_count').css('opacity',0).animate({'opacity':'+=1'},150);
                        }
                    }
                    else
                    {
                        $('#'+this.id).removeClass('linkcat_sel');
                        $('#linkcat').val(cats.replace(id+",",""));
                    }

                /* count and restrict (count again after processing for output as processing adjusts) */
                    cats=$('#linkcat').val();
                    count_cats=cats.split(',').length-1;

                    console.log(cats);
                    console.log(count_cats);

                    for (x=1;x<=3;x++)
                    {
                        if (x<=count_cats)
                        {
                            $('.cat_count'+x).addClass('cat_counted');
                        }
                        else
                        {
                            $('.cat_count'+x).removeClass('cat_counted');
                        }
                    }
            }
        }
    </script>