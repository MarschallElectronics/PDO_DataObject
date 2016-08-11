<?php
/**
 * Object Based Database Query Builder and data store
 *  - Introspection Component.
 *
 * For PHP versions  5 and 7
 * 
 * 
 * Copyright (c) 2015 Alan Knowles
 * 
 * This program is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU Lesser General Public License as   
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * Lesser General Lesser Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *  
 * @category   Database
 * @package    PDO_DataObject
 * @author     Alan Knowles <alan@roojs.com>
 * @copyright  2016 Alan Knowles
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html  LGPL 3
 * @version    1.0
 * @link       https://github.com/roojs/PDO_DataObject
 */
  
  
class PDO_DataObject_Introspection
{
    /**
     * @var PDO_DataObject $do - the dataobject.
     */
    protected $do;
    
    /**
     * Constructor
     * @arg DataObject $do   - the dataobject.
      */
    
    function __construct(PDO_DataObject $do)
    {
        $this->do = $do;
        
    }
    /**
     * 'complex' version of databaseStructure - this is not so 'speed sensitive'
     * only used when
     * b) proxy is set..
     * c) ini_**** is set...
     *
     *
     *
     *    
     * usage :
     * DB_DataObject::databaseStructure(  'databasename',
     *                                    parse_ini_file('mydb.ini',true), 
     *                                    parse_ini_file('mydb.link.ini',true)); 
     *
     * 1 argument:
     * DB_DataObject::databaseStructure(  'databasename')
     *  - returns the structure
     *  - always calls generator...
     
    *
     *  - set's the structure.. and the links data..
          
     *
     * obviously you dont have to use ini files.. (just return array similar to ini files..)
     *  
     * It should append to the table structure array 
     *
     *     
     * @param optional string  name of database to assign / read
     * @param optional array   structure of database, and keys
     * @param optional array  table links
     * @return (varies) - depends if you are setting or getting...
     */
    
    function databaseStructure()
    {
        $proxy = PDO_DataObject::$config['proxy'];
         
        
        // Generator code
        
        if ($args = func_get_args()) { 
             
                // this returns all the tables and their structure..
                
            $this->do->debug("Loading Generator as databaseStructure called with args for database = {$args[0]}",1);
            
            
            $x = new PDO_DataObject();
            $x->database( $args[0]);
            $x->PDO();
            $cls = get_class($this);
             
            $tables = (new $cls ($x))->getListOf('tables');
           
            if (empty($tables)) {
                $this->do->raiseError("Could not introspect database, no table returned from getListOf(tables)");
            }
               
            foreach($tables as $table) {
                
                $this->_generator()->fillTableSchema($x->database(), $table);
                
            }
            // prevent recursion...
            
            PDO_DataObject::$config['proxy'] = false;
            $ret = $x->databaseStructure($x->database(),false); 
            PDO_DataObject::$config['proxy'] = $proxy;
            return $ret;
            // databaseStructure('mydb',   array(.... schema....), array( ... links')
         
            // will not get here....
        }
        
        
        $this->PDO();
        
        
        $database = $this->do->database();
        $table = $this->do->tableName();
        
        // probably not need (pdo() will load it..)
        PDO_DataObject::loadConfig();
        
        // we do not have the data for this table yet...
        
        // if we are configured to use the proxy..
        
        if ( $proxy )  {
            
            $this->_generator()->fillTableSchema($database, $table);
        
           
            PDO_DataObject::$config['proxy'] = false;
            $ret = PDO_DataObject::databaseStructure($database,false); 
            PDO_DataObject::$config['proxy'] = $proxy;
            return $ret;
        }
            
             
        
        
        // if you supply this with arguments, then it will take those
        // as the database and links array...
         
        //           
        
        $schemas = is_array(PDO_DataObject::$config["ini_{$database}"]) ?
            PDO_DataObject::$config["ini_{$database}"] :
            explode(PATH_SEPARATOR,PDO_DataObject::$config["ini_{$database}"]);
        
                    
         
        $ini_out  = array();
        foreach ($schemas as $ini) {
            if (empty($ini)) {
                continue;
            }
            if (!file_exists($ini) || !is_file($ini) || !is_readable ($ini)) {
                $this->do->debug("ini file is not readable / does not exist: $ini","databaseStructure",1);
                return $this->do->raiseError( "Unable to load schema for database and table (turn debugging up to 5 for full error message)",
                                   PDO_DataObject::ERROR_INVALIDARGS, PDO_DataObject::ERROR_DIE);
       
            }
            $ini_out = array_merge(
                $ini_out,
                parse_ini_file($ini, true)
            );
                
             
        }
        
        // are table name lowecased..
        if (PDO_DataObject::$config['portability'] & 1) {
            foreach($ini_out as $k=>$v) {
                // results in duplicate cols.. but not a big issue..
                $ini_out[strtolower($k)] = $v;
            }
        }
        if (!empty($ini_out)) {
            PDO_DataObject::databaseStructure($database,$ini_out,false, true);
        }
        
       
        PDO_DataObject::$config['proxy'] = false;
        $dbini = PDO_DataObject::databaseStructure($database,false); 
        PDO_DataObject::$config['proxy'] = $proxy;
        
        // now have we loaded the structure.. 
        
        if (!empty($dbini[$this->do->tableName()])) {
            return $dbini;
        }
        // previously we tried proxy here... - but it's already supposed to be tried at this point anyway.
        
       
        $this->do->debug("Cant find database schema: {$database}/{$table} \n".
                    "in links file data: " . print_r($dbini,true),"databaseStructure",5);
        // we have to die here!! - it causes chaos if we dont (including looping forever!)
        $this->do->raiseError( "Unable to load schema for database and table (turn debugging up to 5 for full error message)", 
                                   PDO_DataObject::ERROR_INVALIDARGS, PDO_DataObject::ERROR_DIE);
        return false;
        
         
        
    }
    /**
     * Create an instance of the generator.
     *
     * @returns PDO_DataObject_Generator
     */
    private function _generator()
    {
        class_exists('PDO_DataObject_Generator') ? '' : 
                require_once 'PDO/DataObject/Generator.php';
        return new PDO_DataObject_Generator();
    }
    
    /**
     * Lists internal database information
     *
     * @param string $type  type of information being sought.
     *                       Common items being sought are:
     *                       tables, databases, users, views, functions
     *                       Each DBMS's has its own capabilities.
     *
     * @return array  an array listing the items sought.
     *                Or throws an error..
     */
    
    function getListOf($type)
    {
        $this->do->debug($type, __FUNCTION__);
        $sql = $this->getSpecialQuery($type); // can throw an exception...
        if ($sql === null) {
            $this->last_query = '';
            return $this->do->raiseError("Can not get Listof $type", PDO_DataObject::ERROR_INVALIDCONFIG);
        }
        $this->do->debug($sql, __FUNCTION__);
      
        if (is_array($sql)) {
            // Already the result
            return $sql;
        }
        $this->do->query($sql);
        $ret = array();
        while ($this->do->fetch()) {
            // pretty slow way to do this, but if we are doing this stuff it's slow anyway.
            $ret[] = array_values($this->do->toArray())[0];
        }
        //var_export($ret);
        
        return $ret;
    }
    
    // we could put a generic tableInfo here... - using ColumnMeta ??
    
    
}


