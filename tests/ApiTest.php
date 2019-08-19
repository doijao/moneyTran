<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Controller\CsvReader;
use App\Controller\Transaction;

class ApiTest extends TestCase {

    /**
     * @test
     */
    public function testCSVFile() {
        $csvFile = "./resources/bulkTransaction.csv";
        $getContent = new CsvReader($csvFile);        
        $test = new Transaction($getContent->getResults());          

        $this->assertEquals(count($test->getResults()), 13);                
        $this->assertEquals($test->getResults()[0], 0.60);                
        $this->assertEquals($test->getResults()[1], 3.00);                
        $this->assertEquals($test->getResults()[12], 8612.00);

    }
    

}
