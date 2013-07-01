<?php
/*******************************************************************************************************
 * class X2FormElement
 * X2FormElement is used by X2Form for generating different HTML form elements.
 * 
 * Planned Features:
 * 		1. Multiple File upload
 * 		2. Image Handeling/Resizing/Resampling
 * 
 * Author : Sameer Shelavale
 * Email  : samiirds@gmail.com, sameer@techrevol.com, sameer@possible.in 
 * Author website: http://techrevol.com, http://possible.in
 * Phone  : +91 9890103122
 * License: AGPL3, You should keep Package name, Class name, Author name, Email and website credits.
 * 			http://www.gnu.org/licenses/agpl-3.0.html
 * 			For other type of licenses please contact the author.
 * PHP Version: Tested on PHP 5.2.2 & 5.3.10
 * Dependencies : X2FormElement.php, class.dbhelper.php
 * Copyrights (C) 2012-2013 Sameer Shelavale
 * Dependencies : class.dbhelper.php, class.logg.php
 *******************************************************************************************************/
class X2FormElement{
	//major element attributes
	var $id;
	var $name;
	var $outputName;
	
	var $type;
	var $value;
	var $label;
	var $description; //when labels are not enough use this (except for radio and checkbox) 
	
	//minor element attributes
	var $attributes;
	
	//other values to be passed like events or list data
	var $options;
	var $events;
	var $config;
	
	var $oldValue;
	
	var $dbType="default";
	var $dbHandle=false;
	
	var $fileSystemChanges = array();	// old files are deleted, immediately, they are backed up
									// If some error occurs during the file Upload, the backed up files are restored
									
	var $renamedFiles = array();	//this will store all files we renamed and their actual names
	
	var $parent = false;
	
	var $errorString = '';
	
	public function X2FormElement( $eType='text', $attribs = array(), $dbTyp = 'php', &$dbHnd=false, &$parentForm ){
		$this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
		$this->config = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
		$this->events = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
		$this->dbType = $dbTyp;
		$this->dbHandle = $dbHnd;
		$this->parent = $parentForm;
		//basic properties/attributes 
		//$prop = array( 'id'=>'', 'name'=>'', 'type'=>'', 'value'=>'', 'label'=>'', 'description'=>'' );
		
		//configuration attributes that our class recognizes and uses for desired output and validation 
		$conf = array( 'title'=>'', 'language'=>false, 'languages'=>new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS ), 'prompt'=>'', 'direction'=>'', 'mandatory'=>'false', 'datatype'=>'text', 'datapattern'=>'', 'emailcheckdns'=>'false', 'validate'=>'false', 'min'=>null, 'max'=>null, 'filenameprefix'=>null, 'uploaddirectory'=>'./', 'allowmimetypes'=>null, 'allowextensions'=>null, 'maxsize'=>20, 'oldfileaction'=>'renamenew', 'iffileexists'=>'renamenew', 'imgwidth'=>false, 'imgheight'=>false, 'ifempty'=>'', 'ifinvalid'=>''  );
		
		
		if( $this->parent && $this->parent->language ){
			$this->config->language = $this->parent->language; //inherit language from parent
		}
		
		
		//now find the $prop, $conf and $attr
		foreach( $attribs as $k => $v ){
			$k = strtolower( $k );
			if( property_exists( 'X2FormElement', $k ) ){
				$this->$k = $v;
				
			}else if( array_key_exists( $k, $conf ) ){
				if( $k == 'languages' ){
					$this->config[ $k ] = $v;
				}elseif( $k == 'mandatory' || $k == 'validate' || $k == 'emailcheckdns' ){
					$this->config[ $k ] = filter_var( $v, FILTER_VALIDATE_BOOLEAN );
				}else{
					$this->config[ $k ] = "$v";
				}				
			}else{
		    	$this->attributes[ $k ] = "$v";
			}
		}
		
		
		$this->updateOutputName();
		
		
		$this->type = $eType;
			
	}
	
	
	/**************************************************************************
	 * PHP Magic Method - __sleep()
	 * 		This is used to define the properties to save during serialization.
	 * 		This is called during serialization in the getDeepClone() method 
	 **************************************************************************/
	public function __sleep(){
		return array( 'id', 'name','outputName', 'value','type', 'label', 'description', 'attributes', 'options', 'events', 'config', 'dbType', 'oldValue', 'fileSystemChanges', 'renamedFiles', 'errorString' );
	}
	
	
	/**************************************************************************
	 * function updateOutputName()
	 * 		This function updates prefixes name of the parent Collection and 
	 * 		index to the name of element. This applies only to elements within a 
	 * 		form collection.
	 *  	e.g. name of FIRST_NAME becomes ADDRESS[1][FIRST_NAME]
	 *  	and id of same becomes ADDRESS_1_FIRST_NAME
	 *  	here 'ADDRESS' is name of the collection and 1 is index within collection
	 *  	
	 **************************************************************************/
	public function updateOutputName(){
		if( $this->type != 'submit' && $this->type != 'label' 
			&& $this->parent && $this->parent->parent && $this->parent->parent instanceOf X2FormCollection ){
				$this->outputName = $this->parent->parent->name."[{$this->parent->index}][{$this->name}]";
				$this->id = $this->parent->parent->name."_{$this->parent->index}_{$this->name}";
		}else{
			$this->outputName = $this->name;
		}
	}
	
	
	/**************************************************************************
	 * function setEvent()
	 * 		This function adds an javascript event to the element.
	 * 		
	 * TODO: make it so that we can apply multiple functions/actions on same event type
	 * 	
	 **************************************************************************/
	public function setEvent( $eventName, $actionString ){
		$this->events[ $eventName ] = $actionString;
	}
	
	
	
	/***********************************************************************************
	 * function label()
	 * 		This function return label of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function label(){
		
		$label = $this->hasLanguage( $this->config->language, 'label' );
		
		if( !$label ){
			$label = $this->label;
		}
		if( $this->config->mandatory == true || $this->config->mandatory == 'true' ){
			$mand = '<span class="mandatory">*</span>';
		}
		return $label.$mand;
	}
	
	
	
	/***********************************************************************************
	 * function description()
	 * 		This function return description of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function description(){
		$desc = $this->hasLanguage( $this->config->language, 'description' );
		
		if( !$desc ){
			$desc = $this->description;
		}
		return $desc;
	}
	
	
	/***********************************************************************************
	 * function title()
	 * 		This function return label of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function title(){
		
		$title = $this->hasLanguage( $this->config->language, 'title' );
		
		if( !$title && isset( $this->config['title'] ) ){
			$title = $this->config['title'];
		}
		return $title;
	}
	
	
	/***********************************************************************************
	 * function prompt()
	 * 		This function return prompt of element.
	 * 		it is used while rendering dropdown element.
	 ***********************************************************************************/
	public function prompt(){
		
		$prompt = $this->hasLanguage( $this->config->language, 'prompt' );
		
		if( !$prompt && isset( $this->config['prompt'] ) ){
			$prompt = $this->config['prompt'];
		}
		return $prompt;
	}
	
	
	/***********************************************************************************
	 * function render()
	 * 		This function renders form element and returns the output as string.
	 * 		please note that It does not display it to screen.
	 ***********************************************************************************/
	public function render( $formName='' ){
		
		$this->updateOutputName();
		
		$toolTipText = '';
		if( strlen( $this->errorString ) > 0 ){
			$this->attributes['class'] = "errorfield ".$this->attributes['class'];
			$toolTipText = $this->errorString;
		}
		
		//render text for attributes
		foreach( $this->attributes as $atr => $act ){
			$attribTxt .= " $atr=\"$act\""; 
		}
		
		
		
		//render text for events
		foreach( $this->events as $e => $action ){
			$eventsTxt .= " $e=\"". str_replace( '"', '&quot;', trim( $action) )."\""; 
		}
		
		if( $this->id == '' ){
			$id = $this->parent->name."_".$this->outputName;
		}else{
			$id = $this->id;
		}
		
		$type = $this->type;
		
		if( strlen( $this->title() ) >0 ){
			if( strlen( $toolTipText ) >0 ){
				$toolTipText .= '<hr/>';
			}
			$toolTip = ' title="'.$toolTipText.$this->title().'" ';
		}else{
			$toolTip = ' title="'.$toolTipText.'" ';
			
		}
		
		if( $type == "text" || $type == "button" || $type == "submit" || $type == "hidden" || $type == "image" || $type == "password" || $type == "reset" || $type == "submit"  ){
			
			$str = "<input id=\"".$id."\" type=\"$type\" name=\"{$this->outputName}\" value=\"{$this->value}\" $toolTip $attribTxt $eventsTxt />";
			
		}elseif( $type == "textarea" ){
			
			$str = "<textarea id=\"".$id."\" name=\"$this->outputName\" $toolTip $classTxt $styleTxt $attribTxt $eventsTxt >{$this->value}</textarea>";
			
		}elseif( $type == "radio" ){
			
			if( $this->config['direction'] == 'vertical' ){ 
				$spacer='<br/>'; 
			}else{
				$spacer=' ';
			}
			$opts = $this->createOptions( $this->options );
			
			if( count( $opts ) == 0 ){
				$str = "<input type=\"radio\" id=\"".$id."\" name=\"$this->outputName\" value=\"$value\" $attribTxt $eventsTxt />";
			}else{
				$cnt = 0;
				foreach( $opts as $opt ){
					//var_dump( $opt );
					$checked='';
					if( $opt['value'] == $this->value ){ $checked = 'checked="true"'; }
					
					$str .= "<input type=\"radio\" id=\"$id$cnt\" name=\"".$this->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id$cnt\">{$opt['label']}</label>".$spacer;
					$cnt++;
				}
			}
			
		}elseif( $type == "checkbox" ){
			
			if( $this->config['direction'] == 'vertical' ){ 
				$spacer='<br/>'; 
			}else{
				$spacer=' ';
			}
			
			$opts = $this->createOptions( $this->options );
			
			if( count( $opts ) == 0 ){
				$str = "<input type=\"checkbox\" id=\"".$id."\" name=\"$this->outputName\" value=\"$value\" $attribTxt $eventsTxt />";
			}elseif( count( $opts ) == 1 ){
				$opt = $opts[0];
					if( is_array( $this->value ) && in_array( $opt['value'], $this->value ) ){
						$checked = 'checked="true"';
					}elseif( $opt['value'] == $this->value ){ 
						$checked = 'checked="true"'; 
					}
					$str .= "<input type=\"checkbox\" id=\"".$id."\" name=\"".$this->outputName."\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id\">{$opt['label']}</label>".$spacer;
				
			}else{
				$cnt = 0;
				foreach( $opts as $opt ){
					if( is_array( $this->value ) && in_array( $opt['value'], $this->value ) ){
						$checked = 'checked="true"';
					}elseif( $opt['value'] == $this->value ){ 
						$checked = 'checked="true"'; 
					}
					$str .= "<input type=\"checkbox\" id=\"".$id."$cnt\" name=\"".$this->outputName."[$cnt]\" value=\"{$opt['value']}\" $checked $attribTxt $eventsTxt /><label for=\"$id$cnt\">{$opt['label']}</label>".$spacer;
					$cnt++;
				}
			}
			
		}elseif( $type == "dropdown" ){
			
			if( isset( $this->attributes['multiple'] ) ){
				$multipleSuffix = "[]";
			}
			$opts = $this->createOptions( $this->options );
			
			$str = "<select id=\"".$id."\" name=\"".$this->outputName.$multipleSuffix."\" $toolTip $attribTxt $eventsTxt >";
			if( $promt = $this->prompt() ){
				$str .= "<option value=\"\" >{$promt}</option>";
			}
			foreach( $opts as $opt ){
				if( is_array( $opt ) ){
					$option = $opt['label'];
					$val = $opt['value'];
				}else{
					$option = $opt;
					$val = $opt;
				}
				if( isset( $this->attributes['multiple'] ) && is_array( $this->value ) && in_array( $val, $this->value ) ){
					$selected = 'selected="true"';
				}elseif( $val == $this->value ){ 
					$selected = 'selected="true"'; 
				}else{
					$selected = '';
				}
				$str .= "<option value=\"$val\" $selected >$option</option>";
			}
			$str .= "</select>";
		}elseif( $type == "file" ){
			
			$str = "<input id=\"".$id."\" type=\"$type\" name=\"{$this->outputName}\" $toolTip $attribTxt $eventsTxt />";
			if( $this->value ){
				$str .= " <i>( ".$this->config['uploaddirectory'].$this->value." )</i>";
			}
		}elseif( $type == "label" ){
			$labelVal = $this->hasLanguage( $this->config->language, 'value' );
			if( !$labelVal && isset( $this->value ) ){
				$labelVal = $this->value;
			}
			if( $labelVal ){
				$str .= "<label id=\"".$id."\" name=\"{$this->outputName}\"  $attribTxt $eventsTxt />{$labelVal}</label>";
			}
		}
		
		return $str;
	}
	
	
	/***********************************************************************************
	 * function createOptions()
	 * 		This function creates options from passed query or array
	 * 		in case of query, it executes query and picks up fields for name and lable
	 * 		the name of the fields are passed in $options['valuefield'] and $options['labelfield']
	 ***********************************************************************************/
	function createOptions( $options ){
		
		$data = array();
			
		if( $options['query'] ){
			
			//find dynamic query parameters for WHERE clause if any
			
			$helper = new MultiFrameworkDBHelper( $this->dbType );
			
			
			$log = $helper->query( trim( "{$options['query']}" ), MultiFrameworkDBHelper::FETCH_ALL, null, $this->dbHandle );
			if( $log['result'] == 'Success' ){
				$data= $log['data']['records'];
			}			
		}elseif( $options['create_function'] ){
			$tmpFunc = create_function( $options['params'], $options['create_function'] );
			
			if( $options['pass'] && $this->parent && isset( $this->parent->elements[ $options['pass'] ] ) ){
				$passedValue =  $this->parent->elements[ $options['pass'] ]->value ;
				$data = $tmpFunc( $passedValue );
			}else{
				$data = $tmpFunc();
			}
			
			
		}elseif( $options['phpglobal'] ){
			
			$vars = explode( ':', $options['var'] );
			$cur = $GLOBALS;
			$found = true;
			foreach( $vars as $v ){
				if( isset( $cur[ $v] ) ){
					$cur = $cur[$v];
				}else{
					$found = false;
					break;
				}
			}
			if( $found ){
				$data = $cur;
			}else{
				$data = array();
			}
			if( $options['filter'] && preg_match( '/^([a-zA-Z0-9_]+)(<|>|>=|<=|==)([a-zA-Z0-9_]+)$/', $options['filter'], $matches ) > 0 ){
				foreach( $data as $i=>$dat ){
					if( $matches[2] == '==' ){
						if( $dat[ $matches[1] ] != $this->parent->elements[ $matches[3]]->value ){
							unset( $data[$i]);
						}
					}
				}
			}
			
		}elseif( $options['array'] ){
		
			$data = $options['array'];
		}else{
			return $options;
		}
		
		$valueField = 'value';
		$lableField = $valueField;
		
		if( $options['valuefield'] ){
			$valueField = $options['valuefield'];
		}
		if(  $options['labelfield'] ){
			$lableField = $options['labelfield'];
		}
		$opt = array();
		foreach( $data as $dat ){
			if( is_array( $dat ) ){
				$tmp = array();
				if( isset( $dat[ $valueField ] ) ){
					$tmp['value'] =  $dat[ $valueField ];
				}else{
					$tmp['value'] =  $dat[0];
				}
				
				if( isset( $dat[ $lableField ] ) ){
					$tmp['label'] =  $dat[ $lableField ];
				}else{
					$tmp['label'] =  $tmp['value'];
				}
				
				$opt[] = $tmp;
			}else{
				$opt[] = array( 'value'=> $dat, 'label'=> $dat );
			}
			
		}
		
		return $opt;
		
	}
	
	
	
	/***********************************************************************************
	 * function validate()
	 * 		This function validates the value of element.
	 * 		It considers the data type, mandatory and pattern for this.
	 * 		for files it also checks if the file is uplaoded previously(i.e. if oldValue is given )
	 ***********************************************************************************/
	function validate( $oldData= array() ){
		if( $this->type == 'submit' && $this->type == 'button' && $this->type == 'reset' && $this->type == 'label' ){
			return '';
		}
		$this->errorString = '';
		//$uploadedFile = JRequest::getVar( $this->name , null, 'files', 'array');
		$datatype = trim( $this->config['datatype']);
		$datapattern = trim( $this->config['datapattern'] );
		
		if( $this->config['mandatory'] == "true" && ( $this->value== '' || $this->value== null || $this->value=== false ) ){
			//file inputs are handled later
			if( $this->type != 'file' ){
				if( $errMsg = $this->hasLanguage( $this->config->language, 'ifempty' ) ){
					$this->errorString = $errMsg ;
				}elseif( isset( $this->config['ifempty'] )  ){
					$this->errorString = $this->config['ifempty'] ;
				}else{
					$this->errorString =  "Please specify '{$this->name}'. It is a mandatory field.\n";
				}
			}			
		}
		
		//check if its file input and the file is uploaded properly
		if( $this->type == 'file' ){
			if( $_FILES[$this->name]['error'] == 1 ){
				$this->errorString =  "The uploaded file '{$this->name}' exceeds the upload_max_filesize directive in php.ini.\n";
			}elseif( $_FILES[$this->name]['error'] == 2 ){
				$this->errorString =  "The uploaded file '{$this->name}' exceeds the MAX_FILE_SIZE.\n";
			}elseif( $_FILES[$this->name]['error'] == 3 ){
				$this->errorString =  "The uploaded file '{$this->name}' was only partially uploaded.\n";
			}elseif( $_FILES[$this->name]['error'] == 6 ){
				$this->errorString =  "Missing a temporary folder.\n";
			}elseif( $_FILES[$this->name]['error'] == 7 ){
				$this->errorString =  "Failed to write file '{$this->name}' to disk.\n";
			}elseif( $_FILES[$this->name]['error'] == 4 ){
				//no file uploaded 
				if( $this->config['mandatory'] == "true" && ! $this->oldValue ){
					$this->errorString =  "Please specify '{$this->name}'. It is a mandatory field.\n";
				}
			}
		}
		
		
		if( $datatype == "url" ){
			if( !filter_var( $this->value, FILTER_VALIDATE_URL ) ){
				$this->errorString = "'{$this->name}' is not a valid ip address.\n" ;
			}
		}elseif( $datatype == "ip" ){
			if( !filter_var( $this->value, FILTER_VALIDATE_IP ) ){
				$this->errorString = "'{$this->name}' is not a valid ip address.\n" ;
			}
		}elseif( $datatype == "ipv4" ){
			if( !filter_var( $this->value, FILTER_VALIDATE_IP, array( 'flags'=> FILTER_FLAG_IPV4 ) ) ){
				$this->errorString = "'{$this->name}' is not a valid IPv4 address.\n" ;
			}
		}elseif( $datatype == "ipv6" ){
			if( !filter_var( $this->value, FILTER_VALIDATE_IP, array( 'flags'=> FILTER_FLAG_IPV6 ) ) ){
				$this->errorString = "'{$this->name}' is not a valid IPv6 address.\n" ;
			}
		}elseif( $datatype == "color" ){
			if( !preg_match( '/^#[0-9a-f]{6}$/i', $this->value ) ){
				$this->errorString = "'{$this->name}' is not a valid color.\n" ;
			}
		}elseif( $datatype == "datetime" || $datatype == "time" || $datatype == "date"  ){
			if( !strtotime( $this->value ) ){
				//this will validate time as datetime and vice a versa
				//but the exact format user want can be passed in data pattern attribute
				$this->errorString = "'{$this->name}' is not a valid {$this->datatype}.\n" ;
			} 
		}elseif( $datatype == "email" ){
			if( !$this->isEmail( $this->value ) ){
				$this->errorString = "'{$this->name}' is not a valid email address.\n" ;
			}
		}elseif( $datatype == "number" ){
			if( !preg_match( '/^[\+\-]?\d+(\.\d+)?$/', $this->value ) ){
				$this->errorString = "'{$this->name}' is not a number.\n" ;
			}
		}elseif( $datatype == "integer" ){
			if( !preg_match( '/^[\+\-]?\d+$/', $this->value ) ){
				$this->errorString = "'{$this->name}' is not a integer.\n" ;
			}
		}
		
		//check limits
		if( $datatype == "number" || $datatype == "integer" ){		
			if( !is_null( $this->config['min'] ) && $this->config['min'] > $this->value ){
				$this->errorString .= "Value of '{$this->name}' should be at least '{$this->config['min']}'.\n";
			}
			
			if( !is_null( $this->config['max'] ) && $this->config['max'] > $this->value ){
				$this->errorString = "Value of '{$this->name}' should be maximum '{$this->config['max']}' \n";
			}
		}
		
		
		if( strlen( $this->value) > 0 && strlen( $datapattern )>0 && !preg_match( $datapattern, $this->value ) ){
			$this->errorString = "'{$this->name}' does not have a valid $datatype format.\n" ;
		}
		
		/**
		 * TO DO: add more datatypes like date, email or implement an attribute format and dateformat 
		 */
		
		return $this->errorString;
		
	}
	
	
	/****************************************************************************
	 * function isEmail( $email )
	 * Validates an email address.
	 * Returns true if the email address has the email address format and the domain exists.
	 * 
	 ****************************************************************************/
	function isEmail( $email ){
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex ){
	      $isValid = false;
	   }else{
	      $domain = substr($email, $atIndex+1);
	      $local = substr($email, 0, $atIndex);
	      $localLen = strlen($local);
	      $domainLen = strlen($domain);
	      if($localLen < 1 || $localLen > 64 ){
	         // local part length exceeded
	         $isValid = false;
	      }else if ( $domainLen < 1 || $domainLen > 255 ){
	         // domain part length exceeded
	         $isValid = false;
	      }elseif( $local[0] == '.' || $local[$localLen-1] == '.' ){
	         // local part starts or ends with '.'
	         $isValid = false;
	      }elseif( preg_match( '/\\.\\./', $local ) ){
	         // local part has two consecutive dots
	         $isValid = false;
	      }elseif( !preg_match( '/^[A-Za-z0-9\\-\\.]+$/', $domain)){
	         // character not valid in domain part
	         $isValid = false;
	      }elseif( preg_match( '/\\.\\./', $domain)){
	         // domain part has two consecutive dots
	         $isValid = false;
	      }elseif( !preg_match( '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace( "\\\\", "", $local ) ) ){
	         // character not valid in local part unless 
	         // local part is quoted
	         if ( !preg_match( '/^"(\\\\"|[^"])+"$/', str_replace( "\\\\", "", $local ) ) ){
	            $isValid = false;
	         }
	      }
	      
	      if( $this->config['emailcheckdns']=='true' && $isValid && !( checkdnsrr($domain,"MX") || checkdnsrr( $domain, "A" ) ) ){
	         // domain not found in DNS
	         $isValid = false;
	      }
	   }
	   return $isValid;
	}
	
	
	/****************************************************************************
	 * function hasLanguage()
	 * 		checks if particular the element has translations available in given languages
	 * 		if a key is passed, it checks if the the element has translation
	 * 		for that key.
	 * 
	 * parameters:
	 * 		$lang - name of language (string)
	 * 		$key - name of key (string)
	 * 
	 ****************************************************************************/
	function hasLanguage( $lang, $key=false ){
		
		if( !$lang
			|| !isset( $this->config ) 
			|| !is_array( $this->config['languages'] )  
			|| !isset( $this->config['languages'][ $lang ] ) ){
			return false;	
			
		}
		
		if( !$key ){
			return true;
		}

		//key is set so find if the language has text for the key 
		
		if( is_array( $this->config['languages'][ $lang ] )  
			&& isset( $this->config['languages'][ $lang ][ $key ] ) ){
			return $this->config['languages'][ $lang ][ $key ];
		}else{
			return false;
		}
	}
	
	
	/***********************************************************************************
	 * function handleFileUploads()
	 * 		This function handles uploaded file.
	 * 		It is called after validate(). 
	 * 		If an error occurs during handling of a mandatory file, it should cancel uploads.
	 ***********************************************************************************/	
	public function handleFileUpload(){
		if( $this->type == 'file' && $_FILES[ $this->outputName ] ){
			/****
			 * TO DO: Fix the name format for file handling
			 * 			it should be a
			 */
			$file = $_FILES[ $this->outputName ];
			if( $file['error'] == 0 ){
				$result = $this->fileUpload( $file, $this->config['uploaddirectory'], $this->config['filenameprefix'], $this->config['allowmimetypes'], $this->config['allowextensions'], $this->config['maxsizemb'], $this->oldValue );
				
				if( $result['result'] == "Success" ){
					$this->value = $result['filename'];
				}else{
					return false;
				}
			}
		}
		return true;
	}
	
	/***********************************************************************************
	 * function fileUpload()
	 * 		This function handles uploaded file.
	 * 		It moves it to given target directory, this function also checks for file types, 
	 * 		extensions and also size restrictions
	 * PARAMETERS:
	 * 		$postFile		- The upload file object  
	 * 		$upload_dir		- directory where the file is to be uploaded
	 * 		$fileNamePrefix - prefix to be attached to the final file name
	 * 		$allowFileTypes - mime types to be allowed
	 * 		$allowExtensions- file extensions to be allowed( comma separated, without dot )
	 * 		$maxSizeMb		- maximum file size to be allowed
	 * 		$oldFile		- old file if uploaded. if passed the old file will be removed
	 * 						  after successful form submission
	 ***********************************************************************************/		
	function fileUpload( $postFile, $upload_dir,  $fileNamePrefix, $allowFileTypes='', $allowExtensions='', $maxSizeMb=20, $oldFiles=false ){
		
		$maxSize = $maxSizeMb * 100000; //convert mb to b
		//Retrieve file details from uploaded file, sent from upload form
		$allowFileTypes = explode( ',', $allowFileTypes );
		$allowExtensions = explode( ',', $allowExtensions );
	 
		if( !isset( $postFile ) ){
			return Logg( 'Success', '', 'No file uploaded' );
		}
		
		if( $postFile['size'] > $maxSize ){
			return Logg( 'Failure', '', "File size shall not exceed {$maxSize}mb" );
		}
		
		
		//Set up the source and destination of the file
 		$src		= $postFile['tmp_name'];
		$mimeType	= $postFile['type'];
		$ext		= $this->getExtension( $postFile['name'] );
	
		//First check if the file has the right extension, we need jpg only
		if ( !in_array( $mimeType,  $allowFileTypes )&& in_array( $ext,  $allowExtensions ) && $allowFileTypes == '*' && $allowExtensions='*' ){
			return Logg( 'Failure', '', $postFile['name']." has invalid extension or MIME type." );				
		}
		
		//check if its image and has any size restrictions
		if( $mimeType == 'image/jpeg' || $mimeType == 'image/jpg' || $mimeType == 'image/png' || $mimeType == 'image/gif' ){
			$size = getimagesize( $postFile['tmp_name'] );
			$imgWidth = $size[0];
			$imgHeight = $size[1];
			if( $this->config['imgwidth'] && $imgWidth != $this->config['imgwidth'] ){
				return Logg( 'Failure', '', 'Image width should be exactly '.$this->config['imgwidth'].'px.' );
			}elseif( $this->config['minheight'] && $imgHeight != $this->config['minheight'] ){
				return Logg( 'Failure', '', 'Image height should be exactly '.$this->config['imgwidth'].'px.' );
			}			
		}
		
		//check if upload directory is writable
		if( !is_writeable( $upload_dir ) ){
			return Logg( 'Failure', '', "Upload directory $upload_dir is not writable." );				
		}
		
		//prepare a clean filename by cleaning special characters and applying prefix(if specified in xml)
		$filename = $fileNamePrefix . $this->cleanFileName( $postFile['name'] );
		
		
			
		if( !is_array( $oldFiles ) ){
			$oldFiles = array( $oldFiles );
		}
		
		foreach( $oldFiles as $oldFile ){
				
			//check if there is a old file and if we need to back it up
			if( $oldFile && is_file( $upload_dir.$oldFile ) ){
				
				if( $this->config['oldfileaction'] == 'deleteall' || ( $this->config['oldfileaction'] == 'replace' && $oldFile == $filename ) ){
					//backup old file for deletion later
					if( !$bkpName = $this->getAvailableFilename( $oldFile.".bkp", $upload_dir ) ){
						return Logg( 'Failure', '', "Unable to backup old file, Unable to find available filename for backup file." );
					}
					if( !rename( $upload_dir . $oldFile, $upload_dir . $bkpName ) ){
						return Logg( 'Failure', '', "Unable to backup old file." );
					}
					$this->fileSystemChanges[] =  array( 'action'=>'backup', 'fromname'=>$oldFile, 'toname'=> $bkpName );
					Logg( 'LOG', '', "Old $oldFile file backed up to $bkpName" );	
				}elseif( $this->config['oldfileaction'] == 'renameold' ){
					//just rename old file
					if( !$bkpName = $this->getAvailableFilename( $oldFile, $upload_dir ) ){
						return Logg( 'Failure', '', "Unable to find available filename for renaming old file." );
					}
					if( !rename( $upload_dir . $oldFile, $upload_dir . $bkpName ) ){
						return Logg( 'Failure', '', "Unable to rename old file." );
					}
					$this->fileSystemChanges[] = array( 'action'=>'rename', 'fromname'=>$oldFile, 'toname'=> $bkpName );
					Logg( 'LOG', '', "Old $oldFile file renamed to $bkpName" );
				}else{
					//renamenew
					if( !$filename = $this->getAvailableFilename( $filename, $upload_dir ) ){
						return Logg( 'Failure', '', "Unable to find available filename for renaming file." );
					}
				}
			}
		}
		
		//besides old files, there may be a case there is some other file in the directory with same name
		if( is_file( $upload_dir.$filename ) ){
			if( $this->config['iffileexists'] == 'replace' ){
				//delete existing file, bakup the file for later deletion
				if( !$bkpName = $this->getAvailableFilename( $filename.".bkp", $upload_dir ) ){
					return Logg( 'Failure', '', "Unable to backup existing file, Unable to find available filename for backup file." );
				}
				if( !rename( $upload_dir . $filename, $upload_dir . $bkpName ) ){
					return Logg( 'Failure', '', "Unable to backup existing file." );
				}
				$this->fileSystemChanges[] = array( 'action'=>'backup', 'fromname'=>$filename, 'toname'=> $bkpName );
			}else{
				//rename new file 
				if( !$filename = $this->getAvailableFilename( $filename, $upload_dir ) ){
					return Logg( 'Failure', '', "Unable to find available filename for renaming file." );
				}
			}
			
		}
		
		$dest = $upload_dir . $filename;
		
		if( !move_uploaded_file( $src, $dest ) ) { // Short circuit to prevent file permission errors
			return Logg( 'Failure', '', "Unable to move uploaded file {$postFile['name']} to $upload_dir" );
		}
		$this->fileSystemChanges[] = array( 'action'=>'upload', 'filename'=>$filename );
		
		$log = Logg( 'Success', '', "$filename uploaded to $upload_dir " );
		$log['filename'] = $filename;	
		return $log;
	}
	
	
	/***********************************************************************************
	 * function rollBackFileUploads()
	 * 		This function cancels all files uploaded.
	 * 		if some error occurs during file upload, or form submission, this function
	 * 		rolls back all the changes and restores files deleted(backed up and marked for deletion actually) 
	 ***********************************************************************************/	
	public function rollBackFileUploads(){
		Logg( "LOG", '', "Rolling back {$this->name}.<br>" );
		$total = count( $this->fileSystemChanges );
		$upload_dir = $this->config['uploaddirectory'];
		
		if( $total > 0 ){
			for( $i = $total-1; $i >= 0 ; $i-- ){
				$change = $this->fileSystemChanges[$i];
				
				if( $change['action'] == 'upload' ){
					unlink( $upload_dir.$change['filename'] );
				}elseif( $change['action'] == 'backup' || $change['action'] == 'rename' ){
					rename( $upload_dir . $change['toname'], $upload_dir . $change['fromname'] );
				}
				
			}
		}
		$this->value = '';
	}
	
	/***************************************************************************************
	 * function  cleanFileName()
	 * 		This function cleans up the passed filename and removes all non-alphanumeric characters
	 * 		special characters allowed are: . _ -
	 * 		multiple dots in sequence are not allowed
	 * 
	 * PARAMETERS:
	 * 		$fileName - name of file as string
	 **************************************************************************************/
	function cleanFileName( $fileName ) {
		$regex = array( '#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#' );
		return preg_replace( $regex, '', $fileName );
	}
	
	
	
	/***************************************************************************************
	 * function  getExtension( $fileName )
	 * 		returns extension of a given filename
	 * 		It is used by the fileUpload() function to prepare name of a newly uploaded file
	 * PARAMETERS:
	 * 		$fileName - name of file as string
	 **************************************************************************************/
	function getExtension( $fileName ) {

		$chunks = explode('.', $fileName );
		$chunksCount = count($chunks) - 1;

		if($chunksCount > 0) {
			return $chunks[$chunksCount];
		}
		return false;

	}
	
	
	/***************************************************************************************
	 * function  stripExtension( $fileName )
	 * 		returns string with the file extension removed from filename
	 * 		It is used by the fileUpload() function to prepare name of a newly uploaded file
	 * PARAMETERS:
	 * 		$fileName - name of file as string
	 **************************************************************************************/
	function stripExtension( $fileName ) {

		return preg_replace( '#\.[^.]*$#', '', $fileName );

	}
	
	
	/***************************************************************************************
	 * function  getFileNameAvailability( )
	 * 		checks if a file can be created in given directory without replacing any file.
	 * 		If a file with given already exists in the directory, it finds a suitable filename
	 * 		which does not exists.
	 * 		It does so by attaching a number suffix, or if a number suffix exists it tries to increment it
	 * 		till it finds a filename that is not available.  
	 * 		
	 * PARAMETERS:
	 * 		$fileName - desired name of file
	 * 		$directory - path of directory with trailing / ,
	 * RETURNS:
	 * 		returns available filename
	 **************************************************************************************/
	function getAvailableFilename( $fileName, $directory ) {
		
		if( !is_file( $directory.$fileName ) ){
			return $fileName;
		}
		
		$existingfiles = array();
		$extension = $this->getExtension( $fileName );
		$name = $this->stripExtension( $fileName );
		
		if( is_dir( $directory ) ) {
		    if ( $dirHandle = opendir( $directory ) ) {
		    	
		        while ( ( $file = readdir( $dirHandle ) ) !== false ) {
		            $existingfiles[] = $file;
		        }
		        closedir($dh);
		        
		        $suffixNumber = 0;
		        
		        
		        while( in_array( $name.'_'.$suffixNumber.'.'.$extension, $existingfiles ) ){
		        	$suffixNumber++;
		        }
		        return  $name.'_'.$suffixNumber.'.'.$extension;
		        
		    }
		}		
		
		return false;

	}
	
	
	
	
}

?>