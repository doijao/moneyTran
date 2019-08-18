<?php

require_once 'vendor/autoload.php';
use App\Controller\CsvReader;
use App\Controller\Transaction;

$getContent = new CsvReader($argv[1]);        
$test = new Transaction($getContent->getResults());  
 
foreach($test->getResults() as $result)
{
    echo $result  .''. PHP_EOL;
}
