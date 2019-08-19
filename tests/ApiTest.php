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
        $this->assertEquals($test->getResults()[2], 0.00);
        $this->assertEquals($test->getResults()[3], 0.06);
        $this->assertEquals($test->getResults()[4], 0.90);
        $this->assertEquals($test->getResults()[5], 0);
        $this->assertEquals($test->getResults()[6], 0.70);
        $this->assertEquals($test->getResults()[7], 0.30);
        $this->assertEquals($test->getResults()[8], 0.30);
        $this->assertEquals($test->getResults()[9], 5.00);
        $this->assertEquals($test->getResults()[10], 0.00);
        $this->assertEquals($test->getResults()[11], 0.00);
        $this->assertEquals($test->getResults()[12], 8612);
    }
    

}
