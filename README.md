VTU-Result-Parser-php-library
=============================
	Title: VTU Result Parser Php Library 3.0.2
	Description:
		Php library for parsing VTU Results.
		This library can dyanamically parse VTU result site with respect to semester result.
	Version :
			3.0.3 (26/6/2013)
			3.0.2 (1/5/2013)
			2.0.6 (18/3/2013)
			2.0.5 (3/3/2013) 
			2.0.4 (25/2/2013)
	
	Author: Vishal Vijay (V4 Creations)
	Phone: +919995533909, +919739211838
	Email: 0vishalvijay0@gmail.com
	Support from: Matrix Inc., www.vtulife.com
	
	How to use:
		1) Copy this file to your folder.
		2) Then import this library to your php code by using:
				require_once __DIR__ . '/VTUResultParser.php';
		3)Then create an object of this class by using:
			$result=new VTUResultParser(0); //Here passing 0 for 'Regular result' and passing 1 for 'Revaluation result.' 
		4)Then you can request for result by using requestResult($usn) method:
			$result->requestResult("4PA09CSxxx");
		5)Displaying the result :
			$result->name : Name of student.
			$result->usn : USN of student.
			$result->semesters[$index] : It is an array which contains the semester in the order of result in VTU result page.
			$result->$result[$index] : It is an array which contains the result of each semester(Fail or Second Class or etc.).
			$result->percentage[$index] : It is an array which contains the persentage of each semester.
			$result->total[$index] : It is an array which contains the total marks of each semester(Calculated and
				use only for regular result).
			$result->totalInPage : We can get the total mark which is show in VTU result page.
			$result->markInTable : This is a 5 dimensional array for regular result and 6 dimensional array for
				revaluation result which contains the marks in the form of tables.
				markInTable(semester_order_number)(row_of_mark_table_same_as_vtu)(each_cell_as_per_vtu_result_page).		
			$result->errorValue : To check error occurrence. For successful execution $errorValue will be -1.
				If its value is not -1 you can call  $result->getError() to get the error message.
			$result->getError() : To get the error message
			$result->setProxy($proxy) : To set proxy eg.: $proxy="127.0.255.254:3128"
	Thanks : We are expecting your contributions.
