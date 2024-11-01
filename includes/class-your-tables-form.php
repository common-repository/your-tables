<?php
/**
 * Class Your_Tables_Form
 * This class takes care of actually drawing the HTML Form elements
 */
class Your_Tables_Form {
	var $Name;
	var $Type;
	var $Value;
	var $Width = 0;
	var $Height = 50;
	var $Required = false;
	var $CssClass;
	var $MaxLength = 0;
	var $Checked = false;
	var $ReadOnly = false;
	var $JavascriptEvents = array();
	var $RequirementDependencies = array();
	var $Options = array();
	var $RealOptionCount = 0;
	var $ReadonlyOptions = array();
	var $CrLf = "\r\n";

	function __construct( $name, $type, $value, $width = 0, $cssclass = "", $maxlength = 0 ) {
		$this->Name      = $name;
		$this->Type      = $type;
		$this->Value     = $value;
		$this->Width     = $width;
		$this->CssClass  = $cssclass;
		$this->MaxLength = $maxlength;


	}

	/**
	 * This function takes of generating the javascript that checks the validity of the filled-in value
	 *
	 * @param $message What to display if its not valid
	 * @param $formID What form to look in
	 * @param string $type besides required, other options like email and date are possible for future developments
	 *
	 * @return string
	 */
	function DrawRequired( $message, $formID, $type = 'required' ) {
		$this->Required = true;
		$returnstr      = '';
		$tempscript     = '';
		if ( $type == 'required' ) {
			if ( $this->Type == 'text' || $this->Type == 'hidden' || $this->Type == 'date' ) {
				$returnstr .= 'if ( ';
				foreach ( $this->RequirementDependencies as $val ) {
					$returnstr .= $val . ' && ';
				}
				$returnstr .= 'document.getElementById("' . $formID . '").' . $this->Name . '.value=="") {mess+="' . $message . '\r\n"; ok=false; }' . $this->CrLf;
			} else if ( $this->Type == 'select' || $this->Type == 'list' || $this->Type == 'hidden' ) {
				$returnstr .= 'if ( ';
				foreach ( $this->RequirementDependencies as $val ) {
					$returnstr .= $val . ' && ';
				}
				$returnstr .= 'document.getElementById("' . $formID . '").' . $this->Name . '.value=="" ) {mess+="' . $message . '\r\n"; ok=false; }' . $this->CrLf;
			} else if ( $this->Type == 'radio' ) {
				$returnstr .= 'if ( ';
				foreach ( $this->RequirementDependencies as $val ) {
					$returnstr .= $val . ' && ';
				}
				if ( $this->RealOptionCount > 1 ) {

					$count = 0;
					foreach ( $this->Options as $key => $val ) {
						if ( ! $this->ReadonlyOptions[ $key ] ) {
							$tempscript .= ' document.getElementById("' . $formID . '").' . $this->Name . '[' . ( $count ) . '].checked ||';
							$count ++;
						}
					}
					$returnstr .= '!(' . substr( $tempscript, 0, - 2 ) . ')) {mess+="' . $message . '\r\n"; ok=false; }' . $this->CrLf;
				} else {
					$tempscript .= ' document.getElementById("' . $formID . '").' . $this->Name . '.checked ||';
					$returnstr .= '!(' . substr( $tempscript, 0, - 2 ) . ')) {mess+="' . $message . '\r\n"; ok=false; }' . $this->CrLf;
				}
			} else if ( $this->Type == 'checkbox' ) {
				$returnstr .= 'if ( ';
				foreach ( $this->RequirementDependencies as $val ) {
					$returnstr .= $val . ' && ';
				}
				$returnstr .= '!document.getElementById("' . $formID . '").' . $this->Name . '.checked) {mess+="' . $message . '\r\n"; ok=false; }' . $this->CrLf;
			}

		}

		/*
		 * For future use
		if ( $type == 'date' ) {
			$returnstr .= 'if ( ';
			foreach ( $this->RequirementDependencies as $val ) {
				$returnstr .= $val . ' && ';
			}
			$returnstr .= 'testDate(document.getElementById("' . $formID . '").' . $this->Name . '.value,"' . $message . '")!="") {mess+=testDate(document.getElementById("' . $formID . '").' . $this->Name . '.value,"' . $message . '")+"\r\n"; ok=false; }'.$this->CrLf;
		}
		if ( $type == 'email' ) {
			$returnstr .= 'if ( ';
			foreach ( $this->RequirementDependencies as $val ) {
				$returnstr .= $val . ' && ';
			}
			$returnstr .= '!isValidEmail(document.getElementById("' . $formID . '").' . $this->Name . '.value)) {mess+="' . $message . '\r\n"; ok=false; }'.$this->CrLf;
		}
		*/

		return $returnstr;
	}

	/**
	 * Simple function to get result HTML for the form element
	 *
	 * @return string The HTML that is returned
	 */
	function DrawField() {
		if ( $this->Type == 'text' ) {
			return $this->DrawTextField();
		}
		if ( $this->Type == 'textarea' ) {
			return $this->DrawTextArea();
		}
		if ( $this->Type == 'select' ) {
			return $this->DrawSelectList();
		}
		if ( $this->Type == 'list' ) {
			return $this->DrawSelectList( 'list' );
		}
		if ( $this->Type == 'radio' ) {
			return $this->DrawRadioList();
		}
		if ( $this->Type == 'hidden' ) {
			return $this->DrawTextField( 'hidden' );
		}
		if ( $this->Type == 'checkbox' ) {
			return $this->DrawCheckboxField();
		}
	}

	function DrawTextField( $type = 'text' ) {
		$field = '';
		$field .= '<input type="' . $type . '" name="' . $this->Name . '" id="' . $this->Name . '" ';
		if ( $this->Width != 0 ) {
			$field .= 'style="width:' . $this->Width . 'px;" ';
		}
		if ( $this->CssClass != '' ) {
			$field .= 'class="' . $this->CssClass . '" ';
		}
		if ( $this->ReadOnly ) {
			$field .= 'readonly ';
		}
		if ( $this->MaxLength != 0 && $type != 'hidden' ) {
			$field .= 'maxlength="' . $this->MaxLength . '" ';
		}
		if ( $this->Value != '' ) {
			$field .= 'value="' . str_replace( '"', '&quot;', $this->Value ) . '" ';
		}
		foreach ( $this->JavascriptEvents as $key => $val ) {
			$field .= ' ' . $key . '="' . $val . '" ';
		}
		$field .= '>';

		return $field;
	}

	function DrawTextArea() {
		$field = '';
		$field .= '<textarea name="' . $this->Name . '" id="' . $this->Name . '" ';
		$style = '';
		if ( $this->Width != 0 ) {
			$style .= 'width:' . $this->Width . 'px; ';
		}
		if ( $this->Height != 0 ) {
			$style .= 'height:' . $this->Height . 'px; ';
		}
		if ( $style ) {
			$field .= 'style="' . $style . '" ';
		}
		if ( $this->CssClass != '' ) {
			$field .= 'class="' . $this->CssClass . '" ';
		}
		if ( $this->ReadOnly ) {
			$field .= 'readonly ';
		}
		if ( $this->MaxLength != 0 ) {
			$field .= 'maxlength="' . $this->MaxLength . '" ';
		}
		foreach ( $this->JavascriptEvents as $key => $val ) {
			$field .= ' ' . $key . '="' . $val . '" ';
		}
		$field .= '>';
		if ( $this->Value != '' ) {
			$field .= str_replace( '<', '&lt;', $this->Value );
		}
		$field .= '</textarea>';

		return $field;
	}

	function DrawCheckboxField() {
		// let op bij readonly. RealOptionCount moet blijven werken!
		$field = '';
		$field .= '<input type="checkbox" name="' . $this->Name . '" id="' . $this->Name . '" ';
		if ( $this->Width != 0 ) {
			$field .= 'style="width:' . $this->Width . 'px;" ';
		}
		if ( $this->CssClass != '' ) {
			$field .= 'class="' . $this->CssClass . '" ';
		}
		$field .= 'value="' . str_replace( '"', '', $this->Value ) . '" ';
		foreach ( $this->JavascriptEvents as $key => $val ) {
			$field .= ' ' . $key . '="' . $val . '" ';
		}
		if ( $this->Checked ) {
			$field .= 'checked ';
		}
		$field .= '>';

		return $field;
	}

	function DrawRadioList() {
		$br      = '';
		$retval  = '';
		$counter = 0;
		foreach ( $this->Options as $key => $val ) {
			$retval .= $br . '<label>';
			if ( ! $this->ReadonlyOptions[ $key ] ) {
				$retval .= '<input type="radio" name="' . $this->Name . '" id="' . $this->Name . '" value="' . $key . '" ';
				foreach ( $this->JavascriptEvents as $jekey => $jeval ) {
					$retval .= ' ' . $jekey . '="' . $jeval . '" ';
				}
				if ( $this->Value == $key ) {
					$retval .= 'checked ';
				}
				$retval .= ' >';
				$counter ++;
			}
			$retval .= '&nbsp;' . $val . '</label>';
			$br = '<br \>';
		}

		return $retval;
	}

	function DrawSelectList( $type = 'select' ) {
		$retval = '';
		$size   = 1;
		if ( $type == 'list' ) {
			$size = 2;
		}
		$retval .= '<select size="' . $size . '" name="' . $this->Name . '" id="' . $this->Name . '" ';
		$style = '';
		if ( $this->Width != 0 ) {
			$style .= 'width:' . $this->Width . 'px; ';
		}
		if ( $type == 'list' && $this->Height != 0 ) {
			$style .= 'height:' . $this->Height . 'px; ';
		}
		if ( $style ) {
			$retval .= 'style="' . $style . '" ';
		}
		if ( $this->CssClass != '' ) {
			$retval .= 'class="' . $this->CssClass . '" ';
		}
		foreach ( $this->JavascriptEvents as $jekey => $jeval ) {
			$retval .= ' ' . $jekey . '="' . $jeval . '" ';
		}
		$retval .= ' > ';
		foreach ( $this->Options as $key => $val ) {
			if ( ! $this->ReadonlyOptions[ $key ] ) {
				$retval .= '<option value="' . $key . '" ';
				if ( $this->Value == $key ) {
					$retval .= 'selected ';
				}
				$retval .= ' >' . $val . '</option>';
			}
		}
		$retval .= '</select>';

		return $retval;
	}

	/**
	 * Function to add options to radio boxes en select boxes
	 *
	 * @param $value
	 * @param $text
	 * @param bool $readonly
	 */
	function AddOption( $value, $text, $readonly = false ) {
		$this->Options[ $value ]         = $text;
		$this->ReadonlyOptions[ $value ] = $readonly;
		if ( $readonly == false ) {
			$this->RealOptionCount ++;
		}
	}

	/**
	 * Add javascript events to the HTML control
	 * <input onchange='alert();' />
	 * <input $eventname='$code' />
	 *
	 * @param $eventname
	 * @param $code
	 */
	function AddJavascriptEvent( $eventname, $code ) {
		$this->JavascriptEvents[ $eventname ] = $code;
	}

	/**
	 * If certain requirements are based on other values, it can be done here
	 *
	 * @param $type
	 * @param $formID
	 * @param $dependon
	 */
	function AddRequirementDependency( $type, $formID, $dependon ) {
		$dependency = '';
		if ( $type == 'ischecked' ) {
			$dependency = 'document.getElementById("' . $formID . '").' . $dependon . '.checked';
		}
		if ( $type == 'isnotchecked' ) {
			$dependency = '!document.getElementById("' . $formID . '").' . $dependon . '.checked';
		}
		if ( $type == 'Y' ) {
			$dependency = 'document.getElementById("' . $formID . '").' . $dependon . '.value=="' . $type . '"';
		}
		if ( $dependency == '' ) {
			$dependency = 'document.getElementById("' . $formID . '").' . $dependon . '.value=="' . $type . '"';
		}
		array_push( $this->RequirementDependencies, $dependency );
	}

	/**
	 * Helperfunction to generate the script start and end. Is called from the form controller (once)
	 * @return string
	 */
	static function DrawRequiredStart() {
		return 'function validate() {  mess=""; ok=true; ' . "\r\n";
	}

	static function DrawRequiredEnd() {
		return 'if (ok==true) return true; else { alert (mess); return false;} }' . "\r\n";
	}

}