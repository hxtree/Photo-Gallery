<?php

function print_directory_list($elements, $parent_id = 0,$directory_id, $parent_str = 'Share') {
	foreach ($elements as $element) {
		  if ($element['parent_id'] == $parent_id) {
				$children = print_directory_list($elements, $element['id'],$directory_id,$parent_str.' > '.$element['name']);
			if ($children) {
				$element['children'] = $children;
			} else {
				echo '<option value="'.$element['id'].'"'.(($directory_id==$element['id'])?' selected':'').'>'.$parent_str.' > '.$element['name'].'</option>';
			}
		}
	}
}

if(!in_array($_SESSION['user']['uid'],$admin_users)){
	require('pages/unauthorized.php');
} else if ($instance->verify(true)){
	include('lib/build_tree.function.php');
	$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');

	echo '<div class="container background-white">';

	// process post
	switch($_POST['command']){
		case 'folder-move':
			$db->bind('file_id', $record_id);
			$db->bind('folder_id', $_POST['folder']);
			$db->query('UPDATE `file_adjacency_list` SET `parent_id` = :folder_id WHERE `file_id` = :file_id');
			$alert->add('success','File successfully moved');
			break;
		case 'tag-add':
			if(strlen($_POST['tag'])>2){
				$db->bind('file_id', $record_id);
				$db->bind('tag', $_POST['tag']);
				$db->query('INSERT INTO `file_tag` (`file_id`, `tag`) VALUES (:file_id,:tag);');
				$alert->add('success','Tags successfully added. It may take a few minutes for this tag to be searchable, but don\'t worry it will be');
			} else {
				$alert->add('warning','Tags must be at least two characters long');
			}
			break;
		case 'tag-remove':
			$db->bind('file_id', $record_id);
			$db->bind('tag_id', $_POST['tag']);
			$db->query('DELETE FROM `file_tag` WHERE `file_id` = :file_id AND `tag_id` = :tag_id LIMIT 1;');
			$alert->add('success','Tags successfully removed');
			break;
		case 'version-remove':
			// file name of version do not trust POST
			$db->bind('file_id',$record_id);
			$db->bind('version_id', $_POST['version']);
			$filename = $db->single('SELECT `filename` FROM `file_version` WHERE `version_id` = :version_id AND `file_id` = :file_id LIMIT 1;');
			$file = $photo_conf['output_dir'].'/'.$record_id.'/'.$filename;
			if(file_exists($file)){
				// remove version from database
				$db->bind('file_id',$record_id);
				$db->bind('version_id', $_POST['version']);
				$db->query('DELETE FROM `file_version` WHERE `version_id` = :version_id AND `file_id` = :file_id LIMIT 1;');

				// remove version file
				unlink($file);

				// check if any other versions exist if not delete all info
				$db->bind('file_id',$record_id);
				if($db->single('SELECT 1 FROM `file_version` WHERE `file_id` = :file_id LIMIT 1;')!=1){
					// delete from `file_tags`
					$db->bind('file_id', $record_id);
					$db->query('DELETE FROM `file_tag` WHERE `file_id` = :file_id;');

					// delete from `file_adjacency_list`
					$db->bind('file_id', $record_id);
					$db->query('DELETE FROM `file_adjacency_list` WHERE `file_id` = :file_id LIMIT 1;');

					// delete from `file`
					$db->bind('file_id', $record_id);
					$db->query('DELETE FROM `file` WHERE `file_id` = :file_id LIMIT 1;');

					// delete folder contain file
					if (is_dir($photo_conf['output_dir'].'/'.$record_id)) {
						rmdir($photo_conf['output_dir'].'/'.$record_id);
					}
					$alert->add('warning','File permanently removed');
				}
			}
			break;
	}

	$alert->get();

	$db->bind('file_id',$record_id);
	$row = $db->row('SELECT `file_id`, DATE_FORMAT(`last_modified`, \'%M %e, %Y\') AS `taken`, DATE_FORMAT(`processed`, \'%M %e, %Y\') AS `uploaded` FROM `file` WHERE `file_id` = :file_id LIMIT 1;');
	if($row==NULL) {
		echo '<h2>404 - File or directory not found.</h2>';
		echo '<p>Invalid edit file selected.</p>';
	}	else {
		echo '<div class="row">';
		echo '<div class="col-md-8">';
		echo '<h2>File Location</h2>';
		// directory
		$db->bind('file_id',$record_id);
		$folder = $db->row('SELECT `directory_id`,`parent_id` FROM `file_adjacency_list` WHERE `file_id` = :file_id LIMIT 1');
		$db->bind('directory_id',$folder['directory_id']);
		$results2 = $db->query('SELECT `T2`.`name`, `directory_id`, `file_id` FROM (SELECT @r AS _id, (SELECT @r := `parent_id` FROM `file_adjacency_list` WHERE `directory_id` = _id) AS `parent_id` , @l := @l +1 AS `lvl` FROM (SELECT @r := :directory_id, @l :=0) vars, `file_adjacency_list` WHERE @r <>0) `T1` JOIN `file_adjacency_list` `T2` ON T1._id = `T2`.`directory_id` ORDER BY `T1`.`lvl` DESC LIMIT 100');
		echo '<div class="panel panel-primary"><div class="panel-heading">';
		$max = count($results2)-1;
		if($max>0){
			echo '<a href="'.$instance->href('photo-gallery/directory.html').'#results">share</a><span class="glyphicon glyphicon-menu-right"></span>';
			$count = 0;
			foreach($results2 as $key => $value){
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
		}
		echo '</div>';
		echo '</div>';

		// move
		$results2 = $db->query('SELECT `directory_id` AS `id`,`name`, `parent_id` FROM `file_adjacency_list` WHERE `file_id` IS NULL ORDER BY `parent_id`');
		echo '<form method="post" enctype="multipart/form-data">';
		echo '<div class="input-group">';
		echo '<span class="input-group-addon" title="Link">Move To:</span>';
		echo '<select class="form-control" name="folder">';
		print_directory_list($results2,0,$folder['parent_id']);
		echo '</select>';
		echo '<span class="input-group-btn" title="Move"><button name="command" value="folder-move" class="btn btn-success" type="submit"><span class="glyphicon glyphicon-ok"></span></button></span>';
		echo '</div>';
		echo '</form>';

		// tags
		echo '<h2>File Tags</h2>';
		// add new tags
		echo '<form method="post" enctype="multipart/form-data">';
		echo '<div class="input-group">';
		echo '<span class="input-group-addon">Title:</span>';
		echo '<input name="tag" type="text" value="'.$row['tag'].'" placeholder="Enter title" class="form-control">';
		echo '<span class="input-group-btn" title="Add"><button name="command" value="tag-add" class="btn btn-success" type="submit"><span class="glyphicon glyphicon-plus"></span></button></span>';
		echo '</div>';
		echo '</form>';
		echo '<br/>';

		// remove tag
		$db->bind('file_id',$record_id);
		$results = $db->query('SELECT `tag_id`, IF(LENGTH(`tag`) > 33, CONCAT(LEFT(`tag`, 33), \'...\'),`tag`)  AS `tag` FROM `file_tag` WHERE `file_id` = :file_id;');
		if(count($results)>0){
			foreach($results as $row){
				echo '<form method="post" enctype="multipart/form-data">';
				echo '<div class="input-group">';
				echo '<input type="hidden" name="tag" value="'.$row['tag_id'].'"/>';
				echo '<span class="form-control">'.$row['tag'].'</span>';
				echo '<span class="input-group-btn" title="Link"><a class="btn btn-default" target="_blank" href="'.$instance->href('photo-gallery/search.html').'?search[tags]='.$row['tag'].'"><span class="glyphicon glyphicon-link"></span></a></span>';
				echo '<span class="input-group-btn" title="Remove"><button name="command" value="tag-remove" class="btn btn-danger" type="submit"><span class="glyphicon glyphicon-remove"></span></button></span>';
				echo '</div>';
				echo '</form>';
			}
		}

		echo '<h2>File Versions</h2>';
		echo '<p>Removing all versions will permanently delete the file from repository.</p>';
		$db->bind('file_id',$record_id);
		$results2 = $db->query('SELECT `version_id`, `file_id`, `filename` FROM `file_version` WHERE file_id = :file_id');
		if(count($results2)>0){
			foreach($results2 as $row2){
				echo '<form method="post" enctype="multipart/form-data">';
				echo '<div class="input-group">';
				echo '<input type="hidden" name="version" value="'.$row2['version_id'].'"/>';
				echo '<span class="form-control">'.$row2['filename'].'</span>';
				echo '<span class="input-group-btn" title="Link"><a class="btn btn-default" target="_blank" href="'.$instance->href('photo-gallery/download.html', $row2['file_id']).'&version='.$row2['filename'].'"><span class="glyphicon glyphicon-link"></span></a></span>';
				echo '<span class="input-group-btn" title="Remove"><button name="command" value="version-remove" class="btn btn-danger" type="submit"><span class="glyphicon glyphicon-remove"></span></button></span>';
				echo '</div>';
				echo '</form>';
			}
		}

		echo '</div>';

		echo '<div class="col-md-4">';
		echo '<h2>Thumbnail</h2>';
		echo '<p><img src="'.$instance->href('photo-gallery/download.html', $record_id).'&version=300.jpg" alt="File No.'.$record_id.'"/></p>';
		echo '</div>';

		echo '</div>';
	}
	echo '</div>';
} else {
	echo '<div class="container background-white">';
	echo '<h2>404 - File or directory not found.</h2>';
	echo '<p>Invalid edit file selected.</p>';
	echo '</div>';
}
?>
