<?php
echo '<div class="container background-white">';
echo '<h2>403 - Forbidden: Access is Denied</h2>';
echo '<p><b>'.$_SESSION['user']['name'].'</b>, your account is not authorized to access this resource. If you need further assistance, please <a href="'.$instance->href('contact.html', $record_id).'">contact us</a></p>';
echo '</div>';
?>
