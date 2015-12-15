<?php
namespace X2Form\Loaders;
use SimpleXMLElement;
use \X2Form\Helpers\Logger;

class Collection{

    public function load( &$collection, $source, $exclude=[] ){
        if( $source instanceof SimpleXMLElement ){
            return $this->loadXmlElement( $collection, $source, $exclude );
        }elseif( is_subclass_of( $source, 'Illuminate\Database\Eloquent\Model' ) ){
            //passed laravel Model object or classname
            //we wont find any collection specific data from eloquent model
            //so load the schema directly from the eloquent model
            return \X2Form\Loaders\Eloquent::load( $collection->schema, $source, $exclude );
        }elseif( is_array( $source ) ){
            //params array containing elements is passed
            return \X2Form\Loaders\ParamsArray::load( $collection->schema, $source );
        }elseif( is_file( $source ) && is_readable( $source ) ){
            //filename passed
            return $this->loadFile( $collection, $source, $exclude );
        }elseif( is_string( $source ) && strlen( $source ) > 50 ){
            //xml is passed
            return $this->loadXML( $collection, $source, $exclude );
        }
        return Logg( 'Failure', 'The type of source object provided is not supported.' );
    }


    public function loadXmlElement( &$collection, $sXml, $exclude=[] ){
        //find collection attributes
        foreach( $sXml->attributes() as $k => $v ){
            $key = strtolower( (string)$k );
            if( $key == 'label' ){
                $collection->label = (string)$v;
            }elseif( $key == 'description' ){
                $collection->description = (string)$v;
            }else{
                $collection->attributes[ strtolower( "$k" ) ] = "$v";
            }
        }

        //load form schema
        $collection->schema->load( $sXml->schema );

    }


    /*****************************************************************************
     * function loadXML()
     * 		Loads form collection information from a object of type SimpleXMLElement
     * parameters:
     * 		$xmlString - a xml string defining collection as per X2Form standard
     * returns:
     * 		true - if the string is loaded successfully
     * 		false - if the xml string can not be loaded or if its invalid
     ****************************************************************************/
    function loadXML( &$collection, $xmlString, $exclude=[] ){

        //first load the xml String
        try{
            $formXml = new SimpleXMLElement( $xmlString );

        }catch ( Exception $e ){
            $this->errorString =  "BAD XML format.";
            $this->isLoaded = false;
            return;
        }

        $this->loadXmlElement( $collection, $formXml, $exclude );

    }


    /*****************************************************************************
     * loadFile()
     * 		Loads form collection information from a xml file
     * parameters:
     * 		$xmlFileName - full path to the xml file
     * returns:
     * 		true - if the string is loaded successfully
     * 		false - if the xml string can not be loaded or if its invalid
     ****************************************************************************/
    function loadFile( &$collection, $xmlFileName, $exclude=[] ){

        //get xml as string from xml file
        $xmlString = file_get_contents( $xmlFileName );

        //check if file reading was successful
        if( !$xmlString ){ $collection->isLoaded = false; }

        //load xml
        $this->loadXML( $collection, $xmlString, $exclude );

    }
};
