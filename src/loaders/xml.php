<?php
/*******************************************************************************************************
 * class Logger
 * Logger class is used for logging errors to screen or text file, 
 * It is useful in webservices and APIs to return structured results.
 * 
 * Author : Sameer Shelavale
 * Email  : samiirds@gmail.com, sameer@techrevol.com, sameer@possible.in 
 * Author website: http://techrevol.com, http://possible.in
 * Phone  : +91 9890103122
 * License: AGPL3, You should keep Package name, Class name, Author name, Email and website credits.
 * 			http://www.gnu.org/licenses/agpl-3.0.html
 * 			For other type of licenses please contact the author.
 * PHP Version: Tested on PHP 5.2.2 & 5.3.10
 * Copyrights (C) 2012-2013 Sameer Shelavale
 * Dependencies : class.dbhelper.php, class.logg.php
 *******************************************************************************************************/
namespace X2Form\Loaders;
use X2Form\Loaders\Simplexml;
use X2Form\Helpers\Logger;
class Xml{
	

	public static function load( &$form, $xmlString ){
        //first load the xml String
        try{
            $formXml = new SimpleXMLElement( $xmlString );

        }catch ( Exception $e ){
            return Logg( 'Failure', '', "BAD XML format.");
        }

        return SimpleXml::load( $formXml, $form );
	}

    public static function loadFile( $xmlFileName, &$form ){
        //get xml as string from xml file
        $xmlString = file_get_contents( $xmlFileName );

        //check if file reading was successful
        if( !$xmlString ){
            return Logg( 'Failure', '', 'Unable to read the file '.$xmlFileName);
        }

        //load xml
        return self::load( $xmlString, $form );

    }
	
};

?>