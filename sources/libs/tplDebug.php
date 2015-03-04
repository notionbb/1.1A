<?php

	/**
	** Displays debug information at bottom of website
	** 
	*/

	if ( !$allow )
	{
		die();
	}

?>

<div style="width:85%; margin: 20px auto 20px auto; padding: 8px; background-color: #d2d7E1;">
	<div style="background-color: #fff; padding: 10px">
		<?php if ( Debug::$messages ) { ?><div style="font-size: 24px">Messages</div>
		<pre><?= Debug::messages() ?></pre><?php } ?>
		<div style="font-size: 24px">Request Data</div>
		<pre><?= Debug::request_data() ?></pre>
		<div style="font-size: 24px">File Includes (<?= Debug::included_files_count() ?>)</div>
		<pre><?= stripslashes( Debug::included_files() ) ?></pre>
		<div style="font-size: 24px">SQL Queries (<?= cp::db()->qCount ?>)</div>
		<pre><?= Debug::printHistory() ?></pre>
	</div>
</div>