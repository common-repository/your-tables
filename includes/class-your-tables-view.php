<?php
class Your_Tables_View {

	private $AdminPath = '/wp-admin/admin.php?page=your-tables-menu';
	private $shared;

	function __construct( $shared ) {
		$this->shared = $shared;
	}

	function DisplayTable( $table ) {
		global $wpdb;
		$wpdb->hide_errors();
		
		$echoTop='';
		$echoErrors='';
		$echoContents='';
		$transToLabels = array();
		
		// get the table definition
		$tableRow = $wpdb->get_row(
			$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables WHERE active = %s and name = %s order by position', 'Y', $table )
		);
		if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).'</h2><p>'.$wpdb->last_error.' (cyt-view1)</p>';
		if ( count( $tableRow ) ) {
			// get the field definitions
			$fieldRows = $wpdb->get_results(
				$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables_fields WHERE active = %s and table_name = %s order by position', 'Y', $table )
			);
			if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).'</h2><p>'.$wpdb->last_error.' (cyt-view2)</p>';
			if ( count( $fieldRows ) ) {

				$echoTop .= '<div class="wrap"><h2>' . htmlspecialchars($tableRow->label_multiple_items, ENT_QUOTES, 'UTF-8') . ' <a href="' . $this->AdminPath . '&wp_your_tables__action=newform&wp_your_tables__table=' . $tableRow->name . '" class="add-new-h2">' . __( "New", "your-tables" ) . ' ' . htmlspecialchars($tableRow->label_single_item, ENT_QUOTES, 'UTF-8') . '</a></h2>';
				$echoContents .= '<table class="wp-list-table widefat plugins"  ><thead><tr  >';
				$fields      = array();
				$selectFields      = array();
				$primaries   = array();
				$classes     = array();
				$dateFormats = array();
				foreach ( $fieldRows as $fieldRow ) {
					// may we display the column?
					if ( $fieldRow->display_in_table == 'Y' ) {
						$echoContents .=  '<th>' . htmlspecialchars( __( $fieldRow->label, 'your-tables' ) , ENT_QUOTES, 'UTF-8') . '</th>';
						$fields[$fieldRow->field_name] = $fieldRow;
						$selectFields[$fieldRow->field_name]=$fieldRow->field_name;
					}
					// we use $primaries to have the primary keys that are needed in the selections and urls to select the correct record
					if ( $fieldRow->primary_key == 'Y' ) {
						$primaries[] = $fieldRow->field_name;
					}
					// remember the formats
					if ( $fieldRow->field_type == 'date' ) {
						$dateFormats[ $fieldRow->field_name ] = $fieldRow->format;
						$selectFields[$fieldRow->field_name] = 'DATE_FORMAT('.$fieldRow->field_name.',\'%Y-%m-%d %H:%i:%s\') as '.$fieldRow->field_name;
					}
					// remember the css class
					if ( $fieldRow->css_class ) {
						$classes[ $fieldRow->field_name ] = $fieldRow->css_class;
					}
					// if predefined lists are present, prepare them here
					if ( strpos( $fieldRow->field_labels, ',' ) && strpos( $fieldRow->field_values, ',' ) ) {
						$field_labels = explode( ',', $fieldRow->field_labels );
						$field_values = explode( ',', $fieldRow->field_values );
						foreach ( $field_labels as $id => $label ) {
							$transToLabels[ $fieldRow->field_name ][ $field_values[ $id ] ] = $field_labels[ $id ];
						}
					}
					// if a sql statement for a predefined list is ready, prepare and get the values here
					if ( $fieldRow->field_values == '' && $fieldRow->field_labels == '' && $fieldRow->field_query != '' ) {
						$fieldRows = $wpdb->get_results( $this->ProtectSQL( $fieldRow->field_query ), ARRAY_N ); // ProtectSQL acts against malicious SQL statements
						if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).' '.__( 'at field', 'your-tables' ).' "'.$fieldRow->field_name.'"</h2><p>'.$wpdb->last_error.' (cyt-view3)</p>';
						if ( count( $fieldRows ) ) {
							foreach ( $fieldRows as $myFieldRow ) {
								$transToLabels[ $fieldRow->field_name ][ $myFieldRow[0] ] = $myFieldRow[1];
							}
						}

					}
				}
				$echoContents .= '<th>&nbsp;</th>';
				$echoContents .= '</tr>';
				$order_by = '';
				if ( $tableRow->order_by ) {
					$order_by =  ' order by ' . sanitize_sql_orderby($tableRow->order_by) ;
				}
				$valuerows = $wpdb->get_results( 'select ' . implode( ',', $selectFields ) . ' from ' . $table . $order_by );
				if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).'</h2><p>'.$wpdb->last_error.' (cyt-view4)</p>';
				$echoContents .= '</thead><tbody id="the-list">';
				foreach ( $valuerows as $valuerow ) {
					$echoContents .= '<tr class="inactive" >';
					foreach ( $fields as $field ) {
						$fieldName=$field->field_name;
						$display_value = $valuerow->$fieldName;
						if ( isset( $transToLabels[ $fieldName ] ) ) {
							$display_value = $transToLabels[ $fieldName ][ $valuerow->$fieldName ];
						}
						if ( $field->field_type == 'date' &&  isset($dateFormats[ $fieldName ]) && $dateFormats[ $fieldName ] && $valuerow->$fieldName ) {
							$myDt = date_create_from_format( 'Y-m-d H:i:s', $valuerow->$fieldName );
							if ($myDt) {
								setlocale(LC_ALL, __('en_US','your-tables')); //only necessary if the locale isn't already set
								$display_value = strftime($this->ConvertDateJavascriptToPhp( $dateFormats[ $fieldName ] ), $myDt->getTimestamp());
								//$display_value = $myDt->format(  );
								if ( $valuerow->$fieldName == '0000-00-00 00:00:00' || $valuerow->$fieldName == '0000-00-00' || $valuerow->$fieldName == '' ) {
									$display_value = '';
								}
							} else {
								$echoErrors .= $valuerow->$fieldName;
								$display_value =$valuerow->$fieldName;
								$echoErrors .= '<h2>'.__( 'Date error', 'your-tables' ).'</h2><p>'.__( 'This date could not be processed', 'your-tables' ).': "'.$valuerow->$fieldName.'"</p>';
							}
						}
						if ( isset($dateFormats[ $fieldName ]) && $dateFormats[ $fieldName ] && ! $valuerow->$fieldName ) {
							$display_value = '';
						}
						$class = '';
						if ( isset( $classes[ $fieldName ] ) ) {
							$class = ' class="' . $classes[ $fieldName ] . '" ';
						}
						$echoContents .= '<td' . $class . '>' . htmlspecialchars( __( $display_value, 'your-tables' ) , ENT_QUOTES, 'UTF-8') . '</td>';
					}
					$selectQ = '';
					foreach ( $primaries as $prim ) {
						$selectQ .= '&' . $prim . '=' . $valuerow->$prim;
					}
					$echoContents .= '<td>';
					$dashIconEdit='<span class="dashicons dashicons-welcome-write-blog"></span>';
					$dashIconDelete='<span class="dashicons dashicons-trash"></span>';
					if (!$this->shared->dashIconsPresent) {
						$dashIconEdit='<img src="'.plugins_url() . '/your-tables/images/edit-gray.png'.'" alt="'.__( 'Edit', 'your-tables' ).'" />';
						$dashIconDelete='<img src="'.plugins_url(  ) . '/your-tables/images/trash.png'.'" alt="'.__( 'Delete', 'your-tables' ).'" />';
					}
					$echoContents .= '<a href="' . $this->AdminPath . '&wp_your_tables__action=editform&wp_your_tables__table=' . $tableRow->name . $selectQ . '">'.$dashIconEdit.'</a>';
					if ( $tableRow->delete_allowed == 'Y' ) {
						$echoContents .= '&nbsp;<a href="' . $this->AdminPath . '&wp_your_tables__action=deleteitem&wp_your_tables__table=' . $tableRow->name . $selectQ . '">'.$dashIconDelete.'</a>';
					}
					$echoContents .= '</td></tr>';
				}

				$echoContents .= '</tbody></table>';
				$echoContents .= '</div>';
			}

		} else {
			$echoErrors .= '<h2>' . $table . ' ' . __( 'does not exist', 'your-tables' ) . '!</h2>';
		}

		echo $echoTop;
		echo $echoErrors;
		echo $echoContents;
	}

	/*function ConvertDateJavascriptToPhp( $JavascriptFormat ) {
		return strtr( $JavascriptFormat, array(
			'd'  => 'j',
			'dd' => 'd',
			'o'  => 'z',
			'oo' => 'z',
			'D'  => 'D',
			'DD' => 'l',
			'm'  => 'n',
			'mm' => 'm',
			'M'  => 'M',
			'MM' => 'F',
			'y'  => 'y',
			'yy' => 'Y'
		) );
	}*/

	function ConvertDateJavascriptToPhp( $JavascriptFormat ) {
		return strtr( $JavascriptFormat, array(
			'd'  => '%e',
			'dd' => '%d',
			'o'  => '%j',
			'oo' => '%j',
			'D'  => '%a',
			'DD' => '%A',
			'm'  => '%m',
			'mm' => '%m',
			'M'  => '%b',
			'MM' => '%B',
			'y'  => '%y',
			'yy' => '%Y'
		) );
	}

	function DisplayForm() {
		global $wpdb;
		$wpdb->hide_errors();
		
		$echoTop='';
		$echoErrors='';
		$echoContents='';
		$isYourTablesData = false;

		$action           = 'newform';
		if ( isset( $_REQUEST['wp_your_tables__action'] ) ) {
			$action = $_REQUEST['wp_your_tables__action'];
		}
		$table = $wpdb->prefix . 'your_tables';
		if ( isset( $_REQUEST['wp_your_tables__table'] ) ) {
			$table = $_REQUEST['wp_your_tables__table'];
		}
		$FormScript = '';
		$row        = $wpdb->get_row(
			$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables WHERE active = %s and name = %s order by position', 'Y', $table )
		);
		if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).'</h2><p>'.$wpdb->last_error.' (cyt-view5)</p>';
		if ( count( $row ) ) {

			$valuerow  = array();
			$fieldrows = $wpdb->get_results(
				$wpdb->prepare( 'select * from ' . $wpdb->prefix . 'your_tables_fields WHERE active = %s and table_name = %s order by position', 'Y', $table )
			);
			if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).'</h2><p>'.$wpdb->last_error.' (cyt-view6)</p>';
			if ( count( $fieldrows ) ) {
				if ( $action != 'newform' ) {
					$primaries     = array();
					$primary_types = array();
					foreach ( $fieldrows as $fieldrow ) {
						if ( $fieldrow->primary_key == 'Y' ) {
							$primaries[] = $fieldrow->field_name;
							$veld_type   = '%s';
							if ( $fieldrow->field_type == 'int' ) {
								$veld_type = '%d';
							}
							if ( $fieldrow->field_type == 'float' ) {
								$veld_type = '%f';
							}
							$primary_types[] = $veld_type;

						}

					}
					$myvars   = '';
					$myvalues = array();
					foreach ( $primaries as $id => $prim ) {
						if ( isset( $_REQUEST[ $prim ] ) ) {
							$myvars .= $prim . ' = ' . $primary_types[ $id ] . ' and ';
							$myvalues[] = $_REQUEST[ $prim ];
						}
					}
					$valuerow = (array) $wpdb->get_row(
						$wpdb->prepare( 'select * from ' . $table . ' WHERE ' . substr( $myvars, 0, - 5 ), $myvalues )
					);
					if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).'</h2><p>'.$wpdb->last_error.' (cyt-view7)</p>';
				}
				$FormScript .= Your_Tables_Form::DrawRequiredStart();
				if ( $action == 'newform' ) {
					$echoTop .= '<div class="wrap"><h2>New ' . htmlspecialchars($row->label_single_item, ENT_QUOTES, 'UTF-8') . '</h2>';
				}
				if ( $action != 'newform' ) {
					$echoTop .= '<div class="wrap"><h2>Edit ' . htmlspecialchars($row->label_single_item, ENT_QUOTES, 'UTF-8') . '</h2>';
				}
				$echoContents .= '<form name="myform" id="myform" action="' . $this->AdminPath . '" method="post" >';
				if ( $action == 'newform' ) {
					$echoContents .= '<input type="hidden" name="wp_your_tables__action" value="createitem">';
				}
				if ( $action != 'newform' ) {
					$echoContents .= '<input type="hidden" name="wp_your_tables__action" value="saveitem">';
				}
				$echoContents .= '<input type="hidden" name="wp_your_tables__table" value="' . $table . '">';
				$echoContents .= '<input type="hidden" name="wp_your_tables__whichbutton" value="">';
				$echoContents .= '<table class="wp-list-table widefat"  ><colgroup><col style="width:20%;" /><col style="width:80%; text-align:left;" /></colgroup>';
				foreach ( $fieldrows as $fieldrow ) {
					$value = '';

					if ( $action != 'newform' && isset( $valuerow[ $fieldrow->field_name ] ) ) {
						$value = $valuerow[ $fieldrow->field_name ];
					}
					if ( $action == 'newform' && $fieldrow->default_value != '' ) {
						$value = $fieldrow->default_value;
					}
					if ( $fieldrow->field_name == 'name' && ( $value == $wpdb->prefix . 'your_tables' || $value == $wpdb->prefix . 'your_tables_fields' ) ) {
						$isYourTablesData = true;
					}
					if ( $fieldrow->field_name == 'table_name' && ( $value == $wpdb->prefix . 'your_tables' || $value == $wpdb->prefix . 'your_tables_fields' ) ) {
						$isYourTablesData = true;
					}

					$echoContents .= '<tr  ><th>' . htmlspecialchars(__( $fieldrow->label, 'your-tables' ), ENT_QUOTES, 'UTF-8');
					if ( $fieldrow->mandatory == 'Y' && $fieldrow->control_type != 'autonumber' && $fieldrow->control_type != 'checkbox' ) {
						$echoContents .= ' <span style="color:red;">*</span>';
					}
					$echoContents .= '</th><td>';
					if ( $fieldrow->control_type == 'single_line_text' ) {
						$field = new Your_Tables_Form( $fieldrow->field_name, 'text', $value, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
						$echoContents .= $field->DrawField();

					}
					if ( $fieldrow->control_type == 'autonumber' ) {
						if ( $action == 'newform' ) {
							$echoContents .= __( 'Auto Number', 'your-tables' );
						} else {
							$field = new Your_Tables_Form( $fieldrow->field_name, 'text', $value, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
							$echoContents .= $field->DrawField();
						}

					}
					$field_selections = array();
					if ( $fieldrow->control_type == 'listbox' || $fieldrow->control_type == 'combobox' || $fieldrow->control_type == 'radiobox' ) {
						if ( strpos( $fieldrow->field_labels, ',' ) && strpos( $fieldrow->field_values, ',' ) ) {
							$field_labels = explode( ',', $fieldrow->field_labels );
							$field_values = explode( ',', $fieldrow->field_values );
							foreach ( $field_labels as $id => $label ) {
								$field_selections[ $field_values[ $id ] ] = __( $label, 'your-tables' );
							}
						}
						if ( $fieldrow->field_values == '' && $fieldrow->field_labels == '' && $fieldrow->field_query != '' ) {
							//echo "jo";
							$field_selections[''] = '--- ' . __( 'Select', 'your-tables' ) . ' ---';
							$fieldrows            = $wpdb->get_results( $this->ProtectSQL( $fieldrow->field_query ), ARRAY_N );
							if ($wpdb->last_error) $echoErrors .= '<h2>'.__( 'Database error', 'your-tables' ).' '.__( 'at field', 'your-tables' ).' "'.$fieldrow->field_name.'"</h2><p>'.$wpdb->last_error.' (cyt-view8)</p>';
							if ( count( $fieldrows ) ) {
								//var_dump($fieldrows);
								foreach ( $fieldrows as $myfieldrow ) {
									$field_selections[ $myfieldrow[0] ] = $myfieldrow[1];
								}
							}

						}
					}
					if ( $fieldrow->control_type == 'combobox' ) {
						$field = new Your_Tables_Form( $fieldrow->field_name, 'select', $value, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );

						foreach ( $field_selections as $id => $label ) {
							$field->AddOption( $id, htmlspecialchars($label, ENT_QUOTES, 'UTF-8') );
						}
						$echoContents .= $field->DrawField();

					}
					if ( $fieldrow->control_type == 'listbox' ) {
						$height = 44;
						$width  = 500;
						if ( $fieldrow->display_size ) {
							if ( strpos( $fieldrow->display_size, ',' ) ) {
								$sizeArr = explode( ',', $fieldrow->display_size );
								if ( isset( $sizeArr[1] ) ) {
									$height = intval( $sizeArr[1] );
								}
								if ( isset( $sizeArr[0] ) ) {
									$width = intval( $sizeArr[0] );
								}
							} else {
								$width = intval( $fieldrow->display_size );
							}
						}
						$field         = new Your_Tables_Form( $fieldrow->field_name, 'list', $value, $width, $fieldrow->css_class, $fieldrow->value_size );
						$field->Height = $height;

						foreach ( $field_selections as $id => $label ) {
							$field->AddOption( $id, htmlspecialchars($label, ENT_QUOTES, 'UTF-8') );
						}
						$echoContents .= $field->DrawField();

					}
					if ( $fieldrow->control_type == 'radiobox' ) {
						$field = new Your_Tables_Form( $fieldrow->field_name, 'radio', $value, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
						foreach ( $field_selections as $id => $label ) {
							$field->AddOption( $id, htmlspecialchars($label , ENT_QUOTES, 'UTF-8'));
						}
						$echoContents .= $field->DrawField();

					}
					if ( $fieldrow->control_type == 'checkbox' ) {
						$field = new Your_Tables_Form( $fieldrow->field_name, 'checkbox', $fieldrow->field_values, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
						if ( $value == $fieldrow->field_values ) {
							$field->Checked = true;
						}
						$echoContents .= $field->DrawField();
						$echoContents .= '&nbsp;<label for="' . $fieldrow->field_name . '">' .htmlspecialchars (__( $fieldrow->field_labels, 'your-tables' ), ENT_QUOTES, 'UTF-8') . '</label>';

					}
					if ( $fieldrow->control_type == 'number' ) {
						$field = new Your_Tables_Form( $fieldrow->field_name, 'text', $value, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
						$echoContents .= $field->DrawField();

					}
					if ( $fieldrow->control_type == 'date' ) {
						/*
							d - day of month (no leading zero)
							dd - day of month (two digit)
							o - day of year (no leading zeros)
							oo - day of year (three digit)
							D - day name short
							DD - day name long
							m - month of year (no leading zero)
							mm - month of year (two digit)
							M - month name short
							MM - month name long
							y - year (two digit)
							yy - year (four digit)
						*/
						if ( $value == '0000-00-00 00:00:00' || $value == '0000-00-00' ) {
							$value = '';
						}
						$fieldhidden = new Your_Tables_Form( 'hiddenDt_' . $fieldrow->field_name, 'hidden', $value, $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
						$echoContents .= $fieldhidden->DrawField();
						$field = new Your_Tables_Form( $fieldrow->field_name, 'text', '', $fieldrow->display_size, $fieldrow->css_class, $fieldrow->value_size );
						$echoContents .= $field->DrawField();
						$format = 'yy-mm-dd';
						if ( $fieldrow->format ) {
							$format = $fieldrow->format;
						}
						$echoContents .= '  <script>
							  jQuery(function() {
								jQuery( "#' . $fieldrow->field_name . '" ).datepicker( { altField: "#hiddenDt_' . $fieldrow->field_name . '", altFormat: "yy-mm-dd",
									closeText: "'.__('Close','your-tables').'",
									prevText: "'.__('Previous','your-tables').'",
									nextText: "'.__('Next','your-tables').'",
									currentText: "'.__('Today','your-tables').'",
									monthNames: ["'.__('January','your-tables').'","'.__('February','your-tables').'","'.__('March','your-tables').'","'.__('April','your-tables').'","'.__('May','your-tables').'","'.__('June','your-tables').'",
									"'.__('July','your-tables').'","'.__('August','your-tables').'","'.__('September','your-tables').'","'.__('October','your-tables').'","'.__('November','your-tables').'","'.__('December','your-tables').'"],
									monthNamesShort: ["'.__('Jan','your-tables').'.","'.__('Feb','your-tables').'.","'.__('Mar','your-tables').'.","'.__('Apr','your-tables').',","'.__('May','your-tables').'","'.__('Jun','your-tables').'.",
									"'.__('Jul','your-tables').'.","'.__('Aug','your-tables').'.","'.__('Sep','your-tables').'.","'.__('Oct','your-tables').'.","'.__('Nov','your-tables').'.","'.__('Dec','your-tables').'."],
									dayNames: ["'.__('Sunday','your-tables').'","'.__('Monday','your-tables').'","'.__('Thuesday','your-tables').'","'.__('Wednesday','your-tables').'","'.__('Thursday','your-tables').'","'.__('Friday','your-tables').'","'.__('Saturday','your-tables').'"],
									dayNamesShort: ["'.__('Sun','your-tables').'.","'.__('Mon','your-tables').'.","'.__('Tue','your-tables').'.","'.__('Wed','your-tables').'.","'.__('Thu','your-tables').'.","'.__('Fri','your-tables').'.","'.__('Sat','your-tables').'."],
									dayNamesMin: [ "'.__('Su','your-tables').'", "'.__('Mo','your-tables').'", "'.__('Tu','your-tables').'", "'.__('We','your-tables').'", "'.__('Th','your-tables').'", "'.__('Fr','your-tables').'", "'.__('Sa','your-tables').'" ],
									weekHeader: "'.__('wk','your-tables').'.",
									dateFormat: "dd/mm/yy",
									firstDay: 1,
									isRTL: false,
									showMonthAfterYear: false,
									yearSuffix: ""});

							  jQuery( "#' . $fieldrow->field_name . '" ).datepicker( "setDate", "' . $value . '" );
							  jQuery( "#' . $fieldrow->field_name . '" ).datepicker(
									"option","dateFormat", "' . $format . '"
								);
								
							});

							  </script>';

					}
					if ( $fieldrow->control_type == 'multiple_line_text' ) {
						$height = 44;
						$width  = 500;
						if ( $fieldrow->display_size ) {
							if ( strpos( $fieldrow->display_size, ',' ) ) {
								$sizeArr = explode( ',', $fieldrow->display_size );
								if ( isset( $sizeArr[1] ) ) {
									$height = intval( $sizeArr[1] );
								}
								if ( isset( $sizeArr[0] ) ) {
									$width = intval( $sizeArr[0] );
								}
							} else {
								$width = intval( $fieldrow->display_size );
							}
						}
						$field         = new Your_Tables_Form( $fieldrow->field_name, 'textarea', $value, $width, $fieldrow->css_class, $fieldrow->value_size );
						$field->Height = $height;
						$echoContents .= $field->DrawField();

					}
					if ( $action != 'newform' && $fieldrow->primary_key == 'Y' ) {
						$fieldp = new Your_Tables_Form( 'primary_key_' . $fieldrow->field_name, 'hidden', $value, 0, "", $fieldrow->value_size );
						$echoContents .= $fieldp->DrawField();
					}
					if ( $fieldrow->mandatory == 'Y' && $fieldrow->control_type != 'autonumber' && $fieldrow->control_type != 'checkbox' ) {
						$FormScript .= $field->DrawRequired( $fieldrow->label . " " . __( 'is required', 'your-tables' ), 'myform' );

					}
					$echoContents .= "</td></tr>";
				}

				$echoContents .= "<tr><th>&nbsp;</th><td>";
				$maySave = true;
				if ( $this->shared->modifyMe == 'N' && $isYourTablesData ) {
					$maySave = false;
				}
				if ( $maySave ) {
					if ( $action == 'newform' ) {
						$echoContents .= '<input class="add-new-h2" type="button" value="' . __( 'Create', 'your-tables' ) . '"  onclick="SendBack(\'create\');">';
						$echoContents .= '&nbsp;<input class="add-new-h2" type="button" value="' . __( 'Create + New', 'your-tables' ) . '"  onclick="SendBack(\'createandnew\');">';
						$echoContents .= '&nbsp;<input class="add-new-h2" type="button" value="' . __( 'Create + Go to list', 'your-tables' ) . '"  onclick="SendBack(\'ready\');">';
						$echoContents .= '&nbsp;<input class="add-new-h2" type="button" value="' . __( 'Cancel', 'your-tables' ) . '" onclick="SendBack(\'cancel\');">';
					}
					if ( $action != 'newform' ) {
						$echoContents .= '<input class="add-new-h2" type="button" value="' . __( 'Save', 'your-tables' ) . '" onclick="SendBack(\'save\');">';
						$echoContents .= '&nbsp;<input class="add-new-h2" type="button" value="' . __( 'Save + Go to list', 'your-tables' ) . '" onclick="SendBack(\'ready\');">';
						$echoContents .= '&nbsp;<input class="add-new-h2" type="button" value="' . __( 'Cancel', 'your-tables' ) . '" onclick="SendBack(\'cancel\');">';
					}
				} else {
					$echoContents .= __( 'Saving not allowed', 'your-tables' );
				}
				$echoContents .= '</td></tr></table></form></div>';
				$FormScript .= Your_Tables_Form::DrawRequiredEnd();
				$echoContents .= '<script>
			function SendBack($action) {
				document.getElementById("myform").wp_your_tables__whichbutton.value=$action; 
				if ($action=="cancel") {
					document.getElementById("myform").submit();
				}
				else if (validate() ) {
					document.getElementById("myform").submit();
				}
				
			}
		
		' . $FormScript . '</script>';

			}


		} else {
			$echoErrors .= '<h1>' . $table . ' ' . __( 'does not exist', 'your-tables' ) . '!</h1>';
		}

		echo $echoTop;
		echo $echoErrors;
		echo $echoContents;

	}

	function ProtectSQL( $sql ) {
		return str_ireplace( array(
			'delete from ',
			'insert into ',
			'grant ',
			'create ',
			'alter ',
			'update '
		), array( '' ), $sql );
	}

	function DisplaySettings() {
		$modifyMeY = '';
		$modifyMeN = '';
		if ( $this->shared->modifyMe == 'N' ) {
			$modifyMeN = ' selected';
		} else {
			$modifyMeY = ' selected';
		}

		echo '<div class="wrap">
      <h2>' . __( 'Your Tables Settings', 'your-tables' ) . '</h2>

      <form method="post" action="options.php"><input type="hidden" name="page" value="your_tables"><input name="your_tables_database_version" value="' . $this->shared->databaseVersion . '" type="hidden" />';
		settings_fields( 'your_tables' );
		echo '<table class="form-table">
          
      <tr>
      <th scope="row">' . __( 'Enable changes in Your Tables and in Your Tables Fields', 'your-tables' ) . '</th>
      <td><select name="your_tables_modify_me" ><option' . $modifyMeN . ' value="N">' . __( 'No', 'your-tables' ) . '</option><option' . $modifyMeY . ' value="Y">' . __( 'Yes (not recommended)', 'your-tables' ) . '</option></select></td>
      </tr>
	  
      <tr>
      <th scope="row">' . __( 'Which roles can administrate your tables', 'your-tables' ) . '</th>
      <td><select size="6" multiple name="your_tables_admins[]" >';
		if ( ! is_array( $this->shared->adminRoles ) ) {
			$this->shared->adminRoles = array();
		}
		foreach ( get_editable_roles() as $role_name => $role_info ) {
			echo '<option value="' . $role_name . '" ';
			if ( in_array( $role_name, $this->shared->adminRoles ) ) {
				echo 'selected ';
			}
			echo '>' . $role_name . '</option>';
		}
		echo '</select></td>
      </tr>

      <tr>
      <th scope="row">' . __( 'Which roles can use your tables', 'your-tables' ) . '</th>
      <td><select size="6" multiple name="your_tables_users[]" >';
		if ( ! is_array( $this->shared->userRoles ) ) {
			$this->shared->userRoles = array();
		}
		foreach ( get_editable_roles() as $role_name => $role_info ) {
			echo '<option value="' . $role_name . '" ';
			if ( in_array( $role_name, $this->shared->userRoles ) ) {
				echo 'selected ';
			}
			echo '>' . $role_name . '</option>';
		}
		echo '</select></td>
      </tr>

	  
</table>
      
	  ';

		echo '

      <p class="submit">
      <input type="submit" class="button-primary" value="' . __( 'Save Changes', 'your-tables' ) . '" />
      </p>

      </form>
      </div>';

	}

	function doNotUse_JustForLanguage() {
		$novalue = __( 'Active', 'your-tables' );
		$novalue = __( 'Delete Allowed', 'your-tables' );
		$novalue = __( 'Label for multiple items', 'your-tables' );
		$novalue = __( 'Label for Single Item', 'your-tables' );
		$novalue = __( 'Table Name', 'your-tables' );
		$novalue = __( 'Order By Clause', 'your-tables' );
		$novalue = __( 'Position', 'your-tables' );
		$novalue = __( 'Active', 'your-tables' );
		$novalue = __( 'Control Type', 'your-tables' );
		$novalue = __( 'CSS Class', 'your-tables' );
		$novalue = __( 'Default Value', 'your-tables' );
		$novalue = __( 'Display in Table', 'your-tables' );
		$novalue = __( 'Display Size', 'your-tables' );
		$novalue = __( 'Field Labels', 'your-tables' );
		$novalue = __( 'Field Name', 'your-tables' );
		$novalue = __( 'Field Query', 'your-tables' );
		$novalue = __( 'Field Type', 'your-tables' );
		$novalue = __( 'Field Values', 'your-tables' );
		$novalue = __( 'Format', 'your-tables' );
		$novalue = __( 'Label', 'your-tables' );
		$novalue = __( 'Mandatory Field', 'your-tables' );
		$novalue = __( 'Position', 'your-tables' );
		$novalue = __( 'Primary Key', 'your-tables' );
		$novalue = __( 'Table Name', 'your-tables' );
		$novalue = __( 'Value Size', 'your-tables' );

		$novalue = __( 'Single Line', 'your-tables' );
		$novalue = __( 'Text Area', 'your-tables' );
		$novalue = __( 'Number', 'your-tables' );
		$novalue = __( 'Date', 'your-tables' );
		$novalue = __( 'Radiobox', 'your-tables' );
		$novalue = __( 'Checkbox', 'your-tables' );
		$novalue = __( 'Dropdown', 'your-tables' );
		$novalue = __( 'Listbox', 'your-tables' );
		$novalue = __( 'Autonumber', 'your-tables' );
		$novalue = __( 'Yes', 'your-tables' );
		$novalue = __( 'No', 'your-tables' );
		$novalue = __( 'Integer', 'your-tables' );
		$novalue = __( 'Char', 'your-tables' );
		$novalue = __( 'Date', 'your-tables' );
		$novalue = __( 'Text', 'your-tables' );
		$novalue = __( 'Float', 'your-tables' );
		$novalue=__('en_US','your-tables');
		//$novalue=__('','your-tables');
	}

}