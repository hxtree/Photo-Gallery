<?php
echo '<div class="container background-white">';

echo '<div class="form-group">';
echo '<div class="row">';

echo '<div class="col-md-6">';
echo '<h2>Sign In Required</h2>';
echo '<p>Please sign in with your account.</p>';
echo '<form action="#" method="POST">';
echo '<label for="uid">Username:</label>';
echo '<input id="uid" class="form-control" placeholder="" type="text" name="uid" required/>';
echo '<label for="uid">Password:</label>';
echo '<input id="pass" class="form-control" placeholder="******" type="password" name="pass" required/>';
echo '<div class="form-check">';
echo '<label class="form-check-label">';
echo '<input type="checkbox" class="form-check-input" required> I agree to the <a href="#agreement">Use Agreement</a>';
echo '</label>';
echo '</div>';
echo '<input class="btn btn-primary" type="submit" name="submit" value="Sign in"/>';
echo '</form>';
echo '</div>';

echo '<div class="col-md-6">';
echo '<h3 id="agreement">Use Agreement</h3>';
echo '<textarea class="form-control"  style="width: 100%; min-height: 160px; resize: none;" readonly>By using or visiting the Photos website I agree not to distribute in any medium any part of the content without prior written consent. I understand that the copyright for this content is held and reserved.</textarea>';
echo '</div>';

echo '</div>';
echo '</div>';
echo '</div>';
?>
