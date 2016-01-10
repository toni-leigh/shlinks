/*
 * @package     Template
 * @subpackage  Template Libraries
 * @category    Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

/*
    FRIEND SHIP FUNCTIONS - OFTEN AVAILABLE ON MANY PAGES
*/
    $('.js_connect').on('click',function() { make_connection($(this)); });

    function make_connection(t)
    {
        var id=t.attr('id').split('-');

        var connect_type=id[1];
        var user_id=id[2];
        var node_id=id[3];

        //alert('connect_type:'+connect_type+'user_id:'+user_id+'node_id:'+node_id);

        $.ajax({
            type:'GET',
            url: '/connection/'+connect_type,
            dataType: 'json',
            data:
            {
                user_id:user_id,
                node_id:node_id
            },
            success: function (new_html)
            {
                t.parent().html(new_html);
                $('.js_connect').on('click',function() { make_connection($(this)); });
            }
        });
    }


    $('.vote_up').on('click',upbind);
    $('.vote_down').on('click',downbind);

    function upbind()
    {
        var id=this.id.replace('up','');

        if ($(this).hasClass('voted_up'))
        {
            vote_down(id);
        }
        else
        {
            vote_up(id);
        }
    }
    function downbind()
    {
        var id=this.id.replace('down','');

        if ($(this).hasClass('voted_down'))
        {
            vote_up(id);
        }
        else
        {
            vote_down(id);
        }
    }

    function vote_down(id)
    {
        $.ajax({
            type:'GET',
            url: '/voting/down',
            dataType: 'json',
            data: { nid:id },
            success: function (new_html)
            {
                $(".votes"+new_html[0]).html(new_html[1]);
                $('.vote_up').on('click',upbind);
                $('.vote_down').on('click',downbind);
            }
        });
    }

    function vote_up(id)
    {
        $.ajax({
            type:'GET',
            url: '/voting/up',
            dataType: 'json',
            data: { nid:id },
            success: function (new_html)
            {
                $(".votes"+new_html[0]).html(new_html[1]);
                $('.vote_up').on('click',upbind);
                $('.vote_down').on('click',downbind);
            }
        });
    }

// flag bind
    function flag_this()
    {
        var flag_span=$(this);

        var flag_key=flag_span.attr('id');

        $.ajax({
            type:'GET',
            url: '/flag/save',
            dataType: 'json',
            data: { flag_key:flag_key },
            success: function (new_html)
            {
                flag_span.html('flagged');
            }
        });
    }
    $('.flag').on('click',flag_this);

// updates the panel text
    function update_panel_text()
    {
        var nvar_id=$('#nvar_selector').val();
        $.ajax({
            type: 'GET',
            url: '/variation/ajax_get_panel_text',
            dataType: 'json',
            data: { nvar_id:nvar_id },
            success: function (new_html) { $('#add_panel_text').html(new_html); }
        });
    }

// checks the quantity against the stock in the database
    function check_quantity()
    {
        var nvar_id=$('#nvar_selector').val();
        var add_quantity=$('#add_quantity').val();
        if (isNaN(add_quantity))
        {
            $('#add_quantity').addClass('bad_number');
        }
        else
        {
            $('#add_quantity').removeClass('bad_number');
            $.ajax({
                type: 'GET',
                url: '/variation/ajax_check_stock',
                dataType: 'json',
                data: { nvar_id:nvar_id , add_quantity:add_quantity },
                success: function (new_html)
                {
                    $('#not_enough_stock').html(new_html);
                    if (new_html.length>0)
                    {
                        $('#not_enough_stock').css('display','block');
                    }
                    else
                    {
                        $('#not_enough_stock').css('display','none');
                    }
                }
            });
        }
    }

// saves the add in the basket
    function basket_add()
    {
        var nvar_id=$('#nvar_selector').val();
        var add_quantity=$('#add_quantity').val();
        $.ajax({
            type: 'GET',
            url: '/basket/add',
            dataType: 'json',
            data: { nvar_id:nvar_id , add_quantity:add_quantity },
            success: function (new_html)
            {
                $('#header_basket').html(new_html[0]);
                refresh_message(new_html[2]);
            }
        });
    }

function refresh_message(message)
{
    $('#mback').html('').css('opacity','0').html(message).animate({'opacity':'+=1'},200);
}

/*
    FORMAT AND ERROR CHECK
    - each function takes an id to work on and a colour to revert to
*/
function set_error(id)
{
    $(id).css({'background-color':error_bg});
}
function revert_error(id)
{
    $(id).css({'background-color':field_bg});
}

// variation - includes some symbols
    function check_vartype(id)
    {
        var var_value=$(id).val();
        if( /^[\'\"\`a-z0-9.\\/ ]+$/i.test( var_value ) )
        {
            revert_error(id);
            return 1;
        }
        else
        {
            set_error(id);
            return 0;
        }
    }

// alpha num only
    function check_alpha_num(id)
    {
        var var_value=$(id).val();
        if( /^[a-z0-9. ]+$/i.test( var_value ) )
        {
            revert_error(id);
            return 0;
        }
        else
        {
            set_error(id);
            return 1;
        }
    }

// numeric only
    function check_numeric(id)
    {
        if (isNaN($(id).val()) ||
            ''==$(id).val())
        {
            set_error(id);
            return 1;
        }
        else
        {
            revert_error(id);
            return 0;
        }
    }

// empty string
    function check_empty(id)
    {
        if (''==$(id).val())
        {
            set_error(id);
            return 1;
        }
        else
        {
            revert_error(id);
            return 0;
        }
    }

/*
 two functions to set the changes made variable which is used to stop form submission
*/
function set_changes()
{
    changes_made=1;
}

function unset_changes()
{
    changes_made=0;
}

/*
 provides char countdown
*/
function char_count(id,max)
{
    id=ph(id);

    var left=max-$(id).val().length;

    if (0==left)
    {
        $(id+'_count').html(" !! "+left+" !!").removeClass('chars_ok').addClass('chars_low');
    }
    else if (left<=10 &&
             left>0)
    {
        $(id+'_count').html(left).removeClass('chars_ok').addClass('chars_low');
    }
    else
    {
        $(id+'_count').html(left).removeClass('chars_low').addClass('chars_ok');
    }

}

/*
 prepends a # to an id
*/
function ph(id)
{
    if ('#'==id.charAt(0))
    {
        return id;
    }
    else
    {
        return '#'+id;
    }
}

/*
 load different image sizes into src
 id - the element to operate on
 img_type - thumbnail or scaled image (values 't' or 's')
 size - the target size
 new_size - the replacement size
*/
function responsive_src(id,img_type,size,new_size)
{
    id=ph(id);

    src=$(id).attr('src');

    $(id).attr('src',src.replace(img_type+size,img_type+new_size));
}

function open_height(id)
{
    div=$(ph(id+'_panel'));

    div.css('display','block');

    div.animate({'opacity':'+=1'},200);

    $(ph(id+'_heading')).attr('onclick','close_height(\"'+id+'\")');
    $(ph(id+'_show')).attr('onclick','close_height(\"'+id+'\")').removeClass('panel_open').addClass('panel_close');
    $(ph(id+'_panel')).removeClass('panel_closed');
}

function close_height(id)
{
    div=$(ph(id+'_panel'));

    var height='-='+$(id).height()+'px';

    $(id).animate({'height':height},200);

    div.css('display','none').css('opacity','0');

    $(ph(id+'_heading')).attr('onclick','open_height(\"'+id+'\")');
    $(ph(id+'_show')).attr('onclick','open_height(\"'+id+'\")').removeClass('panel_close').addClass('panel_open');
    $(ph(id+'_panel')).addClass('panel_closed');
}


/*
  functions for the html slider
*/
/*
  show and hide large html slider
*/
function hide_slider(hide_type,id)
{
    id=ph(id);
    if ('fade'==hide_type)
    {
        $(id).animate({'opacity':'-=1'},400);
    }
    else
    {
        var off_set=$(id).position();
        var move_amount=1000+off_set.top;
        $(id).animate({'top':'-='+move_amount+'px'},200);
    }
}

function show_slider(show_type,id)
{
    id=ph(id);

    $(id).css('display','block');

    if ('fade'==show_type)
    {
        $(id).animate({'opacity':'+=1'},400);
    }
    else
    {
        var top=$(id).offset();
        if (top.top<0)
        {
            if (isNaN(window.pageYOffset))
            {
                var off_set=document.documentElement.scrollTop;
            }
            else
            {
                var off_set=window.pageYOffset;
            }
            var move_amount=parseInt(off_set)+Math.abs(parseInt($(id).css('top')))+60;
            $(id).animate({top:'+='+move_amount},400);
        }
    }
}

//slide_to,slide_distance,id,slider_name,full_width
function slide_to(id,slide_to_image)
{
    id=ph(id);
    var lp=$(id+' .list_panel');

    // retrieve current panelset then set to the one we are sliding too
        var this_panelset=parseInt($(id+'_tps').html());
        $(id+'_tps').html(slide_to_image);

    if (!lp.is(':animated'))
    {
        var current=parseInt(lp.css('left'));

        var distance=Math.abs(slide_to_image*parseInt($(id+'_slide_distance').html())+current);

        // dont do anything if the same button is clicked
        if (this_panelset!=slide_to_image)
        {
            // highlight
                $(id+'_sqb'+this_panelset).removeClass('sqb_sel');
                $(id+'_sqb'+slide_to_image).addClass('sqb_sel');

            // slider buttons are updated
                set_slider_buttons(id,slide_to_image);

            // animate
                // set the speed to vary based on the number to slide
                    var slide_count=Math.abs(slide_to_image-this_panelset);

                    if (1==slide_count)
                    {
                        var speed=400;
                    }
                    else if (slide_count<=4 &&
                             slide_count>=2)
                    {
                        var speed=slide_count*280;
                    }
                    else
                    {
                        var speed=slide_count*220;
                    }

                // and animate
                    if (this_panelset<slide_to_image)
                    {
                        lp.animate({'left':'-='+distance},speed);
                    }
                    else
                    {
                        lp.animate({'left':'+='+distance},speed);
                    }
        }

        $(id+'_count').html(slide_to_image+1);
    }
}

function set_slider_buttons(id,new_panelset)
{
    if (0==new_panelset)
    {
        new_html="<span class='sprite slld slb'></span>";
    }
    else
    {
        new_html="<span class='sprite sll slb' onclick='slide_to(\""+id+"\","+(new_panelset-1)+","+new_panelset+")'>go right one panel</span>";
    }

    if (new_panelset==parseInt($(id+'_count_of').html())-1)
    {
        new_html+="<span class='sprite slrd slb'></span>";
    }
    else
    {
        new_html+="<span class='sprite slr slb' onclick='slide_to(\""+id+"\","+(new_panelset+1)+","+new_panelset+")'>go left one panel</span>";
    }

    // update
        $(id+'_slbs').html(new_html);
}

/*
    ENGAGE FUNCTIONS
*/


function check_username()
{
    var user_name=$("#user_name").val();
    $.ajax({
        type:'GET',
        url: '/engage/check_username',
        dataType: 'json',
        data: { user_name : user_name },
        success: function (new_html) { $("#check_username").html(new_html); }
    });
}
function check_email()
{
    var email=$("#register_email").val();
    $.ajax({
        type:'GET',
        url: '/engage/check_email',
        dataType: 'json',
        data: { email : email },
        success: function (new_html) { $("#check_email").html(new_html); }
    });
}

function load_inplace(html,user_id)
{
    // set up the edit form html
    var edit_form="";
    edit_form+="<textarea id='node_html_editor' name='node_html' class='form_field'>";
    edit_form+=html;
    edit_form+="</textarea>";
    edit_form+="<div class='full_width'><input class='submit' type='submit' name='submit' value='save edits'/></div>";

    // show the form to the user
    $('.node_html_display').html(edit_form);

    var width=$('.node_html_display').css('width');

    tinyMCE.init({
        // General options
        width: width,
        mode : "textareas",
        theme : "advanced",
        plugins : "autolink,lists,spellchecker,advimage,advlink,inlinepopups,contextmenu,paste,directionality,nonbreaking",
        convert_urls : false,
        body_class : 'tiny_mince_body',
        content_css : '/style/tiny_mce.css',

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste,pastetext,pasteword,spellchecker,bullist,numlist,|,undo,redo,|,link,unlink",
        theme_advanced_buttons2 : '',
        theme_advanced_buttons3 : '',
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Drop lists for link/image/media/template dialogs
        external_link_list_url : '/user_files/'+user_id+'_link_list.js',
        external_image_list_url : '/user_files/'+user_id+'_image_list.js',
        template_external_list_url : "js/template_list.js",
        media_external_list_url : "js/media_list.js",
        elements : 'node_html_editor',

        // Replace values for the template plugin
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }
    });

    document.getElementById('node_html_edit_button').style.display='none';
}

function getScrollOffsets(w) {
// Use the specified window or the current window if no argument
w = w || window;
// This works for all browsers except IE versions 8 and before
if (w.pageXOffset != null) return {x: w.pageXOffset, y:w.pageYOffset};
// For IE (or any browser) in Standards mode
var d = w.document;
if (document.compatMode == "CSS1Compat")
return {x:d.documentElement.scrollLeft, y:d.documentElement.scrollTop};
// For browsers in Quirks mode
return { x: d.body.scrollLeft, y: d.body.scrollTop }; }
