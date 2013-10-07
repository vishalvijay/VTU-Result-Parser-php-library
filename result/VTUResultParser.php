<?php
	/*
		Title: VTU Result Parser Php Library 3.0.4
		Description:
			Php library for parsing VTU Results.
			This library can dynamically parse VTU result site with respect to semester result.
		Version :
			3.0.4 (7/10/2013)
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
	 */
	class VTUResultParser{
		public $name;
		public $usn;
		public $semesters=array();
		public $markInTable=array(array(),array(),array()); 
		public $total=array();
		public $totalInPage;
		public $percentage=array();
		public $result=array();
		public $errorValue;
		public $resultFlag;
		private $proxy;
		private $url;
		
		private $FLAG_REGULAR_RESULT='http://results.vtu.ac.in/vitavi.php?submit=true&rid=';
		private $FLAG_REVAL_RESULT='http://results.vtu.ac.in/vitavireval.php?submit=true&rid=';
		
		function __construct($resultFlag) {
			$this->resultFlag=$resultFlag;
			$this->errorValue=-1;
			$this->proxy="";
			if($resultFlag==0)
				$this->url=$this->FLAG_REGULAR_RESULT;
			else
				$this->url=$this->FLAG_REVAL_RESULT;
		}
		public function setProxy($currentProxy){
			$this->proxy=$currentProxy;
		}
		
		public function requestResult($currentUsn){
			$currentUsn=trim($currentUsn);
			$this->usn=$currentUsn;
			unset($this->markInTable);
			unset($this->semesters);
			
			if (!function_exists('curl_init')){
				$this->errorValue=6;
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
					$this->name=trim(strip_tags($matches[0]));
				else{
					$this->errorValue=0;
					return;
				}
				//Finding semesters from result	
				if(preg_match_all("/Semester:.*?[0-9]+.?<\/b>/", $html, $semMatches, PREG_SET_ORDER))
					foreach($semMatches as $sem) {
						preg_match("/[0-9]+/", $sem[0], $currentSem);
						$this->semesters[]=trim($currentSem[0]);
					}
				else{
					$this->errorValue=1;
					return;
				}
				//Finding current sem result in page
				if(preg_match_all("/Result:&nbsp;&nbsp;[ A-Z]*?<\/b>/", $html, $resultInPagematches, PREG_SET_ORDER)){
					for($j=0;$j<count($this->semesters);$j++){
						$this->result[$j]=trim(substr(strip_tags($resultInPagematches[$j][0]),19,-1));
					}
				}else{
					$this->errorValue=7;
					return;
				}
				if($this->url==$this->FLAG_REGULAR_RESULT){
					//Finding current sem total
					if (preg_match("/Total Marks:.*?[0-9]+.*?<\/td>/", $html, $matches))
						$this->totalInPage=trim(substr(strip_tags($matches[0]),13,-20));
					else{
						$this->errorValue=8;
						return;
					}
					//Finding marks tables for regular result
					if(!preg_match_all("/(<table><tr><td width=250>Subject<\/td>)+.*?<br><br>/", $html, $markTables, PREG_SET_ORDER)){
						$this->errorValue=2;
						return;
					}
				}else{
					//Finding marks tables for revaluation result table type 1
					if(preg_match_all("/Semester:<\/b><\/td>.*?<\/table><br><br>/", $html, $markTables1, PREG_SET_ORDER))
						for($i=0;$i<count($markTables1);$i++)
							$markTables[$i][0]=trim($markTables1[$i][0]);
					//Finding marks tables for revaluation result table type 2
					if(preg_match_all("/Semester:<\/b><\/td><td><b>".$this->semesters[count($this->semesters)-1].".*?<\/TD><\/TR>/", $html, $markTables2, PREG_SET_ORDER))
						for($i=0;$i<count($markTables2);$i++)
							$markTables[count($markTables1)+$i][0]=trim($markTables2[$i][0]);
					else{
						$this->errorValue=9;
						return;
					}
				}
				//Iterating each table
				for($i=0;$i<count($markTables);$i++)
					//Finding each row from the current mark table
					if(preg_match_all("/<tr><td width=250>.*?<\/tr>/", $markTables[$i][0], $tableRaws, PREG_SET_ORDER)){
						//Iterating each row
						for($j=1;$j<count($tableRaws);$j++)
							//Finding value of current table cells
							if(preg_match_all("/<td.*?<\/td>/", $tableRaws[$j][0], $tableCells, PREG_SET_ORDER)){
								//Iterating each cell value
								foreach($tableCells as $cell)
									//storing each cell value
									$this->markInTable[$i][$j-1][]=trim(strip_tags($cell[0]));
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
				$this->errorValue=5;
				curl_close($ch);
				return;
			}
		}
		function calculateTotal(){
			for($j=0;$j<count($this->semesters);$j++){
				$this->total[$j]=0;
				for($i=0;$i<count($this->markInTable[$j]);$i++)
					if(!preg_match("/\(.*?CIV[1-2]8.*?\)/", $this->markInTable[$j][$i][0], $matches)&&!preg_match("/\(.*?CIP[1-2]8.*?\)/", $this->markInTable[$j][$i][0], $matches)&&!preg_match("/MATDIP/", $this->markInTable[$j][$i][0], $matches))
						$this->total[$j]+=$this->markInTable[$j][$i][3];
			}
		}
		private function isFinalYear($sem){
			for($i=0;$i<count($this->markInTable[$sem]);$i++)
					if(preg_match("/.*?Project.*?/", $this->markInTable[$sem][$i][0], $matches))
						return true;
			return false;
		}
		private function calculatePercentage(){
			for($j=0;$j<count($this->semesters);$j++){
				if($this->semesters[$j]<3)
					$this->percentage[$j]=($this->total[$j]*100)/825;
				else if(($this->semesters[$j]==8||$this->semesters[$j]==10)&&$this->isFinalYear($j))
					$this->percentage[$j]=($this->total[$j]*100)/750;
				else
					$this->percentage[$j]=($this->total[$j]*100)/900;
				$this->percentage[$j]=number_format((float)$this->percentage[$j], 2, '.', '');
			}
		}
		private function calculateResult(){
			for($j=0;$j<count($this->semesters);$j++){
				if(trim($this->result[$j])!="")
					continue;
				$passFlag=1;
				for($i=0;$i<count($this->markInTable[$j]);$i++)
					if($this->markInTable[$j][$i][4]!="P"){
						if(!preg_match("/\(.*?CIV[1-2]8.*?\)/", $this->markInTable[$j][$i][0], $matches)&&!preg_match("/\(.*?CIP[1-2]8.*?\)/", $this->markInTable[$j][$i][0], $matches)&&!preg_match("/MATDIP/", $this->markInTable[$j][$i][0], $matches)){ //Avoiding CIV and CIP of 1st or 2nd sem from result calculation.
							$passFlag=0;
							break;
						}
					}
				$tempPercentage=round($this->percentage[$j]);
				if($passFlag==1){
					if($tempPercentage>=70)
						$this->result[$j]="FIRST CLASS WITH DISTINCTION";
					else if($tempPercentage>=60)
						$this->result[$j]="FIRST CLASS";
					else
						$this->result[$j]="SECOND CLASS";
				}else
					$this->result[$j]="FAIL";
			}
		}
		
		public function getError(){
			switch($this->errorValue){
				case 0:
					return "Result not available.";
				case 1:
					return "Error while parsing semesters.";
				case 2:
					return "Error while parsing regular result tables.";
				case 3:
					return "Error while parsing raws.";
				case 4:
					return "Error while parsing columns.";
				case 5:
					return "Can't reach VTU server.";
				case 6:
					return "Sorry cURL is not installed!";
				case 7:
					return "Error while parsing current sem result in page.";
				case 8:
					return "Error while parsing current sem total.";
				case 9:
					return "Error while parsing revaluation result tables.";
				default:
					return "Successful communication.";
			}
		}
	}
?>
