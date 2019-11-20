<?php
$photo_conf = parse_ini_file('resources/config/photo-gallery.conf');

// set var
if(isset($_POST['contact'])){
	$contact = $_POST['contact'];
} else {
	$contact = array(
		'name' => $_SESSION['user']['name'],
		'subject' => null,
		'email' => 	$_SESSION['user']['mail'],
		'message' => null
	);
	if($instance->verify(true)){
		$contact['subject'] = 'PHOTOS request for File#'.$record_id;
		$db->bind('file_id',$record_id);
		$format = $db->single('SELECT `format` FROM `file` WHERE `file_id` = :file_id LIMIT 1');
		$contact['message'] = '<p>To whom it may concern,</p><p>I am in the process of working on <u><b>[describe project]</b></u></p><p>I request you supply and grant me permission to include the below listed file in this project:<ul><li><a href="'.$instance->href('photo-gallery/download.html',$record_id).'&version=original'.$format.'">'.$instance->href('photo-gallery/download.html',$record_id).'&version=original'.$format.'</a></li></ul></p><p>The project will <u><b>[describe how the project and material will be used]</b></u>.</p><p>I would greatly appreciate your consent to my request. If you require any additional information, please do not hesitate to contact me.<br/><br/>'.
		$_SESSION['user']['cn'].'<br/>'.
		$_SESSION['user']['title'].'<br/>'.
		$_SESSION['user']['telephonenumber'].'<br/>';
	}
}
if(isset($_SESSION['user']['account']['username'])){
	$contact['name'] = $_SESSION['user']['account']['username'];
}

// handled post
if(isset($_POST['contact'])) {
	$error = false;

	if(strlen($contact['name'])>0) {
		$contact['name'] = trim($contact['name']);
	} else {
		$alert->add('warning','Provide a valid name'); $error = true;
	}
	if(strlen($contact['subject'])>0) {
		$contact['subject'] = trim($contact['subject']);
	} else {
		$alert->add('warning','Provide a valid subject');	$error = true;
	}
	if((strlen($contact['email'])>0)&&(preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $contact['email']))) {
		$contact['email'] = trim($contact['email']);
	} else {
		$alert->add('warning','Provide a valid email address'); $error = true;
	}
	if(strlen($contact['message'])>10) {
		if(function_exists('stripslashes')) {
			$contact['message'] = stripslashes(trim($contact['message']));
		} else {
			$message = trim($contact['message']);
		}
	} else {
		$alert->add('warning','Provide a message longer than 10 charaters');
		$error = true;
	}

	if($error==false) {
		$headers = 'From: contact form <'.$contact['email'].'>'.PHP_EOL;
		$headers .= 'Reply-To: <'.$contact['email'].'>'.PHP_EOL;
		$headers .= 'MIME-Version: 1.0'.PHP_EOL;
		$headers .= 'Content-Type: text/html; charset=ISO-8859-1'.PHP_EOL;
		$message .= 'Name: '.$contact['name'].'<br/>';
		$message .= 'Subject: '.$contact['subject'].'<br/>';
		$message .= 'IP address: '.$_SERVER['REMOTE_ADDR'].'<br/><br/>';
		$message .= $contact['message'].PHP_EOL;
		mail($instance->website['email'], $contact['subject'], '<html><body>'.$message.'</body></html>', $headers);
		$alert->add('success','Your message was successfully sent. We will be in contact with you shortly');
		$contact['subject'] = '';
		$contact['message'] = '';
	}
}

echo '<div class="container background-white load-transition">';
echo '<div class="row">';
echo '<div class="col-md-8">';
echo '<h2>Message</h2>';
echo '<p>Please first contact us prior to using or distributing any files maintained by this system. Use of any of these files must be cleared first.</p>';

$alert->get();

echo '<form name="contact" id="contact" method="post" enctype="multipart/form-data">';

echo '<fieldset class="form-group">';
echo '<label for="name">Name<em class="required">*</em></label>';
echo '<input name="contact[name]" id="name" value="'.$contact['name'].'" class="form-control" placeholder="Enter name" aria-required="true"/>';
echo '</fieldset>';

echo '<fieldset class="form-group">';
echo '<label for="email">Email address<em class="required">*</em></label>';
echo '<input name="contact[email]" id="email" value="'.$contact['email'].'" type="email" class="form-control"  placeholder="Enter email" aria-required="true"/>';
echo '</fieldset>';

echo '<fieldset class="form-group">';
echo '<label for="subject">Subject<em class="required">*</em></label>';
echo '<input name="contact[subject]" id="subject" value="'.$contact['subject'].'" class="form-control" placeholder="Enter subject" aria-required="true"/>';
echo '</fieldset>';

echo '<fieldset class="form-group">';
echo '<label for="message">Message<em class="required">*</em></label>';
echo '<textarea name="contact[message]" id="message" class="form-control" placeholder="Enter message" aria-required="true" style="resize: none; min-height: 300px;">'.$contact['message'].'</textarea>';
echo '</fieldset>';

echo '<input type="submit" class="btn btn-lg btn-primary" name="command" value="Send"/>';
echo '</form>';
echo '</div>';

echo '<div class="col-md-4">';
echo '<h2>Request Permission.</h2>';
echo '<img src="'.$instance->href('images/common/permission.jpg').'" alt="decorative" style="max-width: 100%"/>';

echo '</div>';
echo '</div>';
echo '</div>';

echo '<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>';
echo '<script>tinymce.init({ selector:\'textarea\', convert_urls: false });</script>';

?>
