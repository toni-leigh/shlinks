<?php
	$c=0;
	foreach ($conversations as $conv)
	{
		$selected=(0==$c) ? "cpanel_sel" : "";

		?>
		<article id='js_conv_<?=$conv['conversation_id']; ?>' class='js_cpanel <?=$selected; ?>'>
			<?php
				foreach ($conv['users'] as $u)
				{
					if ($u['user_id']!=$user['id'])
					{
						echo "<img src='".str_replace("t300.", "t100.", $u['details']['image'])."' title='".$u['details']['name']."' width='48' height='48'/>";
					}						
				}
				$name=($conv['latest_message']['user_id']==$user['id']) ? "YOU" : $conv['latest_message']['user_name'];

				echo "<div class='latest_message'>".$name.": ".substr($conv['latest_message']['message'], 0,100)." ...</div>";

				echo "<div class='unread_count'>".$conv['count']['unread']."</div>";
			?>
		</article>
		<?php
		$c++;
	}
?>