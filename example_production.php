<?php
/**
  * Production example
  */

include './parsePDF.php';

$pdfreader = new ParsePDF();

$path = 'C:\Program Files (x86)\Java\jre1.8.0_201\bin\java.exe';
$pdfreader->setJavaPath($path);

$out = $pdfreader->parse('../samplePDF.pdf', 
                         'Example_template', 
                         'sampleTemplate'
                        );

print_r($out);