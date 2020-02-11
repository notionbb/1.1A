<?php

	/*===================================================
	//	NotionBB © All Rights Reserved
	//---------------------------------------------------
	//	NBB-Core
	//		by notionbb.com
	//---------------------------------------------------
	//	File Updated - 2-11-2020
	//=================================================*/

class tasks {
	
	/**
	** Cache Global Settings
	** 
	*/
	public function cacheGlobals()
	{
		
		if ( cp::$POST['confirm'] )
		{
			
			/* Recache Globals */
			cp::cache()->task_cache_globals();
			
			cp::display()->splash( cp::lang('tools', 'splash'), '?page=tools');
			
			return false;
			
		}
		else
		{
			
			cp::cont()->output .= cp::display()->read('div_tool');
			
			/**
			** Show tool information
			*/
			cp::cont()->output .= cp::display()->form(
				array(
					'name'		=> 'confirm',
					'submit'	=> cp::lang('tools', 'run_tool') . ' >>',
					'submitC'	=> 'grey',
				),
				'but'
			);
			
			return true;
			
		}
		
	}
	
	/**
	** Cache Menu Bar
	** 
	*/
	public function cacheMenubar()
	{
		
		if ( cp::$POST['confirm'] )
		{
			
			/* Recache Globals */
			cp::cache()->task_cache_menubar();
			
			cp::display()->splash( cp::lang('tools', 'splash'), '?page=tools');
			
			return false;
			
		}
		else
		{
			
			cp::cont()->output .= cp::display()->read('div_tool');
			
			/**
			** Show tool information
			*/
			cp::cont()->output .= cp::display()->form(
				array(
					'name'		=> 'confirm',
					'submit'	=> cp::lang('tools', 'run_tool') . ' >>',
					'submitC'	=> 'grey',
				),
				'but'
			);
			
			return true;
			
		}
		
	}
	
	/**
	** Fix Members in groups
	** 
	*/
	public function countMemberGroups()
	{
		
		if ( cp::$POST['confirm'] )
		{
			
			$members = cp::db()->exec(
				"SELECT * FROM users"
			);
			
			while ( $m = $members->fetch_assoc() )
			{
				$gCount[ $m['groupId'] ] = $gCount[ $m['groupId'] ] + 1;
			}
			
			$groups = cp::db()->exec(
				"SELECT * FROM `groups`"
			);
			
			while( $g = $groups->fetch_assoc() )
			{
				cp::db()->exec(
					"UPDATE `groups` SET `userCount` = '".$gCount[ $g['id'] ]."' WHERE `id` = '".$g['id']."'"
				);
			}
			
			cp::display()->splash( cp::lang('tools', 'splash'), '?page=tools');
			
			return false;
			
		}
		else
		{
			
			cp::cont()->output .= cp::display()->read('div_tool');
			
			/**
			** Show tool information
			*/
			cp::cont()->output .= cp::display()->form(
				array(
					'name'		=> 'confirm',
					'submit'	=> cp::lang('tools', 'run_tool') . ' >>',
					'submitC'	=> 'grey',
				),
				'but'
			);
			
			return true;
			
		}
		
	}
	
}
	
?>
