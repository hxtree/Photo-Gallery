<?php
$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');
// parse search terms
$search = $_GET['search'];
$uri = '';
foreach($search as $key => $value){
		$uri .= '&search['.$key.']='.$value;
}

// check start date
$date_start =  date_parse($search['date_start']);
if($date_start['year']==NULL){$date_start['year']=date('Y')-20;}
if($date_start['month']==NULL){$date_start['month']=date('m');}
if($date_start['day']==NULL){$date_start['day']=date('d');}
$search['date_start'] = str_pad($date_start['month'],2,'0',STR_PAD_LEFT).'/'. str_pad($date_start['day'],2,'0',STR_PAD_LEFT).'/'.$date_start['year'];

// check end date
$date_end =  date_parse($search['date_end']);
if($date_end['year']==NULL){$date_end['year']=date('Y');}
if($date_end['month']==NULL){$date_end['month']=date('m');}
if($date_end['day']==NULL){$date_end['day']=date('d');}
$search['date_end'] = str_pad($date_end['month'],2,'0',STR_PAD_LEFT).'/'.str_pad($date_end['day'],2,'0',STR_PAD_LEFT).'/'.$date_end['year'];

echo '<style>a.image-thumbnail{background-size: cover;display: block;height: 140px;width: 100%;}</style>';

echo '<div class="container background-white">';
echo '<div class="row">';
echo '<form action="'.$instance->href('photo-gallery/search.html').'" method="GET" enctype="multipart/form-data">';
// name
echo '<div class="col-md-6 col-sm-6">';
echo '<div class="input-group">';
echo '<span class="input-group-addon" id="basic-addon1">Keyword</span>';
echo '<input name="search[tags]" id="tags" type="text" class="form-control" value="'.$search['tags'].'" placeholder="Search..." aria-describedby="basic-addon1">';
echo '</div>';
echo '</div>';

// date between
echo '<div class="col-md-4 col-sm-6">';
echo '<div class="input-group">';
echo '<span class="input-group-addon" id="basic-addon3">Taken:</span>';
echo '<input type="text" name="search[date_start]" id="date_start" value="'.$search['date_start'].'" placeholder="MM/DD/YYYY" class="form-control" aria-describedby="basic-addon3">';
echo '<span class="input-group-addon" id="basic-addon3">&rarr;</span>';
echo '<input type="text" name="search[date_end]" id="date_end" value="'.$search['date_end'].'"  placeholder="MM/DD/YYYY" class="form-control" aria-describedby="basic-addon3">';
echo '</div>';
echo '</div>';

echo '<div class="col-md-2 col-sm-2">';
echo '<button class="btn btn-primary" type="submit" value="search">Search</button>';
echo '</div>';

echo '</form>';
echo '</div>';
echo '</div>';

if(($search['tags']!=NULL)&&!ctype_space($search['tags'])){
	// bind taken dates
	$db->bind('date_start',$date_start['year'].'-'.str_pad($date_start['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($date_start['day'],2,'0',STR_PAD_LEFT).' 00:00:00');
	$db->bind('date_end',$date_end['year'].'-'.str_pad($date_end['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($date_end['day'],2,'0',STR_PAD_LEFT).' 23:59:59');
	$search_tags_string = '`_file_search`.`last_modified` BETWEEN :date_start AND :date_end ';

	// bind tags
	$search_tags_string .= 'AND (';
	$bool = false;
	foreach(explode(' ',$search['tags']) as $key => $value){
		if(ctype_space($value)||($value==NULL)){continue;}
		$db->bind('tag'.$key, $value);
		if($bool){
			$search_tags_string .= ' AND (`_file_search`.`tags` LIKE CONCAT(\'%\',:tag'.$key.',\'%\'))';
		} else {
			$search_tags_string .= '`_file_search`.`tags` LIKE CONCAT(\'%\',:tag'.$key.',\'%\')';
		}
		$bool = true;
	}
	$search_tags_string .= ') ';

	// count results
	$files['count'] = $db->single('SELECT COUNT(`_file_search`.`file_id`) FROM `_file_search` WHERE '.$search_tags_string);

	$files['per_page'] = 60;
	$pages['min'] = 1;
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
	if($goto['min']<$pages['min']){$goto['min']=$pages['min'];}

	if($files['count']<1){
		echo '<div class="container background-white" id="results">';
		echo '<h3>Sorry, no results were found.</h3>';
		echo '</div>';
	} else {
		echo '<div class="container background-white" id="results">';
		echo '<div class="row">';
		echo '<div class="col-md-6">';
		echo '<h2 style="margin: 0px !important; padding: 0px !important;">Results</h2>';
		echo '</div>';
		echo '<div class="col-md-6 text-right">';
		echo 'Showing <b>'.($limit['min']+1).'</b> - <b>'.$limit['max'].'</b> of <b>'.number_format($files['count']).'</b> ';
		if($pages['previous']==NULL){
			echo '<a class="btn btn-default disabled" href="#results"><span class="glyphicon glyphicon-menu-left"></span></a>';
		} else {
			echo '<a class="btn btn-default" href="?page='.$pages['previous'].$uri.'#results"><span class="glyphicon glyphicon-menu-left"></span></a>';
		}
		if($pages['next']==NULL){
			echo '<a class="btn btn-default disabled" href="#results"><span class="glyphicon glyphicon-menu-right"></span></a>';
		} else {
			echo '<a class="btn btn-default" href="?page='.$pages['next'].$uri.'#results"><span class="glyphicon glyphicon-menu-right"></span></a>';
		}
		echo '</div>';
		echo '</div>';
		echo '<hr/>';

		// bind taken dates
		$db->bind('date_start',$date_start['year'].'-'.str_pad($date_start['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($date_start['day'],2,'0',STR_PAD_LEFT).' 00:00:00');
		$db->bind('date_end',$date_end['year'].'-'.str_pad($date_end['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($date_end['day'],2,'0',STR_PAD_LEFT).' 23:59:59');
		$search_tags_string = '`_file_search`.`last_modified` BETWEEN :date_start AND :date_end ';
		// bind tags
		if(($search['tags']!=NULL)&&!ctype_space($search['tags'])){
			$search_tags_string .= 'AND (';
			$bool = false;
			foreach(explode(' ',$search['tags']) as $key => $value){
				if(ctype_space($value)||($value==NULL)){continue;}
				$db->bind('tag'.$key, $value);
				if($bool){
					$search_tags_string .= ' AND (`_file_search`.`tags` LIKE CONCAT(\'%\',:tag'.$key.',\'%\'))';
				} else {
					$search_tags_string .= '`_file_search`.`tags` LIKE CONCAT(\'%\',:tag'.$key.',\'%\')';
				}
				$bool = true;
			}
			$search_tags_string .= ') ';
		}

		$results = $db->query('SELECT `_file_search`.`file_id` FROM `_file_search` WHERE 	'.$search_tags_string.' ORDER BY DATE(`_file_search`.`last_modified`) DESC,`_file_search`.`filename` DESC LIMIT '.$limit['min'].','.$files['per_page']);

		echo '<div id="gallery">';
		foreach($results as $row){
			echo '<a href="'.$instance->href('photo-gallery/thumbnail-info.html', $row["file_id"]).'" data-toggle="lightbox" data-gallery="multiimages" data-title="Private Content">';
			echo '<img alt="File#'.$row['file_id'].'" src="'.SERVER.'/assets/output/'.$row['file_id'].'/300.jpg" class="image">';
			echo '</a>';
		}
		echo '</div>';

		// goto page
		echo '<div class="row text-center">';
		echo '<ul class="pagination">';
		if($pages['previous']!=NULL){
			echo '<li><a href="?page='.$pages['previous'].$uri.'#results"><span class="glyphicon glyphicon-menu-left"></span></a></li>';
		}
		for($i = $goto['min']; $i <= $goto['max']; $i++){
			if($i==$pages['current']){
				echo '<li class="active"><a href="#results">'.$i.'</a></li>';
			} else {
				echo '<li><a href="?page='.$i.$uri.'#results">'.$i.'</a></li>';
			}
		}
		if($pages['next']!=NULL){
			echo '<li><a href="?page='.$pages['next'].$uri.'#results"><span class="glyphicon glyphicon-menu-right"></span></a></li>';
		}
		echo '</ul>';
		echo '</div>';
		if(!in_array($_SESSION['user']['uid'],$admin_users)){
		} else {
			echo '<ul class="pagination"><li><a class="btn" href="/photo-gallery/download-zip.html?f=download'.$uri.'"><span class="glyphicon glyphicon-download-alt"></span> Download (*.zip)</a></li>';
		}
		echo '</div>';
	}
} else {
	echo '<div class="container background-white" id="results">';
	echo '<h3>Enter search keyword.</h3>';
	echo '</div>';
}
