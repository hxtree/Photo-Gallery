<?php
$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');

echo '<div id="results" class="container background-white">';
if($instance->verify(true)){
			// directory
	$db->bind('directory_id',$record_id);
	$results = $db->query('SELECT `T2`.`name`, `directory_id`, `file_id` FROM (SELECT @r AS _id, (SELECT @r := `parent_id` FROM `file_adjacency_list` WHERE `directory_id` = _id) AS `parent_id` , @l := @l +1 AS `lvl` FROM (SELECT @r := :directory_id, @l :=0) vars, `file_adjacency_list` WHERE @r <>0) `T1` JOIN `file_adjacency_list` `T2` ON T1._id = `T2`.`directory_id` ORDER BY `T1`.`lvl` DESC LIMIT 100');

	$max = count($results)-1;
	echo '<div class="panel panel-primary"><div class="panel-heading"><span class="glyphicon glyphicon-folder-open"></span>&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<a href="'.$instance->href('photo-gallery/directory.html').'#results">share<span class="glyphicon glyphicon-menu-right"></span></a>';
	$count = 0;
	foreach($results as $key => $value){
		echo '<a href="'.$instance->href('photo-gallery/directory.html',$value['directory_id']).'#results"> '.$value['name'].'</a>';
		if($count<$max){
			echo '<span class="glyphicon glyphicon-menu-right"></span>';
		}
		$count++;
	}
	echo '</div></div>';


	$db->bind('directory_id',$record_id);
	$row = $db->row('SELECT `directory_id`, `parent_id`, `name` FROM `file_adjacency_list` WHERE `directory_id` = :directory_id AND `file_id` IS NULL;');
	echo '<a class="list-group-item" href="'.$instance->href('photo-gallery/directory.html',$row['parent_id']).'#results"><span class="glyphicon glyphicon-folder-open"></span>&nbsp;&nbsp;&nbsp;&nbsp;..</a>';

	$db->bind('parent_id', $record_id);
	$results = $db->query('SELECT `directory_id`, `name`, `parent_id` FROM `file_adjacency_list` WHERE `parent_id` = :parent_id AND `file_id` IS NULL ORDER BY `name`;');
	// get parent directory
	foreach($results as $row){
		echo '<a class="list-group-item" href="'.$instance->href('photo-gallery/directory.html',$row['directory_id']).'#results"><span class="glyphicon glyphicon-folder-close"></span>&nbsp;&nbsp;&nbsp;&nbsp;'.$row['name'].'</a>';
	}
} else {
	echo '<div class="panel panel-primary"><div class="panel-heading"><span class="glyphicon glyphicon-folder-open"></span>&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<a href="'.$instance->href('photo-gallery/directory.html').'#results">share<span class="glyphicon glyphicon-menu-right"></span></a>';
	echo '</div></div>';

	$results = $db->query('SELECT `directory_id`, `name`, `parent_id` FROM `file_adjacency_list` WHERE `parent_id` IS NULL AND `file_id` IS NULL ORDER BY `name`;');
	// get parent directory
	foreach($results as $row){
		echo '<a class="list-group-item" href="'.$instance->href('photo-gallery/directory.html',$row['directory_id']).'#results"><span class="glyphicon glyphicon-folder-close"></span>&nbsp;&nbsp;&nbsp;&nbsp;'.$row['name'].'</a>';
	}
}

// display files
if($record_id == NULL){
	$results = $db->query('SELECT `file_adjacency_list`.`file_id` FROM `file_adjacency_list` LEFT JOIN `file` ON `file_adjacency_list`.`file_id` = `file`.`file_id` WHERE `file_adjacency_list`.`file_id` IS NOT NULL AND `file_adjacency_list`.`parent_id` IS NULL ORDER BY DATE(`file`.`last_modified`) DESC, `file_adjacency_list`.`name` DESC');
} else {
	$db->bind('parent_id', $record_id);
	$results = $db->query('SELECT `file_adjacency_list`.`file_id` FROM `file_adjacency_list` LEFT JOIN `file` ON `file_adjacency_list`.`file_id` = `file`.`file_id` WHERE `file_adjacency_list`.`file_id` IS NOT NULL AND `file_adjacency_list`.`parent_id` = :parent_id ORDER BY DATE(`file`.`last_modified`) DESC, `file_adjacency_list`.`name` DESC');
}
if(count($results)<1){
	echo '<hr/>';
	echo '<p>No files were found in this directory</p>';
} else {
	echo '<hr/>';
	echo '<div id="gallery">';
	foreach($results as $row){
		echo '<a href="'.$instance->href('photo-gallery/thumbnail-info.html', $row["file_id"]).'" data-toggle="lightbox" data-gallery="multiimages" data-title="Private Content">';
		echo '<img alt="File#'.$row['file_id'].'" src="'.SERVER.'/assets/output/'.$row['file_id'].'/300.jpg" class="image">';
		echo '</a>';
	}
	echo '</div>';
}
echo '</div>';
?>
