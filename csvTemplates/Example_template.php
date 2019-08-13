<?php
/**
  * Example template class for parsing PDF. 
  *
  * Completely up to you how you make it except having the getData() method 
  * with the $data property in EVERY csvTemplate.
  * Also make sure the tabulaTemplates filenames are in the proper structure. 
  *
  * Everything after the first underscore and up to the first period in the 
  * tabulaTemplates filenames is the 
  * name of the method called here.
  */
class Example_template
{
    function __construct()
    {
        // Constructor:
        
        // initialize data variable
        $this->data = array(
          'sample_data' => '',
          'date' => '',
          'number' => '',
          'line_items' => array(
              'someGroupName' => array()
          ),
        );
    }

    /**
      * MUST HAVE THIS METHOD(getData) AND PROPERTY($data) IN EVERY csvTemplate. 
      * Copy this method and $data property into every template.
      */
    private $data = null;
    public function getData()
    {
        if (property_exists($this, 'data')) {
            return $this->data;
        } else {
            return false; // error occurred
        }
    }
    
    /**
      * Should contain sample date field
      */
    public function box0(&$csv_handle)
    {
        $csv_data = array();
        
        while (($line = fgetcsv($csv_handle)) !== false) {
            //$line is an array of the csv elements
            $csv_data[] = $line;
        }
        
        // check if key exists unless picked wrong template
        if (array_key_exists(0, $csv_data) && array_key_exists(0, $csv_data[0]) 
            && array_key_exists(1, $csv_data[0])
           ) {
            // first line and second column(since where the data is)
            $this->data['sample_data'] = $csv_data[0][1];
        } else {
            return false; // error occurred
        }
        
        return true;
    }

    /**
      * Should contain date and number
      */
    public function box1($csv_handle)
    {
        $csv_data = array();
        
        while (($line = fgetcsv($csv_handle)) !== false) {
            // $line is an array of the csv elements
            $csv_data[] = $line;
        }
        
        if (array_key_exists(0, $csv_data) 
           && array_key_exists(1, $csv_data[0]) 
           && array_key_exists(2, $csv_data[0]) 
           && array_key_exists(1, $csv_data) 
           && array_key_exists(1, $csv_data[1]) 
           && array_key_exists(2, $csv_data[1])
           ) {
            // second line and second column(since where the data is)		
            $this->data['date'] = $csv_data[1][1];
            
            // second line and third column(since where the data is)
            $this->data['number'] = $csv_data[1][2];
        } else {
            return false; // error occurred
        }
        
        return true;
    }


    /**
      * Line items:
      *
      * Columns (numbers represent actual index location of the column in csv 
      * file for help) => data
      *  	[Column 0] => Field 1
      *  	[Column 1] => Field 2
      *  	[Column 2] => Field 3
      */
    public function lineitems($csv_handle)
    {
        /**
          * An example of how to keep track of when csv row is on line item.
          * This can be useful for when data has rows that do not have a fixed 
          * height on every row.
          * As you can see the 2nd line (from the [field1, field2, field3] table) 
          * of the 1st page in samplePDF.pdf has an extra line with text on field 3.
          */
        if (isset($this->data['line_items']['someGroupName']) 
            && is_array($this->data['line_items']['someGroupName'])
           ) {
            $on_line_items['cnt'] = count($this->data['line_items']['someGroupName']);
        } else {
            $on_line_items['cnt'] = 0;
        }
        $on_line_items['on_line_item'] = false;
        
        
        $lineNum = 0;
        while (($line = fgetcsv($csv_handle)) !== false) {
            // skip the first line (this is the heading)
            if ($lineNum != 0) {
                /**
                  * Link the columns to the actual field names to make it 
                  * easier for reading.
                  */
                if (array_key_exists(0, $line) 
                    && array_key_exists(1, $line) 
                    && array_key_exists(2, $line)
                   ) {
                    $field1 = $line[0];
                    $field2 = $line[1];
                    $field3 = $line[2];
                } else {
                    return false; // error occurred
                }

                /**
                  * To know that this row is on an actual new line item, we assume 
                  * that field 1 is always letters, field 2 is always numbers, 
                  * and field 3 is not empty.
                  */
                if (!empty($field1) 
                    && !empty($field2) 
                    && !empty($field3) 
                    && ctype_alpha($field1) 
                    && ctype_digit($field2)
                   ) {
                      
                    if ($on_line_items['on_line_item']) {
                        $on_line_items['cnt']++; // new record
                    } else {
                        $on_line_items['on_line_item'] = true;
                    }
                  
                    $this->data['line_items']['someGroupName'][$on_line_items['cnt']] = 
                    array(
                          'field1' => $field1,
                          'field2' => $field2,
                          'field3' => $field3,
                    );
                } else {
                    /* inside a line_item */

                    // preg_match -> allows letters and spaces ONLY
                    if ($on_line_items['on_line_item'] 
                        && empty($field1) 
                        && empty($field2) 
                        && preg_match("/^[[:alpha:] ]*$/",$field3)
                       ) {
                        $this->data['line_items']['someGroupName'][$on_line_items['cnt']]
                        ['field3'] .= '[new line]' . $field3;
                    } else {
                        $on_line_items['on_line_item'] = false;
                    }
                }
            }
            
            $lineNum++;
        }

        return true;
    }
}