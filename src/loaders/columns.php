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

use Monolog\Logger;

class Columns{
	
    public static  function load( &$form, $columns, $exclude=null ){
        if( !is_array( $columns)){
            return Logg( 'Failure', '', 'Invalid column array passed' );
        }
        if( isset( $columns['column_name'] ) ){
            //this is array containing single column
            return self::loadColumn( $form, $columns );
        }else{
            $errorCount = 0;
            foreach( $columns as $col ){
                if( isset( $col['column_name'] )){
                    if( is_array( $exclude ) && in_array( $col['column_name'], $exclude ) ){
                        continue;
                    }
                    $log = self::loadColumn( $form, $col );
                    if( $log['result'] =='Failure' ){
                        $errorCount++;
                    }
                }
            }
            if( $errorCount > 0 ){
                return Logg( 'Failure', '', "Failed to load $errorCount fields." );
            }
            return Logg( 'Success', '', 'Columns loaded successfully' );
        }
	}

    public static  function loadColumn( &$form, $column ){

        $typeMap = [
            'enum'      => 'dropdown',
            'set'      => 'dropdown',

            'int'           => 'text',
            'tinyint'       => 'text',
            'smallint'      => 'text',
            'mediumint'     => 'text',
            'bigint'     => 'text',
            'integer'     => 'text',
            'real'          => 'text',
            'double'          => 'text',
            'float'          => 'text',
            'decimal'          => 'text',
            'numeric'          => 'text',

            'datetime'      => 'text',
            'date'      => 'text',
            'time'      => 'text',
            'year'      => 'text',
            'timestamp'     => 'text',

            'varchar'       => 'text',
            'char'          => 'text',
            'text'          => 'textarea',
            'tinytext'      => 'textarea',
            'mediumtext'      => 'textarea',
            'longtext'      => 'textarea',
            'blob'          => 'textarea',
            'tinyblob'      => 'textarea',
            'mediumblob'      => 'textarea',
            'longblob'      => 'textarea',
            'binary'      => 'textarea',
            'varbinary'      => 'textarea',

            'point' => 'text',
            'multipoint' => 'textarea',
            'linestring' => 'textarea',
            'multilinestring' => 'textarea',
            'polygon' => 'textarea',
            'multipolygon' => 'textarea',
            'geometry' => 'textarea',
            'geometryCollection' => 'textarea',
        ];

        if( !array_key_exists( $column['data_type'], $typeMap ) ){
            return Logg( 'Failure', '', 'Column type "'.$column['data_type'].'" is not supported.' );
        }

        $options = [];
        if( $column['data_type'] == 'enum' || $column['data_type'] == 'set'){
            if( preg_match_all( '/\'([^\',]+)\'/', $column['column_type'], $matches ) ){
                foreach( $matches[1] as $opt ){
                    $options[] = [ 'value'=>$opt, 'label'=>$opt ];
                }
            }
        }

        $form->elements->$column['column_name'] = new \X2Form\Element(
            $typeMap[$column['data_type']],
            array(
                'name'=>$column['column_name'],
                'label'=>'',
                'mandatory'=>'true',
                'options' => $options
            )
        );

        $form->isLoaded = true;
        return Logg( 'Success', '', "Column {$column['column_name']} loaded successfully" );

    }
	
};

?>