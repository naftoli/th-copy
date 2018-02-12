/* GUIDELINES
 * 1- The primary key should always be the table_name_id (primary key should be the singular of the table_name if it plural)
 *
 * 2- Each table (except tables containing static data) must include the following fields
 * 			created 	DATETIME
 * 			modified 	TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 *			created_by 	FOREIGN KEY TO THE USERS TABLE
 *
 * 3- Do not limit text fields you do not know the length of.
 * If you need to define a varchar and you do not know if it will go beyond 255 characters, then define it as a text.
 *
 * 4- All Foreign key assignments referencing non-static data must include an "ON DELETE CASCADE" clause
 *
 * 5- All account-holding tables must include an is_active boolean field to define whether this account is active or not
 */

SET character_set_client = utf8;

/* institution_types holds the master list of the various types of institutions available on the system */
CREATE TABLE institution_types(
	institution_type		varchar(255)		NOT NULL,
	PRIMARY KEY (institution_type)
);

insert into institution_types values ('Host');
insert into institution_types values ('Network');
insert into institution_types values ('School');
insert into institution_types values ('Camp');
insert into institution_types values ('Synagogue');
insert into institution_types values ('Sunday School');
insert into institution_types values ('Day School');
insert into institution_types values ('Club');

DROP TABLE registration_orders;
CREATE TABLE registration_orders(
	registration_order_id			INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	user_confirmation_code			VARCHAR(30),
	api_confirmation_code			VARCHAR(30),
	institution_id					INTEGER(10)			UNSIGNED,
	user_id							INTEGER(10)			UNSIGNED,
	administrator_first_name		VARCHAR(255),
	administrator_last_name			VARCHAR(255),
	administrator_email				VARCHAR(255),
	administrator_password			VARCHAR(255),
	administrator_confirm_password	VARCHAR(255),
	administrator_address			VARCHAR(255),
	administrator_city				VARCHAR(255),
	administrator_postal	VARCHAR(255),
	administrator_state		VARCHAR(255),
	administrator_country	VARCHAR(255),
	administrator_phone		VARCHAR(255),
	institution_name		VARCHAR(255),
	institution_type		VARCHAR(255),
	institution_address		VARCHAR(255),
	institution_city		VARCHAR(255),
	institution_state		VARCHAR(255),
	institution_postal		VARCHAR(255),
	institution_country		VARCHAR(255),
	institution_phone		VARCHAR(255),
	institution_email		VARCHAR(255),
	institution_website		VARCHAR(255),
	kiosk_regular			INTEGER(10)					UNSIGNED,
	kiosk_sponsored			INTEGER(10)					UNSIGNED,
	kiosk_rental			INTEGER(10)					UNSIGNED,
	kiosk_scanner			INTEGER(10)					UNSIGNED,
	billing_first_name		VARCHAR(255),
	billing_last_name		VARCHAR(255),
	billing_address			VARCHAR(255),
	billing_city			VARCHAR(255),
	billing_postal			VARCHAR(255),
	billing_state			VARCHAR(255),
	billing_country			VARCHAR(255),
	shipping_first_name		VARCHAR(255),
	shipping_last_name		VARCHAR(255),
	shipping_address		VARCHAR(255),
	shipping_city			VARCHAR(255),
	shipping_postal			VARCHAR(255),
	shipping_state			VARCHAR(255),
	shipping_country		VARCHAR(255),
	cc_name					VARCHAR(255),
	cc_number				VARCHAR(255),
	ccv						VARCHAR(255),
	is_active				BOOLEAN						DEFAULT '1',
	created					DATETIME,
	modified				TIMESTAMP 					ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (registration_order_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/* institutions holds all the hosts, networks, schools, camps, etc... */
CREATE TABLE institutions(
	institution_id			INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	institution_type		VARCHAR(255)		NOT NULL,
	host_id					INTEGER				NOT NULL,
	network_id				INTEGER				NOT NULL,
	name					VARCHAR(255)		NOT NULL,
	hebrew_name				VARCHAR(255)		NOT NULL,
	is_active				BOOLEAN				DEFAULT '1',
	address					TEXT,
	city					VARCHAR(255),
	state					VARCHAR(255),
	country					VARCHAR(255),
	phone					VARCHAR(255),
	postal					varchar(255),
	email					VARCHAR(255),
	website					VARCHAR(255),
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (institution_id),
	FOREIGN KEY (institution_type) REFERENCES institution_types(institution_type),
	FOREIGN KEY (host_id) REFERENCES institutions (institution_id) ON DELETE CASCADE,
	FOREIGN KEY (network_id) REFERENCES institutions (institution_id) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8;
INSERT INTO institutions (institution_type,host_id,network_id,name,is_active,address,city,state,country,website,created,created_by) values ('Network',1,0,'IMS Network',1,'5111 De Courtrai','Montreal','Quebec','Canada','http://www.mashpia.com',now(),1);

/* users holds all the system users */
CREATE TABLE users(
	user_id					INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	old_user_id				INTEGER(10)	        UNSIGNED NOT NULL,        
	email					VARCHAR(255)		NOT NULL					UNIQUE,
	password				VARCHAR(255)		NOT NULL,
	image_id				INTEGER(10)			UNSIGNED,
	bar_code				VARCHAR(20),
	first_name				VARCHAR(255),
	last_name				VARCHAR(255),
	hebrew_first_name		varchar(255),
	hebrew_last_name		varchar(255),
	dob						VARCHAR(10), // 01/28/1984
	gender					ENUM('M', 'F'),
	is_active				BOOLEAN				DEFAULT '1',
	address					TEXT,
	city					VARCHAR(255),
	state					VARCHAR(255),
	country					VARCHAR(255),
	postal					VARCHAR(255),
	phone					VARCHAR(255),
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (user_id),
	UNIQUE KEY `email` (`email`),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=MyISAM AUTO_INCREMENT=8711 DEFAULT CHARSET=utf8;

insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('mohannad@icorpa.com',MD5('123456'),'Mohannad','El-Barachi',1,now(),1);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('roman@icorpa.com',MD5('123456'),'Reuven','Korb',1,now(),1);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('andy@icorpa.com',MD5('123456'),'Andy','Dear',1,now(),1);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('principal@icorpa.com',MD5('123456'),'Prin','Cipal',1,now(),2);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('teacher@icorpa.com',MD5('123456'),'Tea','Cher',1,now(),2);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('student@icorpa.com',MD5('123456'),'Stu','Dent',1,now(),2);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('roman@icorpa.com','123456','Reuven','Korb',1,now(),1);
insert into users (email,password,first_name,last_name,is_active,created,created_by) values ('andy@icorpa.com','123456','Andy','Dear',1,now(),1);


/* permission_types defines the various types of users available on the system */
CREATE TABLE permission_types(
	permission_id			INTEGER				UNSIGNED AUTO_INCREMENT,
	permission_type			varchar(255)		NOT NULL,
	PRIMARY KEY (permission_id)
);

insert into permission_types (permission_type) values ('Super Administrator');
insert into permission_types (permission_type) values ('Host Administrator');
insert into permission_types (permission_type) values ('Network Administrator');
insert into permission_types (permission_type) values ('Institution Administrator');
insert into permission_types (permission_type) values ('Teacher');
insert into permission_types (permission_type) values ('Parent');
insert into permission_types (permission_type) values ('Student');

/* permissions holds user access to individual institutions */
CREATE TABLE permissions (
	permission_id			INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	user_id					INTEGER				UNSIGNED NOT NULL,
	institution_id			INTEGER				UNSIGNED NOT NULL,
	permission				VARCHAR(255)		NOT NULL,
	default_permission		BOOLEAN				DEFAULT 1,
	created					DATETIME			NOT NULL,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (permission_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id) ON DELETE CASCADE,
	FOREIGN KEY (permission) REFERENCES permission_types(permission_type) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

insert into permissions (user_id,institution_id,permission,default_permission,created,created_by) values (1,1,'Super Administrator',1,now(),1);
insert into permissions (user_id,institution_id,permission,default_permission,created,created_by) values (2,1,'Super Administrator',1,now(),2);
insert into permissions (user_id,institution_id,permission,default_permission,created,created_by) values (3,1,'Super Administrator',1,now(),3);
insert into permissions (user_id,institution_id,permission,default_permission,created,created_by) values (4,4,'Institution Administrator',1,now(),2);
insert into permissions (user_id,institution_id,permission,default_permission,created,created_by) values (5,4,'Teacher',1,now(),2);
insert into permissions (user_id,institution_id,permission,default_permission,created,created_by) values (6,4,'Student',1,now(),2);

/* background_types defines the various background_types of a student - The reason we define it in the permissions table is that
 * students may potentially have different background types in different institutions
 */
CREATE TABLE background_types(
	background_type			VARCHAR(255)		NOT NULL
);

INSERT INTO background_types VALUES ('Religious');
INSERT INTO background_types VALUES ('Non-Religious');
INSERT INTO background_types VALUES ('Teenager');
INSERT INTO background_types VALUES ('Boys');
INSERT INTO background_types VALUES ('Girls');

CREATE TABLE user_backgrounds (
	user_background_id			INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	permission_id				INTEGER(10)			UNSIGNED NOT NULL,
	user_id						INTEGER				UNSIGNED NOT NULL,
	background_type				VARCHAR(255)		REFERENCES background_types ON DELETE CASCADE,
	created						DATETIME			NOT NULL,
	modified					TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by					INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (user_background_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
	FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE,
	FOREIGN KEY (background_type) REFERENCES background_types(background_type) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* error logging table */
CREATE TABLE zend_log (
  zend_log_id					INTEGER(10)			UNSIGNED NOT NULL AUTO_INCREMENT,
  timestamp						TIMESTAMP			NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  level							VARCHAR(6)			DEFAULT NULL,
  message						TEXT,
  PRIMARY KEY (zend_log_id)
);

/* Campaign_Types table - Holds the different types of campagins available */
CREATE TABLE campaign_types (
	campaign_type			text			NOT NULL
);

INSERT INTO campaign_types VALUES ('General');
INSERT INTO campaign_types VALUES ('Religious');
INSERT INTO campaign_types VALUES ('Sports');
INSERT INTO campaign_types VALUES ('Educational');

/* Campaigns table - Holds the campaigns */
CREATE TABLE campaigns (
	campaign_id				INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	campaign_name			TEXT				NOT NULL,
	campaign_type			TEXT				REFERENCES campaign_types ON DELETE CASCADE,
	institution_id			INTEGER				UNSIGNED NOT NULL,
	is_active				BOOLEAN				DEFAULT 1,
	ladder					BOOLEAN				DEFAULT 20,
	points					BOOLEAN				DEFAULT 1,
	medals					BOOLEAN				DEFAULT 1,
	ranks					BOOLEAN				DEFAULT 1,
	is_editable				BOOLEAN				DEFAULT 0,
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (campaign_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* Mission_Types table - Holds the different types of missions available */
CREATE TABLE mission_types (
	mission_type			TEXT			NOT NULL
);

insert into mission_types values ('Regular');
insert into mission_types values ('Chain');
insert into mission_types values ('Tanya');

/* Missions table - Holds the missions of a campaign */
CREATE TABLE missions (
	mission_id				INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	mission_name			TEXT				NOT NULL,
	mission_type			VARCHAR(255)		REFERENCES mission_types ON DELETE CASCADE,
	campaign_id				INTEGER				UNSIGNED NOT NULL,
	book_id					INTEGER				UNSIGNED,
	book_measurement		ENUM("Line Numbers","Paragraphs","Pages","Chapters","Volumes"),
	institution_id			INTEGER				UNSIGNED NOT NULL,
	start_date				DATE,
	end_date				DATE,
	points_up				INTEGER,
	medal_up				VARCHAR(255),
	rank_up					VARCHAR(255),
	sequence				INTEGER				DEFAULT 1,
	is_active				BOOLEAN				DEFAULT 1,	
	percentage_required		INTEGER				DEFAULT 100,
	default_velocity		DECIMAL(10,2) 		DEFAULT 1,
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (mission_id),
	FOREIGN KEY (mission_type) REFERENCES mission_types(mission_type) ON DELETE CASCADE,
	FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id) ON DELETE CASCADE,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

alter table missions add column percentage_required integer default 100;

/* Tasks table - Holds the tasks of a mission */
CREATE TABLE tasks (
	task_id					INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	task_name				TEXT				NOT NULL,
	mission_id				INTEGER				UNSIGNED NOT NULL,
	campaign_id				INTEGER				UNSIGNED NOT NULL,
	institution_id			INTEGER				UNSIGNED NOT NULL,
	points					INTEGER,
	frequency				ENUM('One-Time','Hourly','Daily','Weekly', 'Shabbos', 'Monthly','Yearly') 	NOT NULL DEFAULT 'One-Time',
	start_date				DATE,
	end_date				DATE,
	start_time				TIME,
	end_time				TIME,
	sequence				INTEGER				NOT NULL DEFAULT 1,
	is_active				BOOLEAN				DEFAULT 1,
	is_required				BOOLEAN				DEFAULT 1,
	velocity				DECIMAL(10,2) 		DEFAULT 1,
	percentage				INTEGER,
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (task_id),
	FOREIGN KEY (mission_id) REFERENCES missions(mission_id) ON DELETE CASCADE,
	FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id) ON DELETE CASCADE,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* User_Campaigns holds all the user's ongoing & completed campaigns, missions, and tasks */
CREATE TABLE user_campaigns (
	user_campaign_id		INTEGER(10)				UNSIGNED AUTO_INCREMENT,
	user_id					INTEGER(10)				UNSIGNED NOT NULL,
	institution_id			INTEGER(10)				UNSIGNED NOT NULL,
	campaign_id				INTEGER(10)				UNSIGNED NOT NULL,
	mission_id				INTEGER(10)				UNSIGNED,
	task_id					INTEGER(10)				UNSIGNED,
	task_increment			INTEGER(10)				UNSIGNED,
	grade_hierarchy			INTEGER(3)				UNSIGNED,
	grade_velocity			INTEGER(10)				UNSIGNED,
	ladder					INTEGER(3)				UNSIGNED,
	ladder_velocity			INTEGER(10)				UNSIGNED,
	status					ENUM('In Progress','Completed','Enrollment'),
	input_value				TEXT					NULL,
	created					DATETIME,
	modified				TIMESTAMP 				ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER(10)				UNSIGNED,
	PRIMARY KEY (user_campaign_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id) ON DELETE CASCADE,
	FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id),
	FOREIGN KEY (mission_id) REFERENCES missions(mission_id),
	FOREIGN KEY (task_id) REFERENCES tasks(task_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE scheduling_params (
	scheduler_id			INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	mission_id				INTEGER(10)			UNSIGNED NULL, /* must not be null if task_id is null */
	task_id					INTEGER(10)			UNSIGNED NULL, /* must not be null if mission_id is null */
	years					VARCHAR(255)		NULL, /* 2010,2020 */
	weeks_in_year			VARCHAR(255)		NULL, /* 1,52 */
	days_in_year			VARCHAR(255)		NULL, /* 1,365 */
	months					VARCHAR(255)		NULL, /* jan,dec */
	weeks_in_month			VARCHAR(255)		NULL, /* 1,4 */
	days_in_month			VARCHAR(255)		NULL, /* 1,31 */
	days_of_week			VARCHAR(255)		NULL, /* sun,sat */
	frequency				ENUM('Yearly','Monthly','Weekly','Daily')	NOT NULL,
	start_time				VARCHAR(5)			NULL, /* 13:22 */
	expiration				INTEGER(8)			NULL, /* minutes */
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (scheduler_id),
	FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE,
	FOREIGN KEY (mission_id) REFERENCES missions(mission_id) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);


/* tasks_scale table - If a task should be added to a ladder / grade system, then the tasks_scale table will be
 * populated with the tasks and their progression
 */
CREATE TABLE tasks_scale (
	tasks_scale_id			INTEGER(10)			UNSIGNED AUTO_INCREMENT,
	task_id					INTEGER				UNSIGNED NOT NULL,
	grade					INTEGER				NOT NULL,
	ladder					INTEGER				NOT NULL,
	mission_id				INTEGER				UNSIGNED NOT NULL,
	campaign_id				INTEGER				UNSIGNED NOT NULL,
	institution_id			INTEGER				UNSIGNED NOT NULL,
	is_required				BOOLEAN				DEFAULT 1,
	velocity				DECIMAL(10,2) 		DEFAULT 1,
	comment					TEXT				NULL,
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER				UNSIGNED NOT NULL,
	PRIMARY KEY (tasks_scale_id),
	FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE,
	FOREIGN KEY (mission_id) REFERENCES missions(mission_id) ON DELETE CASCADE,
	FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id) ON DELETE CASCADE,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id) ON DELETE CASCADE,
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* Rules table - Adds rules & exceptions to tasks
 * Available rules are:
 * Allow / Deny : Background Type / Date / Time / Institution / Student
 */
CREATE TABLE rules (
	rule_id					INT(10)					UNSIGNED AUTO_INCREMENT,
	rule_type				ENUM('Allow','Deny')	NOT NULL,
	rule_applies_to			VARCHAR(255)			NOT NULL, /* Campaigns, Missions, Tasks, Users, Prizes, Misc, ... */
	rule					TEXT					NOT NULL,
	user_id					INT(10)					UNSIGNED DEFAULT NULL,
	institution_id			INT(10)					UNSIGNED DEFAULT NULL,
	campaign_id				INT(10)					UNSIGNED DEFAULT NULL,
	prize_id				INT(10)					UNSIGNED DEFAULT NULL,
	is_active				BOOLEAN					DEFAULT 1,
	created					DATETIME,
	modified				TIMESTAMP 				ON UPDATE CURRENT_TIMESTAMP,
	created_by				INT(10)					UNSIGNED NOT NULL,
	PRIMARY KEY  (rule_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id),
	FOREIGN KEY (prize_id) REFERENCES prizes(prize_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* Prizes table holds the inventory of an institution */
CREATE TABLE prizes (
	prize_id				INTEGER(10)				UNSIGNED AUTO_INCREMENT,
    template_prize_id		INTEGER			    	UNSIGNED DEFAULT 0,
	list					VARCHAR(255)			DEFAULT 'All Prizes',
	institution_id			INTEGER					UNSIGNED NOT NULL,
	class_id				INTEGER					UNSIGNED,
	prize_name				TEXT                    NOT NULL,
	prize_category			varchar(255)			DEFAULT 'General Prize',
	prize_description		TEXT,
	prize_count				INTEGER					DEFAULT 0,
	prize_photo				BLOB,
    prize_photo_type		VARCHAR(12),
	points					INTEGER					NOT NULL,
	prize_type				ENUM('Template','Custom Host','Custom School','School Installed','Custom Parent','Custom Teacher')	NOT NULL default 'Template',
	prize_price				DECIMAL(15,2),
	currency				ENUM('CAD','USD')		DEFAULT 'CAD',
    is_active				BOOLEAN				    DEFAULT 1,
	created					DATETIME,
	modified				TIMESTAMP 				ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER					UNSIGNED,
	PRIMARY KEY (prize_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	FOREIGN KEY (class_id) REFERENCES classes(class_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* user_prizes table holds the user prizes */
CREATE TABLE user_prizes (
	user_prize_id			INTEGER(10)				UNSIGNED AUTO_INCREMENT,
	prize_id				INTEGER					UNSIGNED NOT NULL,
	user_id					INTEGER					UNSIGNED NOT NULL,
	quantity				INTEGER					default 1,
	status					enum('Checked Out','Printed','Redeemed')	default 'Checked Out',
	created					DATETIME,
	modified				TIMESTAMP 				ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER					UNSIGNED,
	PRIMARY KEY (user_prize_id),
	FOREIGN KEY (prize_id) REFERENCES prizes(prize_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* orders table holds the online orders */
CREATE TABLE orders (
	order_id				INTEGER				UNSIGNED AUTO_INCREMENT,
	user_id					INTEGER,
	description				TEXT,
	currency				ENUM('CAD','USD')	DEFAULT 'CAD',
	total_price				DECIMAL(15,2),
	created					DATETIME,
	modified				TIMESTAMP 			ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER,
	PRIMARY KEY (order_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* rules table holds rules relative to the store & the campaigns / missions / tasks */
CREATE TABLE rules (
	rule_id					INTEGER					UNSIGNED AUTO_INCREMENT,
	rule_type				ENUM('Allow','Deny')	NOT NULL DEFAULT 'Deny',
	rule_applies_to			ENUM('Background Type','Full Grade','Full Ladder','Specific Grade','Specific Ladder','Institution','User','Date Range','Date','Time Range','Time')	NOT NULL,
	rule					TEXT					NOT NULL,
	institution_id			INTEGER					NOT NULL,
	host_id					INTEGER					NOT NULL,
	network_id				INTEGER					NOT NULL,
    prize_id				INTEGER					NOT NULL,
	is_enabled				BOOLEAN					DEFAULT 1,
	created					DATETIME,
	modified				TIMESTAMP 				ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER,
	PRIMARY KEY (rule_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	FOREIGN KEY (host_id) REFERENCES institutions(institution_id),
    FOREIGN KEY (prize_id) REFERENCES prizes(prize_id) ON DELETE CASCADE,
	FOREIGN KEY (network_id) REFERENCES institution(institution_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* holds classes relative to teachers for the individual permissions */
CREATE TABLE classes (
        class_id            INTEGER         UNSIGNED AUTO_INCREMENT,
        class_name          VARCHAR(255)    NOT NULL,
        class_name          VARCHAR(64),
        institution_id      INTEGER         UNSIGNED NOT NULL,
        grade               ENUM('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12') collate utf8_unicode_ci NOT NULL,
        gender              ENUM('boys', 'girls', 'mixed')       DEFAULT 'mixed',
        is_active           BOOLEAN	    DEFAULT 1,
        created             DATETIME,
        modified            TIMESTAMP       ON UPDATE CURRENT_TIMESTAMP,
        created_by          INTEGER         UNSIGNED,
        PRIMARY KEY (class_id),
        FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* hold the relationships of users to classes */
CREATE TABLE user_classes (
	user_class_id			INTEGER(10)				        UNSIGNED AUTO_INCREMENT,
	class_id				INTEGER					        UNSIGNED NOT NULL,
	user_id					INTEGER					        UNSIGNED NOT NULL,
	class_role				ENUM("Student", "Teacher"),
	created					DATETIME,
	modified				TIMESTAMP 				        ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER					        UNSIGNED,
	PRIMARY KEY (user_class_id),
	FOREIGN KEY (class_id) REFERENCES classes(class_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);

/* Holds the user points per campaign */
CREATE TABLE user_points (
	user_point_id			INTEGER(10)				        UNSIGNED AUTO_INCREMENT,
	user_id					INTEGER							UNSIGNED NOT NULL,
	campaign_id				INTEGER							UNSIGNED NOT NULL,
	institution_id			INTEGER							UNSIGNED NOT NULL,
	class_id				INTEGER							UNSIGNED NOT NULL,
	points					INTEGER							NOT NULL,
	rule_id					INTEGER							UNSIGNED,
	resource_name			VARCHAR(28),
	created					DATETIME,
	modified				TIMESTAMP 				        ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER					        UNSIGNED,
	PRIMARY KEY (user_point_id),
	FOREIGN KEY (campaign_id) REFERENCES classes(class_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	FOREIGN KEY (class_id) REFERENCES classes(class_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	FOREIGN KEY (rule_id) REFERENCES rules(rule_id),
	FOREIGN KEY (created_by) REFERENCES users(user_id)
);
/*packages table holds package information created by host / network*/
CREATE TABLE packages    (
	package_id          INTEGER        		UNSIGNED AUTO_INCREMENT,
	institution_id      INTEGER         	NOT NULL,
	name                VARCHAR(128)    	NOT NULL,
	description         TEXT,
	price               FLOAT           	NOT NULL,
	currency            ENUM('US', 'CDN')   DEFAULT 'US',
	discount_price      FLOAT UNSIGNED ,
	is_active           BOOLEAN         	DEFAULT 1,
	created		        DATETIME,
	modified		    TIMESTAMP			ON UPDATE CURRENT_TIMESTAMP,
	created_by		    INTEGER,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	PRIMARY KEY (package_id)
);

/*packages table holds package_item information created by host / network*/
CREATE TABLE package_items    (
	package_item_id         INTEGER				UNSIGNED AUTO_INCREMENT,
	institution_id          INTEGER,
	name                    VARCHAR(128)        NOT NULL,
	description             VARCHAR(255),
	price                   FLOAT               NOT NULL,
	currency                ENUM('US', 'CDN')   DEFAULT 'US',
	is_active               BOOLEAN             DEFAULT 1,
	created		            DATETIME,
	modified		        TIMESTAMP           ON UPDATE CURRENT_TIMESTAMP,
	created_by		        INTEGER,
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	PRIMARY KEY (package_item_id)
);

/*packages table holds package_combo information created by host / network*/
CREATE TABLE package_combos    (
	package_combo_id        INTEGER         UNSIGNED AUTO_INCREMENT,
	package_id              INTEGER,
	package_item_id         INTEGER,
	is_active               BOOLEAN         DEFAULT 1,
	created		            DATETIME,
	modified		        TIMESTAMP       ON UPDATE CURRENT_TIMESTAMP,
	created_by		        INTEGER,
	FOREIGN KEY (package_combo_id) REFERENCES packages(package_id),
	FOREIGN KEY (package_item_id) REFERENCES package_items(package_item_id),
	PRIMARY KEY (package_combo_id)
);

/*purchases table holds info that related to one particular purchase*/
CREATE TABLE purchases    (
	purchase_id             INTEGER         UNSIGNED AUTO_INCREMENT,
	user_id                 INTEGER,
	payment_status          ENUM('Pending', 'Completed', 'Refused')    default 'Pending',
	price                   FLOAT               NOT NULL,
	currency                ENUM('US', 'CDN')   DEFAULT 'US',
	is_active               BOOLEAN             DEFAULT 1,
	created		            DATETIME,
	modified		        TIMESTAMP       ON UPDATE CURRENT_TIMESTAMP,
	created_by		        INTEGER,
	FOREIGN KEY (user_id) REFERENCES users(user_id),
	PRIMARY KEY (purchase_id)
);

/*purchase_details holds all the items that belong to a purchase*/
CREATE TABLE purchase_details (
	purchase_detail_id      INTEGER         UNSIGNED AUTO_INCREMENT,
	purchase_id             INTEGER,
	item_description        VARCHAR(255),
	item_name               VARCHAR(128),
	is_active               BOOLEAN         DEFAULT 1,
	created		            DATETIME,
	modified		        TIMESTAMP       ON UPDATE CURRENT_TIMESTAMP,
	created_by		        INTEGER,
	FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id),
	PRIMARY KEY (purchase_detail_id)
);

CREATE TABLE activity_feed (
    activity_feed_id        INTEGER(10)				UNSIGNED AUTO_INCREMENT,
    user_id                 INTEGER					NOT NULL,
    institution_id          INTEGER,
    permission_id           INTEGER,
    action                  TEXT,
    category                ENUM('Create','Edit','Delete', 'Status'),
    created                 DATETIME,
    modified                TIMESTAMP				ON UPDATE CURRENT_TIMESTAMP,
    created_by              INTEGER					UNSIGNED NOT NULL,
    PRIMARY KEY (activity_feed_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id),
    FOREIGN KEY (institution_id) REFERENCES institutions (institution_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

/* Holds the grades per institution */
CREATE TABLE grades (
	grade_id			INTEGER			UNSIGNED AUTO_INCREMENT,
	institution_id		INTEGER			UNSIGNED,
	grade_name			VARCHAR(64),
	grade_hierarchy		INTEGER			UNSIGNED,
	is_active			BOOLEAN			DEFAULT 1,	
	created				DATETIME,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (grade_id),
	FOREIGN KEY (institution_id) REFERENCES institutions (institution_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE velocity_grades (
	velocity_grade_id	INTEGER			UNSIGNED AUTO_INCREMENT,
	campaign_id			INTEGER			UNSIGNED,
	grade_hierarchy		INTEGER			UNSIGNED,
	velocity			INTEGER			UNSIGNED,
	created				DATETIME,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (velocity_grade_id),
	FOREIGN KEY (campaign_id) REFERENCES campaigns (campaign_id),
	FOREIGN KEY (grade_id) REFERENCES grades (grade_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE velocity_ladders (
	velocity_ladder_id	INTEGER			UNSIGNED AUTO_INCREMENT,
	campaign_id			INTEGER			UNSIGNED,
	ladder				INTEGER			UNSIGNED,
	velocity			INTEGER			UNSIGNED,
	created				DATETIME,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (velocity_ladder_id),
	FOREIGN KEY (campaign_id) REFERENCES campaigns (campaign_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE medals (
	medal_id			INTEGER			UNSIGNED AUTO_INCREMENT,
	institution_id		INTEGER			UNSIGNED NOT NULL,
	campaign_id			INTEGER			UNSIGNED NOT NULL,
	medal_hierarchy		INTEGER			UNSIGNED NOT NULL,
	medal_name			VARCHAR(32)		NOT NULL,
	medal_value			INTEGER			UNSIGNED NOT NULL,
	medal_image_id		INTEGER			UNSIGNED,
	medal_image_id_2	INTEGER			UNSIGNED,
	created				DATETIME,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (medal_id),
	FOREIGN KEY (institution_id) REFERENCES campaigns (institution_id),
	FOREIGN KEY (campaign_id) REFERENCES campaigns (campaign_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE ranks (
	rank_id					INTEGER(10)		UNSIGNED AUTO_INCREMENT,
	institution_id			INTEGER(10)		UNSIGNED,
	rank_title				VARCHAR(32)		NOT NULL,
	rank_medals				INTEGER(3)		UNSIGNED NOT NULL,
	rank_image_1			INTEGER(10)		UNSIGNED NOT NULL,
	rank_image_2			INTEGER(10)		UNSIGNED NOT NULL,
	rank_background_image	INTEGER(10)		UNSIGNED NOT NULL,
	rank_color				VARCHAR(16)		NOT NULL,
	created					DATETIME,
	modified				TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER(10)		UNSIGNED NOT NULL,
	PRIMARY KEY (rank_id),
	FOREIGN KEY (institution_id) REFERENCES institutions (institution_id),
	FOREIGN KEY (rank_image_1) REFERENCES images (image_id),
	FOREIGN KEY (rank_image_2) REFERENCES images (image_id),
	FOREIGN KEY (rank_background_image) REFERENCES images (image_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE images (
	image_id			INTEGER(10)		UNSIGNED NOT NULL AUTO_INCREMENT,
	image_category_id	INTEGER(10)		DEFAULT NULL,
	photo				BLOB,
	photo_type			VARCHAR(12)		DEFAULT NULL,
	image_name			varchar(128)	DEFAULT NULL,
	created				DATETIME,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (image_id),
	FOREIGN KEY (image_category_id) REFERENCES image_categories (image_category_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE image_categories (
	image_category_id	INTEGER(10)		UNSIGNED NOT NULL AUTO_INCREMENT,
	institution_id		INTEGER(10)		UNSIGNED NOT NULL,
	name				VARCHAR(128)	DEFAULT NULL,
	created				DATETIME,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (image_category_id),
	FOREIGN KEY (institution_id) REFERENCES institutions (institution_id),
	FOREIGN KEY (created_by) REFERENCES users (user_id)
);

CREATE TABLE config_store (
	config_store_id		INTEGER(10)		UNSIGNED AUTO_INCREMENT,
	institution_id		INTEGER(10)		UNSIGNED NOT NULL,
	army_points			TINYINT(1)		DEFAULT 1,
	base_points			TINYINT(1)		DEFAULT 1,
	created				DATETIME		NOT NULL,
	modified			TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by			INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (config_store_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id)
);

CREATE TABLE books (
	book_id					INTEGER(10)		UNSIGNED AUTO_INCREMENT,
	institution_id			INTEGER(10)		UNSIGNED NOT NULL,
	book_name				VARCHAR(40)		NOT NULL,
	line_numbers_enabled	BOOLEAN			DEFAULT 1,
	paragraphs_enabled		BOOLEAN			DEFAULT 1,
	pages_enabled			BOOLEAN			DEFAULT 1,
	chapters_enabled		BOOLEAN			DEFAULT 1,
	volumes_enabled			BOOLEAN			DEFAULT 1,
	created					DATETIME		NOT NULL,
	modified				TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (book_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id)
);

CREATE TABLE book_lines (
	book_line_id			INTEGER(10)		UNSIGNED AUTO_INCREMENT,
	book_id					INTEGER(10)		UNSIGNED NOT NULL,
	line_data				TEXT			NOT NULL,
	line_number				VARCHAR(10)		DEFAULT NULL,
	paragraphs				VARCHAR(40)		DEFAULT NULL,
	pages					VARCHAR(40)		DEFAULT NULL,
	chapters				VARCHAR(40)		DEFAULT NULL,
	volumes					VARCHAR(40)		DEFAULT NULL,
	created					DATETIME		NOT NULL,
	modified				TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	created_by				INTEGER			UNSIGNED NOT NULL,
	PRIMARY KEY (book_line_id),
	FOREIGN KEY (book_id) REFERENCES books(book_id)
);

CREATE TABLE user_campaign_progress (
	user_campaign_progress_id	INTEGER(10)		UNSIGNED AUTO_INCREMENT,
	institution_id				INTEGER(10)		UNSIGNED NOT NULL,
	campaign_id					INTEGER(10)		UNSIGNED NOT NULL,
	user_id						INTEGER(10)		UNSIGNED NOT NULL,
	current_line				TEXT			NOT NULL,
	campaign_goal				VARCHAR(10)		DEFAULT NULL,
	modified					TIMESTAMP		ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (user_campaign_progress_id),
	FOREIGN KEY (institution_id) REFERENCES institutions(institution_id),
	FOREIGN KEY (campaign_id) REFERENCES campaigns (campaign_id),
	FOREIGN KEY (user_id) REFERENCES users(user_id)
);