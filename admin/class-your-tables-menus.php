<?php
class Your_Tables_Menus {

	private $shared;

	/**
	 * @param $shared contains the class with information and functionality which is needed troughout the whole project
	 */
	function __construct( $shared ) {
		$this->shared = $shared;
	}

	/**
	 * @param $admin contains a reference to the class-your-tables-admin class
	 */
	function hook_menus( $admin ) {
		$dashIcon='dashicons-feedback';
		if (!wp_style_is('dashicons')) {
			$dashIcon='';
		}
		// Create the Top level menu
		add_menu_page( 'Your Tables', 'Your Tables', 'your_tables_cap_user', 'your-tables-menu', array(
			$admin,
			'your_tables_admin_page'
		),$dashIcon , 16000 );
		add_submenu_page( 'your-tables-menu', 'Your Tables', 'Your Tables', 'your_tables_cap_user', 'your-tables-menu' );
		if ( function_exists('remove_submenu_page') ) remove_submenu_page( 'your-tables-menu', 'your-tables-menu' ); // workaround for unwanted WordPress behaviour

		// Get Sub level menu items based on the active tables
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare( 'select * from wp_your_tables WHERE active = %s order by position', 'Y' )
		);
		foreach ( $rows as $row ) {
			$capa = 'your_tables_cap_user';
			if ( $row->name == 'wp_your_tables' || $row->name == 'wp_your_tables_fields' ) {
				$capa = 'your_tables_cap_admin';
			} // another capability is needed for our own base tables
			add_submenu_page( 'your-tables-menu', htmlspecialchars($row->label_multiple_items, ENT_QUOTES, 'UTF-8'), htmlspecialchars($row->label_multiple_items, ENT_QUOTES, 'UTF-8'), $capa, 'your-tables-table-' . $row->name, array(
				$admin,
				$row->name
			) );
		}

		// The settings page
		add_options_page( 'Your Tables Options', __( 'Your Tables Settings', 'your-tables' ), 'manage_options', 'your_tables_admin_settings_page', array(
			$admin,
			'your_tables_admin_settings_page'
		) );

	}


}
