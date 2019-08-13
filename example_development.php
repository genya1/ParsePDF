<?php
/**
  * Development example
  */
  
include './parsePDF.php'; // load library

$pdfreader = new ParsePDF();

// an example file path to MY java.exe file. Yours will depend on YOUR file 
// paths.
$path = 'C:\Program Files (x86)\Java\jre1.8.0_201\bin\java.exe';
$pdfreader->setJavaPath($path);

// the bottom is for custom setup, the values used here are just for display 
// and all of them are the actual defaults in the class.

// path to tabula files
$pdfreader->setTabulaPath('./tabula');

// path and filename to tabula jar file
$pdfreader->setJarFilePath('tabula-1.0.2-jar-with-dependencies.jar');

// number of bytes to read
$pdfreader->setLineBytes(4096);

// do not delete the .csv files for debugging how to setup my 
// Example_template.php class
$pdfreader->DontDeleteCSV();

// parse the samplePDF.pdf file using ./csvTemplate/Example_template.php as 
// the csv template and the directory ./tabulaTemplate/sampleTemplate as 
// the tabulaTemplate
$out = $pdfreader->parse('../samplePDF.pdf', 
                        'Example_template', 
                        'sampleTemplate'
                        );

// debug in file
file_put_contents('./example_development_output/debug.txt', print_r($out, true));

// debug in browser
print_r($out);