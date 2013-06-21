x2form
======

X2Form is an architecture for effective creation & management of web forms. 
The HTML forms are defined in XML(instead of normal HTML) to allow adding extra attributes which induce additional functionality.
The PHP class(X2Form) read the XML definition and render the form as well as process & validate submitted form.   
 
This architecture effectively separates of the  processing of form, definition of the form elements, rendering of the elements and positioning of the elements(layout)
 
Main features:
	1. Create HTML forms from XML file/string(predefined format)
	2. Can generate forms using only pure PHP calls as well(without XML files)
	3. It can read values of dropdowns, checkboxes and radio from PHP functions, PHP Closures, PHP Global variables MYSQL queries.
	4. Supports HTML/PHP template for customizing of the form layout
	4. Can handle file uploads, it can also rollback the file system changes if something goes wrong
	5. This class can do validation of the form values depending on the 'datatype' or 'datapattern'(using regular expressions)
	6. Values to be  pre-populated in the form can be passes in as array
	7. Multi-Language support, you can define labels, tooltips, description as well as error messages in multiple languages.
	   Thus making your form to render properly in multiple languages
 
