<article id='comment_<?=$comm['comment_id']; ?>' class='comment'>
	<div class='image_column'>
		<a href='/<?=$comm['url']; ?>'>
			<img src='<?=str_replace("t300.", "t100.", $comm['image']); ?>' title='<?=$comm['name']; ?>' width='48' height='48'/>
		</a>
	</div>
	<div class='comment_details'>
		<h3>
			<a href='/<?=$comm['url']; ?>'>
				<?=$comm['name']; ?>
			</a>
			<span class='time'>
				<?php 
					$date=('just now'==$comm['comment_time']) ? 'just now' : date("H:i jS M Y",strtotime($comm['comment_time']));
					echo $date;
				?>
			</span>
		</h3>
		<?=$comm['comment']; ?>
		<?php 
			$comm['flag_id']="comment_".$comm['comment_id'];
			echo $this->load->view("template/node/flag",$comm); 
		?>
	</div>
</article>