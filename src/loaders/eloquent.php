<?php
namespace X2Form\Loaders;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use \X2Form\Loaders\Columns;
class Eloquent{
	

	public static function load( &$form, $model ){
        if( !is_object( $model ) ){
            //class name is passed
            $model = new $model();
        }
        if( $tableName = $model->getTable() ){
            $log = self::getColumns( $tableName );
            if( $log['result'] == 'Failure' ){
                $log['message'] .= ' Unable to load fields for Laravel Model';
                return $log;
            }
        }

        $log = Columns::load( $form, $log['columns'] );

        //hide the primary key field by default as it auto-increments
        if( $primary = $model->getKeyName() ){
            $form->elements->$primary->type = 'hidden';
        }
	}

    public static function getColumns( $tableName ){

        switch( DB::connection()->getConfig('driver') ){
            case 'pgsql':
                $query = "SELECT column_name, data_type, character_maximum_length, column_default, is_nullable FROM information_schema.columns WHERE table_name = '".$tableName."'";
                $columnName = 'column_name';
                $dataType = 'data_type';
                $charMaxLen = 'character_maximum_length';
                $columnDefault = 'column_default';
                $isNullable = 'is_nullable';
                $columnType = 'column_type';

                $reverse = true;
                break;

            case 'mysql':
                $query = "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName' AND TABLE_SCHEMA='".DB::connection()->getConfig('database')."'";
                $columnName = 'COLUMN_NAME';
                $dataType = 'DATA_TYPE';
                $charMaxLen = 'CHARACTER_MAXIMUM_LENGTH';
                $columnDefault = 'COLUMN_DEFAULT';
                $isNullable = 'IS_NULLABLE';
                $columnType = 'COLUMN_TYPE';
                $reverse = false;
                break;

            case 'sqlsrv':
                $parts = explode('.', $tableName);
                $num = (count($parts) - 1);
                $table = $parts[$num];
                $query = "SELECT column_name, data_type, character_maximum_length, column_default, is_nullable  FROM ".DB::connection()->getConfig('database').".INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$table."'";
                $columnName = 'column_name';
                $dataType = 'data_type';
                $charMaxLen = 'character_maximum_length';
                $columnDefault = 'column_default';
                $isNullable = 'is_nullable';
                $columnType = 'column_type';
                $reverse = false;
                break;

            default:
                $error = 'Database driver not supported: '.DB::connection()->getConfig('driver');
                return Logg( 'Failure', '', $error );
        }


        try{
            $result = DB::select( $query );
        }catch( QueryException $e ){
            return Logg( 'Failure', $e->getCode(), $e->getMessage(), $query  );
        }

        $columns = array();

        foreach( $result as $column ){
            $columns[] = [
                'column_name' => $column->$columnName,
                'data_type' => $column->$dataType,
                'max_length' => $column->$charMaxLen,
                'default' => $column->$columnDefault,
                'is_nullable' => $column->$isNullable,
                'column_type' => $column->$columnType
            ];
        }

        $log = Logg( 'Success', '', "Columns found for table $tableName" );
        $log['columns'] = $columns;
        return $log;
    }
};

?>