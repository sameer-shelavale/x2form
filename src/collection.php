<?php
/*******************************************************************************************************
 * class X2Form\Collection
 * X2FormCollection is used by X2Form for generating a array of group of fields.
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
namespace X2Form;
use ArrayObject;

class Collection{

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
	var $schema;			// schema of the form of recordset
	
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
	
	
	
	public function __construct( $name, $params ){
        //$definitionType="xml", $definitionValue=false, $lang, $dbTyp = 'php', $dbHnd=false, &$parentForm
		$this->name = $name;

        //params
        $this->parent = (isset( $params['parent']))?$params['parent']: $this->parent = false;
        $this->template = (isset( $params['template']))?$params['template']: $this->template = false;
        $this->language = (isset( $params['language']))?$params['language']: $this->language = false;
        $this->dbType = (isset( $params['dbType']))?$params['dbType']: $this->dbType = 'php';
        $this->dbHandle = (isset( $params['dbHandle']))?$params['dbHandle']: $this->dbHandle = false;
        $this->renderer = (isset( $params['renderer']))?$params['renderer']: $this->renderer = new Renderers\Table\Renderer(); //render using tables by default

        //initialize vars
        $this->elements = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
		$this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );

        //load data
        if( isset( $params['xml']) ){
            $this->loadXML( $params['xml'] );
        }
        if( isset( $params['file'] ) ){
            $this->loadXMLFile( $params['file'] );
        }
        if( isset( $params['SimpleXMLElement'] ) ){
            $this->loadXMLElement( $params['SimpleXMLElement'] );
        }
		 
	}
	
	/*****************************************************************************
	 * loadXMLFile()
	 * 		Loads form collection information from a xml file
	 * parameters:
	 * 		$xmlFileName - full path to the xml file
	 * returns:
	 * 		true - if the string is loaded successfully
	 * 		false - if the xml string can not be loaded or if its invalid   
	 ****************************************************************************/
	function loadXMLFile( $xmlFileName ){
		
		//get xml as string from xml file 
		$xmlString = file_get_contents( $xmlFileName );
		
		//check if file reading was successful
		if( !$xmlString ){ $this->isLoaded = false; } 
		
		//load xml
		$this->loadXML( $xmlString );
		
	}
	
	
	/*****************************************************************************
	 * loadXML()
	 * 		Loads form collection information from a xml string
	 * parameters:
	 * 		$xmlString - XML string
	 * returns:
	 * 		true - if the string is loaded successfully
	 * 		false - if the xml string can not be loaded or if its invalid   
	 ****************************************************************************/
	function loadXMLElement( $formXml ){

		//find collection attributes
		foreach( $formXml->attributes() as $k => $v ){
			$key = strtolower( (string)$k ); 
			if( $key == 'label' ){
				$this->label = (string)$v;
			}elseif( $key == 'description' ){
				$this->description = (string)$v;
			}else{
				$this->attributes[ strtolower( "$k" ) ] = "$v";
			}
			
		}
		
		//initialize form schema
		$this->schema = new Form(
            $this->name,
            [
                'SimpleXMLElement' => $formXml->schema,
                'template' => false,
                'language' => $this->language,
                'dbType' => $this->dbType,
                'dbHandle' => $this->dbHandle,
                'index' => $this->elements->count(),
                'parent' => $this
            ]
            );
		
		if( $formXml->headertemplate ){
			$this->headerTemplate = "$formXml->headertemplate";
		}
		
		if( $formXml->headertemplate ){
			$this->headerTemplate = "$formXml->headertemplate";
		}
		
	}
	
	
	/*****************************************************************************
	 * function loadXMLElement()
	 * 		Loads form collection information from a object of type SimpleXMLElement
	 * parameters:
	 * 		$formXmlObj - a SimpleXMLElement object
	 * returns:
	 * 		true - if the string is loaded successfully
	 * 		false - if the xml string can not be loaded or if its invalid   
	 ****************************************************************************/
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


    /*****************************************************************************
     * function finalize()
     *      evaluates all queries, php expressions passed for finding datasets for
     *      elements like dropdowns etc. Also computes final output names for fields
     *      It is essential to call this function before rendering or validation
     *****************************************************************************/
    function finalize(){
        if( !$this->ready ){
            foreach( $this->schema->elements as $i => $elem ){
                //set $this as parent of all elements
                //we do this here because we dont want to call function for adding elements
                //nor do we want the user to have pain of specifying the parent when its so obvious
                $this->schema->elements[$i]->parent = $this;
                //now finalize the elment itself
                $this->schema->elements[$i]->finalize();
            }
            $this->ready = true;
        }
    }

	
	/***********************************************************************************
	 * function SetValues( $valueArray )
	 * 		This function populates the subforms with values passed in $valueArray. 
	 * PARAMETERS:
	 *		$valueArray  - associative array of submitted values(generally $_POST or $_REQUEST )
	 ***********************************************************************************/
	public function setValues( $valueArray = false ){
		foreach( $valueArray as $idx => $formValue ){
			if( !isset( $this->elements[ $idx ] ) ){
				
				$this->elements[ $idx ] = $this->schema->deepClone();;
				$this->elements[ $idx ]->index = $idx;
			}
			$this->elements[ $idx ]->setValues( $formValue );
			
		}
		
	}
	
	
	/***********************************************************************************
	 * function getValues( )
	 * 		This function returns array of values of sub-forms of the collection as a 
	 * 		associative array. 
	 *	
	 ***********************************************************************************/
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
	
	
	/***********************************************************************************
	 * function validate()
	 * 		This function validates the form data within each sub-form of the collection. 
	 * Returns:
	 *		returns true on success, false on failure.
	 *		it sets $errorString variable on failure.
	 ***********************************************************************************/
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