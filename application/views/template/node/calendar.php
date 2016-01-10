<div class='node_calendar'>

	<?=$meta_data['name'] ?>

	<?php foreach($back_links as $back_to=>$link): ?>

		<a href='<?=$link ?>'>back to <?=$back_to ?></a>

	<?php endforeach; ?>

	<a href='<?=$previous['link'] ?>'><?=$previous['label'] ?></a>

	<a href='<?=$next['link'] ?>'><?=$next['label'] ?></a>

	<?=$this->load->view("template/node/".$granularity,$cells); ?>

</div>