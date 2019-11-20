<?php
if($instance->verify(true)){
	$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');
	$db->bind('file_id',$record_id);
	$results = $db->query('SELECT `file_id`, DATE_FORMAT(`last_modified`, \'%M %e, %Y\') AS `taken`, DATE_FORMAT(`processed`, \'%M %e, %Y\') AS `uploaded` FROM `file` WHERE `file_id` = :file_id LIMIT 1;');
	foreach($results as $row){
		echo '<p>Use is forbbidden without prior approval.</p>';

		echo '<img src="'.$instance->href('photo-gallery/download.html', $record_id).'&version=300.jpg" alt="File No.'.$row["file_id"].'"/><br/><br/>';

		echo '<a class="btn btn-primary" href="'.$instance->href('contact.html',$record_id).'">Ask to Use <span class="glyphicon glyphicon glyphicon-question-sign"></span></a> ';
		echo '<div class="btn-group">';
		echo '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">View <span class="glyphicon glyphicon-eye-open"></span><span class="sr-only">Toggle Dropdown</span></button>';
		echo '<ul class="dropdown-menu">';
		$db->bind('file_id',$record_id);
		$results2 = $db->query('SELECT `file_id`, `filename` FROM `file_version` WHERE file_id = :file_id');
		foreach($results2 as $row2){
			echo '<li><a href="'.$instance->href('photo-gallery/view.html', $row2['file_id']).'&version='.$row2['filename'].'">'.$row2['filename'].' </a></li>';
		}
		echo '</ul>';
		echo '</div> ';
		echo '<div class="btn-group">';
		echo '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Download <span class="glyphicon glyphicon-download-alt"></span><span class="sr-only">Toggle Dropdown</span></button>';
		echo '<ul class="dropdown-menu">';
		$db->bind('file_id',$record_id);
		$results2 = $db->query('SELECT `file_id`, `filename` FROM `file_version` WHERE file_id = :file_id');
		foreach($results2 as $row2){
			echo '<li><a href="'.$instance->href('photo-gallery/download.html', $row2['file_id']).'&version='.$row2['filename'].'">'.$row2['filename'].' </a></li>';
		}
		echo '</ul>';
		echo '</div> ';
		echo '<a class="btn btn-default" href="'.$instance->href('photo-gallery/edit.html',$record_id).'">Edit <span class="glyphicon glyphicon-pencil"></span></a> ';

		echo '<h4>Overview</h4>';
		echo '<div class="input-group">';
		echo '<span class="input-group-addon">Created/Taken</span>';
		echo '<span class="form-control">'.$row['taken'].'</select>';
		echo '</div>';

		echo '<div class="input-group">';
		echo '<span class="input-group-addon">File Uploaded</span>';
		echo '<span class="form-control">'.$row['uploaded'].'</select>';
		echo '</div>';

		// directory
		$db->bind('file_id',$record_id);
		$directory_id = $db->single('SELECT `directory_id` FROM `file_adjacency_list` WHERE `file_id` = :file_id LIMIT 1');
		$db->bind('directory_id',$directory_id);
		$results = $db->query('SELECT `T2`.`name`, `directory_id`, `file_id` FROM (SELECT @r AS _id, (SELECT @r := `parent_id` FROM `file_adjacency_list` WHERE `directory_id` = _id) AS `parent_id` , @l := @l +1 AS `lvl` FROM (SELECT @r := :directory_id, @l :=0) vars, `file_adjacency_list` WHERE @r <>0) `T1` JOIN `file_adjacency_list` `T2` ON T1._id = `T2`.`directory_id` ORDER BY `T1`.`lvl` DESC LIMIT 100');

		$max = count($results)-1;
		if($max>0){
			echo '<h4>Location</h4>';
			echo '<div class="panel panel-primary">';
			echo '<div class="panel-heading">';
			echo '<a href="'.$instance->href('photo-gallery/directory.html').'#results">share<span class="glyphicon glyphicon-menu-right"></span>';
			$count = 0;
			foreach($results as $key => $value){
				if ($value['file_id']==NULL) {
					echo '<a href="'.$instance->href('photo-gallery/directory.html',$value['directory_id']).'"> '.$value['name'].'</a>';
				} else {
					echo $value['name'];
				}
				if($count<$max){
					echo '<span class="glyphicon glyphicon-menu-right"></span>';
				}
				$count++;
			}
			echo '</div>';
			echo '</div>';
		}
		// tags
		$db->bind('file_id',$record_id);
		$results = $db->query('SELECT `tag` FROM `file_tag` WHERE `file_id` = :file_id;');
		if(count($results)>0){
			echo '<h4>Tags</h4>';
			foreach($results as $row){
				echo '<a href="'.$instance->href('photo-gallery/search.html').'?search[tags]='.$row['tag'].'"><span class="badge">'.$row['tag'].'</span></a> ';
			}
		}
		echo '<hr/>';
		echo '<p class="text-center"><span class="glyphicon glyphicon-copyright-mark"></span> '.date('Y').'. All rights reserved.</p>';
	}
} else {
	$instance->window('header', true);
	echo '<div class="container background-white">';
	echo '<h2>404 - File or directory not found.</h2>';
	echo '<p>Invalid thumbnail selected.</p>';
	echo '</div>';
	$instance->window('footer', true);
}
?>
