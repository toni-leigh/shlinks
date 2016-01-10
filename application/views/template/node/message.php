<?php
	$name=($m['user_id']==$user['id']) ? "YOU" : $m['user_name'];
	$read=(1==$m['message_read']) ? "read" : "unread";
	$js_id="js_".$user['id'].'-'.$m['message_id'];
	if (0==$m['message_read'])
	{
		$unread="<span class='unread'>unread</span>";
		$unread_marker='js_unread';
	}
	else
	{
		$unread="";
		$unread_marker='';
	}
?>
<article id='<?=$js_id; ?>' class='<?=$unread_marker; ?>'>
	<div class='image_column'>
		<a href='/<?=$m['user_url']; ?>'>
			<img src='<?=str_replace("t300.", "t100.", $m['user_image']); ?>' title='<?=$m['user_name']; ?>' width='48' height='48'/>
		</a>
	</div>
	<div class='message_details'>
		<h3>
			<?=$unread; ?>
			<a href='/<?=$m['user_url']; ?>'>
				<?=$name; ?>
			</a>
			<span class='time'>
				<?=date("H:i jS M Y",strtotime($m['message_time'])); ?>
			</span>
		</h3>
		<?=$m['message']; ?>
		<?php 
			$m['flag_id']="message_".$m['message_id'];
			echo $this->load->view("template/node/flag",$m); 
		?>
	</div>
</article>