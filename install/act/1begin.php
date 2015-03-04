<?php

	if ( IS_CP !== true ) die('exit');

?>

<div class="infodiv" style="font-weight: bold; font-size: 20px; text-align: center;">
	<?= lang('beg_ins') ?>
</div>
<div class="infodiv">
	<?= lang('need') ?>: <br />
	<ul>
		<li><?= lang('files') ?></li>
		<li><?= lang('sql') ?></li>
	</ul>
	<?= lang('ready') ?>
</div>

<div style="float: right; margin: 5px 50px 5px 20px; font-size: 18px;">
	<a style="color: #4B7CA1" href="<?= $url_next_act ?>"><?= lang('next_step') ?></a>
</div>