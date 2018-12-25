<?php
// load the PDO connection
require_once( __DIR__ . '/api/header/db.php' );
require_once( __DIR__ . '/api/header/setCurrentUser.php' );

// set the current user
if ( !isset( $current_user ) ) {
	$current_user = setCurrentUser();
}

/** Get all the Tanya Only schools */
$tanya_only_query = $MASHPIA_DB->query(
	"SELECT school_id FROM schools WHERE tanya = 1 AND chayolei = 0"
);
$tanyaOnlySchools = array();
while ($tanya_only = mysql_fetch_assoc($tanya_only_query)) {
	$tanyaOnlySchools[] = $tanya_only['school_id'];
}

/** Get all the Chidon Only schools */
$chidonSchools = array();
$chidon_school_query = mysql_query(
	"SELECT school_id FROM schools WHERE chidon = 1 AND chayolei = 0"
);
while ($rw = mysql_fetch_assoc($chidon_school_query)) {
	$chidonSchools[] = $rw['school_id'];
}
// hardcode the ballpeh only schools
$bpOnly = [ 82 ];

// define function to get menu reducer
function getMenuReducer( $default_users = [ 'HQ', 'INST', 'BC' ] ) {
	return function( $menu, $item ) use ( $default_users ) {
		global $current_user;

		// if we have sub items, run this function recursivly
		if ( isset( $item['items'] ) && is_array( $item['items'] ) ) {

			if ( isset( $item['user_types'] ) ) {
				$item['items'] = array_reduce( $item['items'], getMenuReducer( $item['user_types'] ), [] );
			} else {
				$item['items'] = array_reduce( $item['items'], getMenuReducer( $default_users ), [] );
			}
			// hide the menu if there are no valid children
			if ( count( $item['items'] ) == 0 )
				return $menu;
		}
		// hide the item if we are not showing legacy links
		if ( $current_user && isset( $item['legacy'] ) && !$current_user->login->legacy ) {
			return $menu;
		}
		// hide the item if we are not enrolled in this module
		if ( $current_user && isset( $item['module'] ) &&
			!$current_user->login->modules[ $item['module'] ] ) {
			return $menu;
		}
		// make sure the custom user type is valid if present
		if ( $current_user && isset( $item['user_types'] ) &&
			array_search( $current_user->login->code, $item['user_types'] ) !== false )
		{
			$menu[] = $item;
		// make sure the user_type is valid
		} else if ( $current_user && !isset( $item['user_types'] ) &&
			array_search( $current_user->login->code, $default_users ) !== false )
		{
			$menu[] = $item;
		}

		return $menu;
	};
}

// render the sidebar items correctly
function renderSidebarItem( $item, $nested = false ) {
	// if the url is in the path
	$label = T_( $item['label'] );
	$url = isset( $item['path'] ) ? $item['path'] : false;
	$icon = isset( $item['icon'] ) ? $item['icon'] : false;
	// if the link is in the react app, make some changes
	if ( !isset( $item['legacy'] ) || !$item['legacy'] ) {
		$url = $url ? "/new$url" : false;
		$icon = $icon ? "<i class='fas fa-$icon'></i>" : false;
	// legacy links need a link to the picture
	} else {
		$icon = $icon ? "<img height='28' width='28' src='$icon'/>" : '';
	}
	// generate the class names
	$classnames = [];
	// if we are at the top level and have children, add the list parent class
	if ( !$nested ) $classnames[] = 'list_parent';
	// if the url contains the current page, then it is the current one
	if ( strrpos( $_SERVER['REQUEST_URI'], $url ) !== false ) $classnames[] = 'current';
	// generate the class string
	$class = implode( ' ', $classnames );

	if ( $nested && isset( $item['items'] ) ) {
		$html = "<li class='submenu $class'>"
							."<a href='#'>".$label."</a>";
	} else {
		$html = "<li class='$class'>"
			."<a href='".( $url ? $url : '#' )."'>"
				."<span class='icon'>"
					. $icon . $label
				."</span>"
			."</a>"
		."</li>";
	}

	if ( isset( $item['items'] ) ) {
		$html .= '<ul class="'.( $nested ? '' : 'list_second' ).'">';
		// render each submenu item
		foreach( $item['items'] as $item ) {
			$html .= renderSidebarItem( $item, true );
		}
		// close the list
		$html .= '</ul>';

		// if nested list we need to close the tag li here.
		if ( $nested ) $html .= '</li>';
	// for top level items without kids, add the list second item to fix js problems
	} else if ( !$nested ) {
		$html .= '<ul class="list_second"></ul>';
	}

	return $html;
}

// get the menu structure
$string = file_get_contents( __DIR__ . "/../includes/menu.json");
$menu = json_decode( $string, true ); // will be nested arrays
$menu = array_reduce( $menu, getMenuReducer(), [] );

// Set the admin if not loaded yet.
if ( !isset( $admin ) ) {
	include_once( __DIR__ . "/camps/includes/classes/admin.php" ); // load up the admin class.
	$query = mysql_query(
		"SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id']
	);
	$row = mysql_fetch_assoc($query);
	$admin = new \camps\classes\admin($row);
	$admin->get_school_id();
	$admin->get_auths();
}

// get the school_id of the request if variable not already set.
if ( !isset( $school_id ) ) {
	$school_id = gri('school_id', -1); 
}

if ( !isset($_SESSION['program_name']) || $_SESSION['program_name'] != 'children_tasks' ) { ?>
	<script src="/scripts/jquery-1.8.3.js"></script>
	<script src="/scripts/js.cookie.js"></script>
	<script type="text/javascript" src="/scripts/jquery.tools.min.js"></script>
	<script type="text/javascript" src="/scripts/jquery.styleselect.js"></script>
	<script type="text/javascript" src="/scripts/bug_report/bug_report.js"></script>
	<script type="text/javascript" src="/jquery-ui.js"></script>
	<script src="/scripts/scripts.js"></script>
	
	<script>
		$(function() {
			// get the current tab from the server
			var curr_tab = $("li.list_parent").index($("li.list_parent.current"));
			if ( curr_tab < 0 ) curr_tab = 0;
			// setup the dropdown functionality
			$( ".list_first:not(.list_small,.user_list)" ).tabs( 
				".list_first > ul", 
				{ tabs: '.list_parent', effect: 'slide', initialIndex: curr_tab }
			);
			// add submenus to the UI
			$( '#nav li:has(ul)' ).addClass( 'submenu' );
			// fix links and make them work
			$('.list_parent a').click( function(){
				if ( window.location !== $( this ).attr( 'href' ) )
					window.location = $( this ).attr( 'href' );
			});

			$( ".blog" ).click( function() {
				document.blog.submit();
			});

			// $('.col_title_bg > select').change( function( e ) {
			// 	var selected = $(':selected', this);
			// 	var label = selected.closest('optgroup').attr('id');
			// 	// if they selected a link, navigate to that link
			// 	if ( label === 'links-select' ) {
			// 		return window.location.href = e.target.value;
			// 	}
			// 	// change the login and refresh the page
			// 	var login = e.target.value;
			// 	var expires = new Date();
			// 	expires.setFullYear(new Date().getFullYear() + 10);
			// 	// set the login cookie
			// 	if ( login.split('-')[0] !== 'PARENT' ) {
			// 		Cookies.set( 'login', login, { path: '/', expires: expires } );
			// 		window.location.reload(); // refresh the page
			// 	// set the login key and navigate to the parent site
			// 	} else {
			// 		Cookies.set( 'admin', selected[0].dataset.key, { path: '/', expires: expires } );
			// 		window.location.href = '/mobile/reg/parent_detail.html';
			// 	}
			// });
		});
		// run correctHeight when the page loads
		$( window ).load( function() {
			correctHeight();
		});
		// fix the hight of the sidebar
		function correctHeight() {
			$('#nav').animate({ height: $('#content').height() },1000);
		}
	</script>
	<!-- font awesome and fix sliding in issue -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" integrity="sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP" crossorigin="anonymous">
	<style> ul.list_second { display: none; } </style>
<? } // end if the program name is not set (or if set not equal to "children_tasks") ?>
<div id="wrapper">
	<div id="nav">
		<div class="col_title_bg"></div>
		<div class="col_title">Menu</div>

		<?php if ( isset( $admin_user['auth'] ) ) { ?>
			<ul class="list_first">
				<?php // render the menu from the file
					foreach( $menu as $item ) {
						echo renderSidebarItem( $item );
					}
				?>

				<li>
					<a heref='/logout.php'>
						<span class='icon'>
							<img src='/images/parentIcons/logout.gif' />
							Logout
						</span>
					</a>
				</li>
				
			</ul>
		<?php } ?>
	</div>

	<div id="content">
		<div class="col_title_bg">

			<!-- <select style="position: absolute; right: -0; height: 44px; background: none; border: none;">
				<optgroup label="Logins" id='logins-select'>

					<?php // render the logins dropdown
					// foreach( $current_user->logins() as $login ) {
						// the value of the option.
						// $value = $login->type.'-'.$login->id;
						// check if the value is selected.
						// $selected = $value == $current_user->login->type.'-'.$current_user->login->id ? 'selected' : '';
						// check if the login is a parent login and add the login key.
						// $key = $login->type == 'PARENT' ? "data-key='$login->key'" : '';
						?>
						<option value='<?// $value ?>' <?// $selected ?> <?// $key ?>>
							<?// $login->name ?>
						</option>
					<?php } ?>

				</optgroup>

				<optgroup label="Links" id='links-select'>
					<option value='/new/myaccount'>My Account</option>
					<option value='/helpdesk/?p=open'>Technical Support</option>
					<option value='/logout.php'>Logout</option>
				</optgroup>
				
			</select> -->
		</div>

		<div class="slider_container">
			<div class="slider">
				<div class="col_title"></div>
				<div class="col_content">
			<!-- close .slider in footer -->
		<!-- close .slider_container in footer -->
	<!-- close #content in footer -->
<!-- close wrapper in footer -->
