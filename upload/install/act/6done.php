<?php

	if ( IS_CP !== true ) die('exit');

	/* Lock installer */
	cp::call('cache')->put( 'conf', array( 'lock_installer'=>true ), false, ROOT .'/conf.php' );

?>


<div class="infodiv" style="font-weight: bold; font-size: 20px; text-align: center;">
	6. <?= lang('s_tit') ?>
</div>

<div class="infodiv">
	<?= lang('s_done') ?>
</div>

<div class="infodiv">
	<p><?= lang('s_forum') ?>
	<p><?= lang('s_admin') ?>
	<p><?= lang('s_cp') ?>
</div>
