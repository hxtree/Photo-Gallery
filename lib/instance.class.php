<?php
class instance {
	private $config;
	private $raw_request;
	private $href_cache = array();
	private $hash_cache = array();
	public $uri;
	public $website = array("theme" => "default");
	public $page = array();
	public $user = array("id" => null);
	public $support = array();

	private function href_hash($record_id, $page){
		$ui = "{$page}{$record_id}";
		if(array_key_exists($ui, $this->hash_cache)){
			return $this->hash_cache[$ui];
		} else {
			$string = urlencode(str_replace(".","",crypt($ui, '$1$r'.md5($record_id.$this->config["href_salt"]))));
			$this->hash_cache[$ui] = $string;
			return $string;
		}
	}
	public function verify($silent = false){
		if($this->href($this->page["current"]["link"],$_GET["q"]) == SERVER."/{$this->page["current"]["link"]}?q=".urlencode($_GET["q"])."&amp;a=".urlencode($_GET["a"])){
			global $record_id;
			$record_id = $_GET["q"];
			return true;
		} else {
			if($silent==false){
				echo "<h1><b>404 - Error</b>: Invalid Request.</h1>";
				echo "<p>The requested record could not be accessed. If you have received this message in error, feel free to <a href=\"{$this->href("contact.html")}\">contact</a> me for assistance.</p>";
			}
			return false;
		}
	}
	public function href($string = "", $record_id = null){
		$extension = null;
		if(substr($string, 0, 4) === "http") {return $string;}
		if(($record_id!=null)&&($string=="")){$string = $this->page["current"]["link"];}
		if(array_key_exists($string,$this->href_cache)&&($record_id==null)){
			// return cached href
			return $this->href_cache[$string.$record_id];
		} else {
			// determine absolute href
			$href = "";
			$path_parts = pathinfo($string);
			if (strpos($path_parts["extension"], '?') !== FALSE){
				$extension = substr($path_parts["extension"], 0, strpos($path_parts["extension"], "?"));
			}
			if (strpos($path_parts["extension"], '#') !== FALSE){
				$extension = substr($path_parts["extension"], 0, strpos($path_parts["extension"], "#"));
			}
			if($extension==null){
				$extension = $path_parts["extension"];
			}

			if(in_array($extension, array("html","xml","cvs","pdf"))){
				// page href
				$parts = explode("/",$string);
				if (strpos($string,SERVER) !== false) {
					$href = $string;
				} else {
					$href = SERVER."/{$string}";
				}
				if($record_id!=NULL){
					// add $_GET url security encode for record_id
					$href .= "?q={$record_id}&amp;a={$this->href_hash($record_id,$path_parts["filename"].".".$extension)}";
				}
			} else {
				// check if file exists in current theme, else use default theme
				if(!file_exists("resources/themes/{$this->website["theme"]}/{$string}")){
					$href = SERVER."/resources/themes/default/{$string}";
				} else {
					$href = SERVER."/resources/themes/{$this->website["theme"]}/{$string}";
				}
			}
			$this->href_cache[$string.$record_id] = $href;
			return $href;
		}
	}
	function __construct(){
		global $db;
		$this->config = parse_ini_file("resources/config/default.conf");
		date_default_timezone_set($this->config["timezone"]);
		define("SERVER",$this->config["server"]);
		$this->website = array(
			"name" => $this->config["name"],
			"abbreviation" => $this->config["abbreviation"],
			"theme" => $this->config["theme"],
			"email" => $this->config["email"],
		);

		if ($_SERVER["REMOTE_ADDR"]!=$this->config["debug_ip"]){
			error_reporting(0);
		} else {
			error_reporting(E_ALL);
		}

		$db = new database($this->config["host"], $this->config["user"], $this->config["password"], $this->config["dbname"]);
		// parse raw request to determine page requested
		if(isset($_GET["request"])){
			$this->raw_request = preg_split("/\//", substr($_GET["request"],1));
		} else {
			$this->raw_request[0] = "home.html";
		}
		$_extension = pathinfo(end($this->raw_request), PATHINFO_EXTENSION);
		// 	end(explode('.', end($this->raw_request)));
		$this->page["current"]["link"] = implode("/", $this->raw_request);
		$this->page["current"]["file"] = str_replace(".{$_extension}",".php", $this->page["current"]["link"]);

		// load page info based on page file name if available
		$db->bind("link",$this->page["current"]["link"]);
		$row = $db->row("SELECT `pages`.`id`, `pages`.`name`, `pages`.`standalone`, `pages`.`signin_required`, `pages`.`meta_description`, `page_permissions`.`state` FROM `pages` LEFT JOIN `page_permissions` ON `pages`.`id` = `page_permissions`.`page_id` WHERE `link` = :link LIMIT 1;");
		if($row!=NULL){
			$this->page["current"] = $row + $this->page["current"];
			// load breadcrumb
			$db->bind("page_id",$this->page["current"]["id"]);
			$this->page["breadcrumbs"] = $db->query("SELECT `T2`.`id`, `T2`.`link`, `T2`.`name` FROM (SELECT @r AS _id, (SELECT @r := `parent_id` FROM `pages` WHERE `id` = _id) AS `parent_id` , @l := @l +1 AS `lvl` FROM (SELECT @r := :page_id, @l :=0) vars, `pages` m WHERE @r <>0) `T1` JOIN `pages` `T2` ON T1._id = `T2`.`id` ORDER BY `T1`.`lvl` DESC LIMIT 10;");
		} else {
			if(strlen($this->page["current"]["link"])>0){
				$this->page["current"]["id"] = 0;
				$this->page["current"]["meta_description"] = "Page not found";
				$this->page["current"]["file"] = "page-not-found.php";
				$this->page["current"]["link"] = "page-not-found.html";
				$this->page["current"]["name"] = "Page Not Found";
				$this->page["current"]["standalone"] = false;
				$this->page["current"]["state"] = null;
				$this->page["breadcrumbs"] = array(array("id" => 1, "link" => "home.html", "name"=>"Home"), array("id"=>NULL, "link" => "page-not-found.html", "name"=>"Page Not Found"));
			} else {
				$this->page["current"]["id"] = 1;
				$this->page["current"]["meta_description"] = "Home page";
				$this->page["current"]["file"] = "home.php";
				$this->page["current"]["link"] = "home.html";
				$this->page["current"]["name"] = "Home";
				$this->page["current"]["standalone"] = false;
				$this->page["current"]["state"] = "active";
				$this->page["breadcrumbs"] = array(array("id" => 1, "link" => "home.html", "name"=>"Home"));
			}
		}
		// get uri
		$this->uri = $this->href($this->page["current"]["link"]);
		if(count($_GET)>1){
			$bool = false;
			foreach ($_GET as $key => $value) {
				if($key=="request"){continue;}
				if($bool){
					$this->uri .= "&";
				} else {
					$this->uri .= "?";
					$bool = true;
				}
				if(is_array($value)) {
					foreach($value as $key2 => $value2){
						if(is_array($value2)){continue;}
						$this->uri .= "{$key}[{$key2}]=".urldecode($value2);
					}
				} else {
					$this->uri .= $key."=".urldecode($value);
				}
			}
		}
	}
	public function window($type, $override = false){
		global $instance;
		if(($instance->page['current']['standalone']==1)&&($override==false)){
			return false;
		} else {
			switch ($type) {
				case 'header': include('resources/themes/'.$instance->website['theme'].'/header.php'); break;
				case 'footer': include('resources/themes/'.$instance->website['theme'].'/footer.php'); break;
			}
		}
	}
}

$instance = new instance();
?>
