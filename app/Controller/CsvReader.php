<?php
declare(strict_types=1);

namespace App\Controller;

class CsvReader
{
    private $results;
    private $urlForParsing;

    public function __construct(string $pathFile)
    {
        $this->getUrlForParsing($pathFile);
        $this->checkFormat();                
    }

    public function getUrlForParsing( string $pathFile ) : string
    {
        if(!file_exists($pathFile)) 
        {
            die("Terminated: Can't find " . $pathFile);
        }

        return $this->urlForParsing = $pathFile;
    }    

    public function checkFormat() : void
    {               
        $mime_types = [ 
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'text/anytext',
            'application/octet-stream',
            'application/txt',
        ];

        $finfo = finfo_open( FILEINFO_MIME_TYPE );

        $file_type = finfo_file( $finfo, $this->urlForParsing );
    
        if(!in_array( $file_type, $mime_types ))
        {
            die("Terminated: Make sure the file is CSV format.");
        }
        
    }
    
    public function getResults(): array
    {
        $file = fopen($this->urlForParsing,"r");

        while (($result = fgetcsv($file)) !== false) 
        {
            if (array(null) !== $result) 
            { 
                $this->results[] = $result;
            }
        }
        
        fclose($file);      
        
        if(empty($this->results[0]))
        {
            die("Terminated: We didn't find content in the file.");
        }

        return $this->results;
    }
}