<?PHP
class DBI {
	// This class can help with database interfacing

	protected $VERBOSE;
	public $objHandle;
	public $strHost = tanya_db_host;
	public $strUser = tanya_db_user;
	public $strPass = tanya_db_pass;
	public $objResultHandle; // Current query result handle

	public function __construct($VERBOSE = false) {
		$this->VERBOSE = $VERBOSE;
	}

	public function __destruct() {
		if ($this->objResultHandle)
			$this->free();
		if (is_resource($this->objHandle))
			$this->close();
	}

	public function free() {
		if (
			$this->objResultHandle
			&& @mysql_num_rows($this->objResultHandle)
		) {
			@mysql_free_result($this->objResultHandle);
			$this->objResultHandle = 0;
			return 1;
		}
	}

	public function close() {
		if ($this->objResultHandle)
			$this->free();
		if (!is_resource($this->objHandle)) {
			if ($this->VERBOSE)
				print "Unable to close; no resource is open.<br>\n";
			return 1;
		}
		mysql_close($this->objHandle);
		if ($this->VERBOSE)
			print "DBI -> Instance closed<br>\n";
		return 1;
	}

	public function open() {
		$this->free(); // Free any opened results before opening new ones
		if (is_resource($this->objHandle)) {
			if ($this->VERBOSE)
				print "Unable to open a new connection; there is already one open<br>\n";
			return 1;
		}
		$this->objHandle = @mysql_connect($this->strHost, $this->strUser, $this->strPass);
		if (!is_resource($this->objHandle)) {
			if ($this->VERBOSE) {
				die("Could not connect: " . mysql_error() . "\n");
			} else {
				die("There was a problem connecting to the database, try again.\n");
			}
		}
		if ($this->VERBOSE)
			print "DBI -> Instance opened<br>\n";
		return 1;
	}

	public function query($strSql, $objHandle=false) {
		if (!is_object($objHandle))
			$objHandle = $this->objHandle;
		if (
			!isset($strSql)
			|| !strlen($strSql)
		) {
			if ($this->VERBOSE)
				print "MySql Query failed; No query string provided<br>\n";
			return 0;
		}
		//print "[" . $strSql . "]<br>\n";
		$this->objResultHandle = mysql_query($strSql, $objHandle);
		if (!$this->objResultHandle) {
			if ($this->VERBOSE)
				print "Query failed; " . mysql_error($objHandle) . "<br>\n";

			// Log the query string, error, time
			return 0;
		}
		return $this->objResultHandle;
	}

	public function toggle() {
		if (is_resource($this->objHandle)) {
			$this->close();
			return 0;
		} else {
			$this->open();
			return 1;
		}
	}
}
?>