# ParsePDF

PHP library that uses [tabula](https://github.com/tabulapdf/tabula) to extract
PDF data into your custom structured PHP array.  
You can make template selections with tabula and then process them with your
own custom template class.

## Getting Started

### Installing

* Download the zip from github and unzip it where you would want to use parsePDF.

### Prerequisites

1. First you need to have Java JRE installed. And remember the path to the Java  
   executable.  
   This is where I have Java installed:
   > C:\Program Files (x86)\Java\jre1.8.0_201\bin\java.exe
2. There is a tabula directory included in this project, it might be outdated  
   when you are using parsePDF. Although, I try to keep it up to date.  
   Test if you can use tabula first, by clicking or running in the command-line  
   the file './tabula/run_tabula_8081.cmd'. This will run tabula on  
   127.0.0.1:8081 and you can access it from a browser.  
   Make sure you can upload a pdf and parse it using the tabula interface, 
   since this is what you will use to make the tabula templates.
3. This was written under a Windows OS, but it is possible 
   (with some small modifications) to make all the things work under another OS.
4. PHP 5.6+
5. exec() command allowed in php.ini

### Making a tabula template

1. Run `./tabula/run_tabula_8081.cmd` This will run tabula on port 8081.
2. Goto the tabula browser interface. An example would be, `http://127.0.0.1:8081/`.
3. Import the PDF file into tabula.
4. Here is an example screen shot of the box1 selection:
   ![image](https://github.com/genya1/ParsePDF/blob/master/misc/tabula_template01.PNG)
5. After making your selections, click on the Templates button in the top
   left corner, and save the template.
6. Now goto My templates in the tabula interface and download your template into  
   the `./tabulaTemplates/areas` folder. (For example,  
   let the filename be `myFirstTemplate_box001.tabula-template.json`).
7. The steps after this are for making the separators.
8. Here is an example screen shot of the line_items separator selection:
   ![image](https://github.com/genya1/ParsePDF/blob/master/misc/tabula_template02.PNG)  
   You can see that the right edge of each box is the point where  
   the separation will be made when creating the .csv files.  
9. After making your selections, click on the Templates button in the top  
   left corner, and save the template.
10. Now goto `My templates` in the tabula interface and download your template  
    into the `./tabulaTemplates/separators` folder. (If this separator is for  
    the same selection as on top, then you would have to name the file,  
    `myFirstTemplate_box001.tabula-template.json`).

### Making a csv template

Please refer to the './csvTemplate/Example_template.php' file provided.  
The samplePDF included has a break in the line items and the  
Example_template.php shows how you can handle reading this.

1. Make a new file in `./csvTemplate` folder and create a PHP class inside the file.
2. Copy over this code into your class:
   ```
	private $data = null;
	public function getData()
	{
		if(property_exists($this, 'data'))
		{
			return $this->data;
		}
		else
		{
			return false; // error occurred
		}
	}   
   ```
3. Then create the methods with the names matching the
   tabulaTemplate filenames.(For example, if you  
   followed the above 'Making a tabula template,'  
   then one of your method names would be `box001,`  
   this is because from the tabulaTempate filename,  
   `myFirstTemplate_box001.tabula-template.json`)
4. When creating this class, it would be helpful to see the  
   .csv files being created. Thus, run ParsePDF with a fake  
   csvTemplate class (could be the `Example_template`) and  
   enable the [DontDeleteCSV](#DontDeleteCSV) method.
   
In this csvTemplate class is where you will be able to read the  
csv file handles then put the csv data into the `data` array
property of the class.

### Parsing your PDF
Below is the simplest example of the use of parsePDF in production.  
This is assuming you have made a tabula template  
and csv template. (The ParsePDF project comes with a sample PDF,  
example csv template, and example tabula template so it is safe to
simply run this code and check the output)
```
include './parsePDF.php';

$pdfreader = new ParsePDF();

$path = 'C:\Program Files (x86)\Java\jre1.8.0_201\bin\java.exe';
$pdfreader->setJavaPath($path);

$out = $pdfreader->parse('../samplePDF.pdf', 
                         'Example_template', 
                         'sampleTemplate');

print_r($out);
```

You will see the 'debug.txt' file in the `./example_development_out` folder, debug.txt is an  
output from the `./example_development.php`
that reads the samplePDF.pdf file.

The basics of how it works is it uses tabula CLI interface to make .csv files,  
and then you parse those csv files with your own custom PHP class.

## Documentation

### Complete summary of how it all works

The parsePDF class begins by reading the `./tabulaTemplate` directory.  
Every tabulaTemplate directory __MUST__ have 
an 'areas' and 'separators' directory, this  
is the default structure of the ParsePDF project folder, so  
you do not need to do anything to set up these directories.

The `areas` directory will contain the `.tabula-templates.json` files from the  
template selections you make in tabula, these files filenames  
__MUST__ have this format,  
`[your template name]_[the linked method name from your csv template PHP file].tabula-templates.json`
excluding the single quotes and square brackets. In the actual filename,  
'your template name' is just some name you can use to name your box selection,  
it can be anything except underscores and periods (this is because ParsePDF  
uses the underscore and period to match the method names to the csv template).  
The method name from your csv template PHP file  
should be exactly the same as everything between the first underscore and  
first period of the tabula template filename. Everything between the first  
underscore and first period is what is used to link the method name.  
This format is important.
An example is included in the parsePDF directory, which is  
'sampleTemplate_box0.tabula-template.json', as you can see in ./csvTemplate/Example_template.php
has a 'box0' method and the csv file handle is the argument  
given to that method after converting the selection you made in tabula to a
.csv file.

Now the 'separators' directory is if tabula cannot identify where the boundaries  
are between columns in a table. To make a separator,  
like 'sampleTemplate_lineitems.tabula-template.json' for example,  
you would go into the tabula browser interface and open the PDF you are parsing  
then make a selection that places the __right__ edge of the box as the point  
where tabula should always separate the columns.  
The name of this file __MUST__ be exactly the same as the area selection you are  
doing it for. This whole process of making a template in the separators directory  
is to improve the accuracy of tabula to cutoff the columns exactly where you  
specify. The process is optional, since otherwise tabula will guess where to  
make the cutoffs for the columns.
You can have an empty separators directory, and everything will work, but you  
must have a separators directory in the `./tabulaTemplate` folder.

Then parsePDF creates 'box.csv' files in the `./tabula` folder (or if you change  
this path with [setTabulaPath()](#setTabulaPath) method it will create them  
there). The csv file handle is sent to the method and class you specified in  
the 'parse' method. In the csvTemplate you can read the .csv file, as long as  
you put the data you read into the 'data' property of the class.  
After parsePDF goes through every box selection it returns the array you created  
in the csvTemplate class.

That is it!

### setJavaPath() - (_Required_)

This must be set when using parsePDF, it sets the complete path to your 
Java executable.

Example:
```
$pdfreader = new ParsePDF();

// an example file path to MY java.exe file. Yours will depend on YOUR file path.
$pdfreader->setJavaPath('C:\Program Files (x86)\Java\jre1.8.0_201\bin\java.exe');
```

### parse(_PDF-filepath_, _csvTemplate_, _tabulaTemplate_) - (_Required_)

This is what makes it all work. Provide the complete path to your pdf file,  
then just state the name of you csvTemplate, and tabulaTemplate directory.

Example:
```
$pdfreader = new ParsePDF();
.
.
.
// parse the samplePDF.pdf file using ./csvTemplate/Example_template.php as the 
// csv template and the directory ./tabulaTemplate/sampleTemplate 
// as the tabulaTemplate
$out = $pdfreader->parse('../samplePDF.pdf', 
                         'Example_template', 
                         'sampleTemplate');
```

### setJarFilePath(_path_) - (_Optional_)

If you want to use a different .jar file than the provided one in parsePDF,  
you will need to give the complete path to this method.

Example:
```
$pdfreader = new ParsePDF();

// path and filename to tabula jar file
$pdfreader->setJarFilePath('tabula-1.0.2-jar-with-dependencies.jar');
```

### setTabulaPath(_path_) - (_Optional_)

If you want to use a different path to the tabula stuff, you will need to  
give the complete path to this method.

Example:
```
$pdfreader = new ParsePDF();

// path to tabula files
$pdfreader->setTabulaPath('./tabula');
```

### DontDeleteCSV() - (_Optional_)

This is useful when creating the csvTemplate class, since you need to see  
what exact column and row numbers your data is in.  
It tells parsePDF to not delete the 'box.csv' files in the tabula path.

Example:
```
$pdfreader = new ParsePDF();

// do not delete the .csv files for debugging how to setup my
// Example_template.php class
$pdfreader->DontDeleteCSV();
```

### setLineBytes(_bytes_) - (_Optional_)

This sets the exact number of bytes to read from the tabulaTemplate files.  
The default it 4096 bytes or until EOF is reached.

Example:
```
$pdfreader = new ParsePDF();

$pdfreader->setLineBytes(4096); // number of bytes to read
```

## Built With

* [tabula](https://github.com/tabulapdf/tabula)
* PHP 5.6+

## Contributing

Please feel free to submit pull requests and report any issues you encounter.

## Author

* Genya

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE)  
file for details

## Acknowledgments

* [tabula github](https://github.com/tabulapdf/tabula), [tabula website](https://tabula.technology/), [tabula-java](https://github.com/tabulapdf/tabula-java/)
