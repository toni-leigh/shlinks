<ul id='link_cats'>
    <?php
        foreach ($output_order[$user['user_type']] as $o)
        {
            echo $categories[$o];
        }
    ?>
</ul>

<script type='text/javascript'>
    if (window.focus)
    {
        $('#link_cats a.open').on('click',open);
        $('#link_cats a.close').on('click',close);
        
        function open()
        {
            var id=this.id.replace('c','subs');
            var ma=$('#'+id)[0].scrollHeight-90;
            
            $('#'+id).animate({'height':'+='+ma,'opacity':'+=0.7'},200);
            $('#'+this.id).off('click')
                          .on('click',close)
                          .removeClass('open')
                          .addClass('close');
        }
        
        function close()
        {
            var id=this.id.replace('c','subs');
            var ma=$('#'+id).height()-68;
            
            $('#'+id).animate({'height':'-='+ma,'opacity':'-=0.7'},200);
            
            $('#'+this.id).off('click')
                          .on('click',open)
                          .removeClass('close')
                          .addClass('open');
        }
    }
</script>