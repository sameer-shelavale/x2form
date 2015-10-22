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
use SimpleXMLElement;

class Collection{

    //special/class-specific properties (can be initialized from constructor)
    var $formTemplate;				// template to be used for generating form
    var $listTemplate;

    //basic properties (can be initialized from constructor)
    var $type = 'collection';
	var $id;
	var $name;
	var $value;
	var $label;
	var $description; //when labels are not enough use this (except for radio and checkbox)
    var $parent = false;
	
	//other variables (can be initialized from constructor)
    var $loader;
    var $renderer;
    var $language = false;
	var $dbType = "php";		// framework to be used for running queries, possible values 'php', 'php-pdo', 'joomla'
	var $dbHandle = false;		// database handle will be required for pdo and some other frameworks
	
	//properties used internally for storing data
    var $attributes;		// attributes to be used
	var $schema;			// schema of the form of recordset
	var $elements;			// this will be used internally to store individual form objects
	
    // properties related to error handling
	var $errorString = '';
	var $errorFields = array();

    //properties indicating state of the collection
	var $isLoaded = false;
    var $ready = false;
	
	public function __construct( $params ){
        //initialize storage vars
        $this->elements = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );
        $this->attributes = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );

		// set params
        foreach( $params as $key => $value ){
            if( in_array( $key, [ 'id', 'name', 'value', 'label', 'description', 'parent', 'formTemplate', 'listTemplate', 'language', 'dbType', 'dbHandle', 'renderer', 'loader'] ) ){
                $this->$key = $value;
            }
        }

        //set attributes
        if( isset( $params['attributes'] ) ){
            $this->attributes = $params['attributes'] ;
        }

        //set renderer
        if( !$this->renderer ){
            $this->renderer = new Renderers\Table\Renderer();
        }

        //set loader
        if( !$this->loader ){
            $this->loader = new Loaders\Collection();
        }

        //initialize schema for subforms
        $this->schema = new Form( $this->name,[
            'parent' => $this,
            'language' => $this->language,
            'dbType' => $this->dbType,
            'dbHandle'=> $this->dbHandle,
            'renderer'=> $this->renderer,
            'index' => 'X2F_INDEX'
        ] );

        //load schema details if 'from' parameter is set
        if( isset( $params['from']) ){
            $exclude = [];
            if( isset( $params['exclude'] ) ){
                $exclude = $params['exclude'];
            }
            $this->schema->load( $params['from'], $exclude );
        }

		 
	}


    function load( $from, $exclude=[] ){
        if( method_exists( $this->loader, 'load' ) ){
            $log = $this->loader->load( $this, $from, $exclude );
            if( $log['result'] == 'Failure' ){
                $this->isLoaded = false;
                $this->errorString = 'Unable to load form fields';
            }
        }else{
            $log = Logg( 'Failure', '', 'load() function missing in loader object.' );
        }
        return $log;
    }
	
	
	 /*****************************************************************************
     * function finalize()
     *      evaluates all queries, php expressions passed for finding datasets for
     *      elements like dropdowns etc. Also computes final output names for fields
     *      It is essential to call this function before rendering or validation
     *****************************************************************************/
    function finalize(){
        if( !$this->ready ){
            $this->schema->finalize();
            foreach( $this->elements as &$element ){
                //set $this as parent of all elements
                //we do this here because we dont want to call function for adding elements
                //nor do we want the user to have pain of specifying the parent when its so obvious
                $element->parent = $this;
                //now finalize the elment itself
                $element->finalize();
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
		foreach( $this->elements as &$formObj ){
			$this->value[ $formObj->index ] = $formObj->getValues();
		}
		return $this->value;
		
	}
	

    /***********************************************************************************
     * function storeOldValues()
     *      This function is only called during processing & validation of submitted form data.
     *      Main use of old values is in situations such as old files being present for the field,
     *      In which case it is okay not to upload the the same file again.
     *
     *      For a setting old values on a collection its necessary to have a primary/unique field
     *
     ***********************************************************************************/
    public function storeOldValues( $values ){
        if( is_array( $values ) ){
            if( $pk = $this->schema->primary ){
                foreach( $values as $oldValues ){
                    $found = false;
                    if( isset( $oldValues[ $pk ] ) && $oldValues[ $pk ] ){
                        //lets find the subform with the same primery key value
                        $found = false;
                        foreach( $this->elements as $k => &$subForm ){
                            if( property_exists( $subForm, $pk ) && $oldValues[$pk] == $subForm->$pk->value  ){
                                $found = true;
                                $subForm->storeOldValues( $oldValues );
                                break;
                            }
                        }
                    }//else ignore the old value array is most probably deleted
                }
            }
        }
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
		foreach( $this->elements as $i => &$subForm ){
			if( ! $subForm->validate() ){
				if( $subForm->errorString ){
                    $this->errorString .= $subForm->errorString;
                }
			}
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