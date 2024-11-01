<?php
/**
 * Class Your_Tables_Shared
 *
 * Contains all functionality and information that is needed throughout the whole project
 */
class Your_Tables_Shared {

	public $Pagination;
	public $modifyMe;
	public $databaseVersion;
	public $adminRoles;
	public $userRoles;
	public $dashIconsPresent=true;

	function __construct() {

	}

	function RegisterSettings() {

		// Pagination
		register_setting( 'your_tables', 'your_tables_pagination' );

		// Admin Roles
		register_setting( 'your_tables', 'your_tables_admins' );

		// User Roles
		register_setting( 'your_tables', 'your_tables_users' );

		// Allow your_tables and your_tables-Fields to be modified
		register_setting( 'your_tables', 'your_tables_modify_me' );

		// Database version
		register_setting( 'your_tables', 'your_tables_database_version' );

		$this->GetSettings();
	}

	private function GetSettings() {
		$this->Pagination      = get_option( 'your_tables_pagination', 'N' );
		$this->modifyMe        = get_option( 'your_tables_modify_me', 'N' );
		$this->databaseVersion = get_option( 'your_tables_database_version', 1 );
		$this->adminRoles      = get_option( 'your_tables_admins', array( 'administrator' ) );
		$this->userRoles       = get_option( 'your_tables_users', array( 'administrator' ) );

	}


}