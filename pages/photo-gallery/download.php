<?php
if($instance->verify()){
	$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');


	// check if version exists	
	$filename = (isset($_GET['version'])?$_GET['version']:NULL);
	$db->bind('filename', $filename);
	$db->bind('file_id',$record_id);
	$bool = $db->single('SELECT 1 FROM `file_version` WHERE `file_id` = :file_id AND `filename` = :filename LIMIT 1;');
	
	if($bool != 1){
		$instance->window('header', true);
		echo '<div class="container background-white">';
		echo '<h2>404 - File or directory not found.</h2>';
		echo '<p>The resource you are looking for might have been removed, had its name changed or is temporarily unavailable.</p>';
		echo '</div>';
		$instance->window('footer', true);
	} else if ((strpos($filename, 'original') !== false)&&(!in_array($_SESSION['user']['uid'],$admin_users))){
		$instance->window('header', true);
		require('pages/unauthorized.php');
		$instance->window('footer', true);
	} else {
		$file = $photo_conf['output_dir'].'/'.$record_id.'/'.$filename;
		$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
		$mime_type = finfo_file($finfo, $file);
		finfo_close($finfo);
		$db->bind('file_id',$record_id);
		$real_filename = $db->single('SELECT `name` FROM `file_adjacency_list` WHERE `file_id` = :file_id');

		header('Content-type: '.$mime_type);
		header("Content-Transfer-Encoding: Binary");
		header('Content-Disposition: attachment; filename="'.$real_filename.'"');
		readfile($file);
		exit();
	}
}
?>
