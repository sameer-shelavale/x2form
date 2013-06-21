<?php
/*******************************************************************************************************
 * class X2FormCollection
 * X2FormCollection is used by X2Form for generating a group of fields.
 * 
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

class X2FormCollection{

	//major element attributes
	var $id;
	var $name;
	var $value;
	var $label;
	var $description; //when labels are not enough use this (except for radio and checkbox) 
	
	
	//variables initialized by constructor
	var $language = false;

	var $dbType = "php";		// framework to be used for running queries, possible values 'php', 'php-pdo', 'joomla'
	var $dbHandle = false;		// database handle will be required for pdo and some other frameworks
	
	var $formTemplate;				// template to be used for generating form
	var $listTemplate;
	
	//variables storing form and element details
	var $prototype;			// prototype of the form of recordset
	
	var $attributes;		// attributes to be used
	var $elements;			// this will be used internally to store individual form objects
	
	var $parent = false;
	var $errorString = '';
	var $errorFields = array();
	
	var $callBack = array(); //this array will hold all the callback functions/closures : not yet implemented
	
	//variables for storing raw information about form
	var $xml;				//
	var $xmlfile;			//
	
	var $isLoaded = false;
	
	var $headerTemplate = false;
	var $itemTemplate = false;
	
	
	
	public function X2FormCollection( $name, $definitionType="xml", $definitionValue=false, $lang, $dbTyp = 'php', $dbHnd=false, &$parentForm ){
		
		$this->name = $name;
		$this->parent = $parentForm;
		//$this->template = $template;
		$this->dbType = $dbTyp;
		$this->dbHandle = $dbHnd;
		$this->elements = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
		$this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
		
		$this->language = $lang;
		
		$definitionType = strtolower( $definitionType );
		if( $definitionType == 'xml' && $definitionValue ){
			$this->loadXML( $definitionValue );
		}elseif( $definitionType == 'xmlfile' && $definitionValue ){
			$this->loadXMLFile( $definitionValue );
		}elseif( $definitionType == 'simplexmlelement' && $definitionValue ){
			$this->loadXMLElement( $definitionValue );
		}else{
			//should return false?
		}
		 
	}
	
	function loadXMLElement( $formXml ){

		//find collection attributes
		foreach( $formXml->attributes() as $k => $v ){
			$this->attributes[ strtolower( "$k" ) ] = "$v";
			
		}
		
		//initialize form prototype
		$this->prototype = new X2Form( $this->name, 'SimpleXMLElement', $formXml->prototype, false, $this->language, $this->dbType, $this->dbHandle, $this->elements->count(), $this );
		
		if( $formXml->headertemplate ){
			$this->headerTemplate = "$formXml->headertemplate";
		}
		
		if( $formXml->headertemplate ){
			$this->headerTemplate = "$formXml->headertemplate";
		}
		
	}
	
	function loadXML( $xmlString ){

		//first load the xml String
		try{
			$formXml = new SimpleXMLElement( $xmlString );
			
		}catch ( Exception $e ){
			$this->errorString =  "BAD XML format.";
			$this->isLoaded = false;
			return;
		}
		
		$this->loadXMLElement( $formXml );
		
	}
	
	function loadXMLFile( $xmlFileName ){
		
		//get xml as string from xml file 
		$xmlString = file_get_contents( $xmlFileName );
		
		//check if file reading was successful
		if( !$xmlString ){ $this->isLoaded = false; } 
		
		//load xml
		$this->loadXML( $xmlString );
		
	}
	
	
	public function render(){
		$html = $this->renderList();
		/*foreach( $this->elements as $elem ){
			$html .= $elem->render( false );
		}*/
		
		$this->prototype->index = 'X2F_INDEX';
		
		$addHtml .= '<tr>';
		foreach( $this->prototype->elements as $i => $elem ){
			$addHtml .= '<td>'.$elem->render().'</td>';			
		}
		$addHtml .= '</tr>';
		
		if( $this->attributes['showbbuttons'] != 'false' ){
			$html .= '<input type="button" value="Add" onclick="AddToX2'.$this->parent->name.'_'.$this->name.'_list()" />
					<input type="button" value="Remove" onclick="RemoveFromX2'.$this->parent->name.'_'.$this->name.'_list()" />
			';
		}
		
		$js = '
		 
		<script type="text/javascript"> 
			var  '.$this->parent->name.'_'.$this->name.'_count = '.$this->elements->count().';
			function AddToX2'.$this->parent->name.'_'.$this->name.'_list( ){
				var tmp = \''.$addHtml. '\';
				tmp = tmp.replace( /X2F_INDEX/gi, '.$this->parent->name.'_'.$this->name.'_count );
				$(\'#'.$this->parent->name.'_'.$this->name.'_list tr:last\').after(tmp);
				'.$this->parent->name.'_'.$this->name.'_count ++;
			}
			
			function RemoveFromX2'.$this->parent->name.'_'.$this->name.'_list( ){
				$(\'#'.$this->parent->name.'_'.$this->name.'_list tr:last\').remove();
				'.$this->parent->name.'_'.$this->name.'_count--;
			}	
		</script>';
		
		
		
		$html .= $js;
		return $html;
	}
	
	public function renderTemplate(){
		
	}
	
	public function renderRaw(){
		
	}
	
	
	public function renderList(){
		$html = '<table class="list" id="'.$this->parent->name.'_'.$this->name.'_list">';
		$html .= $this->renderListHeader();
		foreach( $this->elements as $i => $subForm ){
			$html .= '<tr>';
			foreach( $subForm->elements as $elem ){
				$html .= '<td>'.$elem->render().'</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>';
		
		return $html;
	}
	
	public function renderListHeader(){
		$header = '<tr>';
		foreach( $this->prototype->elements as $elem ){
			$header .= '<th>'.$elem->label().'</th>'; 
		}
		$header .= '</tr>';
		return $header;
	}
	
	public function renderListRaw(){
		
	}
	
	public function renderListTemplate(){
		
	}
	
	public function setValues( $valueArray = false ){
		foreach( $valueArray as $idx => $formValue ){
			if( !isset( $this->elements[ $idx ] ) ){
				
				$this->elements[ $idx ] = $this->prototype->deepClone();;
				$this->elements[ $idx ]->index = $idx;
			}
			$this->elements[ $idx ]->setValues( $formValue );
			
		}
		
	}
	
	public function getValues( ){
		$this->value= array();
		foreach( $this->elements as $formObj ){
			$this->value[ $formObj->index ] = $formObj->getValues();
		}
		return $this->value;
		
	}
	
	
	
	
	/***********************************************************************************
	 * function label()
	 * 		This function return label of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function label(){
		return $this->label;
	}
	
	/***********************************************************************************
	 * function description()
	 * 		This function return description of element.
	 * 		it is used while rendering element.
	 ***********************************************************************************/
	public function description(){
		return $this->description;
	}
	
	public function validate(){
		$this->errorString = '';
		foreach( $this->elements as $i => $subForm ){
			if( ! $subForm->validate() ){
				$tmp = $subForm->errorString;
			}
			
			$this->errorString .= $tmp;
			if( $tmp ){
				$this->errorString .= '<br/>';
			} 
			$this->errorString .= $tmp;
		}
		
		return $this->errorString;
	}
	
	
	/***********************************************************************************
	 * function setErrorFields()
	 * 		This function sets error messages on each field provided in input array
	 * parameters:
	 *		$eFields  - associative array with field names as keys and error messages as values
	 * 
	 ***********************************************************************************/	
	public function setErrorFields( $eFields ){
		foreach( $this->elements as $index => $e ){
			$e->setErrorFields( $eFields[ $index ] );
		}
	}
	
}


?>