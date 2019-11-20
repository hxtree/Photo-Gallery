<?php

/* breadcrumbs */
$max = count($instance->page['breadcrumbs'])-1;
if($max>0){
	echo '<div class="breadcrumb">';
	echo '<div class="container">';
	$count = 0;
	foreach($instance->page['breadcrumbs'] as $key => $value){
		if($count==0){
			echo '<a class="crumb-last" href="'.$instance->href($value['link']).'">';
			echo $value['name']; // '<span class="glyphicon glyphicon-home"></span>';
			echo '</a>';
		} else if ($count==$max) {
			echo '<a class="crumb-last" href="'.$instance->href($value['link']).'">'.$value['name'].'</a>';
		} else {
			echo '<a class="crumb-{$count}" href="'.$instance->href($value['link']).'">'.$value['name'].'</a>';
		}
		if($count<$max){
			echo '<span class="glyphicon glyphicon-menu-right"></span>';
		}
		$count++;
	}
	echo '</div>';
	echo '</div>';
}

?>
		<footer class="footer">
			<div class="copyright">
				<div class="container">
					<div class="row">
						<div class="col-xs-8 text-left">
							<span class="glyphicon glyphicon-copyright-mark"></span> 
							<?php echo date('Y');?> All rights reserved.
						</div>
						<div class="col-xs-4 text-right">
							<a class="block" href="#pagetop"><span class="glyphicon glyphicon glyphicon-chevron-up"></span></a>
						</div>
					</div>
				</div>
			</div>
		</footer>

<?php
// jquery
echo '<script src="'.$instance->href('javascript/jquery.min.js').'"></script>';
echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>';
echo '<script src="'.$instance->href('javascript/gallery.min.js').'"></script>';
echo '<script>$("#gallery").justifiedGallery();</script>';

// lightbox
echo '<script src="'.$instance->href('javascript/lightbox.min.js').'"></script>';
echo '<script>$(document).delegate(\'*[data-toggle="lightbox"]\', \'click\', function(event) { event.preventDefault(); $(this).ekkoLightbox();}); </script>';

echo '<link rel="stylesheet" href="'.$instance->href('stylesheets/bottom.min.css').'" type="text/css"/>';
?>
	</body>
</html>
