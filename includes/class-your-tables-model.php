<?php
/**
 * Class Your_Tables_Model
 */
class Your_Tables_Model {

	/**
	 * @var
	 */
	private $shared;

	/**
	 * @param $shared
	 */
	function __construct( $shared ) {
		$this->shared = $shared;
	}

	/**
	 * Add a new record for the requested table
	 */
	function CreateItem() {
		$isYourTablesData = false;

		global $wpdb;
		$table = $wpdb->prefix . 'your_tables';
		if ( isset( $_REQUEST['wp_your_tables__table'] ) ) {
			$table = $_REQUEST['wp_your_tables__table'];
		}
		$row = $wpdb->get_row(
			$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables WHERE active = %s and name = %s order by position', 'Y', $table )
		);
		if ( count( $row ) ) {
			$fieldrows = $wpdb->get_results(
				$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables_fields WHERE active = %s and table_name = %s order by position', 'Y', $table )
			);
			if ( count( $fieldrows ) ) {
				$valuerow       = array();
				$fields         = array();
				$field_types    = array();
				$primary_fields = array();
				foreach ( $fieldrows as $fieldrow ) {
					if ( $fieldrow->primary_key == 'Y' ) {
						$primary_fields[] = $fieldrow->field_name;
					}
					// speciaal voor checkbox:
					if ( ! isset( $_REQUEST[ $fieldrow->field_name ] ) && $fieldrow->control_type == 'checkbox' ) {
						$_REQUEST[ $fieldrow->field_name ] = '';
					}
					if ( isset( $_REQUEST[ $fieldrow->field_name ] ) ) {
						if ( $fieldrow->control_type == 'autonumber' ) {
							continue;
						}
						$fields[ $fieldrow->field_name ] = $fieldrow->field_name;
						$veld_type                       = '%s';
						if ( $fieldrow->field_type == 'int' ) {
							$veld_type = '%d';
						}
						if ( $fieldrow->field_type == 'float' ) {
							$veld_type = '%f';
						}
						$field_types[ $fieldrow->field_name ] = $veld_type;
						$valuerow[ $fieldrow->field_name ]    = $this->InputEscape( $_REQUEST[ $fieldrow->field_name ] );
						if ( $fieldrow->control_type == 'date' ) {
							$valuerow[ $fieldrow->field_name ] = $this->InputEscape( $_REQUEST[ 'hiddenDt_' . $fieldrow->field_name ] );
						}
						if ( $fieldrow->field_name == 'name' && ( $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables' || $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables_fields' ) ) {
							$isYourTablesData = true;
						}
						if ( $fieldrow->field_name == 'table_name' && ( $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables' || $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables_fields' ) ) {
							$isYourTablesData = true;
						}

					}

				}
				$maySave = true;
				if ( $this->shared->modifyMe == 'N' && $isYourTablesData ) {
					$maySave = false;
				}
				if ( ! current_user_can( 'your_tables_cap_user' ) ) {
					$maySave = false;
				}
				if ( ( $table == $wpdb->prefix . 'your_tables' || $table == $wpdb->prefix . 'your_tables_fields' ) && ! current_user_can( 'your_tables_cap_admin' ) ) {
					$maySave = false;
				}
				if ( $maySave ) {
					$wpdb->insert( $table, $valuerow, $field_types );
					if ( $wpdb->insert_id && count( $primary_fields ) == 1 ) {
						$_REQUEST[ $primary_fields[0] ] = $wpdb->insert_id;
						//echo 'Gedaan';
					}
				}
			} else {
				echo __( 'No fields found', 'your-tables' );
			}
		} else {
			echo __( 'Table not found', 'your-tables' );
		}
	}

	/**
	 * Update the record
	 */
	function SaveItem() {
		$isYourTablesData = false;
		global $wpdb;
		$table = $wpdb->prefix . 'your_tables';
		if ( isset( $_REQUEST['wp_your_tables__table'] ) ) {
			$table = $_REQUEST['wp_your_tables__table'];
		}
		$row = $wpdb->get_row(
			$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables WHERE active = %s and name = %s order by position', 'Y', $table )
		);
		if ( count( $row ) ) {
			$fieldrows = $wpdb->get_results(
				$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables_fields WHERE active = %s and table_name = %s order by position', 'Y', $table )
			);
			if ( count( $fieldrows ) ) {
				$valuerow            = array();
				$field_types         = array();
				$primary_fields      = array();
				$primary_field_types = array();
				foreach ( $fieldrows as $fieldrow ) {
					// speciaal voor checkbox:
					if ( ! isset( $_REQUEST[ $fieldrow->field_name ] ) && $fieldrow->control_type == 'checkbox' ) {
						$_REQUEST[ $fieldrow->field_name ] = '';
					}
					if ( isset( $_REQUEST[ $fieldrow->field_name ] ) ) {
						$veld_type = '%s';
						if ( $fieldrow->field_type == 'int' ) {
							$veld_type = '%d';
						}
						if ( $fieldrow->field_type == 'float' ) {
							$veld_type = '%f';
						}
						if ( $fieldrow->primary_key == 'Y' ) {
							$primary_fields[ $fieldrow->field_name ]      = $_REQUEST[ 'primary_key_' . $fieldrow->field_name ];
							$primary_field_types[ $fieldrow->field_name ] = $veld_type;
						}
						$valuerow[ $fieldrow->field_name ]    = $this->InputEscape( $_REQUEST[ $fieldrow->field_name ] );
						$field_types[ $fieldrow->field_name ] = $veld_type;
						if ( $fieldrow->control_type == 'date' ) {
							$valuerow[ $fieldrow->field_name ] = $this->InputEscape( $_REQUEST[ 'hiddenDt_' . $fieldrow->field_name ] );
						}
						if ( $fieldrow->field_name == 'name' && ( $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables' || $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables_fields' ) ) {
							$isYourTablesData = true;
						}
						if ( $fieldrow->field_name == 'table_name' && ( $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables' || $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables_fields' ) ) {
							$isYourTablesData = true;
						}


					}
				}
				$maySave = true;
				if ( $this->shared->modifyMe == 'N' && $isYourTablesData ) {
					$maySave = false;
				}
				if ( ! current_user_can( 'your_tables_cap_user' ) ) {
					$maySave = false;
				}
				if ( ( $table == $wpdb->prefix . 'your_tables' || $table == $wpdb->prefix . 'your_tables_fields' ) && ! current_user_can( 'your_tables_cap_admin' ) ) {
					$maySave = false;
				}
				if ( $maySave ) {
					$wpdb->update( $table, $valuerow, $primary_fields, $field_types, $primary_field_types );
				}
			}
		}

	}

	/**
	 * Delete the record
	 */
	function DeleteItem() {
		$isYourTablesData = false;
		global $wpdb;
		$table = $wpdb->prefix . 'your_tables';
		if ( isset( $_REQUEST['wp_your_tables__table'] ) ) {
			$table = $_REQUEST['wp_your_tables__table'];
		}
		$row = $wpdb->get_row(
			$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables WHERE active = %s and name = %s order by position', 'Y', $table )
		);
		if ( count( $row ) ) {
			$fieldrows = $wpdb->get_results(
				$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables_fields WHERE active = %s and table_name = %s order by position', 'Y', $table )
			);
			if ( count( $fieldrows ) ) {
				$primary_fields      = array();
				$primary_field_types = array();
				foreach ( $fieldrows as $fieldrow ) {
					// speciaal voor checkbox:
					if ( ! isset( $_REQUEST[ $fieldrow->field_name ] ) && $fieldrow->control_type == 'checkbox' ) {
						$_REQUEST[ $fieldrow->field_name ] = '';
					}
					if ( isset( $_REQUEST[ $fieldrow->field_name ] ) ) {
						$veld_type = '%s';
						if ( $fieldrow->field_type == 'int' ) {
							$veld_type = '%d';
						}
						if ( $fieldrow->field_type == 'float' ) {
							$veld_type = '%f';
						}
						if ( $fieldrow->primary_key == 'Y' ) {
							$primary_fields[ $fieldrow->field_name ]      = $_REQUEST[ $fieldrow->field_name ];
							$primary_field_types[ $fieldrow->field_name ] = $veld_type;
						}
						if ( $fieldrow->field_name == 'name' && ( $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables' || $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables_fields' ) ) {
							$isYourTablesData = true;
						}
						if ( $fieldrow->field_name == 'table_name' && ( $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables' || $valuerow[ $fieldrow->field_name ] == $wpdb->prefix . 'your_tables_fields' ) ) {
							$isYourTablesData = true;
						}


					}
				}
				$maySave = true;
				if ( $this->shared->modifyMe == 'N' && $isYourTablesData ) {
					$maySave = false;
				}
				if ( ! current_user_can( 'your_tables_cap_user' ) ) {
					$maySave = false;
				}
				if ( ( $table == $wpdb->prefix . 'your_tables' || $table == $wpdb->prefix . 'your_tables_fields' ) && ! current_user_can( 'your_tables_cap_admin' ) ) {
					$maySave = false;
				}
				if ( $maySave ) {
					$wpdb->delete( $table, $primary_fields, $primary_field_types );
				}
			}
		}

	}

	/**
	 * After saving the administration and user roles, translate them to WordPress Capabilities
	 */
	function AssignCapabilities() {

		if ( ! is_array( $this->shared->userRoles ) ) {
			$this->shared->userRoles = array();
		}
		if ( ! is_array( $this->shared->adminRoles ) ) {
			$this->shared->adminRoles = array();
		}
		foreach ( get_editable_roles() as $role_name => $role_info ) {
			$role = get_role( $role_name );
			if ( in_array( $role_name, $this->shared->adminRoles ) ) {
				$role->add_cap( 'your_tables_cap_admin' );
			} else {
				$role->remove_cap( 'your_tables_cap_admin' );
			}

		}
		foreach ( get_editable_roles() as $role_name => $role_info ) {
			$role = get_role( $role_name );
			if ( in_array( $role_name, $this->shared->userRoles ) || in_array( $role_name, $this->shared->adminRoles ) ) {
				$role->add_cap( 'your_tables_cap_user' );
			} else {
				$role->remove_cap( 'your_tables_cap_user' );
			}

		}
	}

	/**
	 * @param $str
	 *
	 * @return mixed
	 */
	function InputEscape( $str ) {
		$search  = array( "\\\\", "\0", "\x1a", "\\'", '\"' );
		$replace = array( "\\", "\\0", "\Z", "'", '"' );

		return str_replace( $search, $replace, $str );
	}
}