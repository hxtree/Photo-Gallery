<?php
echo "<div class=\"container background-white load-transition\">";
echo "<h2>Sorry, your request could not be completed.</h2>";
echo "<p>It seems that the page you were trying to reach is unavailable, or maybe it has just been moved. The best thing to do is to start again from the <a href=\"{$instance->href("home.html")}\">home</a> page.</p>";
echo "<p>If the problem persists or if you definitely cannot find what you are looking for, please feel free to <a href=\"{$instance->href("contact.html")}\">report the issue</a>.</p>";
echo "</div>";
?>
