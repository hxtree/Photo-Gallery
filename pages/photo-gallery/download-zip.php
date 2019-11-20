<?php
if(!in_array($_SESSION['user']['uid'],$admin_users)){
	require('pages/unauthorized.php');
//} else if ($instance->verify(true)){
} else {
	// allow for 10 hour of processing
	set_time_limit(36000);	
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

		$files['per_page'] = 10000;
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

			$results = $db->query('SELECT `_file_search`.`file_id`, `_file_search`.`filename` FROM `_file_search` WHERE 	'.$search_tags_string.' ORDER BY DATE(`_file_search`.`last_modified`) DESC,`_file_search`.`filename` DESC LIMIT '.$limit['min'].','.$files['per_page']);

			$zip = new ZipArchive();
			$zip_name = 'download/'.(isset($_SESSION['user']['uid'])?$_SESSION['user']['uid']:'unknown').'-'.date('Y-m-d-h-i-s').'.zip';
			if ($zip->open($zip_name, ZipArchive::CREATE)!==TRUE) {
				exit("Cannot open <$zip_name>\n");
			}
			// add search results to zip
			foreach($results as $row){
				foreach (glob('assets/output/'.$row['file_id'].'/original*') as $filename) {
					if(file_exists($filename)){
						$zip->addFile($filename, $row['file_id'].'_'.$row['filename']);
					}
				}
			}
			$zip->close();
			if(file_exists ($zip_name)){
				// redirect to the file for download
				header('Location: '.SERVER.'/'.$zip_name);
			} else {
				echo '<div class="container background-white" id="results">';
				echo '<h3>Failed to create zip. No original files found.</h3>';
				echo '</div>';
			}
		}
	} else {
		echo '<div class="container background-white" id="results">';
		echo '<h3>Invalid download request.</h3>';
		echo '</div>';
	}
}
