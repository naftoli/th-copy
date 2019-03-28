<?php
class QueryGen
{
	private $_db;
	private $_user_session_data;
	private $cells = array(
		'ckids_mission_cards' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"order_id",
					"user_id",
					"order_status",
					"modified",
					"created"
				)
			)
		),
		'ckids_mission_app' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"task_id",
					"network_id",
					"card_serial",
					"holiday_name",
					"code",
					"description",
					"access_level",
					"badge",
					"date_label",
					"start_date",
					"end_date",
					"pic_source",
					"image_id",
					"modified",
					"created"
				)
			)
		),
		'ckids_mission_networks' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"network_id",
					"network_name",
					"created"
				)
			)
		),
		'ckids_mission_marking' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"marking_id",
					"network_id",
					"task_id",
					"user_id",
					"modifed",
					"created"
				)
			)
		),
		'error_reports' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"error_report_id",
					"user_id",
					"institution_id",
					"permission_id",
					"sequence",
					"code",
					"location",
					"message",
					"other",
					"created"
				)
			)
		),
		'networks' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"network_id",
					"network_name",
					'network_keyword',
					"network_email",
					'admin_user_id',
					'network_terminology',
					'image_id',
					"modified",
					"created"
				)
			)
		),
		'network_alerts' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"network_alert_id",
					"network_id",
					'alert_location',
					"alert_email",
					'created'
				)
			)
		),
		'temp_missions_statuses' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_id",
					"institution_id",
					"missions",
					"status_msg",
					"created"
				)
			)
		),
		"announcement_students" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"announcement_student_id",
					"announcement_id",
					"user_id",
					"created",
					"created_by"
				)
			)
		),
		"announcement_classes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"announcement_class_id",
					"announcement_id",
					"class_id",
					"created",
					"created_by"
				)
			)
		),
		"announcement_institutions" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"announcement_institution_id",
					"announcement_id",
					"institution_id",
					"created",
					"created_by"
				)
			)
		),
		"announcements" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"announcement_id",
					"class_id",
					"institution_id",
					'campaign_id',
					'task_id',
					"status",
					"reason",
					"headline",
					"content",
					"modified",
					"created",
					"created_by"
				)
			)
		),
		"achievementcards2" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"barcode",
					"serial",
					"points"
				)
			)
		),
		"achievement_cards" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"achievement_card_id",
					"institution_id",
					"campaign_id",
					"mission_id",
					"task_id",
					"class_id",
					"card_serial",
					"card_points",
					"card_type",
					"left_circle",
					"right_circle",
					"campaign_image_id",
					"achievement",
					"status",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"admin_credits" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"admin_credit_id",
					"institution_id",
					"user_id",
					"credit_title",
					"credit_amount",
					"credit_description",
					'start_epoch',
					'end_epoch',
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"app_language_pref" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"app_language_pref_id",
					"institution_id",
					"user_id",
					"section",
					"language_id"
				)
			)
		),
		"app_text" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"app_text_id",
					"primary_app_text_id",
					"institution_id",
					"app_name",
					"permission",
					"resource_name",
					"controller",
					"action",
					"resource_id",
					"priority",
					"language_id",
					"order_found",
					"content",
					"created_by",
					"modified",
					"created"
				)
			)
		),
		"app_keywords" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"app_keyword_id",
					"primary_app_keyword_id",
					'app_keyword_type',
					"language_id",
					"app_name",
					"institution_id",
					"content",
					"created_by",
					"modified",
					"created"
				)
			)
		),
		'app_keyword_types' => array(
			"arrAllowed" => array(
				"arrAll" => array(
					'app_keyword_type_id',
					'keyword_type'
				),
			),
		),
		"app_text_languages" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"app_text_language_id",
					"app_text_language",
					"hierarchy",
					"is_active"
				)
			)
		),
		"auth_card_orders" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"auth_card_order_id",
					'date_completed',
					'institution_id',
					"confirmation_code",
					"creditcard_first_name",
					"creditcard_last_name",
					"creditcard_name",
					"creditcard_number",
					"creditcard_ccv",
					"creditcard_expiration_month",
					"creditcard_expiration_year",
					"shipping_address",
					"shipping_city",
					"shipping_state",
					"shipping_postal",
					"shipping_country",
					"order_processed_date",
					"price_per_unit",
					"quantity_purchased",
					"sub_total",
					"user_ids",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"auth_cards" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"auth_card_id",
					"user_id",
					"rank_id",
					"institution_id",
					"card_expires",
					"date_printed",
					"host_printed",
					"date_card_ordered",
					"auth_card_order_id",
					"date_card_redeemed",
					"card_status",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"campaign_school_types" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"campaign_school_type_id",
					"campaign_id",
					"school_type"
				)
			)
		),
		"campaigns" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"campaign_id",
					"installed_campaign_id",
					"default_installed",
					"campaign_name",
					"description",
					"commitments",
					"slogan",
					"campaign_photo",
					"campaign_gold_photo",
					"campaign_black_photo",
					"campaign_type",
					"institution_id",
					"network_id",
					"is_active",
					"ladder",
					"points",
					"medals",
					"ranks",
					"is_editable",
					"image_id",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"classes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"class_id",
					"class_name",
					"class_hierarchy",
					"institution_id",
					"grade",
					"grade_id",
					"sub",
					"gender",
					"is_active",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"config_settings" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"config_setting_id",
					"institution_id",
					"class_id",
					"user_id",
					"set",
					"key",
					"val",
					"modified",
					"created"
				)
			)
		),
		"grades" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"grade_id",
					"institution_id",
					"grade_hierarchy",
					"grade_name",
					"created",
					"created_by"
				)
			)
		),
		"institutions" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"institution_id",
					"reg_expires",
					"template_style",
					"host_id",
					"network_id",
					"name",
					"hebrew_name",
					"is_active",
					"address",
					"city",
					"state",
					"country",
					"phone",
					"postal",
					"email",
					"website",
					"image_id",
					"light_image_id",
					"custom_fields",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"legacy_lookup" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"legacy_lookup_id",
					"legacy_id",
					"ims_id",
					"legacy_table",
					"ims_table",
					"modified"
				)
			)
		),
		"medals" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"medal_id",
					"institution_id",
					"campaign_id",
					"medal_hierarchy",
					"medal_name",
					"medal_value",
					"medal_image_id",
					"medal_image_id_2",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"missions" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"mission_id",
					"installed_mission_id",
					"mission_name",
					"mission_type",
					"campaign_id",
					"book_id",
					"book_measurement",
					"institution_id",
					"start_date",
					"end_date",
					"points_up",
					"medal_up",
					"rank_up",
					"sequence",
					"is_active",
					"percentage_required",
					"default_velocity",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"permissions" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"permission_id",
					"template_style",
					"registration_expiration",
					"registration_date",
					"user_id",
					"institution_id",
					"permission",
					"auth_hash",
					"default_permission",
					"registration_location",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"prize_classes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"prize_class_id",
					"prize_id",
					"class_id"
				)
			)
		),
		"prize_school_types" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"prize_class_id",
					"prize_id",
					"school_type"
				)
			)
		),
		"prize_sizes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"prize_size_id",
					"prize_id",
					"prize_size_hierarchy",
					"prize_size",
					"created_by"
				)
			)
		),
		"prizes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"prize_id",
					"template_prize_id",
					"parent_prize_id",
					"legacy_add_on_id",
					"teacher_id",
					"guardian_id",
					"institution_id",
					"network_id",
					"prize_name",
					"prize_category",
					"bar_code",
					"prize_description",
					"image_id",
					"add_on_restricted",
					"use_sub_prizes",
					"one_per_user",
					"prize_count",
					"points",
					"prize_type",
					"installable_default_on",
					"prize_price",
					"prize_discounted_price",
					"is_active",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"payment_processes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"payment_process_id",
					"user_id",
					"institution_id",
					"amount",
					"response",
					"created",
					"created_by"
				)
			)
		),
		"ranks" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"rank_id",
					"institution_id",
					"rank_title",
					"rank_points",
					"rank_image",
					"created",
					"modifed",
					"created_by"
				)
			)
		),
		"tasks" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"task_id",
					"installed_task_id",
					"school_type",
					"task_name",
					"mission_id",
					"campaign_id",
					"class_id",
					"institution_id",
					"points",
					"min_points",
					"max_points",
					"frequency",
					"start_date",
					"end_date",
					"sequence",
					"velocity",
					"is_checkbox",
					"is_locked",
					"is_card",
					"is_grid",
					"is_required",
					"is_active",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"user_addons" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_addon_id",
					"user_id",
					"prize_id",
					"expires",
					"created",
					"created_by"
				)
			)
		),
		"user_campaign_progress" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_campaign_progress_id",
					"institution_id",
					"campaign_id",
					"user_id",
					"current_line",
					"campaign_goal",
					"modified"
				)
			)
		),
		"user_campaign_logs" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_campaign_log_id",
					"institution_id",
					"campaign_id",
					"user_id",
					"campaign_goal",
					"log_date"
				)
			)
		),
		"user_campaigns" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_campaign_id",
					"user_id",
					"institution_id",
					"campaign_id",
					"mission_id",
					"mission_increment",
					"task_id",
					"class_id",
					"book_id",
					"task_increment",
					"status",
					"line_offset",
					"ladder",
					"ladder_velocity",
					"grade_hierarchy",
					"grade_velocity",
					"schedule_date",
					"input_value",
					"points_given",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"user_classes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_class_id",
					"institution_id",
					"class_id",
					"user_id",
					"class_role",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"user_orders" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_order_id",
					"confirmation_code",
					"api_confirmation_code",
					"user_registrations_list",
					"user_addons_list",
					"creditcard_first_name",
					"creditcard_last_name",
					"creditcard_number",
					"creditcard_ccv",
					"creditcard_name",
					"creditcard_expiration_month",
					"creditcard_expiration_year",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"user_points" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_point_id",
					"reversed_user_point_id",
					"prize_id",
					"user_prize_id",
					"achievement_card_id",
					"user_id",
					"campaign_id",
					"mission_id",
					"task_id",
					"institution_id",
					"class_id",
					"points",
					"rule_id",
					"resource_name",
					"description",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"user_points_backup" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_point_id",
					"reversed_user_point_id",
					"prize_id",
					"user_prize_id",
					"achievement_card_id",
					"user_id",
					"campaign_id",
					"mission_id",
					"task_id",
					"institution_id",
					"class_id",
					"points",
					"rule_id",
					"resource_name",
					"description",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"user_prizes" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_prize_id",
					"prize_id",
					"user_id",
					"institution_id",
					"quantity",
					"prize_size",
					"serial",
					"status",
					"is_reversed",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"users" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_id",
					"old_user_id",
					"email",
					"student_email",
					"password",
					"bar_code",
					"user_serial",
					"first_name",
					"last_name",
					"hebrew_first_name",
					"hebrew_last_name",
					"user_start_date",
					"dob",
					"dob_he",
					"dob_he_offset",
					"gender",
					"address",
					"city",
					"state",
					"country",
					"postal",
					"phone",
					"cell",
					"image_id",
					"is_active",
					"custom_fields",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"users_deleted" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"user_id",
					"old_user_id",
					"email",
					"password",
					"bar_code",
					"user_serial",
					"first_name",
					"last_name",
					"hebrew_first_name",
					"hebrew_last_name",
					"user_start_date",
					"dob",
					"dob_he",
					"dob_he_offset",
					"gender",
					"address",
					"city",
					"state",
					"country",
					"postal",
					"phone",
					"image_id",
					"is_active",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"velocity_grades" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"velocity_grade_id",
					"campaign_id",
					"grade_hierarchy",
					"velocity",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"velocity_ladders" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"velocity_ladder_id",
					"campaign_id",
					"ladder",
					"velocity",
					"created",
					"modified",
					"created_by"
				)
			)
		),
		"slow_queries" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					"slow_query_id",
					"seconds",
					"data",
					"created_by",
					"created"
				)
			)
		),
		"registration_orders" => array(
			"arrAllowed" => array(
				"arrAll" => array(
					'registration_orders_id',
					'user_confirmation_code',
					'api_confirmation_code',
					'institution_id',
					'user_id',
					'template_style',
					'administrator_first_name',
					'administrator_last_name',
					'administrator_email',
					'administrator_phone_number',
					'administrator_cell_phone',
					'administrator_address',
					'administrator_city',
					'administrator_postal',
					'administrator_state',
					'administrator_country',
					'institution_name',
					'institution_type',
					'institution_address',
					'institution_city',
					'institution_state',
					'institution_postal',
					'institution_country',
					'institution_phone',
					'institution_email',
					'institution_website',
					'kioskaccessories_regular',
					'kioskaccessories_campers',
					'kioskaccessories_sponsored',
					'kioskaccessories_rental',
					'kioskaccessories_scanner',
					'kioskaccessories_handbook',
					'billing_first_name',
					'billing_last_name',
					'billing_phone_number',
					'billing_address',
					'billing_city',
					'billing_postal',
					'billing_state',
					'billing_country',
					'shipping_first_name',
					'shipping_last_name',
					'shipping_phone_number',
					'shipping_address',
					'shipping_city',
					'shipping_postal',
					'shipping_state',
					'shipping_country',
					'creditcard_name',
					'creditcard_number',
					'creditcard_expiration_month',
					'creditcard_expiration_year',
					'creditcard_ccv',
					'created'
				)
			)
		)
	);

	public function __construct()
	{
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
   	}
	public function network_alerts__select ($arrParams)
	{
		$strTableName = "network_alerts";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function network_alerts__insert ($arrParams)
	{
		$strTableName = "network_alerts";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function network_alerts__update ($arrParams)
	{
		$strTableName = "network_alerts";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function network_alerts__delete ($arrParams)
	{
		$strTableName = "network_alerts";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function error_reports__select ($arrParams)
	{
		$strTableName = "error_reports";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function error_reports__insert ($arrParams)
	{
		$strTableName = "error_reports";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function error_reports__update ($arrParams)
	{
		$strTableName = "error_reports";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function error_reports__delete ($arrParams)
	{
		$strTableName = "error_reports";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	
	
	public function ckids_mission_networks__select ($arrParams)
	{
		$strTableName = "ckids_mission_networks";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function ckids_mission_networks__insert ($arrParams)
	{
		$strTableName = "ckids_mission_networks";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function ckids_mission_networks__update ($arrParams)
	{
		$strTableName = "ckids_mission_networks";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function ckids_mission_networks__delete ($arrParams)
	{
		$strTableName = "ckids_mission_networks";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	
	
	public function ckids_mission_marking__select ($arrParams)
	{
		$strTableName = "ckids_mission_marking";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function ckids_mission_marking__insert ($arrParams)
	{
		$strTableName = "ckids_mission_marking";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function ckids_mission_marking__update ($arrParams)
	{
		$strTableName = "ckids_mission_marking";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function ckids_mission_marking__delete ($arrParams)
	{
		$strTableName = "ckids_mission_marking";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function ckids_mission_cards__select ($arrParams)
	{
		$strTableName = "ckids_mission_cards";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function ckids_mission_cards__insert ($arrParams)
	{
		$strTableName = "ckids_mission_cards";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function ckids_mission_cards__update ($arrParams)
	{
		$strTableName = "ckids_mission_cards";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function ckids_mission_cards__delete ($arrParams)
	{
		$strTableName = "ckids_mission_cards";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function ckids_mission_app__select ($arrParams)
	{
		$strTableName = "ckids_mission_app";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function ckids_mission_app__insert ($arrParams)
	{
		$strTableName = "ckids_mission_app";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function ckids_mission_app__update ($arrParams)
	{
		$strTableName = "ckids_mission_app";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function ckids_mission_app__delete ($arrParams)
	{
		$strTableName = "ckids_mission_app";
		return $this->_gen_delete($strTableName, $arrParams);
	}

	public function networks__select ($arrParams)
	{
		$strTableName = "networks";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function networks__insert ($arrParams)
	{
		$strTableName = "networks";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function networks__update ($arrParams)
	{
		$strTableName = "networks";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function networks__delete ($arrParams)
	{
		$strTableName = "networks";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function temp_missions_statuses__select ($arrParams)
	{
		$strTableName = "temp_missions_statuses";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function temp_missions_statuses__insert ($arrParams)
	{
		$strTableName = "temp_missions_statuses";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function registration_orders__select ($arrParams)
	{
		$strTableName = "registration_orders";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function registration_orders__insert ($arrParams)
	{
		$strTableName = "registration_orders";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function registration_orders__update ($arrParams)
	{
		$strTableName = "registration_orders";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function registration_orders__delete ($arrParams)
	{
		$strTableName = "registration_orders";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function announcements__select ($arrParams)
	{
		$strTableName = "announcements";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function announcements__insert ($arrParams)
	{
		$strTableName = "announcements";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function announcements__update ($arrParams)
	{
		$strTableName = "announcements";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function announcements__delete ($arrParams)
	{
		$strTableName = "announcements";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function announcement_students__select ($arrParams)
	{
		$strTableName = "announcement_students";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function announcement_students__insert ($arrParams)
	{
		$strTableName = "announcement_students";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function announcement_students__update ($arrParams)
	{
		$strTableName = "announcement_students";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function announcement_students__delete ($arrParams)
	{
		$strTableName = "announcement_students";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function announcement_classes__select ($arrParams)
	{
		$strTableName = "announcement_classes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function announcement_classes__insert ($arrParams)
	{
		$strTableName = "announcement_classes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function announcement_classes__update ($arrParams)
	{
		$strTableName = "announcement_classes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function announcement_classes__delete ($arrParams)
	{
		$strTableName = "announcement_classes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function announcement_institutions__select ($arrParams)
	{
		$strTableName = "announcement_institutions";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function announcement_institutions__insert ($arrParams)
	{
		$strTableName = "announcement_institutions";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function announcement_institutions__update ($arrParams)
	{
		$strTableName = "announcement_institutions";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function announcement_institutions__delete ($arrParams)
	{
		$strTableName = "announcement_institutions";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function slow_queries__select ($arrParams)
	{
		$strTableName = "slow_queries";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function slow_queries__insert ($arrParams)
	{
		$strTableName = "slow_queries";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function slow_queries__update ($arrParams)
	{
		$strTableName = "slow_queries";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function slow_queries__delete ($arrParams)
	{
		$strTableName = "slow_queries";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function app_language_pref__select ($arrParams)
	{
		$strTableName = "app_language_pref";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function app_language_pref__insert ($arrParams)
	{
		$strTableName = "app_language_pref";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function app_language_pref__update ($arrParams)
	{
		$strTableName = "app_language_pref";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function app_language_pref__delete ($arrParams)
	{
		$strTableName = "app_language_pref";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function app_text_languages__select ($arrParams)
	{
		$strTableName = "app_text_languages";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function app_text_languages__insert ($arrParams)
	{
		$strTableName = "app_text_languages";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function app_text_languages__update ($arrParams)
	{
		$strTableName = "app_text_languages";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function app_text_languages__delete ($arrParams)
	{
		$strTableName = "app_text_languages";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function app_text__select ($arrParams)
	{
		$strTableName = "app_text";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function app_text__insert ($arrParams)
	{
		$strTableName = "app_text";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function app_text__update ($arrParams)
	{
		$strTableName = "app_text";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function app_text__delete ($arrParams)
	{
		$strTableName = "app_text";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function app_keywords__select ($arrParams)
	{
		$strTableName = "app_keywords";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function app_keywords__insert ($arrParams)
	{
		$strTableName = "app_keywords";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function app_keywords__update ($arrParams)
	{
		$strTableName = "app_keywords";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function app_keywords__delete ($arrParams)
	{
		$strTableName = "app_keywords";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function app_keyword_types__select ($arrParams)
	{
		$strTableName = "app_keyword_types";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function app_keyword_types__insert ($arrParams)
	{
		$strTableName = "app_keyword_types";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function app_keyword_types__update ($arrParams)
	{
		$strTableName = "app_keyword_types";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function app_keyword_types__delete ($arrParams)
	{
		$strTableName = "app_keyword_types";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function tasks__select ($arrParams)
	{
		$strTableName = "tasks";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function tasks__insert ($arrParams)
	{
		$strTableName = "tasks";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function tasks__update ($arrParams)
	{
		$strTableName = "tasks";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function tasks__delete ($arrParams)
	{
		$strTableName = "tasks";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function missions__select ($arrParams)
	{
		$strTableName = "missions";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function missions__insert ($arrParams)
	{
		$strTableName = "missions";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function missions__update ($arrParams)
	{
		$strTableName = "missions";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function missions__delete ($arrParams)
	{
		$strTableName = "missions";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function admin_credits__select ($arrParams)
	{
		$strTableName = "admin_credits";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function admin_credits__insert ($arrParams)
	{
		$strTableName = "admin_credits";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function admin_credits__update ($arrParams)
	{
		$strTableName = "admin_credits";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function admin_credits__delete ($arrParams)
	{
		$strTableName = "admin_credits";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_campaign_logs__select ($arrParams)
	{
		$strTableName = "user_campaign_logs";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_campaign_logs__insert ($arrParams)
	{
		$strTableName = "user_campaign_logs";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_campaign_logs__update ($arrParams)
	{
		$strTableName = "user_campaign_logs";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_campaign_logs__delete ($arrParams)
	{
		$strTableName = "user_campaign_logs";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_campaign_progress__select ($arrParams)
	{
		$strTableName = "user_campaign_progress";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_campaign_progress__insert ($arrParams)
	{
		$strTableName = "user_campaign_progress";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_campaign_progress__update ($arrParams)
	{
		$strTableName = "user_campaign_progress";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_campaign_progress__delete ($arrParams)
	{
		$strTableName = "user_campaign_progress";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function ranks__select ($arrParams)
	{
		$strTableName = "ranks";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function ranks__insert ($arrParams)
	{
		$strTableName = "ranks";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function ranks__update ($arrParams)
	{
		$strTableName = "ranks";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function ranks__delete ($arrParams)
	{
		$strTableName = "ranks";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function payment_processes__select ($arrParams)
	{
		$strTableName = "payment_processes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function payment_processes__insert ($arrParams)
	{
		$strTableName = "payment_processes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function payment_processes__update ($arrParams)
	{
		$strTableName = "payment_processes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function payment_processes__delete ($arrParams)
	{
		$strTableName = "payment_processes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function medals__select ($arrParams)
	{
		$strTableName = "medals";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function medals__insert ($arrParams)
	{
		$strTableName = "medals";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function medals__update ($arrParams)
	{
		$strTableName = "medals";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function medals__delete ($arrParams)
	{
		$strTableName = "medals";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_orders__select ($arrParams)
	{
		$strTableName = "user_orders";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_orders__insert ($arrParams)
	{
		$strTableName = "user_orders";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_orders__update ($arrParams)
	{
		$strTableName = "user_orders";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_orders__delete ($arrParams)
	{
		$strTableName = "user_orders";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_addons__select ($arrParams)
	{
		$strTableName = "user_addons";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_addons__insert ($arrParams)
	{
		$strTableName = "user_addons";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_addons__update ($arrParams)
	{
		$strTableName = "user_addons";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_addons__delete ($arrParams)
	{
		$strTableName = "user_addons";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function auth_card_orders__select ($arrParams)
	{
		$strTableName = "auth_card_orders";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function auth_card_orders__insert ($arrParams)
	{
		$strTableName = "auth_card_orders";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function auth_card_orders__update ($arrParams)
	{
		$strTableName = "auth_card_orders";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function auth_card_orders__delete ($arrParams)
	{
		$strTableName = "auth_card_orders";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function auth_cards__select ($arrParams)
	{
		$strTableName = "auth_cards";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function auth_cards__insert ($arrParams)
	{
		$strTableName = "auth_cards";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function auth_cards__update ($arrParams)
	{
		$strTableName = "auth_cards";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function auth_cards__delete ($arrParams)
	{
		$strTableName = "auth_cards";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function config_settings__select ($arrParams)
	{
		$strTableName = "config_settings";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function config_settings__insert ($arrParams)
	{
		$strTableName = "config_settings";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function config_settings__update ($arrParams)
	{
		$strTableName = "config_settings";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function config_settings__delete ($arrParams)
	{
		$strTableName = "config_settings";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function permissions__select ($arrParams)
	{
		$strTableName = "permissions";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function permissions__insert ($arrParams)
	{
		$strTableName = "permissions";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function permissions__update ($arrParams)
	{
		$strTableName = "permissions";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function permissions__delete ($arrParams)
	{
		$strTableName = "permissions";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function legacy_lookup__select ($arrParams)
	{
		$strTableName = "legacy_lookup";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function legacy_lookup__insert ($arrParams)
	{
		$strTableName = "legacy_lookup";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function legacy_lookup__update ($arrParams)
	{
		$strTableName = "legacy_lookup";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function legacy_lookup__delete ($arrParams)
	{
		$strTableName = "legacy_lookup";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function achievementcards2__select ($arrParams)
	{
		$strTableName = "achievementcards2";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function achievementcards2__insert ($arrParams)
	{
		$strTableName = "achievementcards2";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function achievementcards2__update ($arrParams)
	{
		$strTableName = "achievementcards2";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function achievementcards2__delete ($arrParams)
	{
		$strTableName = "achievementcards2";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function achievement_cards__select ($arrParams)
	{
		$strTableName = "achievement_cards";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function achievement_cards__insert ($arrParams)
	{
		$strTableName = "achievement_cards";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function achievement_cards__update ($arrParams)
	{
		$strTableName = "achievement_cards";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function achievement_cards__delete ($arrParams)
	{
		$strTableName = "achievement_cards";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function institutions__select ($arrParams)
	{
		$strTableName = "institutions";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function institutions__insert ($arrParams)
	{
		$strTableName = "institutions";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function institutions__update ($arrParams)
	{
		$strTableName = "institutions";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function institutions__delete ($arrParams)
	{
		$strTableName = "institutions";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_points__select ($arrParams)
	{
		$strTableName = "user_points";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_points__insert ($arrParams)
	{
		$strTableName = "user_points";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_points__update ($arrParams)
	{
		$strTableName = "user_points";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_points__delete ($arrParams)
	{
		$strTableName = "user_points";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	
	
	
	public function user_points_backup__select ($arrParams)
	{
		$strTableName = "user_points_backup";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_points_backup__insert ($arrParams)
	{
		$strTableName = "user_points_backup";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_points_backup__update ($arrParams)
	{
		$strTableName = "user_points_backup";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_points_backup__delete ($arrParams)
	{
		$strTableName = "user_points_backup";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	
	
	
	public function velocity_ladders__select ($arrParams)
	{
		$strTableName = "velocity_ladders";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function velocity_ladders__insert ($arrParams)
	{
		$strTableName = "velocity_ladders";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function velocity_ladders__update ($arrParams)
	{
		$strTableName = "velocity_ladders";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function velocity_ladders__delete ($arrParams)
	{
		$strTableName = "velocity_ladders";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function velocity_grades__select ($arrParams)
	{
		$strTableName = "velocity_grades";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function velocity_grades__insert ($arrParams)
	{
		$strTableName = "velocity_grades";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function velocity_grades__update ($arrParams)
	{
		$strTableName = "velocity_grades";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function velocity_grades__delete ($arrParams)
	{
		$strTableName = "velocity_grades";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function grades__select ($arrParams)
	{
		$strTableName = "grades";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function grades__insert ($arrParams)
	{
		$strTableName = "grades";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function grades__update ($arrParams)
	{
		$strTableName = "grades";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function grades__delete ($arrParams)
	{
		$strTableName = "grades";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_classes__select ($arrParams)
	{
		$strTableName = "user_classes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_classes__insert ($arrParams)
	{
		$strTableName = "user_classes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_classes__update ($arrParams)
	{
		$strTableName = "user_classes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_classes__delete ($arrParams)
	{
		$strTableName = "user_classes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function classes__select ($arrParams)
	{
		$strTableName = "classes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function classes__insert ($arrParams)
	{
		$strTableName = "classes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function classes__update ($arrParams)
	{
		$strTableName = "classes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function classes__delete ($arrParams)
	{
		$strTableName = "classes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function users_deleted__select ($arrParams)
	{
		$strTableName = "users_deleted";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function users_deleted__insert ($arrParams)
	{
		$strTableName = "users_deleted";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function users_deleted__update ($arrParams)
	{
		$strTableName = "users_deleted";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function users_deleted__delete ($arrParams)
	{
		$strTableName = "users_deleted";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function users__select ($arrParams)
	{
		$strTableName = "users";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function users__insert ($arrParams)
	{
		$strTableName = "users";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function users__update ($arrParams)
	{
		$strTableName = "users";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function users__delete ($arrParams)
	{
		$strTableName = "users";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function campaigns__select ($arrParams)
	{
		$strTableName = "campaigns";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function campaigns__insert ($arrParams)
	{
		$strTableName = "campaigns";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function campaigns__update ($arrParams)
	{
		$strTableName = "campaigns";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function campaigns__delete ($arrParams)
	{
		$strTableName = "campaigns";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_campaigns__select ($arrParams)
	{
		$strTableName = "user_campaigns";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_campaigns__insert ($arrParams)
	{
		$strTableName = "user_campaigns";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_campaigns__update ($arrParams)
	{
		$strTableName = "user_campaigns";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_campaigns__delete ($arrParams)
	{
		$strTableName = "user_campaigns";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function campaign_school_types__select ($arrParams)
	{
		$strTableName = "campaign_school_types";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function campaign_school_types__insert ($arrParams)
	{
		$strTableName = "campaign_school_types";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function campaign_school_types__update ($arrParams)
	{
		$strTableName = "campaign_school_types";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function campaign_school_types__delete ($arrParams)
	{
		$strTableName = "campaign_school_types";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function prize__select ($arrParams)
	{
		$strTableName = "prizes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function prize__insert ($arrParams)
	{
		$strTableName = "prizes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function prize__update ($arrParams)
	{
		$strTableName = "prizes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function prize__delete ($arrParams)
	{
		$strTableName = "prizes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function prize_school_types__select ($arrParams)
	{
		$strTableName = "prize_school_types";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function prize_school_types__insert ($arrParams)
	{
		$strTableName = "prize_school_types";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function prize_school_types__update ($arrParams)
	{
		$strTableName = "prize_school_types";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function prize_school_types__delete ($arrParams)
	{
		$strTableName = "prize_school_types";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function prize_classes__select ($arrParams)
	{
		$strTableName = "prize_classes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function prize_classes__insert ($arrParams)
	{
		$strTableName = "prize_classes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function prize_classes__update ($arrParams)
	{
		$strTableName = "prize_classes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function prize_classes__delete ($arrParams)
	{
		$strTableName = "prize_classes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function prize_sizes__select ($arrParams)
	{
		$strTableName = "prize_sizes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function prize_sizes__insert ($arrParams)
	{
		$strTableName = "prize_sizes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function prize_sizes__update ($arrParams)
	{
		$strTableName = "prize_sizes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function prize_sizes__delete ($arrParams)
	{
		$strTableName = "prize_sizes";
		return $this->_gen_delete($strTableName, $arrParams);
	}
	public function user_prizes__select ($arrParams)
	{
		$strTableName = "user_prizes";
		return $this->_gen_select($strTableName, $arrParams);
	}
	public function user_prizes__insert ($arrParams)
	{
		$strTableName = "user_prizes";
		return $this->_gen_insert($strTableName, $arrParams);
	}
	public function user_prizes__update ($arrParams)
	{
		$strTableName = "user_prizes";
		return $this->_gen_update($strTableName, $arrParams);
	}
	public function user_prizes__delete ($arrParams)
	{
		$strTableName = "user_prizes";
		return $this->_gen_delete($strTableName, $arrParams);
	}

	// processing

	public function _current_user()
	{
		if (!$this->_user_session_data)
			$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		$intUser = @$this->_user_session_data->user_id;
		if ($intUser)
			return $intUser;
		$this->_user_session_data = new Zend_Session_Namespace('hebrewschools');
		$intUser = @$this->_user_session_data->user_id;
		if ($intUser)
			return $intUser;
		$this->_user_session_data = new Zend_Session_Namespace('kiosk_user_session_data');
		$intUser = @$this->_user_session_data->user_id;
		if ($intUser)
			return $intUser;
		return NULL;
	}

	private function _proc_select_params ($arrParams, $intIndentation=0)
	{
		$boolVerbose = FALSE;
		if (!$boolVerbose && isset($arrParams["_VERBOSE"]) && $arrParams["_VERBOSE"])
			$boolVerbose = TRUE;
		if (!isset($arrParams["strOperator"]))
			$arrParams["strOperator"] = "=";
		if (!isset($arrParams["strDelimiter"]))
			$arrParams["strDelimiter"] = "AND";
		if (!isset($arrParams["strWrapperStart"]))
			$arrParams["strWrapperStart"] = "";
		if (!isset($arrParams["strWrapperEnd"]))
			$arrParams["strWrapperEnd"] = "";

		$arrOperatorLogic = array(
			"_NORMAL" => array(
				"strWrapperStart" => "(",
				"strWrapperEnd" => ")",
				"strOperator" => "="
			),
			"_TIMESTAMP" => array(
				'_TIMESTAMP' => TRUE
			),
			"_NOT" => array(
				"strWrapperStart" => "!(",
				"strWrapperEnd" => ")",
				"strOperator" => "="
			),
			"_GREATER" => array(
				"strWrapperStart" => "(",
				"strWrapperEnd" =>  ")",
				"strOperator" => ">"
			),
			"_EGREATER" => array(
				"strWrapperStart" => "(",
				"strWrapperEnd" =>  ")",
				"strOperator" => ">="
			),
			"_LESSER" => array(
				"strWrapperStart" => "(",
				"strWrapperEnd" => ")",
				"strOperator" => "<"
			),
			"_ELESSER" => array(
				"strWrapperStart" => "(",
				"strWrapperEnd" => ")",
				"strOperator" => "<="
			)
		);

		$arrSql = array();
		$arrNewThreads = array();

		foreach ($arrParams["arrSelect"] as $strKey => $mixedValue)
		{
			if (is_integer($strKey))
			{
				$arrNewParams = array_merge($arrParams, $arrOperatorLogic["_NORMAL"]);
				$arrNewParams["arrSelect"] = $mixedValue;
				$arrNewParams["strDelimiter"] = $arrNewParams["strDelimiter"] == "AND" ? "OR" : "AND";
				$mixedResult = $this->_proc_select_params($arrNewParams, $intIndentation + 1);
				if (is_null($mixedResult))
					continue;
				$arrSql[] = $mixedResult;
			}
			else if (isset($arrOperatorLogic[$strKey]))
			{
				$arrNewParams = $arrParams;
				//$arrNewParams = array_merge($arrParams, $arrOperatorLogic["_NORMAL"]);
				$arrNewParams = array_merge($arrNewParams, $arrOperatorLogic[$strKey]);
				$arrNewParams["strDelimiter"] = isset($arrParams["_OR"]) ? "OR" : "AND";
				$arrNewParams["arrSelect"] = $mixedValue;
				$mixedResult = $this->_proc_select_params($arrNewParams, $intIndentation + 1);
				if (is_null($mixedResult))
					continue;
				$arrSql[] = $mixedResult;
			}
		}

		if (isset($arrParams["arrSelect"]["_OR"]))
			$arrParams["strDelimiter"] = "OR";
		else if (isset($arrParams["arrSelect"]["_AND"]))
			$arrParams["strDelimiter"] = "AND";
		$arrModulators = array("_IN");
		$arrColumns = array();

		foreach ($arrModulators as $strKey)
		{
			if (
				isset($arrParams["arrSelect"][$strKey])
				&& !is_null($arrParams["arrSelect"][$strKey])
			)
				$arrColumns[$strKey] = $arrParams["arrSelect"][$strKey];
		}

		foreach ($arrParams["arrAllowed"] as $strKey)
		{
			if (
				isset($arrParams["arrSelect"][$strKey])
				&& !is_null($arrParams["arrSelect"][$strKey])
			)
				$arrColumns[$strKey] = $arrParams["arrSelect"][$strKey];
		}
		if (count($arrColumns))
		{
			$mixedResult = $this->_gen_where_string2(
				$arrColumns,
				$arrParams
			);
			if (is_null($mixedResult))
				return NULL;
			$arrSql[] = $mixedResult;
		}

		$arrStaticKeys = array(
			"_IS_NOT_NULL" => "IS NOT NULL",
			"_IS_NOT" => "IS NOT",
			"_IS_NULL" => "IS NULL",
			"_IS" => "IS"
		);
		if (isset($arrParams["arrSelect"]["_NOT_NULL"]))
			$arrParams["arrSelect"]["_IS_NOT_NULL"] = $arrParams["arrSelect"]["_NOT_NULL"];
		foreach ($arrStaticKeys as $strStaticKey => $strStaticValue)
		{
			if (isset($arrParams["arrSelect"][$strStaticKey]))
			{
				if (!is_array($arrParams["arrSelect"][$strStaticKey]))
					$arrParams["arrSelect"][$strStaticKey] = array($arrParams["arrSelect"][$strStaticKey]);
				foreach ($arrParams["arrSelect"][$strStaticKey] as $strColName)
				{
					if (in_array($strColName, $arrParams["arrAllowed"]))
						$arrSql[] = "`" . $strColName . "` " . $strStaticValue;
				}
			}
		}
		//dumper($arrSql);
		$arrSql = array_flatten2($arrSql);
		//dumper($arrSql);
		if (!count($arrSql))
			return NULL;
		$strSql = $arrParams["strWrapperStart"];
		$strSql .= join(" " . $arrParams["strDelimiter"] . " ",  $arrSql);
		$strSql .= $arrParams["strWrapperEnd"];
		return $strSql;
	}

	private function _gen_select ($strTableName, $arrParams, $boolSqlOnly=FALSE)
	{
		$arrAllowed = $this->cells[$strTableName]["arrAllowed"]["arrAll"];
		$arrParams = (array) array_clean_sql($arrParams);

		$strSql = "
			SELECT ";

		// Special header functions
		$arrSelectTypes = array("max", "sum", "count");
		$arrSelectParams = array();
		$boolMode = FALSE;
		foreach ($arrSelectTypes as $strSelectType)
		{
			if (isset($arrParams["_" . strtoupper($strSelectType)]))
			{
				if (!is_array($arrParams["_" . strtoupper($strSelectType)]))
					$arrParams["_" . strtoupper($strSelectType)] = array($arrParams["_" . strtoupper($strSelectType)]);
				foreach ($arrParams["_" . strtoupper($strSelectType)] as $strSelectItem)
				{
					if (!empty($strSelectItem))
					{
						if ($strSelectType == "count")
							$boolMode = $strSelectType;
						if (in_array($strSelectItem, array("*")))
						{
							$strValue = "*";
							$strName = "`_" . $strSelectType . "`";
						}
						else
						{
							$strValue = "`" . $strSelectItem . "`";
							$strName = "`_" . $strSelectType . "_" . $strSelectItem . "`";
						}
						$arrSelectParams[] = strtoupper($strSelectType) . "(" . $strValue . ") AS " . $strName;
					}
				}
			}
		}

		// Query Header
		$arrQueryHeader = array();
		if (count($arrSelectParams))
			$arrQueryHeader[] = "
				" . join(" AND ", $arrSelectParams);

		if (isset($arrParams["_COLUMNS"]))
		{
			foreach ($arrParams["_COLUMNS"] as $strColName)
			{
				$arrQueryHeader[] = "
					" . $strTableName . "." . $strColName;
			}
		}
		else
		{
			$arrQueryHeader[] = "
				" . $strTableName . ".*";
		}

		$strSql .= join(",", $arrQueryHeader);

		// Data sources
		$strSql .= "
			FROM
				" . $strTableName . "
		";

		$mixedResult = $this->_proc_select_params(array(
			"arrSelect" => $arrParams,
			"arrAllowed" => $arrAllowed,
			"strTableName" => $strTableName
		));
		if (
			is_null($mixedResult)
			&& !(
				isset($arrParams["_ALL"])
				&& $arrParams["_ALL"]
			) && !(
				isset($arrParams["_VERBOSE"])
				&& $arrParams["_VERBOSE"]
				&& is_dev()
			)

		)
			return array();
		if (strlen($mixedResult))
			$strSql .= " WHERE " . $mixedResult;

		$arrExtraKeywords = array(
			"_GROUP" => "GROUP BY",
			"_ORDER" => "ORDER BY",
			"_LIMIT" => "LIMIT"
		);

		foreach ($arrExtraKeywords as $strKey => $strKeyword)
		{
			if (isset($arrParams[$strKey]))
				$strSql .= "
					" . $strKeyword . "
						" . $arrParams[$strKey];
		}
		if ($boolSqlOnly)
			return $strSql;
		$intTimeStart = time();
		if (isset($arrParams["_VERBOSE"]) && $arrParams["_VERBOSE"] && is_dev())
		{
			print '<div onclick="this.style.position=\'absolute\';this.style.zIndex=\'100000\';" style="overflow:auto;background-color:white !important;color:black !important;border:1px solid green !important;font-size:14pt !important;">arrParams:<br /><pre>';
			dumper($arrParams);print "<br />\n";
			print "</pre>strSql:<br />";print sql_format($strSql);print "</div>\n";
			if ($arrParams["_VERBOSE"] == 4)
			{
				$arrResult = $this->_db->fetchAll($strSql);
				dumper($arrResult,1,1);
			}
			if ($arrParams["_VERBOSE"] == 2)
				exit;
		}
		if (
			@$arrParams["_VERBOSE"] != 3
			&& !(
				is_null($mixedResult)
				&& !(
					isset($arrParams["_ALL"])
					&& $arrParams["_ALL"]
				) && !(
					isset($arrParams["_VERBOSE"])
					&& $arrParams["_VERBOSE"]
					&& is_dev()
				)
			)
		) {
			$arrResult = $this->_db->fetchAll($strSql);
			$intTimeEnd = time();
			$intTotalTime = $intTimeEnd - $intTimeStart;
			if ($intTotalTime >= 2)
			{
				$this->slow_queries__insert(array(
					"seconds" => $intTotalTime,
					"data" => $strSql
				));
			}
			return array_clean_slashes($arrResult);
		}
	}

	public function _gen_where_string2 ($arrData, $arrParams=array())
	{
		if (!isset($arrParams["strOperator"]))
			$arrParams["strOperator"] = "=";
		$arrSql = array();
		if (!is_array($arrData) || !count($arrData))
			return NULL;
		foreach ($arrData as $strColumn => $Value)
		{

			if (!is_null($Value))
			{
				if ($strColumn == "_IN")
				{
					$strSubTable = $arrParams["strTableName"];
					if (isset($Value["_TABLE"]))
						$strSubTable = $Value["_TABLE"];
					$strTopTable = $arrParams["strTableName"];
					$Value["_COLUMNS"] = array($Value["_DEPENDENT"]);
					$strSql = $this->_gen_select ($strSubTable, $Value, 1);
					$arrSql[] = "`".$Value["_INDEPENDENT"] . "` IN (" . $strSql . ")";
					// AND `" . $strSubTable . "`.`" . $Value["_DEPENDENT"] . "` = `" . $strTopTable . "`.`" . $Value["_INDEPENDENT"] . "`
				}
				else if (is_array($Value) && count($Value) > 1 && $arrParams["strOperator"]=="=")
				{
					if (!count($Value))
						return NULL;
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (is_null($Value1))
							continue;
						if (!is_int($Value1))
						{

							$Value1 = "'" . mysql_real_escape_string($Value1) . "'";
						}
						$arrValues[$Value1] = 1;

					}
					if (!count($arrValues))
						return NULL;
					$strSql = "";
					//dumper($arrParams,0,1);
					if (isset($arrParams["_TIMESTAMP"]))
						$strSql .= "unix_timestamp(";
					$strSql .= "`" . preg_replace("/\./", "`.`", $strColumn) . "`";
					if (isset($arrParams["_TIMESTAMP"]))
						$strSql .= ")";
					$strSql .= " IN (" . join(",", array_keys($arrValues)) . ")";
					$arrSql[] = $strSql;
				}
				else
				{
					if (is_array($Value))
						$Value = reset($Value);
					if (!is_int($Value))
						$Value = "'" . $Value . "'";
					$strSql = "";
					if (isset($arrParams["_TIMESTAMP"]))
					{
						$strSql .= "unix_timestamp(";
					}
					$strSql .= "`" . preg_replace("/\./", "`.`", $strColumn) . "`";
					if (isset($arrParams["_TIMESTAMP"]))
						$strSql .= ")";
					$strSql .= " " . $arrParams["strOperator"] . " " . $Value;
					$arrSql[] = $strSql;
				}
			}
		}
		return $arrSql;
	}

	private function _gen_insert ($strTableName, $arrParams)
	{
		$arrAllowed = $this->cells[$strTableName]["arrAllowed"]["arrAll"];
		$arrParams = (array) array_clean_sql($arrParams);

		if (
			in_array("created_by", $arrAllowed)
			&& (
				!isset($arrParams["created_by"])
				|| is_null($arrParams["created_by"])
			)
		) {
			$arrParams["created_by"] = $this->_current_user();
		}
		if (
			in_array("created", $arrAllowed)
			&& !isset($arrParams["created"])
		)
			$arrParams["created"] = date("Y-m-d H:i:s");

		$arrColumns = array();
		foreach ($arrAllowed as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrColumns[$strKey] = $arrParams[$strKey];
		}

		// Execute
		$boolResult = $this->_db->insert($strTableName, $arrColumns);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	private function _gen_update ($strTableName, $arrParams)
	{
		$arrAllowed = array(
			"where" => $this->cells[$strTableName]["arrAllowed"]["arrAll"],
			"values" => $this->cells[$strTableName]["arrAllowed"]["arrAll"]
		);
		$arrParams = (array) array_clean_sql($arrParams);

		$arrValues = array();
		$arrWhere = array();

		// Values
		foreach ($arrAllowed["values"] as $strKey)
		{
			if (isset($arrParams["values"][$strKey]) && !is_null($arrParams["values"][$strKey]))
				$arrValues[$strKey] = $arrParams["values"][$strKey];
		}
		if (in_array("modified", $arrAllowed))
			$arrValues["modified"] = date("Y-m-d H:i:S");
		// Where

		// check for an item which is not on the allow list because it could be
		// a typo. This is very important for updates and deletes
		foreach ($arrParams["where"] as $Key => $Value)
		{
			if (!in_array($Key, $arrAllowed["where"]))
			{
				throw new Exception("QueryGen Warning: Update missing allowance parameter \"$Key\".");
			}
		}

		foreach ($arrAllowed["where"] as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto("`" . $strKey . '` = ?', $arrParams["where"][$strKey]);
		}
		if (!count($arrWhere) || !count($arrValues))
			return false;
		// Execute
		//dumper($arrValues);
		//dumper($arrWhere);
		$intResult = $this->_db->update($strTableName, $arrValues, $arrWhere);
		return $intResult;
	}

	private function _gen_delete ($strTableName, $arrParams)
	{
		$arrAllowed = $this->cells[$strTableName]["arrAllowed"]["arrAll"];
		$arrParams = (array) array_clean_sql($arrParams);
		$arrFields = array();
		foreach ($arrAllowed as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFields[] = $this->_db->quoteInto('`' . $strKey . '` = ?', $arrParams[$strKey]);
		}
		if (!count($arrFields))
		{
			throw new Exception("QueryGen Warning: Delete without matching parameteres on `$strTableName`.");
			exit;
		}
		$boolResult = $this->_db->delete($strTableName, $arrFields);
		return $boolResult;
	}

	public function _proc_query_instructions2($arrSourceData, $arrNewData, $arrPrimaryKeys, $arrPertinentParams, $arrRequiredParams=array())
	{
		if (is_string($arrPrimaryKeys))
		{
			$arrPrimaryKeys = array($arrPrimaryKeys);
		}
		$arrResult = array(
			"_INSERT" => array(),
			"_UPDATE" => array(),
			"_DELETE" => array(),
			"_ERRORS" => array()
		);
		if (!count($arrPrimaryKeys))
		{
			// if there is nothing to match, insert is the only option
			foreach ($arrNewData as $arrData)
			{
				$intPertinent = 0;
				foreach ($arrPertinentParams as $strPertinentField)
				{
					if (isset($arrData[$strPertinentField]) && $arrData[$strPertinentField])
						$intPertinent++;
				}
				if ($intPertinent == count($arrPertinentParams))
					$arrResult["_INSERT"][] = $arrData;
			}
			return $arrResult;
		}

		// Unique keys
		$arrPrimaryKeys = array_keys(array_flip($arrPrimaryKeys));

		// All keys provided must be found within the data set or an insert must be performed
		$arrSourceDataHash = $arrNewDataHash = array();
		foreach ($arrSourceData as $objData)
		{
			$arrSourceDataHash[join("|", array_extract($arrPrimaryKeys, $objData))] = $objData;
		}
		foreach ($arrNewData as $objData)
		{
			$arrNewDataHash[join("|", array_extract($arrPrimaryKeys, $objData))] = $objData;
		}
		$arrNewData = array_reverse($arrNewData);
		foreach ($arrNewData as $objRow)
		{
			$arrRow = (array) $objRow;
			$strCustomKey = join("|", array_extract($arrPrimaryKeys, $objRow));

			foreach ($arrRequiredParams as $strRequiredParam)
			{
				if (!isset($arrRow[$strRequiredParam]) || !strlen($arrRow[$strRequiredParam]))
				{
					$arrResult["_ERRORS"][$strCustomKey][$strRequiredParam] = "The field is required.";
				}
			}

			if (!isset($arrSourceDataHash[$strCustomKey]))
			{
				$arrResult["_INSERT"][] = $objRow;
				continue;
			}
			if (isset($arrSourceDataHash[$strCustomKey]))
				$objSource = $arrSourceDataHash[$strCustomKey];
			if (isset($objSource))
			{
				$arrSource = (array) $objSource;
				$arrNewValues = array();
				foreach ($arrPertinentParams as $strPertinentParam)
				{
					if (
						@$arrRow[$strPertinentParam] != @$arrSource[$strPertinentParam]
					) {
						if (!isset($arrRow[$strPertinentParam]))
						{
							if (@$arrSource[$strPertinentParam] == "0")
								continue;
							$arrRow[$strPertinentParam] = "0";
						}
						$arrNewValues[$strPertinentParam] = $arrRow[$strPertinentParam];
					}
				}
				// do update
				if (count($arrNewValues))
				{
					$arrResult["_UPDATE"][] = array(
						"where" => array_extract($arrPrimaryKeys, $objRow),
						"values" => $arrNewValues
					);
				}
			}
		}
		// Look for deleted items
		foreach ($arrSourceDataHash as $objSourceData)
		{
			$arrSourceRow = (array) $objSourceData;
			$strCustomKey = join("|", array_extract($arrPrimaryKeys, $arrSourceRow));
			if (!isset($arrNewDataHash[$strCustomKey]))
			{
				$arrNewParams = array();
				$arrResult["_DELETE"][] = array_extract($arrPrimaryKeys, $arrSourceRow);
			}
		}
		return $arrResult;
	}


	// Depreciated. see: _gen_where_string2
	public function _gen_where_string ($arrData, $strPreFix="", $strSuffix="", $strOperator='=', $strDelimiter="AND")
	{
		$strSql = "";
		if (!is_array($arrData) || !count($arrData))
			return NULL;
		foreach ($arrData as $strColumn => $Value)
		{
			if (!is_null($Value))
			{
				if (is_array($Value) && count($Value) > 1 && $strOperator=="=")
				{
					if (!count($Value))
						return NULL;
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (is_null($Value1))
							continue;
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					if (!count($arrValues))
						return NULL;
					if (strlen($strSql))
						$strSql .= " " . $strDelimiter . " ";
					$strSql .= "
						`" . preg_replace("/\./", "`.`", $strColumn) . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
				{
					if (is_array($Value))
						$Value = reset($Value);
					if (!is_int($Value))
						$Value = '"' . $Value . '"';
					if (strlen($strSql))
						$strSql .= " " . $strDelimiter . " ";
					$strSql .= "
						`" . preg_replace("/\./", "`.`", $strColumn) . "` " . $strOperator . " " . $Value . "
					";
				}
			}
		}
		return strlen($strSql) ? $strPreFix . $strSql . $strSuffix : "";
	}

	/*
	 * Depreciated. See: _proc_query_instructions2
	 * Return with the new data along with _INSTRUCTIONS value which indicates
	 * what type of database action is required to do based what is pertinent.
	 */
	public function _proc_query_instructions($arrSourceData, $arrNewData, $strPrimaryKey, $arrPertinentParams)
	{
		$arrResult = array(
			"_INSERT" => array(),
			"_UPDATE" => array(),
			"_DELETE" => array()
		);
		// Find all inserts
		$arrSourceData = array_hash($strPrimaryKey, $arrSourceData);
		$arrNewDataHash = array_hash($strPrimaryKey, $arrNewData);
		$arrNewData = array_reverse($arrNewData);
		foreach ($arrNewData as $objRow)
		{
			$arrRow = (array) $objRow;
			// No primary key found, do insert
			if (!isset($arrRow[$strPrimaryKey]))
			{
				$arrResult["_INSERT"][] = $objRow;
			}
			if (isset($arrRow[$strPrimaryKey]) && isset($arrSourceData[$arrRow[$strPrimaryKey]]))
				$objSource = $arrSourceData[$arrRow[$strPrimaryKey]];
			if (isset($objSource))
			{
				$arrSource = (array) $objSource;
				$arrNewValues = array();
				foreach ($arrPertinentParams as $strPertinentParam)
				{
					if (@$arrRow[$strPertinentParam] != @$arrSource[$strPertinentParam])
					{
						$arrNewValues[$strPertinentParam] = @$arrRow[$strPertinentParam];
					}
				}
				// do update
				if (count($arrNewValues))
				{
					$arrResult["_UPDATE"][] = array(
						"where" => array(
							$strPrimaryKey => $arrSource[$strPrimaryKey]
						),
						"values" => $arrNewValues
					);
				}
			}
		}
		// Look for deleted items
		foreach ($arrSourceData as $objSourceData)
		{
			$arrSourceRow = (array) $objSourceData;
			if (!isset($arrNewDataHash[$arrSourceRow[$strPrimaryKey]]))
			{
				$arrResult["_DELETE"][] = array(
					$strPrimaryKey => $arrSourceRow[$strPrimaryKey]
				);
			}
		}
		return $arrResult;
	}

}