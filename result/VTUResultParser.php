<?php
	/*
		Title: VTU Result Parser Php Library
		Description:
			Php library for parsing VTU Results.
			This library can dyanamically parse the VTU site with respect to back pepers etc.
		
		Version : 2.0.5 (3/3/2013) 
				2.0.4 (25/2/2013)
	 
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
	 */
	class VTUResultParser{
		public $name;
		public $usn;
		public $semesters=array();
		public $markInTable=array(array(),array(),array()); 
		public $total;
		public $result;
		public $percentage;
		public $errorValue=-1;
		public $givenUsn;
		private $proxy="";
		
		private $FLAG_REGULAR_RESULT='http://results.vtu.ac.in/vitavi.php?submit=true&rid=';
		private $FLAG_REVAL_RESULT='http://results.vtu.ac.in/vitavireval.php/vitavi.php?submit=true&rid=';
		
		function __construct($resultFlag) {
			if($resultFlag==0)
				$this->url=$this->FLAG_REGULAR_RESULT;
			else
				$this->url=$this->FLAG_REVAL_RESULT;
		}
		
		public function setProxy($currentProxy){
			$this->proxy=$currentProxy;
		}
		public function getError(){
			switch($this->errorValue){
				case 0:
					return "Result not available.";
				case 1:
					return "Error while parsing USN.";
				case 2:
					return "Error while parsing semesters.";
				case 3:
					return "Error while parsing tables.";
				case 4:
					return "Error while parsing raws.";
				case 5:
					return "Error while parsing columns.";
				case 6:
					return "Can't reach VTU server.";
				case 7:
					return "Sorry cURL is not installed!";
				default:
					return "Successful communication.";
			}
		}
    
		function calculateTotal(){
			$this->total=0;
			for($i=0;$i<sizeof($this->markInTable[0]);$i++)
				if(!preg_match("/\(.*?CIV[1-2]8.*?\)/", $this->markInTable[0][$i][0], $matches)&&!preg_match("/\(.*?CIP[1-2]8.*?\)/", $this->markInTable[0][$i][0], $matches))
					$this->total+=$this->markInTable[0][$i][3];
		}
	
		private function calculatePercentage(){
			if($this->semesters[0]<3)
				$this->percentage=($this->total*100)/825;
			else
				$this->percentage=($this->total*100)/900;
			$this->percentage=number_format((float)$this->percentage, 2, '.', '');
		}
	
		private function calculateResult(){
			$passFlag=1;
			for($i=0;$i<sizeof($this->markInTable[0]);$i++)
				if($this->markInTable[0][$i][4]!="P"){
					if(!preg_match("/\(.*?CIV[1-2]8.*?\)/", $this->markInTable[0][$i][0], $matches)&&!preg_match("/\(.*?CIP[1-2]8.*?\)/", $this->markInTable[0][$i][0], $matches)){ //Avoiding CIV and CIP of 1st or 2nd sem from result calculation.
						$passFlag=0;
						break;
					}
				}
			$tempPercentage=round($this->percentage);
			if($passFlag==1){
				if($tempPercentage>=70)
					$this->result="FIRST CLASS WITH DISTINCTION";
				else if($tempPercentage>=60)
					$this->result="FIRST CLASS";
				else
					$this->result="SECOND CLASS";
			}else
				$this->result="FAIL";
		}
	
		public function requestResult($currentUsn){
			ini_set('max_execution_time', 60);
			$this->givenUsn=$currentUsn;
			$this->errorValue=-1;
			unset($this->markInTable);
			unset($this->semesters);
			
			if (!function_exists('curl_init')){
				$this->errorValue=7;
				return;
			}
			
			$ch = curl_init($this->url.$currentUsn);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$html = curl_exec($ch);
			if(curl_errno($ch)==0){
				curl_close($ch);
				$html = preg_replace( '/\s+/', ' ', $html );
				
				//Finding name of student
				if (preg_match("/<TD width=\"513\"> <B>[. A-Z]*/", $html, $matches))
					$this->name=strip_tags($matches[0]);
				else{
					$this->errorValue=0;
					return;
				}
			
				//Finding USN of student
				if (preg_match("/[0-9][A-Za-z][A-Za-z][0-9][0-9][A-Za-z][A-Za-z][0-9][0-9][0-9]/", $html, $matches))
					$this->usn=$matches[0];
				else{
					$this->errorValue=1;
					return;
				}
				
				//Finding semesters from result	
				if(preg_match_all("/Semester:.*?[0-9]+?/", $html, $semMatches, PREG_SET_ORDER))
					foreach($semMatches as $sem) {
						preg_match("/[0-9]+/", $sem[0], $currentSem);
						$this->semesters[]=$currentSem[0];
					}
				else{
					$this->errorValue=2;
					return;
				}
			
				//Finding marks tables
				if(preg_match_all("/(<table><tr><td width=250>Subject<\/td>)+.*?<br><br>/", $html, $markTables, PREG_SET_ORDER)){
					//Iteratting each table
					for($i=0;$i<sizeof($markTables);$i++)
						//Finding each row from the current mark table
						if(preg_match_all("/<tr><td width=250><i>.*?<\/tr>/", $markTables[$i][0], $tableRaws, PREG_SET_ORDER)){
							//Iteratting each row
							for($j=0;$j<sizeof($tableRaws);$j++)
								//Finding value of current table cells
								if(preg_match_all("/<td.*?<\/td>/", $tableRaws[$j][0], $tableCells, PREG_SET_ORDER))
									//Iteratting each cell value
									foreach($tableCells as $cell)
										//storing each cell value
										$this->markInTable[$i][$j][]=strip_tags($cell[0]);
								else{
									$this->errorValue=5;
									return;
								}	
						}else{
							$this->errorValue=4;
							return;
						}
				}else{
					$this->errorValue=3;
					return;
				}
				$this->calculateTotal();
				$this->calculatePercentage();
				$this->calculateResult();
			}else{
				$this->errorValue=6;
				curl_close($ch);
				return;
			}
		}
	}
?>
