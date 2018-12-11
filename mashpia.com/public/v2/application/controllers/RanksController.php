<?php
    class RanksController extends Zend_Controller_Action
    {
        private $_user_session_data;
        private $_feeds;

        function init()
        {}

        function preDispatch()
        {
            // Get the session object
            $this->_user_session_data = new Zend_Session_Namespace('user_session_data');

            //instantiate feeds
            $this->_feeds = new Feeds();
            
            /*
            if ($this->_user_session_data)
            {
                if (
                    empty($this->_user_session_data->user_id)
                    || empty($this->_user_session_data->institution_id)
                    || empty($this->_user_session_data->permission)
                    || !$this->_user_session_data->is_user_active
                ) {
                    // Not allowed in
                    $this->_redirect('logout');
                }
                $this->view->permission = $this->_user_session_data->permission;
            }
            else
            {
                // Not allowed in
                $this->_redirect('logout');
            }
            */
        }

        public function indexAction()
        {}

        /*
        ************VIEWS add hosts,networks,institutions for ranks. ********
        */
          // HOSTS //
        public function ranksaddhostsAction()
        {
            $objRoles = new Roles();
            $objInstitutions = new Institutions();
            $this->view->objHosts = $objInstitutions->get_all_active_hosts();
        }
          // HOSTS //

         // NETWORKS //
        public function ranksaddnetworksAction()
        {
            $this->view->intHost = $this->_request->getParam("host_id");

            $objInstitutions = new Institutions();
            $this->view->objNetworks =
                $objMission =
                    $objInstitutions->_institutions_select(
                        array(
                            "institution_type" => "Network",
                            "host_id" => $this->view->intHost,
                            "is_active" => 1
                        )
                    );
        }
        // NETWORKS //

        // INSTITUTIONS //
        public function ranksaddinstitutionsAction()
        {
            $this->view->intHost = $this->_request->getParam("host_id");
            $this->view->intNetwork = $this->_request->getParam("network_id");

            $objInstitutions = new Institutions();
            $this->view->objInstitutions =
                $objMission =
                    $objInstitutions->_institutions_select(
                        array(
                            "institution_type" => "School",
                            "host_id" => $this->view->intHost,
                            "network_id" => $this->view->intNetwork,
                            "is_active" => 1
                        )
                    );
        }
        // INSTITUTIONS //
        /*
        ********* VIEWS hosts, network, institutions for ranks******
        */
        // HOSTS //
        public function rankshostsAction()
        {
            $objInstitutions = new Institutions();
            $this->view->objHosts = $objInstitutions->get_all_active_hosts();
        }
        // HOSTS //

        // NETWORKS //
        public function ranksnetworksAction()
        {
            $this->view->intHost = $this->_request->getParam("host_id");

            $objInstitutions = new Institutions();
            $this->view->objNetworks =
                $objMission =
                    $objInstitutions->_institutions_select(
                        array(
                            "institution_type" => "Network",
                            "host_id" => $this->view->intHost,
                            "is_active" => 1
                        )
                    );
        }
        // NETWORKS //

        // INSTITUTIONS //
        public function ranksinstitutionsAction()
        {
            $this->view->intHost = $this->_request->getParam("host_id");
            $this->view->intNetwork = $this->_request->getParam("network_id");

            $objInstitutions = new Institutions();
            $this->view->objInstitutions =
                $objMission =
                    $objInstitutions->_institutions_select(
                        array(
                            "institution_type" => "School",
                            "host_id" => $this->view->intHost,
                            "network_id" => $this->view->intNetwork,
                            "is_active" => 1
                        )
                    );
        }
        // INSTITUTIONS //

        // RANKS //
        public function rankslistAction()
        {
            $host_id = $network_id = $institution_id = $intInstitution = 0;

            if(isset($this->_request->institution_id)){
               $this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");
            }elseif(isset($this->_request->network_id)){
               $this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
            }elseif(isset($this->_request->host_id)){
               $this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");
            }

            $objRanks = new Ranks();
            $this->view->objRanks = $objRanks->ranks_select_by_institution_id($intInstitution);

        }

        public function ranksaddAction()
        {
            $host_id = $network_id = $institution_id = $intInstitution = 0;

            if(isset($this->_request->institution_id)){
                $this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");
            }elseif(isset($this->_request->network_id)){
                $this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
            }elseif(isset($this->_request->host_id)){
                $this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");
            }

            if($this->_request->isPost())
            {
                //create an object
                $objRanks = new Ranks();

                $intInstitution = intval($intInstitution);
                if (
                    !isset($intInstitution)
                    || !is_int($intInstitution)
                    || !$intInstitution
                ) {
                    print text("Sorry, there was an error") . ": RC-RA101-345GTR";
                    exit;
                }
                //collect all data passed from a form
                $strRankName = $this->_request->getPost("rank_name");
                $intMedalCount = $this->_request->getPost("rank_medals");
                $strRankColor = $this->_request->getPost("rank_color");
                // Filter
                Zend_Loader::loadClass('Zend_Filter_StripTags');
                $objFilter = new Zend_Filter_StripTags();
                $strRankName = $objFilter->filter($strRankName);
                $intMedalCount = $objFilter->filter($intMedalCount);
                $strRankColor = $objFilter->filter($strRankColor);

                    // Validate
                if (!isset($intInstitution) || empty($intInstitution))
                {
                    print "You must include an institution.";
                    exit;
                }
                if (!isset($strRankName) || empty($strRankName))
                {
                    print "You must include a rank name.";
                    exit;
                }
                if ($objRanks->rank_select_name($strRankName, $intInstitution))
                {
                    print "A rank by this name already exists in your institution.";
                    exit;
                }
                $date = date('Y-m-d H:i:s', time());
                $intAI = $objRanks->rank_insert(
				array (
					"institution_id" 	=> $intInstitution,
					"rank_title" 	    => $strRankName,
					"rank_medals" 	    => $intMedalCount,
					"rank_color"		=> $strRankColor,
					"created"			=> $date,
                    "modified"          => '',
					"created_by"		=> $this->_user_session_data->user_id
				)
			);

                    $category = 'Create';
                    $action = ' created rank '.$strRankName;
                    $feed_inst_id = $intInstitution;

                    $result = $this->_feeds->add_feed($feed_inst_id, $action, $category);

                    // Result
                    print $intAI;
                    exit; // Ajax
            }
        }

        public function rankseditAction()
        {
            $objRanks = new Ranks();
            $this->view->rank_id = $rank_id = $this->_request->rank_id;
            $this->view->objRanks = $objRanks->rank_select_by_rank_id($this->view->rank_id);

            //CHECK IF RANK ID IS NOT EMPTY
            if (empty($this->view->rank_id) && preg_match("/^[0-9]$/", $this->view->rank_id))
            {
                print text("Sorry, there was an error") . ": RC-RE101-FGT547";
                exit;
            }
            //check if this rank_id exists in our database rank table
            $this->view->objRanks = $objRanks->rank_select_id($this->view->rank_id);
		    if (!$this->view->objRanks)
            {
                print text("Sorry, there was an error") . ": CC-CE102-SDF456";
                exit;
            }

            if($this->_request->isPost())
            {
                // Declare
                $arrUpdate = array();
                // Define
                $rank_color = $this->_request->getPost("rank_color");
                $rank_title= $this->_request->getPost("rank_title");
                $rank_medals = $this->_request->getPost("rank_medals");

                // Filter
                Zend_Loader::loadClass('Zend_Filter_StripTags');
                $objFilter = new Zend_Filter_StripTags();
                $rank_title = $objFilter->filter($rank_title);
                $rank_color = $objFilter->filter($rank_color);
                // Merge
                if (isset($rank_color) && !empty($rank_color))
                {
                    $arrUpdate["rank_color"] = $rank_color;
                }
                if (isset($rank_title) && !empty($rank_title))
                {
                    $arrUpdate["rank_title"] = $rank_title;
                }
                if (isset($rank_medals) && !empty($rank_medals))
                {
                    $arrUpdate["rank_medals"] = $rank_medals;
                }
                // Process
                if (count($arrUpdate)) {
                    $strResult = $objRanks->ranks_update($arrUpdate, $rank_id);

                    //log event
                    $row = $this->_feeds->get_row('SELECT * FROM ranks WHERE rank_id = '.$rank_id);
                    $action = ' Rank '.$row->rank_title;
                    $result = $this->_feeds->add_feed($row->institution_id, $action, 'Edit');
                } else {
                    $strResult = text("Sorry, there was an error") . ": RC-RE102-1DF12D";
                }
                // Result
                print $strResult;
                exit; // Ajax
            }
        }

        // RANKS //

        /******************** Printing of Ranks views ************************/

        public function ranksprinthostsAction()
        {
            $objInstitutions = new Institutions();
            $this->view->objHosts = $objInstitutions->get_all_active_hosts();
        }
        public function ranksprintnetworksAction()
        {
            $this->view->intHost = $this->_request->getParam("host_id");
            $objInstitutions = new Institutions();
            $this->view->objNetworks =
                $objMission =
                    $objInstitutions->_institutions_select(
                        array(
                            "institution_type" => "Network",
                            "host_id" => $this->view->intHost,
                            "is_active" => 1
                        )
                    );
        }
        public function ranksprintinstitutionsAction()
        {
            $this->view->intHost = $this->_request->getParam("host_id");
            $this->view->intNetwork = $this->_request->getParam("network_id");

            $objInstitutions = new Institutions();
            $this->view->objInstitutions =
                $objMission =
                    $objInstitutions->_institutions_select(
                        array(
                            "institution_type" => "School",
                            "host_id" => $this->view->intHost,
                            "network_id" => $this->view->intNetwork,
                            "is_active" => 1
                        )
                    );
        }
        public function ranksprintclassesAction()
        {
            $institution_id = $intInstitution = 0;
            $objClasses = new Classes();
            if(isset($this->_request->institution_id)){
               $this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");

               $this->view->objClasses = $objClasses->classes_select_institution($intInstitution);
            }
            else{
                $this->view->objClasses = $objClasses->classes_select(0);
            }
        }
        public function ranksprintranksAction()
        {
            $objRanks = new Ranks();
            if(isset($this->_request->class_id)){
                $this->view->class_id = $class_id = $this->_request->getParam("class_id");
                $this->view->arrRanks = $objRanks->ranks_select_by_class($class_id);
            }
            if(isset($this->_request->institution_id))
            {
                $this->view->institution_id = $class_id = $this->_request->getParam("institution_id");
                $this->view->arrRanks = $objRanks->ranks_select_by_institution($this->view->institution_id);
            }
        }

		public function ranksprinthebrewschool1Action()
		{
			$query = new QueryGen();
			$objUsers = new Users();
			$arrUsers = $this->view->arrUsers = $objUsers->_users_select_hierarchal(array(
				"institution_id" => $this->_user_session_data->institution_id,
				"permission" => "Student"
			));
			$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
				"institution_id" => $this->_user_session_data->institution_id,
			)));
		}

        public function ranksprintlistAction()
        {
            $objRanks = new Ranks();
			$this->view->class_id = $class_id = intval($this->_request->getParam("class_id"));
			$this->view->institution_id = $institution_id = intval($this->_request->getParam("institution_id"));
			if (!$institution_id)
				$institution_id = $this->_user_session_data->institution_id;
			$this->view->arrStudentRanks = $objRanks->rank_cards_select(array(
				"institution_id"    => $institution_id,
				"class_id"  		=> $class_id
			));
        }

        public function ranksprintAction()
        {
            $objRanks = new Ranks();
            $this->view->card_type = $this->_request->card_type;
            $mode1 = $this->_request->updateMode1 == 'SendtoTH' ? "host" : "";
            $mode2 = $this->_request->updateMode2 == 'Redeem' ? "Redeemed" : "";
            //var_dump($_POST); exit;
            if(!isset($_POST["user_rank_id"]))
            {
                $this->view->arrStudentRankCards = array(); //return empty array
            }
            if($mode2 == "Redeemed")
            {
                $user_id = $this->_request->user_id;
                $userRankId = $_POST["user_rank_id"];
                foreach($_POST["user_rank_id"] as $key => $value)
                {
                    $arrIds[] = $value;
                }
                $result = $objRanks->user_rank_update($arrIds);
                print 1;
                exit;
            }
            if($mode1 == "host")
            {
                foreach($_POST["user_rank_id"] as $key => $value)
                {
                    $arrIds[] = $value;
                }
                $result = $objRanks->user_rank_send_to_th($arrIds);
                $this->_send_notice_to_th();
                print 1;
                exit;
            }
            else
            {
                $user_id = $this->_request->user_id;
                $userRankId = $_POST["user_rank_id"];
                if($this->_user_session_data->permission == "Super Administrator")
                {
                    foreach($_POST["user_rank_id"] as $key => $value)
                    {
                        $arrIds[] = $value;
                    }
                    //var_dump($arrIds);
                    $this->view->arrStudentRanks = $objRanks->batch_print_th($arrIds);
                }
                else
                {
                    foreach($_POST["user_rank_id"] as $key => $value)
                    {
                        $arrIds[] = $value;
                    }
                    $this->view->arrStudentRanks = $objRanks->batch_print($arrIds);
                }
            }
        }
        public function ranksprinteditAction()
        {
            $user_rank_id = $this->_request->user_rank_id;
            $institution_id = $this->_request->institution_id;
            $class_id = $this->_request->class_id;

            $objUserRanks = new Ranks();
            $this->view->arrStudentRanks = $objUserRanks->batch_print(array("user_rank_id" => $user_rank_id));
        }
        // RANKS //
        private function _send_notice_to_th()
        {
            $institution = $this->_user_session_data->institution_name;

            $subject = 'Request for printing of permanent ID cards';
            $fromEmail = 'noreply@mashpia.com';
            $fromName = 'Mashpia';

            $bodyText = 'Dear Tzivos Hashem,<br>
            Camp '.$institution.' has just sent a request for permanent ID cards to be printed at TH Headquarters.<br>
            Please print them as soon as you can.<br>
            Thank You.';

            $bodyHtml = 'Dear Tzivos Hashem,<br>
            Camp '.$institution.' has just sent a request for permanent ID cards to be printed at TH Headquarters.<br>
            Please print them as soon as you can.<br>
            Thank You.';

            $mail = new Zend_Mail();
            $mail->setBodyText($bodyText);
            $mail->setBodyHtml($bodyHtml);
            $mail->setFrom($fromEmail, $fromName);
            $mail->addTo("roman.korb@gmail.com");
            $mail->addTo("shimmy@jcm.museum");
            $mail->addTo('shimmy@tzivoshashem.org');
            $mail->addTo('naftolir@gmail.com');
            $mail->addTo('chaniem@jcm.museum');
            $mail->setSubject($subject);
            $mail->send();

            return true;
        }
    }
?>