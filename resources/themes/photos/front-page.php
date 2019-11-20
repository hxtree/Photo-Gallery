<?php
echo '<div class="cover">';
echo '<div class="container">';
echo '<div class="jumbotron">';
echo '<div class="row">';
echo '<div class="col-md-12 ss-transparent">';
echo '<h1>Photos</h1>';
echo '<h3>The home for many of our photos.</h3>';
echo '<p>Easily access and share photos*.</p>';
echo '<br/><p><button type="button" class="btn btn-primary btn-lg" onclick="window.location.href=\''.$instance->href("photo-gallery/browse.html").'\'">Browse<span class="glyphicon glyphicon-menu-right"></span></button> <button type="button" class="btn btn-default btn-lg" onclick="window.location.href=\''.$instance->href("contact.html").'\'">Request Permission <span class="glyphicon glyphicon-menu-right"></span></button></p>';
echo '<p><small>*Use of any photo <u><b>must</b></u> be approved first.</small></p>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
?>
