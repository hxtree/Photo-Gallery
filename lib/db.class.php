<?php
class database {
	private $pdo;
	private $sQuery;
	private $settings;
	private $bConnected = false;
	private $log;
	private $parameters;
	public function __construct($host, $user, $password, $dbname) {
		$this->settings = array(
			'host' => $host,
			'user' => $user,
			'password' => $password,
			'dbname' => $dbname,
		);
		$this->log = new Log();
		$this->Connect();
		$this->parameters = array();
	}
	private function Connect() {
		try {
			# connect and set UTF8
			$this->pdo = new PDO("mysql:dbname={$this->settings["dbname"]};host={$this->settings["host"]}", $this->settings["user"], $this->settings["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

			# We can now log any exceptions on Fatal error.
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			# Disable emulation of prepared statements, use REAL prepared statements instead.
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			# Connection succeeded, set the boolean to true.
			$this->bConnected = true;
		} catch (PDOException $e) {
			# Write into log
			echo $this->ExceptionLog($e->getMessage());
			die();
		}
	}
 	public function CloseConnection() {
 		$this->pdo = null;
	}
	private function Init($query,$parameters = "") {
		if(!$this->bConnected) { $this->Connect(); }
		try {
			$this->sQuery = $this->pdo->prepare($query);
			$this->bindMore($parameters);
			if(!empty($this->parameters)) {
				foreach($this->parameters as $param) {
					$parameters = explode("\x7F",$param);
					if($parameters[1]==NULL){
						$this->sQuery->bindValue($parameters[0], null, PDO::PARAM_INT);
					} else {
						$this->sQuery->bindParam($parameters[0],$parameters[1]);
					}
				}
			}
			$this->succes 	= $this->sQuery->execute();
		} catch(PDOException $e) {
			echo $this->ExceptionLog($e->getMessage(), $query );
			die();
		}
		$this->parameters = array();
	}
	public function bind($para, $value) {
		$this->parameters[sizeof($this->parameters)] = ':' . $para . "\x7F" . $value;
	}
	public function bindMore($parray) {
		if(empty($this->parameters) && is_array($parray)) {
			$columns = array_keys($parray);
			foreach($columns as $i => &$column)	{
				$this->bind($column, $parray[$column]);
			}
		}
	}
 	public function query($query,$params = null, $fetchmode = PDO::FETCH_ASSOC) {
		$query = trim($query);
		$this->Init($query,$params);
		$statement = strtolower(substr($query, 0 , 6));
		if ($statement === 'select') {
			return $this->sQuery->fetchAll($fetchmode);
		} elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
			return $this->sQuery->rowCount();
		} else {
			return NULL;
		}
	}
	public function lastInsertId() {
		return $this->pdo->lastInsertId();
	}
	public function column($query,$params = null) {
		$this->Init($query,$params);
		$Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
		$column = null;
		foreach($Columns as $cells) {
			$column[] = $cells[0];
		}
		return $column;
	}
	public function row($query,$params = null,$fetchmode = PDO::FETCH_ASSOC) {
		$this->Init($query,$params);
		return $this->sQuery->fetch($fetchmode);
	}
	public function single($query,$params = null) {
		$this->Init($query,$params);
		return $this->sQuery->fetchColumn();
	}
	private function ExceptionLog($message , $sql = "") {
		if(!empty($sql)) {
			$message .= "\r\nRaw SQL : "  . $sql;
		}
		$this->log->write($message);
		return "Unhandled Exception. <br/>{$message}<br/> This error is further detailed in the system log.";
	}
}
?>
