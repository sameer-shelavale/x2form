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
namespace X2Form\Helpers;

class Logger{
	
	var $log = array();
	var $fileName = "log.txt";
	var $logToScreen = false;
	var $logQueriesToScreen = false;
	var $logErrorsToScreen = false;
	var $logOnlyErrors = true;
	var $logToFile = true;
	var $errorFields = array();
	var $logFileHandle;
	var $logText;
	
	
	public function Logger($file = "log.txt" ){
		$this->fileName = $file;
		if( $this->logToFile ){
			$this->logFileHandle = fopen( $this->fileName , "a" );	// we do this so that we can use the handle in destructor, 
																	// to avoid bug with file writing in destructors
		}
		$this->logText = "\r\n\r\n===========================================\r\n"
						."Call started: ".date( "Y-m-d H:i:s" )."\r\n"
						.$_SERVER['REQUEST_URI']."\r\n"
						."From:".$_SERVER['REMOTE_ADDR'];
		
	}
	
	public function Log( $result, $code, $message = '', $query='', $sqlError='' ){
		
		$newLog['result']	= $result;
		$newLog['code']		= $code;
		$newLog['message']	= $message;		
		$newLog['query']	= $query;		
		$newLog['sqlError'] = $sqlError;
		$this->log[] = $newLog;
		
		if( !$this->logOnlyErrors || ( $this->logOnlyErrors && $result == 'Failure' ) ){
			$this->logText .= "\r\n". date( "Y-m-d H:i:s" )." ". $newLog['result'] . " :: " . $newLog['message'] . (( $newLog['query'] != '')? "\r\n------------\r\n{$newLog['query']} ": '' ). (( $newLog['sqlError'] != '')? "\r\n------------\r\n{$newLog['sqlError']} ": '' ). "\n\n";
		}
		if( $this->logToScreen ){
			echo  "\r\n".$newLog['result'] . " :: " . $newLog['message'] ;
			if( $this->logQueriesToScreen ){
				echo "\r\n------------\r\n{$newLog['query']} ";
			}
			
			if( $this->logErrorsToScreen ){
				echo  "\r\n------------\r\n{$newLog['sqlError']}\n\n";
			}
			
			
		}
		
		
		$newLog['errorFields'] = $this->errorFields;		
		return $newLog;
	}
	
	public function AddErrorField( $fieldName, $errorString ){
		$this->errorFields[ $fieldName ] = $errorString;
		$this->logText .= " FIELD-ERROR :: " . $fieldName . " - ". $errorString;
		if( $this->logToScreen ){
			echo  "FIELD-ERROR :: " . $fieldName . " - ". $errorString;
		}
	}
	
	function __destruct(){
		if( $this->logToFile ){
			$this->logText .= "\r\n\r\n\r\n\r\n ";
			//file_put_contents( $this->fileName, $txt , FILE_APPEND );
			
			fwrite( $this->logFileHandle, $this->logText );
			fclose( $this->logFileHandle );
			usleep(20000);
		}
		
	}
	
	function ToLogFile(){
		
	}
	
};


global $__LOGGER;
if( defined( 'LOGFILE' ) ){
	$__LOGGER = new Logger( LOGFILE );
}else{
	$__LOGGER = new Logger( "log".date("y-m-d").".txt" );
}



function Logg( $result, $code, $message = '', $query='', $sqlError='' ){
	global 	$__LOGGER;
	return $__LOGGER->Log( $result, $code, $message, $query, $sqlError );
}

?>