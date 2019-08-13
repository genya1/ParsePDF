<?php
/**
  * ParsePDF
  *
  * PHP library to extract data from PDF with the help of tabula
  *
  * Copyright (C) 2019  genya1
  * https://github.com/genya1
  *
  * MIT LICENSE
  */

/**
  * Main class that links tabula and the csvTemplates 
  * to perform the parsing of the PDF.
  */
class ParsePDF
{
    function __construct()
    {
        // Constructor
    }

    // the complete path to the Java executable.
    protected $JavaPath;

    /** 
      * Defaults
      */
    // the default path to the tabula components.
    protected $TabulaPath = './tabula';
    
    // the name of the original .jar file. Also this is in ./tabula originally.
    protected $JarFile = 'tabula-1.0.2-jar-with-dependencies.jar';
    
    // read 4096 bytes or until EOF of the tabulaTemplate files
    protected $LineBytes = 4096;
    
    // will delete the .csv files. Should be FALSE in production mode.
    protected $dontDeleteCSV = false;


    /**
      * INTERNAL FUNCTIONS
      */
    private function getPDFPages($document)
    {
        /**
          * TODO: future
          * Helper function for program to know how many pages in PDF.
          */
        $pagecount = 0;

        return $pagecount;
    }

    /**
      * USER FUNCTIONS
      */

    /**
      * Give your systems path to the java.exe file.
      *
      * For example: 'C:\Program Files (x86)\Java\jre1.8.0_201\bin\java.exe'
      *
      * Arguments:
      *   $path => The path in string format.		
      */
    public function setJavaPath($path = null)
    {
        if (!empty($path) && file_exists($path)) {
            $this->JavaPath = $path;
            return true;
        } else {
            return false; // error occurred
        }		
    }

    /**
      * Change path to tabula CLI jar file.
      *
      * Default: tabula-1.0.2-jar-with-dependencies.jar
      *
      * Note that since in the command line the 'cd' command is executed the
      * complete path of the jar file is 
      * './tabula/tabula-1.0.2-jar-with-dependencies.jar'
      *
      * Arguments:
      *   $path => The path in string format.			
      */
    public function setJarFilePath($path = null)
    {
        if (!empty($path) && file_exists($path)) {
            $this->JarFile = $path;
            return true;
        } else {
            return false; // error occurred
        }	
    }

    /**
      * Change path to tabula files.
      *
      * Default: ./tabula
      *
      * Arguments:
      *   $path => The path in string format.		
      */
    public function setTabulaPath($path = null)
    {
        if (!empty($path) && is_dir($path)) {
            $this->TabulaPath = $path;
            return true;
        } else {
            return false; // error occurred
        }		
    }

    /**
      * For debugging the csv files when making your csvTemplates.
      * This should only be used when debugging the .csv files, but in 
      * production the .csv files should be deleted.
      *
      * Also note that if you open the .csv files and do not close them the 
      * program will not work properly since it will try to delete the .csv 
      * files if this option is not used.
      */
    public function DontDeleteCSV()
    {
        $this->dontDeleteCSV = true;
    }

    /**
      * Specify how many bytes to read from the tabulaTemplate files.
      *
      * Default: 4096 bytes or until EOF
      *
      * Arguments:
      *   $bytes => The number of bytes.		
      */
    public function setLineBytes($bytes = null)
    {
        if (ctype_digit($bytes) && $bytes > 0) {
            $this->LineBytes = $bytes;
            return true;
        } else {
            return false; // error occurred
        }
    }

    /**
      * The method that combines your tabulaTemplates and processes them and 
      * uses the specified csvTemplate to return a PHP array of the PDF data.
      *
      * Arguments:
      *   $PDF_filepath => The complete file path of your PDF file.
      *   $csvTemplate => The name of your csvTemplate file in ./csvTemplates. 
      *                   Must be the same as the class name.
      *   $tabulaTemplate => The name of the file in ./tabulaTemplate. 
      *                      This is all the box selections and separations of 
      *                       what part of the PDF you want to extract.
      */
    public function parse($PDF_filepath, $csvTemplate, $tabulaTemplate)
    {
        // load template class
        include './csvTemplates/' . $csvTemplate . '.php'; 
        $templateClass = new $csvTemplate();

        $directory = "./tabulaTemplates/$tabulaTemplate/areas";
        $json_boxes = array_diff(scandir($directory), array('..', '.'));

        $i = 0;
        // loop through each box selection in tabula
        foreach ($json_boxes as $box_file) {
            // tabula process PDF -> CSV

            // figure out box number
            // +1 to get rid of underscore
            $underscore_pos = strpos($box_file, '_') + 1;
            $period_pos = strpos($box_file, '.');
            $method_name = substr($box_file, 
                                  $underscore_pos, 
                                  ($period_pos - $underscore_pos)
                                 );

            $json_temp = fopen($directory . "/" . $box_file, 'r');
            
            // read bytes or until EOF
            $boxes = json_decode(fread($json_temp, $this->LineBytes));
            fclose($json_temp);

            // check if separators exist for box selection
            $columns = "";
            $filename = "./tabulaTemplates/$tabulaTemplate/
                          separators/$box_file";
            if (file_exists($filename)) {
                $json_temp = fopen($filename, 'r');
                
                // read bytes or until EOF
                $separator = json_decode(fread($json_temp, $this->LineBytes));
                fclose($json_temp);

                // assuming no last separator box since only in 
                // between lines needed
                foreach($separator as $ele)
                {
                    $columns .= "{$ele->x2},";
                }
                // remove last comma
                $columns = substr_replace($columns ,"", -1);
            }

            foreach ($boxes as $box) {
                // executing tabula to extract the box selections into .csv files.
                if (!empty($columns)) {    // have separators
                    $cmd = 'cd '.$this->TabulaPath.' && "'.$this->JavaPath.
                                '" -jar '.$this->JarFile.
                                ' --use-line-returns --pages '.$box->page.
                                ' -c '.$columns.' --area '.$box->y1.','.
                                $box->x1.','.$box->y2.','.$box->x2.
                                ' --outfile box'.$i.'.csv '.$PDF_filepath;
                    exec($cmd, $cmd_out);
                } else {                  // no separators 
                    $cmd = 'cd '.$this->TabulaPath.' && "'.$this->JavaPath.
                                '" -jar '.$this->JarFile.
                                ' --use-line-returns --pages '.$box->page.
                                ' --area '.$box->y1.','.$box->x1.','.
                                $box->y2.','.$box->x2.' --outfile box'.$i.
                                '.csv '.$PDF_filepath;
                    exec($cmd, $cmd_out);
                }

                // process CSV box file	
                $box_handle = fopen($this->TabulaPath.'/box'.$i.'.csv', 'r');
                if (method_exists($templateClass, $method_name)) {
                    if ( ($templateClass->$method_name($box_handle) ) === false) {
                        // something wrong occurred in csv templates
                        return false; // error occurred
                    }
                }
                fclose($box_handle);

                // Whether to delete or don't delete the .csv files
                if (!$this->dontDeleteCSV) {
                    // delete csv file made by tabula
                    unlink($this->TabulaPath.'/box'.$i.'.csv');
                }
                
                $i++;
            }

        }

        /**
          * csvTemplate must have this variable and method or will not work.
          */
        if (method_exists($templateClass, 'getData')) {
            return $templateClass->getData(); // return the data
        } else {
            return false; // error occurred
        }
    }
}