<?php

	//===================================================
	//	Cipher Pixel Board © All Rights Reserved
	//---------------------------------------------------
	//	CP-Core
	//		by michaelbenner.net
	//---------------------------------------------------
	//	File created: April 04, 2013 
	//===================================================

class pub_home extends controller {
	
	/**
	** Default Controller
	*/
	public $defPage = 'home';
	
	public function main()
	{
		
		if ( !cp::logged()->cur['acp'] )
		{
			$this->loginForm();
		}
		else
		{
			
			$folder = ( cp::$GET['f'] ) ?: 'admin';
			
			/**
			** Does the controller exist? false prevents page from giving PHP errors
			*/
			if ( !cp::$GET['page'] )
				$page = $this->defPage;
			else
			if ( cp::call( 'admin_' . cp::$GET['page'], 'apps/' . $folder . '/admin/' . cp::$GET['page'], true ) )
				$page = cp::$GET['page'];
			else
				$page = $this->defPage;

			/**
			** If controller exists and it returns true, show whole page.
			** If false, a splash may have been called.
			*/
			if ( cp::call( 'admin_' . $page, 'apps/admin/admin/' . $page )->main() )
			{
				
				/**
				** Nav bar
				*/
				$this->loadNavBar();
				
				/**
				** Disp
				*/
				cp::output('norm');
				
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
	
	/**
	** loadRightBar() - loads nav bar to give to norm
	**
	**
	*/
	public function loadNavBar()
	{
		
		$modules = cp::db()->resort( cp::$apps, 'id' );
		
		foreach( $modules as $name => $array )
		{
			cp::display()->read( 'leftbar/' . $name, 'rightbar', true );
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