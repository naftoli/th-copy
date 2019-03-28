<?
class Templates {
	protected $VERBOSE;
	protected $strFile;

	public function __construct($VERBOSE = false) {
		$this->VERBOSE = $VERBOSE;
		// Load the files required for this object
		if ($this->VERBOSE)
			print "Templates activated<br>\n";
	}

	public function process($boolNoTemplate=0) {
		global $strTanyaTitle;
		// Add generic replacements here
		$this->replace("__!Self!__", path);
		$objBorderTemplate = new Templates();
		if ($boolNoTemplate)
		{
			$objBorderTemplate->load(template_blank);
		}
		else
		{
			$objBorderTemplate->load(template_wrapper);
		}
		$this->strFile = $objBorderTemplate->replace("__!HTML Contents Place Holder!__", $this->strFile);
		$this->strFile = $objBorderTemplate->replace("__!GetVars!__", $_SERVER["QUERY_STRING"]);
		$this->strFile = $objBorderTemplate->replace("__!PATH!__", path);
		$this->strFile = $objBorderTemplate->replace("__!BASE_URI!__", BASE_URI);
		$this->strFile = $objBorderTemplate->replace("__!TanyaTitle!__", $strTanyaTitle);
		return $this->strFile;
	}

	public function toString() {
		return $this->strFile;
	}

	public function load($strFileName) {
		if (
			!isset($strFileName)
			|| !strlen($strFileName)
		) {
			die("Fatal error; Template name not provided.<br>\n");
		}
		$this->strFile = file_get_contents($strFileName);
		if ($this->strFile) {
			return $this->strFile;
		} else {
			if ($this->VERBOSE)
				print "Unable to load template `$strFileName`.<br>\n";
		}
	}

	public function preg($strRegEx, $strReplacement) {
		$this->strFile = preg_replace("/\r?\n/", "__!RNL!__", $this->strFile);
		$this->strFile = preg_replace($strRegEx, $strReplacement, $this->strFile);
		$this->strFile = preg_replace("/__!RNL!__/", "\n", $this->strFile);
		return $this->strFile;
	}

	public function replace($strKey, $strValue) {
		$this->strFile = str_replace($strKey, $strValue, $this->strFile);
		return $this->strFile;
	}

	public function footer($strData) {
		return $this->preg("/(?:<\/body>)?[\n\t\r ]*<\/html>/i", $strData . "\n\t</body>\n</html>");
	}
}
?>