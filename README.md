VTU Result Parser php library
=============================

 Php library for parsing VTU Results. This library can dynamically parse VTU result site with respect to semester result.
 
  **Author:** Vishal Vijay (V4 Creations)
  
  **Email:** 0vishalvijay0@gmail.com

  **Support from:** Team Matrix, www.vtulife.com

#### Versions with change log: ####
 
  * 3.1.0 (14/7/2014)
    1. Hidden dyanamic secret value for fetching result issue fixed (#3).
    2. Made all URLs as constance.
  * 3.0.6 (18/2/2014)
  * 3.0.5 (29/1/2014)
  * 3.0.4 (7/10/2013)
  * 3.0.2 (1/5/2013)
  * 2.0.6 (18/3/2013)
  * 2.0.5 (3/3/2013) 
  * 2.0.4 (25/2/2013)

####How to use:####

1. Copy `VTUResultParser.php` file to your folder.
2. Then import this library to your php code by using:

    ```
        require_once __DIR__ . '/VTUResultParser.php';
    ```

3. For better performance create a folder with name `cashe` and give `777` permission.

4. Then create an object of `VTUResultParser` class by using:

    ```
        $result=new VTUResultParser(0); //Here passing 0 for 'Regular result' and passing 1 for 'Revaluation result.' 
    ```
			
5. Then you can request for result by using requestResult($usn) method:

    ```
        $result->requestResult("4PA12CSxxx");
    ```
		
6. Displaying the result and other API's :
    * `$result->name` : Name of student.
	* `$result->usn` : USN of student.
	* `$result->semesters[$index]` : It is an array which contains the semester in the order of result in VTU result page.
	* `$result->$result[$index]` : It is an array which contains the result of each semester(Fail or Second Class or etc.).
	* `$result->percentage[$index]` : It is an array which contains the persentage of each semester.
	* `$result->total[$index]` : It is an array which contains the total marks of each semester(Calculated and use only for regular result).
    * `$result->totalInPage` : We can get the total mark which is show in VTU result page.
	* `$result->markInTable` : This is a 5 dimensional array for regular result and 6 dimensional array for revaluation result which contains the marks in the form of tables.

    `markInTable(semester_order_number)(row_of_mark_table_same_as_vtu)(each_cell_as_per_vtu_result_page).`
    
	* `$result->errorValue` : To check error occurrence. For successful execution $errorValue will be -1.If its value is not -1 you can call  $result->getError() to get the error message.
    * `$result->getError()` : To get the error message
	* `$result->setProxy($proxy)` : To set proxy eg.: $proxy="127.0.255.254:3128"
	

Thanks : We are expecting your contributions.