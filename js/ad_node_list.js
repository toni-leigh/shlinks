
function filter()
{
    var value=$('#filter').val();
    var f='#adnode_list_panel ';
    if (value!='')
    {
        // hide all            
            $('#adnode_list_panel .onscreen').val(0);
            $(f+'.ad_npanel').css('display','none');
            $(f+'.ad_npanel :input').css('display','none');
            
        // get filter value
            value=value.replace(' ','_');
            value=value.replace("'",'');
            value=value.toLowerCase();
        
        // show those that pass the filter
            $(f+"[id*="+value+"]").css('display','');
            $(f+"[id*="+value+"] :input").attr('display','');
            
        // get the visible ones as target
            $('#adnode_list_panel .ad_npanel').filter(':visible').find('.onscreen').val(1);
            
        if (0==atag_filter_open)
        {
            $('#admin_tags').css('display','none');
        }
        if (0==mass_adjust_open)
        {
            $('#mass_adjust_panel').css('display','none');
        }
    }
    else
    {
        // show all if filter is empty
            $('#adnode_list_panel .onscreen').val(1);
            $(f+'.ad_npanel').css('display','');
            $(f+'.ad_npanel :input').css('display','');
    }
}

function show_filters()
{
    if ('none'==$('#admin_tags').css('display'))
    {
        $('#admin_tags').css('display','');
        $('#admin_tags').animate({'height':'+=214'},100);
        $('#show_atag_filter_panel').html('hide admin tag filters');
        atag_filter_open=1;
    }
    else
    {
        $('#admin_tags').animate({'height':'-=214'},100);
        $('#admin_tags').css('display','none');
        $('#show_atag_filter_panel').html('show admin tag filters');
        atag_filter_open=0;
    }
}

function get_option_array()
{
    return $("option:selected").map(function(){ return this.value }).get().toString().split(',');
}
                
function filter_and()
{
    $('#and_filter').removeClass('ft_unselected').addClass('ft_selected');
    $('#or_filter').removeClass('ft_selected').addClass('ft_unselected');
    
    var options=get_option_array();
    
    // build the string of classes to look at
        var class_string='';
        for (x=0;x<options.length;x++)
        {
            class_string+="."+options[x];
        }
    
    // dot bug hack
        if ('.'==class_string)
        {
            class_string='';
        }
    
    // the show or hide - no selection shows all
        if (class_string!='')
        {
            $('#adnode_list_panel .ad_npanel').css('display','none');
            $('#adnode_list_panel .ad_npanel :input').attr('disabled',true);
            $(class_string).css('display','');
            $(class_string+' :input').attr('disabled',false);
        }
        else
        {
            $('#adnode_list_panel .ad_npanel').css('display','');
            $('#adnode_list_panel .ad_npanel :input').attr('disabled',false);
        }
}

function filter_or()
{
    $('#or_filter').removeClass('ft_unselected').addClass('ft_selected');
    $('#and_filter').removeClass('ft_selected').addClass('ft_unselected');  
    
    var options=get_option_array(); 
    
    // build the string of classes to look at
        var class_string='';
        for (x=0;x<options.length;x++)
        {
            class_string+="."+options[x]+",";
        }
        
        class_string=class_string.substring(0,class_string.length-1);
    
    // dot bug hack
        if ('.'==class_string)
        {
            class_string='';
        }
    
    // the show or hide - no selection shows all
        if (class_string!='')
        {
            $('#adnode_list_panel .ad_npanel').css('display','none');
            $('#adnode_list_panel .ad_npanel :input').attr('disabled',true);
            $(class_string).css('display','');
            $(class_string+' :input').attr('disabled',false);
        }
        else
        {
            $('#adnode_list_panel .ad_npanel').css('display','');
            $('#adnode_list_panel .ad_npanel :input').attr('disabled',false);
        }
}
    
function choice_response()
{
    if ($('#and_filter').hasClass('ft_selected'))
    {
        filter_and();
    }
    else
    {
        filter_or();
    }
}

function show_deletes()   
{
     $('.ad_ndelete').css('visibility','visible');
     $('#show_deletes').html('hide delete buttons').attr('onclick','').bind('click',function(){ hide_deletes(); });
}

function hide_deletes()   
{
     $('.ad_ndelete').css('visibility','hidden');
     $('#show_deletes').html('show delete buttons').attr('onclick','').bind('click',function(){ show_deletes(); });
}

function set_views()
{
    if ($('#master_view_check').is(':checked'))
    {
        $('.ad_nvisible_check').attr('checked',true);
    }
    else
    {
        $('.ad_nvisible_check').attr('checked',false);
    }
    set_checked('');
}

function set_checked(id)
{                    
    if (''==id)
    {
        var t=$('#adnode_list_panel .ad_npanel').filter(':visible');
        if ($('#master_view_check').is(':checked'))
        {
            t.removeClass('ad_ninvisible').addClass('ad_nvisible');
            console.log(t.find('.ad_nvisible_check').is(':checked'));
            t.find('.visnum').val(1);
            t.find('.ad_nvisible_check').attr('checked',true);
        }
        else
        {
            t.removeClass('ad_nvisible').addClass('ad_ninvisible');
            console.log(t.find('.ad_nvisible_check').is(':checked'));
            t.find('.visnum').val(0);
            t.find('.ad_nvisible_check').attr('checked',false);
        }
    }
    else
    {                    
        if ($('#'+id+'_check').is(':checked'))
        {
            $('#'+id).removeClass('ad_ninvisible').addClass('ad_nvisible');
            $('#'+id+'visnum').val(1);
        }
        else
        {
            $('#'+id).removeClass('ad_nvisible').addClass('ad_ninvisible');
            $('#'+id+'visnum').val(0);
        }
    }
}

function force_focus()
{
    $('#perc_pound_perc').attr('checked',false);
    $('#perc_pound_pound').attr('checked',true);
    $('#perc_pound_perc').attr('disabled',true);
}

function force_unfocus()
{
    $('#perc_pound_perc').attr('disabled',false);
}

function show_mass_adjust()
{
    if ($('#adjust_check').is(':checked'))
    {
        $('#mass_adjust_panel').css('display','');
        $('#mass_adjust_panel').animate({'width':'+=500'},150);
        mass_adjust_open=1;
    }
    else
    {
        $('#mass_adjust_panel').animate({'width':'-=500'},150);
        $('#mass_adjust_panel').css('display','none');
        mass_adjust_open=0;
    }
}