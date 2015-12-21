<?php
namespace X2Form\Loaders;
use X2Form\Helpers;
use ArrayObject;

class Simplexml{


    /*****************************************************************************
     * function load()
     * 		Loads form information from a object of type SimpleXMLElement
     * parameters:
     * 		$formXmlObj - a SimpleXMLElement object
     *      $form - Form object in which the xml elements are to be loaded
     * returns:
     * 		true - if the SimpleXmlElement is loaded successfully
     * 		string - Error message
     ****************************************************************************/
	public static function load( &$form, $formXmlObj ){

        //attributes for the FORM tag, that are to be read from xml, if provided
        $fAttr =  array( 'method'=>'', 'action'=>'', 'enctype'=>'', 'target'=>'_self' );

        //find form attributes
        foreach( $formXmlObj->attributes() as $k => $v ){
            if( array_key_exists( $k, $fAttr ) ){
                $fAttr[ strtolower( "$k" ) ] = "$v";
            }
        }
        $form->attributes = $fAttr;


        //find the elements in the form
        $elems = new ArrayObject( array(), ArrayObject::ARRAY_AS_PROPS );


        foreach( $formXmlObj->elements->children() as $elem ){

            //create X2Form\Element object and store it in array
            $params = self::parseChild( $elem );

            if( isset( $params['type'] ) && isset( $params['name'] ) ){
                $form->addElement( $params );
            }
        }

        $form->isLoaded = true;
        return Logg( 'Success', '', 'SimpleXml is loaded successfully.');
	}


    /********************************************************************************
     * function parseChild()
     *		This function create a parameter array for passing to Form->add() method
     * parameters:
     * 		$elem - a SimpleXMLElement object
     * returns:
     * 		array of parameters ready for passing to Form->add() method
     ********************************************************************************/
    function parseChild( $elem ){

        $prop = array();
        $children = array(); //in case the element is a 'group'

        //now find the $prop, $conf and $attr
        foreach( $elem->attributes() as $k => $v ){
            $prop[ strtolower( "$k" ) ] = "$v";
        }



        $opt = array();

        if( isset( $prop['type'] ) && $prop['type'] =="collection" || strtolower( $elem->getName() ) == 'collection' ){
            $prop['type'] = 'collection';

            //collection xml structure has elements as a child and may have other properties like
            // headertemplate. itemtemplate, listfields etc. as children (from legacy 1.0 version
            // but these are not implemented in 2.0 for now)
            foreach( $elem->children() as $child ){
                if( $child->getName() == 'schema' ){
                    $prop['from'] = $child;
                }else{
                    //use text value of the child as property-value
                    $prop[ $child->getName() ] = "$child";
                }
            }
            return $prop;

        }elseif( isset( $prop['type'] ) && $prop['type'] =="group" || strtolower( $elem->getName() ) == 'group' ){
            $prop['type'] = 'group';

            //find the group elements
            $prop['elements'] = array();

            foreach( $elem->children() as $child ){
                $prop['elements'][] = self::parseChild( $child );
            }
            return $prop;

        }else{

            //find the values of different attributes in different languages
            if( $elem->languages ){
                foreach( $elem->languages->children() as $lang ){

                    foreach( $lang->children() as $langSetting ){
                        $prop['languages'][ (string)$lang->attributes()->name ][$langSetting->getName()] = (string)$langSetting;
                    }
                }
            }


            //find options of the element,
            //options are to be mentioned for dropdowns, multiple checkboxes and multiple radio boxes

            if( $prop['type'] =="textarea" && "$elem" != '' ){
                $prop['value'] = (string)$elem;
            }

            if( $elem->options && isset( $prop['type'] ) && $prop['type'] =="captcha" ){
                //if options are passed for CAPTCHA in xml itself as children of the element tag
                if( $elem->options->option ){
                    foreach( $elem->options->option as $o ){
                        $optAtr = array();
                        foreach( $o->attributes() as $k => $v ){
                            $k = "$k";
                            $optAtr[ $k ] = "$v";
                        }
                        if( isset( $optAtr['type'] ) ){
                            $opt[ $optAtr['type'] ] = $optAtr;
                        }
                    }
                }
            }elseif( $elem->options && $elem->options->option ){
                //if options are passed in xml itself as children of the element tag
                foreach( $elem->options->option as $o ){
                    $optAtr = array();
                    foreach( $o->attributes() as $k => $v ){
                        $k = strtolower( "$k" );
                        if( $k == 'value' || $k == 'label' ){
                            $optAtr[ $k ] = "$v";
                        }
                    }
                    //check if only value is given
                    if( $optAtr['label'] || $optAtr['value'] ){
                        if( !isset($optAtr['label']) || $optAtr['label'] === false || $optAtr['label'] === null){
                            $optAtr['label'] = $optAtr['value'];
                        }elseif( !isset( $optAtr['value'] ) || $optAtr['value'] === false || $optAtr['value'] === null ){
                            $optAtr['value'] = $optAtr['label'];
                        }

                    }
                    $opt[] = $optAtr;
                }
            }elseif( $elem->options && $elem->options->query ){
                //options are to be fetched from result of a passed query
                //the query is passed by 'query' tag which is child of options tag
                $opt['query']['sql'] = "{$elem->options->query}";

                //find the valuefield and labelfield
                //these will be used to pick up values from the query results
                foreach( $elem->options->query->attributes() as $k => $v ){
                    $k = strtolower( "$k" );
                    $opt['query'][$k] = "$v";
                }
            }elseif( $elem->options && $elem->options->create_function ){
                //options are to be fetched from result of a passed query
                //the query is passed by 'query' tag which is child of options tag
                $opt['create_function']['code'] = "{$elem->options->create_function}";

                //find the valuefield and labelfield
                //these will be used to pick up values from the query results
                foreach( $elem->options->create_function->attributes() as $k => $v ){
                    $k = strtolower( "$k" );
                    $opt['create_function'][$k] = "$v";
                }
            }elseif( $elem->options && $elem->options->phpglobal ){
                //options are to be fetched from result of a passed query
                //the query is passed by 'query' tag which is child of options tag
                $opt['phpglobal'] = [];

                //find the valuefield and labelfield
                //these will be used to pick up values from the query results
                foreach( $elem->options->phpglobal->attributes() as $k => $v ){
                    $k = strtolower( "$k" );
                    $opt['phpglobal'][$k] = "$v";
                }
            }

            //find events if any
            //events are defined in 'event' tag which is child of 'events', which in turn is child of the element tag
            $events = array();
            if( $elem->events->event ){
                foreach( $elem->events->event as $e ){
                    foreach( $e->attributes() as $k => $v ){
                        if( $k == 'type' ){
                            $events[ "$v" ] = "$e";

                        }
                    }
                }
            }

            //so we have now all data required for creating the X2Form\Element object
            $prop[ 'options' ] = $opt;
            $prop[ 'events' ] = $events;

            return $prop;

        }

    }

};

?>