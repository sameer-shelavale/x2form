<?php
/*******************************************************************************************************
 * class MultiFrameworkDBHelper
 * This class is used for runing a queries in different PHP frameworks,
 * The purpose of class is to support development of modules/classes which can 
 * work on different frameworks in a way compatible the respective framework.
 * Only joomla and php frameworks are supported yet, I will add drupal and php-pdo soon.
 * 
 * Author: Sameer Shelavale
 * Email: sameer@techrevol.com, samiirds@gmail.com
 * Author website: http://techrevol.com, http://possible.in
 * Phone: +91 9890103122
 * License: AGPL3, You should keep Package name, Class name, Author name, Email and website credits.
 * 			http://www.gnu.org/licenses/agpl-3.0.html
 * 			For other type of licenses please contact the author.
 * PHP Version: Tested on PHP 5.2.2, 5.3.10, 5.3.13
 * Copyrights (C) 2012-2013 Sameer Shelavale
 * 
 * NOTE: I have started on this class quiet recently, 
 * 		 I have only added functions which I needed so far. 
 * 		 You may still use this class at your own responsibility 
 *******************************************************************************************************/
namespace X2Form;

class MultiFrameworkDBHelper{
	var $framework = 'default';
	
	const GET_COUNT = 1;
	const GET_INSERT_ID = 2;
	const GET_FOUND = 4;
	const FETCH_ONE = 8;
	const FETCH_ALL = 16;
	
	function MultiFrameworkDBHelper( $frmwrk = "default" ){
		$this->framework = $frmwrk; 
	}
	
	function query( $queryString, $options=false, $queryData=false, $dbHandle=false ){

		switch( $this->framework ){
			
			case 'joomla':
				return $this->joomlaQuery( $queryString, $options, $queryData );
			case 'php':
				return $this->defaultQuery( $queryString, $options, $queryData );
			case 'php-pdo':
				return $this->pdoQuery( $queryString, $options, $queryData, $dbHandle );
			default:
				return Logg( "Failure", "ERRORCODE", "The framework name you supplied is not supported" );
		}
	}
	
	function joomlaQuery( $queryString, $options, $queryData  ){
		$db	= & JFactory::getDBO();
		$db->setQuery( $queryString );
		$db->query();
		
		if ($db->getErrorNum()) {
			return  Logg( "Failure", "ERRORCODE", "Mysql Error.", $queryString, $db->getErrorMsg() );
		}
		
		$log =  Logg( "Success", "RESULTCODE", "Query executed.", $queryString );
		
		if( ( $options & self::FETCH_ONE ) == self::FETCH_ONE ){
			$log['data']['records'] = $db->loadAssoc();
		}

		if( ($options & self::FETCH_ALL) == self::FETCH_ALL ){
			$log['data']['records'] = $db->loadAssocList();
			
		}	
		
		if( ( $options & self::GET_COUNT ) == self::GET_COUNT ){
			$log['data']['count'] = count( $records );
		}
			
		if( ( $options & self::GET_INSERT_ID ) == self::GET_INSERT_ID ){
			$log['data']['insert_id'] = mysql_insert_id( $res );
		}
		return $log;
	}
	
	
	function defaultQuery( $queryString, $options, $queryData  ){
		$res = mysql_query( $queryString );
		if( ! $res ){
			return  Logg( "Failure", "1009", "Mysql Error.", $queryString, mysql_error() );
		}
		$log =  Logg( "Success", "RESULTCODE", "Query executed.", $queryString );
		
		if( ( $options & self::GET_COUNT ) == self::GET_COUNT ){
			$log['data']['count'] = mysql_num_rows( $res );
		}
		
		if( ( $options & self::FETCH_ONE ) == self::FETCH_ONE ){
			$log['data']['records'] = mysql_fetch_array( $res );
		}
		
		if( ( $options & self::FETCH_ALL ) == self::FETCH_ALL ){
			while( $tmp = mysql_fetch_array( $res ) ){
				$log['data']['records'][] = $tmp;
			}
		}
		
		if( ( $options & self::GET_INSERT_ID ) == self::GET_INSERT_ID ){
			$log['data']['insert_id'] = mysql_insert_id( $res );
		}
		
		return $log;
	}
	
	
	function pdoQuery( $queryString, $options, $queryData=null, $dbHandle=null  ){
		
		if( ! $dbHandle ){
			return  Logg( "Failure", "10010", "Invalid DB Handle."  );
		}
		
		$stmt = $dbHandle->prepare( $queryString );
		
		if( !stmt ){
			return  Logg( "Failure", "10011", "Unable to prepare query", $queryString );
		}
		
		if( ! $stmt->execute( $queryData ) ){
			$error = $stmt->errorInfo();
			
			return  Logg( "Failure", "#MYSQL ".$error[0], "MySQL Error..", $stmt->_debugQuery(), $error[2] );
		}
		
		$log =  Logg( "Success", "RESULTCODE", "Query executed.", $queryString );
		
		if( ( $options & self::GET_COUNT ) == self::GET_COUNT ){
			$log['data']['count'] = $stmt->rowCount();
		}
		
		if( ( $options & self::FETCH_ONE ) == self::FETCH_ONE ){
			$log['data']['records'] = $stmt->fetch( PDO::FETCH_ASSOC );
		}
		
		if( ( $options & self::FETCH_ALL ) == self::FETCH_ALL ){
			$log['data']['records'] = $stmt->fetchAll( PDO::FETCH_ASSOC );
			
		}
		
		if( ( $options & self::GET_INSERT_ID ) == self::GET_INSERT_ID ){
			$log['data']['insert_id'] = $dbHandle->lastInsertId();
		}
		
		return $log;
	}
	
	
}




?>