VTU-Result-Parser-php-library
=============================

Php library for parsing VTU Results.This library can dyanamically parse the VTU site with respect to back pepers etc.

Version : 2.0.5 (3/3/2013) 

Author: Vishal Vijay (V4 Creations)
Phone: +919995533909 and +919739211838
Email: 0vishalvijay0@gmail.com
Support from: Matrix Inc., www.vtulife.com

How to use:
	1) Copy this file to your folder.
	2) Then import this to your php code by using:
		require_once __DIR__ . '/VTUResultParser.php';
	3)Then create an object of this class by using:
		$result=new VTUResultParser(0); //Here passing 0 for 'Regular result' and passing 1 for 'Revaluation result.' 
	4)Then you can request your usn by using:
		$result->requestResult("4PA09CSxxx");
		It will calculate the result by parsing vtu result site.
	5)Next we want to display the result.
		$result->getError() : To get the error message
		$result->setProxy($proxy) : To set proxy eg.: $proxy="127.0.255.254:3128"
		$result->name : Name of student.
		$result->usn : USN of student.
		$result->result : Result of student(Fail or Second Class or etc.).
		$result->percentage : To get the percentage.
		$result->total : To get the total.
		$result->givenUsn : You can use this to know which usn is executed last.
		$result->errorValue : To check error occurrence. For successful execution $errorValue will be -1.
			If its value is not -1 you can call  $result->getError() to get the error message.
		$result->semesters : It is an array which contains the semester number in the order of result.
		$result->markInTable : This is a three dimensional array which contains the marks in the form of tables.
			markInTable(semester_order_number)(row_of_mark_table_same_as_vtu)(each_cell_subject_or_mark).		
Thanks : We are expecting your contributions.
