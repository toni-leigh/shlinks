$('.js_comment_submit').on('click',save_comment);

function save_comment(e)
{	
	e.preventDefault();

	var comment=$('.js_comment').val();
    var node_id=$('.js_node_id').val();

    if (comment.length>0)
    {
        $.ajax({
            type:'GET',
            url: '/comment/save',
            dataType: 'json',
            data:
            { 
                comment:comment,
                node_id:node_id
            },
            success: function (new_html) 
            { 
                $('.comment_panels').append(new_html);
                $('.js_comment').val("");
            }
        });
    }
}