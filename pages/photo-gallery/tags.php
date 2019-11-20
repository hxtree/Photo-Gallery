
<div class="container background-white">
	<form  method="get" enctype="multipart/form-data">
		<label>
			<input type="text" name="q" value="<?= htmlspecialchars($_GET['q'])?>" placeholder="Contains what?"/>
		</label>
		<input type="submit" value="Search"/>
	</form>
	<p><i>Tags are added based on what objects are recognized, the directory location, and by authorized personnel to make it easier to search for the photo.</p></p>
<?php

// bind search tags
$bool = false;
$sql = '';
foreach(explode(' ',$_GET['q']) as $key => $value){
	if( ctype_space($value) || ($value==NULL) ){ continue;	}
	//$db->bind('tag'.$key, $value);
	$sql .=  $bool ? ' AND ' : ' WHERE ';
	$bool = true;
	$sql .= " `file_tag`.`tag` LIKE CONCAT('%','{$value}','%')";
}
$files['count'] = $db->single('
	SELECT COUNT(DISTINCT(`file_tag`.`tag`)) 
	FROM `file_tag`
	'.$sql
);

if($files['count']<1){ ?>
	<h2>List</h2>
	<p>Currently, there are no tags that match.</p>
<?php
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
?>

	<div class="row">
		<div class="col-md-6">
			<h2 style="margin: 0px !important; padding: 0px !important;">List</h2>
		</div>
		<div class="col-md-6 text-right">
			Showing <b><?= ($limit['min']+1) ?></b> - <b><?= ($limit['max']) ?></b> of <b><?= $files['count'] ?></b> 
			<?php if($pages['previous'] == NULL){ ?>
				<a class="btn btn-default disabled" href="#results"><span class="glyphicon glyphicon-menu-left"></span></a>
			<?php } else { ?>
				<a class="btn btn-default" href="?q=<?= $_GET['q']?>&page=<?= $pages['previous']?>#results"><span class="glyphicon glyphicon-menu-left"></span></a>
			<?php }
			if($pages['next'] == NULL){ ?>
				<a class="btn btn-default disabled" href="#results"><span class="glyphicon glyphicon-menu-right"></span></a>
			<?php } else { ?>
				<a class="btn btn-default" href="?q=<?= $_GET['q']?>&page=<?= $pages['next'] ?>#results"><span class="glyphicon glyphicon-menu-right"></span></a>
			<?php } ?>
		</div>
	</div>
	<hr/>

<?php
	$bool = false;
	$sql = '';
	foreach(explode(' ',$_GET['q']) as $key => $value){
		if( ctype_space($value) || ($value==NULL) ){ continue;	}
		//$db->bind('tag'.$key, $value);
		$sql .=  $bool ? ' AND ' : ' WHERE ';
		$bool = true;
		$sql .= " `file_tag`.`tag` LIKE CONCAT('%','{$value}','%')";
	}
	$results = $db->query('
		SELECT `tag` 
		FROM `file_tag`
		'.$sql.' 
		GROUP BY `tag` 
		ORDER BY `tag` 
		ASC LIMIT '.$limit['min'].','.$files['per_page']
	);

	echo '<div class="row">';
	foreach($results as $row){
		echo '<div class="col-md-4 col-sm-2">';
		echo '<a class="list-group-item" href="'.$instance->href('photo-gallery/search.html').'?search[tags]='.$row['tag'].'"><span class="glyphicon glyphicon-tag"></span> '.$row['tag'].'</a>';
		echo '</div>';
	}
	echo '</div>';

	echo '<div class="row text-center">';
	echo '<ul class="pagination">';
	if($pages['previous']!=NULL){
		echo '<li><a href="?q='.$_GET['q'].'&page='.$pages['previous'].'#results"><span class="glyphicon glyphicon-menu-left"></span></a></li>';
	}
	for($i = $goto['min']; $i <= $goto['max']; $i++){
		if($i==$pages['current']){
			echo '<li class="active"><a href="#results">'.$i.'</a></li>';
		} else {
			echo '<li><a href="?q='.$_GET['q'].'&page='.$i.'#results">'.$i.'</a></li>';
		}
	}
	if($pages['next']!=NULL){
		echo '<li><a href="?q='.$_GET['q'].'&page='.$pages['next'].'#results"><span class="glyphicon glyphicon-menu-right"></span></a></li>';
	}
	echo '</ul>';
	echo '</div>';
}
?>
</div>