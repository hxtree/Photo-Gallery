<?php

session_start();

require('lib/log.class.php');
require('lib/db.class.php');
require('lib/instance.class.php');
require('lib/alert.class.php');

// get user config
$user_conf = parse_ini_file('resources/config/users.conf');
$allowed_groups = array_map('trim', explode(',', $user_conf['allowed_groups']));
$allowed_users = array_map('trim', explode(',', $user_conf['allowed_users']));
$admin_users = array_map('trim', explode(',', $user_conf['admin_users']));

// sign out if post
if($_POST['command']=='signout'){
	unset($_SESSION['user']['uid']);
}

// sign in if post
if(isset($_POST['uid'])){
	// injection check
	if (preg_match('/[^A-Za-z0-9.-]/', $_POST['uid'])){
		$alert->add('warning','Invalid username submitted'); 
	} else {
		$ldapconn = ldap_connect('ldaps://ldap.example.com');
		if (!$ldapconn) {
			$alert->add('warning', 'Could not connect to autcentication server.');
		} else {
			// try authenticating user
			$ldaprdn = 'uid='.$_POST['uid'].',ou=people,dc=example,dc=com';
			ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
			$ldapbind = @ldap_bind($ldapconn, $ldaprdn, $_POST['pass']);

			if (!$ldapbind) {
				$alert->add('warning', 'Invalid username or password.');
			} else {
				// allow only valid users who are staff, faculty, or gc-faculty
				$filter='(uid='.$_POST['uid'].')';
				$results = ldap_search($ldapconn,'dc=example,dc=com',$filter);
				ldap_sort($ldapconn,$results,'sn');
				$info = ldap_get_entries($ldapconn, $results);

				for ($i=0; $i<$info['count']; $i++) {
					if($info['count'] > 1) {break;}

					// store important variables
					$_SESSION['user']['uid'] = $info[$i]['uid'][0];
					$_SESSION['user']['name'] = $info[$i]['cn'][0];
					$_SESSION['user']['status'] = $info[$i]['status'];
					$_SESSION['user']['mail'] = $info[$i]['mail'][0];
					$_SESSION['user']['telephonenumber'] = $info[$i]['telephonenumber'][0];
					$_SESSION['user']['title'] = $info[$i]['title'][0];
				}
			}
		}
		@ldap_close($ldapconn); // close ldap connection
	}
}

switch ($instance->page['current']['state']){
	case 'active':
		$instance->window('header');
		require('pages/'.$instance->page['current']['file']);
		$instance->window('footer');
		break;
	case 'protected':
		$instance->window('header');
		if(!isset($_SESSION['user']['uid'])){
			require('pages/sign-in.php');
		} else if (count(array_intersect($_SESSION['user']['status'],$allowed_groups))>0){
			require('pages/'.$instance->page['current']['file']);
		} else if(in_array($_SESSION['user']['uid'], $allowed_users)){
			require('pages/'.$instance->page['current']['file']);			
		} else {
			require('pages/unauthorized.php');
		}
		$instance->window('footer');
		break;
	default:
		$instance->page['current']['name'] = 'Page Not Found';
		$instance->page['current']['file'] = 'page-not-found.php';
		$instance->page['current']['link'] = 'page-not-found.html';
		$instance->page['current']['page_description'] = 'Page not found';
		$instance->page['current']['standalone'] = false;
		$instance->page['current']['state'] = 'active';
		$instance->page['breadcrumbs'] = array(
			array('id' => 1, 'link' => 'home.html', 'name'=>'Home'), 
			array('id'=>NULL, 'link' => 'page-not-found.html', 'name'=>'Page Not Found')
		);
		$instance->window('header');
		include('pages/'.$instance->page['current']['file']);
		$instance->window('footer');
		break;
}
?>
