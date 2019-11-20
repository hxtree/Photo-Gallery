<?php
class user_alert {
	public $_array = array();
	
	function add($type = "info", $input){
		$this->_array[] = array($type,$input);
	}
	function get(){
		// remove duplicates
		$this->_array = array_map("unserialize", array_unique(array_map("serialize", $this->_array)));
		$types = array("success","info","warning","danger");
		$current = 0;
		$string = "";
		foreach($this->_array as $key => $value){
			$new_current = array_search($value[0], $types);
			if($new_current>$current){
				$current = $new_current;
			}
			$string .= "{$value[1]}. ";
		}
		if(isset($string)&&(strlen($string)>0)){
			echo "<div class=\"alert alert-{$types[$current]}\" title=\"Close\" onclick=\"this.style.display='none';\">";
			echo "<strong>{$types[$current]}!</strong> ";
			echo "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a> ";
			echo $string;
			echo "</div>";
		}
	}
}
$alert = new user_alert;
?>