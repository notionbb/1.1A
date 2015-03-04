<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class act_home {
	
	public function main()
	{
		
		if ( !cp::logged()->cur['acp'] )
		{
			$this->loginForm();
		}
		else
		{
			
			$settings = cp::DB()->fetch_dep( array(
				'table' => 'groupsettings',
				'key'	=> 'name',
			) );
			
			$settings['p_start'] = array(
				'name'			=> 'p_start',
				'title' 		=> 'Permission Masks that can edit',
				'desc'			=> 'Set what permission masks may edit this blog',
				'valueType'		=> 'list',
				'valueFunction'	=> 'getPerms',
			);
			
			$settings['p_g_start'] = array(
				'name'			=> 'p_g_start',
				'title' 		=> 'Groups that can edit',
				'desc'			=> 'Set what groups may edit this blog',
				'valueType'		=> 'list',
				'valueFunction'	=> 'getGroups',
			);
			
			$values = cp::DB()->get( 'groups', '1' );
			
			if ( cp::$POST['submit'] )
			{
				
				// Values only required here if slug need to be edited
				$new = cp::call('dtools')->processSettings( $settings, $values );
				
				print_r( $new );
				
			}
			else
			{
				
				echo '<form method="post">'.cp::call('dtools')->settingToFields( $settings, $values ).'<input class="submit" type="submit" name="submit" value="Update Group"></form>';
			
			}
				
		}
		
	}
	
	public function loginForm()
	{
		
		if ( cp::$POST['email'] 
		AND
		( !cp::logged()->loginError( cp::$POST['email'], cp::$POST['pass'] ) ) )
		{
			cp::display()->splash('Login successful', '');
		}				
		else
		{				
			cp::output('adminLoginPage');	
		}
		
	}
	
	public function temp()
	{
		
		return array (
		
				array(
					'name' 		=> 'name',
					'title' 	=> 'Blog Name',
					'desc'		=> 'Change the blog\'s name',
					'valueType'	=> 'field',
				),
				
				array(
					'name' 		=> 'slug',
					'title' 	=> 'Slug',
					'desc'		=> 'If left empty <i>(recommended)</i>, an automatic slug will be created; else you can enter it yourself',
					'valueType'	=> 'field',
				),
				
				array(
					'name' 		=> 'desc',
					'title' 	=> 'Blog Description',
					'desc'		=> 'Enter a description for your blog',
					'valueType'	=> 'area',
				),
				
				array(
					'name' 		=> 'public',
					'title' 	=> 'Is the blog public',
					'desc'		=> 'Set whether the blog is public',
					'valueType'	=> 'onoff',
				),
				
				array(
					'name' 			=> 'permArray',
					'title' 		=> 'Permission Masks that can edit',
					'desc'			=> 'Set what permission masks may edit this blog',
					'valueType'		=> 'list',
					'valueFunction'	=> 'getPerms',
					'valueOW'		=> array_filter(explode(',', $permArray['p_start'] )),
				),
				
				array(
					'name' 			=> 'groupArray',
					'title' 		=> 'Groups that can edit',
					'desc'			=> 'Set what permission masks may edit this blog',
					'valueType'		=> 'list',
					'valueFunction'	=> 'getGroups',
					'valueOW'		=> array_filter(explode(',', $permArray['p_g_start'] )),
				),
				
				array(
					'name' 			=> 'memberArray',
					'title' 		=> 'Members that can edit',
					'desc'			=> 'Either;<br /> - enter each ID in the top text box seperated by a comma<br /> - enter the member\'s names seperated by a comma using the bottom text box (BETA)',
					'valueType'		=> 'sugbox',
					'valueFunction'	=> 'users',
					'valueOptions'	=> array( 'table' => 'users', 'field' => 'displayName', 'save' => 'id' ),
					'valueOW'		=> array_filter(explode(',', $permArray['p_u_start'] )),
					'rawData'		=> TRUE
				),
				array(
					'name' 			=> 'permArray',
					'title' 		=> 'Set user permissions',
					'valueType'		=> 'custom',
					'valueFunction' => 'permTable',
					'extra'			=> array('post'),
				),
				
			);
			
		}
	
}

?>