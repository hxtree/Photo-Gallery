<?php
global $db;

// build custom menu
$menu = array(
	array('title' => 'Browse', 'link' => 'photo-gallery/browse.html',),
	array('title' => 'Tags', 'link' => 'photo-gallery/tags.html'),
	array('title' => 'Directory', 'link' => 'photo-gallery/directory.html'),
//	array('title' => 'Process Files', 'link' => 'photo-gallery/process-files.html'),
	array('title' => 'Contact', 'link' => 'contact.html', 'class' => 'outline'),
);

?><!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?php echo $instance->page['current']['name'];?></title>
		<meta name="description" content="<?php echo $instance->page['current']['meta_description'];?>"/>
		<meta name="keywords" content="<?php echo $instance->page['current']['name'];?>"/>
		<link type="text/plain" rel="author" href="<?php echo SERVER;?>/humans.txt"/>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="theme-color" content="#000">
		<meta name="robots" content="noindex,nofollow">
		<link rel="shortcut icon" href="<?php echo $instance->href('images/favicon/favicon.ico');?>" type="image/x-icon">
		<link rel="icon" href="<?php echo $instance->href('icons/favicon/favicon.ico');?>" type="image/x-icon">
		<link rel="stylesheet" href="<?php echo $instance->href('stylesheets/top.min.css');?>" type="text/css"/>
	</head>
<body>

<div class="window" id="pagetop">
<?php if($instance->page['current']['link']!='home.html'){?>
	<div class="container">
		<div class="brand">
			<a href="<?php echo $instance->href('home.html');?>" title="Home">
				<img src="<?php echo $instance->href('images/common/logo.png');?>" style="height:2em !important;" alt="decorative"/>
			</a>
		</div>
	</div>
<?php } ?>
	<nav class="navbar navbar-inverse navbar-static-top">
		<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
		</div>
		<div id="navbar" class="navbar-collapse collapse window">
			<ul class="nav navbar-nav navbar-left">
<?php
foreach($menu as $key => $value){
	echo '<li'.(($value['link']==$instance->page['current']['link'])?' class="active"':'').'>';
	if(isset($value['submenu'])&&(is_array($value['submenu']))){
		echo '<a href="'.$instance->href($value['link']).'" class="dropdown-toggle" data-toggle="dropdown">'.$value['title'].' <b class="caret"></b></a>';
		echo '<ul class="dropdown-menu multi-level">';
		foreach($value['submenu'] as $key2 => $value2){
			echo '<li'.(($value2['link']==$instance->page['current']['link'])?' class="active"':'').'><a ';
			if(!isset($value2['class'])){
				echo ' class="'.$value2['class'].'"';
			}
			if(isset($value2['target'])){
				echo ' target="'.$value2['target'].'"';
			}
			echo ' href="'.$instance->href($value2['link']).'">'.$value2['title'].'</a></li>';
		}
		echo '</ul>';
	} else {
		echo '<a '.(!isset($value['class'])?'':' class="'.$value['class'].'"');
		echo (!isset($value['target'])?'':' target="'.$value['target'].'"');
		echo ' href="'.$instance->href($value['link']).'">'.$value['title'].'</a>';
	}
	echo '</li>';
}
?>
					<?php	if(isset($_SESSION['user']['uid'])){?>
					<li>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>
						<ul class="dropdown-menu multi-level">
							<li class="text-center"><b><?php echo $_SESSION['user']['name'];?></b></li>
							<li class="divider"></li>
							<form method="post" id="account" enctype="multipart/form-data">
								<input type="hidden" name="command" value="signout"/>
								<li class="text-center"><button class="btn btn-primary" name="command" value="signout" type="submit" form="account">Sign out</button></li>
							</form>
						</ul>
					</li>
					<?php } ?>
				</ul>
				<div class="nav navbar-nav navbar-right">
				<form method="GET" name="top" action="<?php echo $instance->href('photo-gallery/search.html');?>" enctype="multipart/form-data" style="max-width: 220px; display:inline-block;">
						<div class="input-group">
							<input type="text" class="form-control" id="search" name="search[tags]" placeholder="Search">
							<span class="input-group-btn">
								<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
							</span>
						</div>
					</form>
				</div>
			</div>
			<!--/.nav-collapse -->
			</div>
		</nav>
	</div>
	<?php if($instance->page['current']['link']!='home.html'){?>
	<div id="page-title" style="background-image: url('<?php echo $instance->href('images/common/heading-bg.jpg');?>');">
		<h1 class="container" style="background-color: #6A6A6A;">
			<?php echo $instance->page['current']['name'];?>
		</h1>
	</div>
	<?php } ?>
