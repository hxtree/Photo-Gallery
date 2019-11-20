<?php
$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');

$files['count'] = $db->single('SELECT COUNT(`file_adjacency_list`.`file_id`) FROM `file_adjacency_list` WHERE `file_adjacency_list`.`file_id` IS NOT NULL'); // cannot bind limits
//$db->single('SELECT MAX(`file_id`) FROM `file`;');

echo '<div class="container background-white">';
if($files['count']<1){
	echo '<h2>Files</h2>';
	echo '<p>Currently, there are no files to browse.</p>';
} else {
	$files['per_page'] = 60;
	$pages['max'] = ceil($files['count']/$files['per_page']);
	// get page current
	$pages['current'] = $_GET['page'];
	switch($pages['current']){
		case NULL:
			$pages['current'] = 1;
			break;
		case ($pages['current']>$pages['max']):
			$pages['current'] = $pages['max'];
			break;
		case ($pages['current']<$pages['min']):
			$pages['current'] = $pages['min'];
			break;
	}

	// determine previous next
	if($pages['current']==$pages['min']){
		$pages['previous'] = NULL;
	} else {
		$pages['previous'] = $pages['current'] - 1;
	}
	if($pages['current']==$pages['max']){
		$pages['next'] = NULL;
	} else {
		$pages['next'] = $pages['current'] + 1;
	}

	// get limit issues on last page?
	if($pages['current']==1){
		$limit['min'] = 0;
	} else {
		$limit['min'] = ($pages['current']-1)*$files['per_page'];
	}
	$limit['max'] = $limit['min'] + $files['per_page'];
	if($limit['max']>$files['count']){$limit['max']=$files['count'];}

	// set goto page values
	$d1 = $pages['current']-1;
	$d2 = $pages['max']-$pages['current'];
	$goto['min'] = $pages['current'] - (($d1>10)?10:$d1);
	$goto['max'] = $pages['current'] + (($d2>10)?10:$d2);
	if($d1<10){$goto['max'] += abs($d1-10);}
	if($d2<10){$goto['min'] -= abs($d2-10);}
	if($goto['max']>$pages['max']){$goto['max']=$pages['max'];}
	if ($goto['min'] < 1) {
		$goto['min']= 1;
	}	else if ($goto['min']<$pages['min']){
		$goto['min']=$pages['min'];
	}

	echo '<div class="row">';
	echo '<div class="col-md-6">';
	echo '<h2 style="margin: 0px !important; padding: 0px !important;">Files</h2>';
	echo '</div>';
	echo '<div class="col-md-6 text-right">';
	echo 'Showing <b>'.($limit['min']+1).'</b> - <b>'.($limit['max']).'</b> of <b>'.$files['count'].'</b> ';
	if($pages['previous']==NULL){
		echo '<a class="btn btn-default disabled" href="#results"><span class="glyphicon glyphicon-menu-left"></span></a>';
	} else {
		echo '<a class="btn btn-default" href="?page='.$pages['previous'].'#results"><span class="glyphicon glyphicon-menu-left"></span></a>';
	}
	if($pages['next']==NULL){
		echo '<a class="btn btn-default disabled" href="#results"><span class="glyphicon glyphicon-menu-right"></span></a>';
	} else {
		echo '<a class="btn btn-default" href="?page='.$pages['next'].'#results"><span class="glyphicon glyphicon-menu-right"></span></a>';
	}
	echo '</div>';
	echo '</div>';
	echo '<hr/>';

	//$results = $db->query('SELECT `file`.`file_id` FROM `file` LEFT JOIN `file_version` ON `file`.`file_id` = `file_version`.`file_id` WHERE `file_version`.`filename` = \'300.jpg\' GROUP BY `file`.`file_id` ORDER BY `last_modified` DESC, `file_id` DESC LIMIT '.$limit['min'].','.$files['per_page']);

	$results = $db->query('SELECT `file`.`file_id` FROM `file_adjacency_list` LEFT JOIN `file` ON `file_adjacency_list`.`file_id` = `file`.`file_id` WHERE `file_adjacency_list`.`file_id` IS NOT NULL ORDER BY DATE(`file`.`last_modified`) DESC, `file_adjacency_list`.`name` DESC LIMIT '.$limit['min'].','.$files['per_page']);

	echo '<div id="gallery">';
	foreach($results as $row){
		echo '<a href="'.$instance->href('photo-gallery/thumbnail-info.html', $row["file_id"]).'" data-toggle="lightbox" data-gallery="multiimages" data-title="Private Content">';
		echo '<img alt="File#'.$row['file_id'].'" src="'.SERVER.'/assets/output/'.$row['file_id'].'/300.jpg" class="image">';
		echo '</a>';
	}
	echo '</div>';

	echo '<p class="text-center"><small>Note: Only files with a thumbnail are shown above. Use the <a href="'.$instance->href('photo-gallery/tags.html').'">tags</a>, <a href="'.$instance->href('photo-gallery/directory.html').'">directory</a>, or <a href="'.$instance->href('photo-gallery/search.html').'">search</a> pages to find files without a thumbnail. Photos recently added will appear first until the following day when they will be sorted by the date taken.</small></p>';
	// goto page
	echo '<div class="row text-center">';
	echo '<ul class="pagination">';
	if($pages['previous']!=NULL){
		echo '<li><a href="?page='.$pages['previous'].'#results"><span class="glyphicon glyphicon-menu-left"></span></a></li>';
	}
	for($i = $goto['min']; $i <= $goto['max']; $i++){
		if($i==$pages['current']){
			echo '<li class="active"><a href="#results">'.$i.'</a></li>';
		} else {
			echo '<li><a href="?page='.$i.'#results">'.$i.'</a></li>';
		}
	}
	if($pages['next']!=NULL){
		echo '<li><a href="?page='.$pages['next'].'#results"><span class="glyphicon glyphicon-menu-right"></span></a></li>';
	}
	echo '</ul>';
	echo '</div>';
}
echo '</div>';

?>
