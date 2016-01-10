$('.js_message_submit').on('click',save_message);

$('.js_cpanel').on('click',load_conversation);

process_unread();

function process_unread()
{
    $('.js_unread').each(function(){

        var target=$(this);
        var id=target.attr('id')
            .replace('js_','')
            .split('-');

        var uid=id[0];
        var mid=id[1];

        $.ajax({
            type:'GET',
            url: '/message/mark_read',
            dataType: 'json',
            data:
            { 
                uid:uid,
                mid:mid 
            },
            success: function (new_html) 
            { 
                var unread_target=target.find('.unread');
                unread_target.animate({'opacity':'-=1'},15000, function() { unread_target.remove(); });
            }
        });

    });
}

function save_message(e)
{	
	e.preventDefault();

	var cid=$('.js_conversation_id').val();
    var uid=$('.js_user_id').val();
	var message=$('.js_message').val();

    if (comment.length>0)
    {
        $.ajax({
            type:'GET',
            url: '/message/save',
            dataType: 'json',
            data:
            { 
                cid:cid,
                uid:uid,
                message:message 
            },
            success: function (new_html) 
            { 
            	$('.message_panels').append(new_html);
            }
        });
    }
}

function load_conversation()
{   
    var cid=$(this).attr('id').replace('js_conv_','');

    $('.cpanel').removeClass('cpanel_sel');
    $(this).addClass('cpanel_sel');


    $.ajax({
        type:'GET',
        url: '/message/load_conversation',
        dataType: 'json',
        data:
        { 
            cid:cid
        },
        success: function (new_html) 
        { 
            $('.message_panels')
                .css('opacity','0')
                .html(new_html)
                .animate({'opacity':'+=1'},50);
            $('.js_conversation_id').val(cid);
            process_unread();
        }
    });
}