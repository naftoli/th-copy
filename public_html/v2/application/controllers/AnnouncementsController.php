<?php
class AnnouncementsController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function announcementsAction()
	{
		$query = new QueryGen();
		$roles = new Roles();
		$arrAnnouncementParams = array();
		$arrAnnouncementParams['created_by'] = $this->_user_session_data->user_id;
		$arrAnnouncementParams['institution_id'] = $this->_user_session_data->institution_id;
		$arrAnnouncements = $query->announcements__select($arrAnnouncementParams);
		$this->view->arrYourAnnouncements = array_bubble_hash('status',$arrAnnouncements);
		$arrAnnouncementParams = array();
		$arrAnnouncementParams['status'] = array('Publish Request', 'Denied Request');
		$arrAnnouncementParams['institution_id'] = $this->_user_session_data->institution_id;
		$arrAnnouncements = $query->announcements__select($arrAnnouncementParams);
		$this->view->arrClassAnnouncements = array_bubble_hash('status',$arrAnnouncements);
		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
	}

	public function posteditorAction()
	{
		$query = new QueryGen();
		$role = new Roles();
		$arrPost = $this->_request->getPost();
		$this->view->arrGet = $arrGet = $this->_request->getParams();
		$this->view->announcement_id = @$arrGet['announcement_id'];
		$this->view->arrAnnouncementStudents = array();
		$arrTeacherClassIds = array_stack('class_id', $query->user_classes__select(array(
			'user_id' => $this->_user_session_data->user_id,
			'class_role' => 'Teacher'
		)));
		$this->view->arrTeacherClasses = array_hash('class_id', $query->classes__select(array(
			'class_id' => $arrTeacherClassIds,
			'institution_id' => $this->_user_session_data->institution_id
		)));
		$objInstitution = first($query->institutions__select(array(
			'institution_id' => $this->_user_session_data->institution_id
		)));
		if (!isset($arrGet['announcement_id']))
			$arrGet['announcement_id'] = '';
		else
		{
			$arrAnnouncementParams = array(
				'announcement_id' => $arrGet['announcement_id']
			);
			if (!$role->isAllowed('Super Administrator'))
			{
				$arrAnnouncementParams['institution_id'] = $this->_user_session_data->institution_id;
			}
			$objAnnouncement = first($query->announcements__select($arrAnnouncementParams));
			if ($role->isAllowed('Super Administrator'))
			{
				$objInstitution = first($query->institutions__select(array(
					'institution_id' => $objAnnouncement->institution_id
				)));
			}
			$arrAnnouncementStudents = $this->view->arrAnnouncementStudents = $this->view->arrAnnouncementStudents = $query->announcement_students__select(array(
				'announcement_id' => $objAnnouncement->announcement_id
			));
			$arrStudents = $this->view->arrStudents = array_hash('user_id', $query->users__select(array(
				'user_id' => array_keys(array_stack('user_id', $arrAnnouncementStudents))
			)));
			$this->view->strStudentsOutput = http_build_query(array_stack('user_id',$arrAnnouncementStudents));
			$objAnnouncement->content = str_replace('|NEW_LINE|', '\n', $objAnnouncement->content);
			$this->view->objAnnouncement = $objAnnouncement;
			$this->view->arrAnnouncementClasses = $arrAnnouncementClasses = array_stack('class_id', $query->announcement_classes__select(array(
				'announcement_id' => $objAnnouncement->announcement_id
			)));
			$this->view->strAnnouncementClasses = http_build_query($arrAnnouncementClasses);
			if ($objAnnouncement->campaign_id)
			{
				$this->view->objCampaign = first($query->campaigns__select(array(
					'campaign_id' => $objAnnouncement->campaign_id
				)));
				if ($objAnnouncement->task_id)
					$this->view->objTask = first($query->tasks__select(array(
						'task_id' => $objAnnouncement->task_id
					)));
			}

		}
		$boolReadonly = FALSE;
		if (!isset($objAnnouncement))
			$boolReadonly = FALSE;
		else if ($objAnnouncement->created_by != $this->_user_session_data->user_id)
			$boolReadonly = TRUE;
		else if ($objAnnouncement->status == 'Published')
			$boolReadonly = TRUE;
		$this->view->boolReadonly = $boolReadonly;
		if (isset($arrGet['setter']))
		{
			if (isset($arrPost['deny_request']))
			{
				$query->announcement_classes__delete(array(
					'announcement_id' => $arrPost['announcement_id']
				));
				$query->announcements__update(array(
					'where' => array(
						'announcement_id' => $arrPost['announcement_id']
					),
					'values' => array(
						'status' => 'Denied Request',
						'reason' => $arrPost['reason']
					)
				));
			}
			else if (isset($arrPost['publish_to_classes']))
			{
				parse_str($arrPost['publish_to_classes'], $arrClassIds);
				// verify that you have access to these classes
				if ($role->isAllowed('Teacher'))
				{
					$arrClasses = array_hash('class_id', $query->user_classes__select(array(
						'user_id' => $this->_user_session_data->user_id,
						'class_id' => array_keys($arrClassIds)
					)));
					$query->announcement_classes__delete(array(
						'announcement_id' => $arrPost['announcement_id']
					));
					foreach ($arrClasses as $intClass => $objUserClass)
					{
						$query->announcement_classes__insert(array(
							'class_id' => $intClass,
							'announcement_id' => $arrPost['announcement_id']
						));
					}
				}
				print json_encode(array(
					'success' => 'true',
					'arrPost' => $arrPost,
					'arrClasses' => $arrClasses
				));
				exit;
			}

			if ($boolReadonly)
			{
				$arrAnnouncementsValues = array();
				if (isset($arrPost["remove_publish"]) && $arrPost["remove_publish"] == 'true')
				{
					$arrAnnouncementsValues['status'] = 'Saved';
				}
				else if (isset($arrPost["delete"]) && $arrPost["delete"] == 'true')
				{
					$arrAnnouncementsValues['status'] = 'Deleted';
				}
				else if (isset($arrPost["draft"]) && $arrPost["draft"] == 'true')
				{
					$arrAnnouncementsValues['status'] = 'Saved';
				}
				$query->announcements__update(array(
					'where' => array(
						'announcement_id' => $arrGet['announcement_id'],
						'institution_id' => $objInstitution->institution_id
					),
					'values' => $arrAnnouncementsValues
				));
				print json_encode(array(
					'success' => 'true',
					'announcement_id' => $arrGet['announcement_id']
				));
				exit;
			}
			$arrResult = array();
			// Validation
			if (empty($arrPost['headline']))
			{
				$arrResult['error']['headline'] = "The headline is a required feild.";
			} else if (strlen($arrPost['headline']) > 80)
			{
				$arrResult['error']['headline'] = "The headline must not contain more than 80 characters.";
			}
			if (empty($arrPost['content']))
			{
				$arrResult['error']['content'] = "You must provide some content.";
			} else if (strlen($arrPost['content']) > 65535)
			{
				$arrResult['error']['content'] = "The content must not contain more than 65,535 characters.";
			}
			if (isset($arrResult['error']))
			{
				print json_encode($arrResult);
				exit;
			}
			// Student validation
			if (@$objAnnouncement->status != 'Published')
			{
				parse_str($arrPost["associate_students"], $arrStudentIds);
				$arrStudents = $query->permissions__select(array(
					'user_id' => array_keys($arrStudentIds),
					'institution_id' => $objInstitution->institution_id
				));/*
				if (count($arrStudents) != count($arrStudentIds))
				{
					print json_encode(array(
						'error' => 'Sorry, there was an error: CA-PE101-sd098f'
					));
					exit;
				}*/
			}
			$strContent = $arrPost["content"];
			$strContent = preg_replace_all('/([^\\\\])\\n/', "$1|NEW_LINE|", $strContent);
			$strContent = preg_replace('/^\\n/', '|NEW_LINE|', $strContent);
			// Save announcement
			$arrAnnouncementsValues = array(
				'headline' => $arrPost["headline"],
				'content' => $strContent,
				'institution_id' => $objInstitution->institution_id
			);
			if (isset($arrPost["publish"]) && $arrPost["publish"] == 'true')
			{
				if ($role->isAllowed('Institution Administrator'))
					$arrAnnouncementsValues['status'] = 'Published';
				else
					$arrAnnouncementsValues['status'] = 'Publish Request';
			}
			else if (isset($arrPost["remove_publish"]) && $arrPost["remove_publish"] == 'true')
			{
				if ($role->isAllowed('Super Administrator'))
					$arrAnnouncementsValues['status'] = 'Denied Request';
				else
					$arrAnnouncementsValues['status'] = 'Saved';
			}
			else if (isset($arrPost["delete"]) && $arrPost["delete"] == 'true')
			{
				$arrAnnouncementsValues['status'] = 'Deleted';
			}
			else if (isset($arrPost["draft"]) && $arrPost["draft"] == 'true')
			{
				$arrAnnouncementsValues['status'] = 'Saved';
			}
			$boolNew = 0;
			if (!empty($arrGet['announcement_id']))
			{
				$query->announcements__update(array(
					'where' => array(
						'announcement_id' => $arrGet['announcement_id'],
						'institution_id' => $objInstitution->institution_id
					),
					'values' => $arrAnnouncementsValues
				));
			}
			else
			{
				$arrGet['announcement_id'] = $query->announcements__insert($arrAnnouncementsValues);
				$boolNew = 1;
			}
			// Save student association
			if (@$objAnnouncement->status != 'Published')
			{
				if (count($arrStudents))
				{
					if (!$boolNew)
					{
						$query->announcement_students__delete(array(
							'announcement_id' => $arrGet['announcement_id']
						));
					}
					foreach ($arrStudents as $objUser)
					{
						$query->announcement_students__insert(array(
							'announcement_id' => $arrGet['announcement_id'],
							'user_id' => $objUser->user_id
						));
					}
				}
			}
			print json_encode(array(
				'success' => 'true',
				'announcement_id' => $arrGet['announcement_id']
			));
			exit;
		}
	}
}
?>