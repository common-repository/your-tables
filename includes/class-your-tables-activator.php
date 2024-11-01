<?php
/**
 * Fired during plugin activation
 */
class Your_Tables_Activator {

	public static function activate() {
		Your_Tables_Activator::sql001();
	}

	/**
	 * First installation of the plugin. Creates tables and records in it and sets basic WordPress variables
	 */
	public static function sql001() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'your_tables';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			//table not in database. Create new table

			$sql = "CREATE TABLE `" . $table_name . "` (
  `name` varchar(32) NOT NULL,
  `label_single_item` varchar(64) NOT NULL,
  `label_multiple_items` varchar(64) NOT NULL,
  `order_by` varchar(64) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '1',
  `active` char(1) NOT NULL DEFAULT 'Y',
  `delete_allowed` char(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$wpdb->query( $sql );

			$sql = "ALTER TABLE `" . $table_name . "`
  ADD PRIMARY KEY (`name`);";
			$wpdb->query( $sql );

			$sql = "INSERT INTO `" . $table_name . "` (`name`, `label_single_item`, `label_multiple_items`, `order_by`, `position`, `active`, `delete_allowed`) VALUES
('" . $table_name . "', 'Table', 'Your Tables', '', -2, 'Y', 'N'),
('" . $table_name . "_fields', 'Your Table Field', 'Your Table Fields', NULL, -1, 'Y', 'N');";
			$wpdb->query( $sql );

			$sql = "CREATE TABLE `" . $table_name . "_fields` (
  `table_name` varchar(32) NOT NULL,
  `field_name` varchar(32) NOT NULL,
  `field_type` enum('int','char','text','date','float') NOT NULL,
  `value_size` int(11) DEFAULT NULL,
  `primary_key` char(1) NOT NULL DEFAULT 'N',
  `mandatory` char(1) NOT NULL DEFAULT 'N',
  `active` char(1) NOT NULL DEFAULT 'Y',
  `control_type` enum('single_line_text','multiple_line_text','number','date','radiobox','checkbox','combobox','listbox','autonumber') NOT NULL,
  `label` varchar(32) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '1',
  `display_size` varchar(10) DEFAULT NULL,
  `field_values` varchar(256) DEFAULT NULL,
  `field_labels` varchar(256) DEFAULT NULL,
  `field_query` varchar(256) DEFAULT NULL,
  `default_value` varchar(256) DEFAULT NULL,
  `format` varchar(128) DEFAULT NULL,
  `css_class` varchar(32) DEFAULT NULL,
  `display_in_table` char(1) NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$wpdb->query( $sql );

			$sql = "ALTER TABLE `" . $table_name . "_fields`
  ADD PRIMARY KEY (`table_name`,`field_name`);";
			$wpdb->query( $sql );

			$sql = "INSERT INTO `" . $table_name . "_fields` (`table_name`, `field_name`, `field_type`, `value_size`, `primary_key`, `mandatory`, `active`, `control_type`, `label`, `position`, `display_size`, `field_values`, `field_labels`, `field_query`, `default_value`, `format`, `css_class`, `display_in_table`) VALUES
('" . $table_name . "', 'active', 'char', 1, 'N', 'Y', 'Y', 'combobox', 'Active', 2, '100', 'Y,N', 'Yes,No', NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "', 'delete_allowed', 'char', 1, 'N', 'Y', 'Y', 'combobox', 'Delete Allowed', 6, '', 'N,Y', 'No,Yes', '', 'N', '', '', 'Y'),
('" . $table_name . "', 'label_multiple_items', 'char', 64, 'N', 'Y', 'Y', 'single_line_text', 'Label for multiple items', 5, '400', NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "', 'label_single_item', 'char', 64, 'N', 'Y', 'Y', 'single_line_text', 'Label for Single Item', 4, '400', NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "', 'name', 'char', 32, 'Y', 'Y', 'Y', 'single_line_text', 'Table Name', 1, '400', NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "', 'order_by', 'char', 64, 'N', 'N', 'Y', 'single_line_text', 'Order By Clause', 3, '400', '', '', '', '', '', NULL, 'Y'),
('" . $table_name . "', 'position', 'int', NULL, 'N', 'N', 'Y', 'single_line_text', 'Position', 3, '100', NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'active', 'char', 1, 'N', 'Y', 'Y', 'combobox', 'Active', 7, '100', 'Y,N', 'Yes,No', NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'control_type', 'char', 0, 'N', 'Y', 'Y', 'combobox', 'Control Type', 8, '200', 'single_line_text,multiple_line_text,number,date,radiobox,checkbox,combobox,listbox,autonumber', 'Single Line,Text Area,Number,Date,Radiobox,Checkbox,Dropdown,Listbox,Autonumber', NULL, '', '', NULL, 'Y'),
('" . $table_name . "_fields', 'css_class', 'char', 32, 'N', 'N', 'Y', 'single_line_text', 'CSS Class', 17, '200', '', '', '', '', '', NULL, 'Y'),
('" . $table_name . "_fields', 'default_value', 'char', 255, 'N', 'N', 'Y', 'single_line_text', 'Default Value', 15, '400', '', '', NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'display_in_table', 'char', 1, 'N', 'Y', 'Y', 'combobox', 'Display in Table', 17, '100', 'Y,N', 'Yes,No', NULL, 'Y', '', NULL, 'Y'),
('" . $table_name . "_fields', 'display_size', 'char', 10, 'N', 'N', 'Y', 'single_line_text', 'Display Size', 11, '0', '', '', NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'field_labels', 'char', 255, 'N', 'N', 'Y', 'single_line_text', 'Field Labels', 13, '800', '', '', NULL, '', '', NULL, 'N'),
('" . $table_name . "_fields', 'field_name', 'char', 32, 'Y', 'Y', 'Y', 'single_line_text', 'Field Name', 2, '300', NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'field_query', 'text', 256, 'N', 'N', 'Y', 'multiple_line_text', 'Field Query', 14, '800,88', '', '', '', '', '', NULL, 'N'),
('" . $table_name . "_fields', 'field_type', 'char', NULL, 'N', 'Y', 'Y', 'combobox', 'Field Type', 3, '200', 'int,char,text,date', 'Integer,Char,Text,Date', NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'field_values', 'char', 255, 'N', 'N', 'Y', 'single_line_text', 'Field Values', 12, '800', '', '', NULL, '', '', NULL, 'N'),
('" . $table_name . "_fields', 'format', 'char', 128, 'N', 'N', 'Y', 'single_line_text', 'Format', 16, '400', '', '', NULL, '', NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'label', 'char', 32, 'N', 'Y', 'Y', 'single_line_text', 'Label', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'mandatory', 'char', 1, 'N', 'Y', 'Y', 'combobox', 'Mandatory Field', 6, '100', 'Y,N', 'Yes,No', NULL, 'N', '', NULL, 'Y'),
('" . $table_name . "_fields', 'position', 'int', 11, 'N', 'N', 'Y', 'single_line_text', 'Position', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Y'),
('" . $table_name . "_fields', 'primary_key', 'char', 1, 'N', 'Y', 'Y', 'combobox', 'Primary Key', 5, '100', 'Y,N', 'Yes,No', NULL, 'N', '', NULL, 'Y'),
('" . $table_name . "_fields', 'table_name', 'char', 32, 'Y', 'Y', 'Y', 'combobox', 'Table Name', 1, '300', '', '', 'select name, name as myname from " . $table_name . "', '', '', '', 'Y'),
('" . $table_name . "_fields', 'value_size', 'int', 0, 'N', 'N', 'Y', 'single_line_text', 'Value Size', 4, '100', NULL, NULL, NULL, NULL, NULL, NULL, 'Y');";
			$wpdb->query( $sql );

			update_option( 'your_tables_database_version', 1, 'yes' );
			$role = get_role( 'administrator' );
			$role->add_cap( 'your_tables_cap_admin' );
			$role->add_cap( 'your_tables_cap_user' );
		}
	}

}
