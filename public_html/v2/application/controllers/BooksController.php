<?php
class BooksController extends Zend_Controller_Action
{
    private $_user_session_data;
	private $objPermission;

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

    public function bookeditorAction()
    {
		$intItemsPerPage = $this->view->intItemsPerPage = 10;

		$_VERBOSE = 0;
		if ($this->_request->getParam("book_id"))
			$this->view->book_id = $intBook = intval($this->_request->getParam("book_id"));
		$objBooks = new Books();
		if (isset($intBook))
		{
			// Is Book

			$this->view->objBook = $objBook = current($objBooks->_books_select(array(
				"book_id" => $intBook
			)));
			if (!$objBook)
			{
				print text("Sorry, there was an error") . ": CB-CE101-SD7FD7";
				exit;
			}
			$this->view->intTotalLines = $objBooks->book_lines_select_count(array(
				"book_id" => $intBook
			));


			// AJAX: Load the book and lines
			if ($this->_request->getParam("load_ajax") == "true")
			{

				$intPage = $this->_request->getParam("page");
				if ($intPage < 0)
				{
					print text("Sorry, there was an error") . ": BC-BE101-F6G8F6";
					exit;
				}

				$arrBookLines = $objBooks->_book_lines_select(array(
					"book_id" => $intBook,
					"LIMIT" => ($intPage * $intItemsPerPage) . "," . $intItemsPerPage
				));

				$intBookLinesCount = $objBooks->book_lines_select_count(array(
					"book_id" => $intBook
				));

				foreach ($arrBookLines as $intKey => $objBookLine)
				{
					$arrBookLines[$intKey]->line_data = preg_replace("/\\\\+/", "", $objBookLine->line_data);
				}
				print json_encode(array(
					"book" => $objBook,
					"book_lines" => $arrBookLines,
					"lines_count" => $intBookLinesCount
				));
				exit;
			}


			// AJAX: Delete a book line
			if ($this->_request->getParam("del_item") == "true")
			{
				$intItemId = intval($this->_request->getParam("item_id"));
				if (!$intItemId)
				{
					print text("Sorry, there was an error") . ": CB-BE101-S8D9S9";
					exit;
				}
				$boolResult = $objBooks->book_lines_delete_fix(array(
					"line_hierarchy" => $intItemId,
					"book_id" => $intBook
				));
				print $boolResult;
				exit;
			}


		}
		if($this->_request->isPost()) // Save / update book data w/ ajax
		{
			//var_dump($this->_request->getPost());
			$arrLines = array();
			if (isset($intBook) && $objBook)
			{
				$objBooks->_books_update(array(
					"where" => array(
						"book_id" => $intBook
					),
					"values" => array(
						"book_name"				=> $this->_request->getPost("book_name"),
						"line_numbers_enabled"	=> $this->_request->getPost("line_number") == "true" ? 1 : 0,
						"paragraphs_enabled"	=> $this->_request->getPost("paragraphs") == "true" ? 1 : 0,
						"pages_enabled"			=> $this->_request->getPost("pages") == "true" ? 1 : 0,
						"chapters_enabled"		=> $this->_request->getPost("chapters") == "true" ? 1 : 0,
						"volumes_enabled"		=> $this->_request->getPost("volumes") == "true" ? 1 : 0
					)
				));
				if ($_VERBOSE)
					print "updated book,";
			}
			else
			{
				// Create the book
				$intBook = $objBooks->_books_insert(array(
					"institution_id"		=> $this->_user_session_data->institution_id,
					"book_name"				=> $this->_request->getPost("book_name"),
					"line_numbers_enabled"	=> $this->_request->getPost("line_number") == "true" ? 1 : 0,
					"paragraphs_enabled"	=> $this->_request->getPost("paragraphs") == "true" ? 1 : 0,
					"pages_enabled"			=> $this->_request->getPost("pages") == "true" ? 1 : 0,
					"chapters_enabled"		=> $this->_request->getPost("chapters") == "true" ? 1 : 0,
					"volumes_enabled"		=> $this->_request->getPost("volumes") == "true" ? 1 : 0
				));
				print "book_id:$intBook,";
			}

			// Parse the lines
			$arrItems = $this->_request->getPost();
			foreach ($arrItems as $strKey => $strValue)
			{
				if (preg_match("/row_([0-9]+)(_+)col_(.+)/", $strKey, $arrMatched))
				{
					$arrLines[$arrMatched[1]][$arrMatched[2]][$arrMatched[3]] = $strValue;
				}
			}
			// Loop through the lines and update / insert the new data
			foreach ($arrLines as $intKey => $arrItem)
			{
				foreach ($arrItem as $strFlag => $arrLine)
				{
					if (!isset($arrLine["line_data"]) || $arrLine["line_data"] == "")
					{
						continue;
					}
					// First check if the line already exists
					if ($strFlag == "__") // If the line was just created from the ui an added underscore is available
					{
						if ($_VERBOSE)
							print "book_id:$intBook,";
						$intBookLineAI = $objBooks->_book_lines_insert(array(
							"book_id" => $intBook,
							"institution_id" => $this->_user_session_data->institution_id,
							"line_hierarchy" => $intKey,
							"line_data" => $arrLine["line_data"],
							"line_number" => @$arrLine["line_number"],
							"paragraphs" => @$arrLine["paragraphs"],
							"pages" => @$arrLine["pages"],
							"chapters" => @$arrLine["chapters"],
							"volumes" => @$arrLine["volumes"]
						));
						if ($_VERBOSE)
							print "insert_line:$intBookLineAI,";
					}
					else
					{
						$boolSuccess = $objBooks->_book_lines_update(array(
							"where" => array(
								"line_hierarchy" => $intKey,
								"book_id" => $intBook
							),
							"values" => array(
								"line_data" => $arrLine["line_data"],
								"line_number" => @$arrLine["line_number"],
								"paragraphs" => @$arrLine["paragraphs"],
								"pages" => @$arrLine["pages"],
								"chapters" => @$arrLine["chapters"],
								"volumes" => @$arrLine["volumes"]
							)
						));
						if ($_VERBOSE)
							print "update_line:$boolSuccess,";
					}
				}
			}
			print 1;
			exit; // ajax end
		}
	}

	public function bookconfigAction()
	{
		$this->view->book_id = $intBook = intval($this->_request->getParam("book_id"));
		$objBooks = new Books();
		if ($intBook)
		{
			$objBook = current($objBooks->_books_select(array(
				"book_id" => $intBook
			)));
		}
		// Handle the ajax submit of config setting
		// Note: The view bookeditor may be resposible for this post
		if($this->_request->isPost()) // Save / update books w/ ajax
		{
			if ($intBook && $objBook)
			{
				print "1";exit;
				// The book exists, apply the new configuration settings
				$arrUpdateParams = array(
					"book_id" => $intBook
				);
				//if ($this->_request->getParam("book_id"))

				$objBooks->_books_update();
			}
			else
			{

				print "2";
				exit;
			}
		}
	}

	public function bookslistAction()
	{
		$objBooks = new Books();
		$this->view->arrBooks = $objBooks->_books_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
	}
}
?>