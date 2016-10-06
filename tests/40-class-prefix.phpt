--TEST--
config - class_prefix Test  
--FILE--
<?php
require_once 'includes/init.php';

 

PDO_DataObject::config(array(
        'class_location' =>
                    __DIR__.'/includes/sample_classes/DataObjects_' .
                    PATH_SEPERATOR .
                    __DIR__.'/includes/sample_classes/',
        'class_prefix' => 'DataObjects_' . PATH_SEPERATOR ,
        
        'database' => '',
        'databases' => array(
             'inserttest' => 'mysql://user:pass@localhost/inserttest',
        ),
        
          
     
));

PDO_DataObject::debugLevel(1);
 

echo "\n\n--------\n";
echo "listed with seperators\n" ;
PDO_DataObject::config(array(
    'schema_location' => __DIR__.'/includes' . PATH_SEPARATOR . __DIR__.'/includes/test_ini'
));


PDO_DataObject::factory('Events')
        ->limit(1)
        ->find(true);
        
print_r(PDO_DataObject::factory('Events')->tableColumns());

PDO_DataObject::factory('account_transaction')
        ->limit(1)
        ->find();
        
print_r(PDO_DataObject::factory('account_transaction')->tableColumns());

PDO_DataObject::reset();
echo "\n\n--------\n";
echo "listed associative array\n" ;

PDO_DataObject::config(array(
    'schema_location' => array(
        'inserttest' =>    __DIR__.'/includes' ,
        'mysql_anotherdb' =>   PATH_SEPARATOR . __DIR__.'/includes/test_ini'
    )
));



PDO_DataObject::factory('Events')
        ->limit(1)
        ->find(true);
        
print_r(PDO_DataObject::factory('Events')->tableColumns());

PDO_DataObject::factory('account_transaction')
        ->limit(1)
        ->find();
        
print_r(PDO_DataObject::factory('account_transaction')->tableColumns());


PDO_DataObject::reset();
echo "\n\n--------\n";
echo "listed associative array with absolute path. \n" ;

// we could test array's here...
PDO_DataObject::config(array(
    'schema_location' => array(
        'inserttest' =>    __DIR__.'/includes/mysql_somedb.ini' ,
        'mysql_anotherdb' =>   __DIR__.'/includes/test_ini/mysql_anotherdb.ini'
    )
));



PDO_DataObject::factory('account_code')
        ->limit(1)
        ->find(true);
        
print_r(PDO_DataObject::factory('account_code')->tableColumns());

PDO_DataObject::factory('account_transaction')
        ->limit(1)
        ->find();
        
print_r(PDO_DataObject::factory('account_transaction')->tableColumns());



PDO_DataObject::reset();
echo "\n\n--------\n";
echo "only list one schema location....\n" ;

// we could test array's here...
PDO_DataObject::config(array(
    'schema_location' => array(
        'inserttest' =>    array(__DIR__.'/includes/mysql_somedb.ini' , __DIR__.'/includes/test_ini/mysql_anotherdb.ini')
    ),
    // we need to change this, as it hunt's the databases for schema's...
    'databases' => array(
          'inserttest' => 'mysql://user:pass@localhost/inserttest',
    ),
));



PDO_DataObject::factory('account_code')
        ->limit(1)
        ->find(true);
        
print_r(PDO_DataObject::factory('account_code')->tableColumns());

PDO_DataObject::factory('account_transaction')
        ->limit(1)
        ->find();
        
print_r(PDO_DataObject::factory('account_transaction')->tableColumns());




?>
--EXPECT--
