<?php
	/*
		Title: VTU Result Parser Php Library
		Description: Php library for parsing VTU Results. This library can dynamically parse VTU result site with respect to semester result.
		Version : 3.1.0
		Author: Vishal Vijay (V4 Creations)
		Email: 0vishalvijay0@gmail.com
		Support from: Team Matrix, www.vtulife.com
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
		
		const BASE_URL='http://results.vtu.ac.in/';
		const FLAG_REGULAR_RESULT='http://results.vtu.ac.in/vitavi.php';
		const FLAG_REVAL_RESULT='http://results.vtu.ac.in/vitavireval.php';
		const TOKEN_FILE = './cashe/token_cashe';
		
		function __construct($resultFlag) {
			$this->resultFlag=$resultFlag;
			$this->errorValue=-1;
			$this->proxy="";
			if($resultFlag==0)
				$this->url=self::FLAG_REGULAR_RESULT;
			else
				$this->url=self::FLAG_REVAL_RESULT;
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

			$fields = array('rid'=> urlencode($currentUsn),'submit' => urlencode('SUBMIT'), '1f0a-B9BB_7e826562' => urlencode($this->getToken()));
			$ch = curl_init($this->url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			curl_setopt($ch, CURLOPT_TIMEOUT, 45);
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
				if($this->url==self::FLAG_REGULAR_RESULT){
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

		private function calculateTotal(){
			for($j=0;$j<count($this->semesters);$j++){
				$this->total[$j]=0;
				for($i=0;$i<count($this->markInTable[$j]);$i++)
					if(!preg_match("/\(.*?CIV[1-2]8.*?\)/", $this->markInTable[$j][$i][0], $matches)&&!preg_match("/\(.*?CIP[1-2]8.*?\)/", $this->markInTable[$j][$i][0], $matches)&&!preg_match("/MATDIP/", $this->markInTable[$j][$i][0], $matches))
						$this->total[$j]+=$this->markInTable[$j][$i][3];
			}
		}

		private function isMCA(){
      if(preg_match('/MCA/i',$this->usn))
        return true;
      return false;
    }

		private function isFinalYear($sem){
			for($i=0;$i<count($this->markInTable[$sem]);$i++)
					if(preg_match("/.*?Project.*?/", $this->markInTable[$sem][$i][0], $matches))
						return true;
			return false;
		}

		private function calculatePercentage(){
      for($j=0;$j<count($this->semesters);$j++){
        $maxTotal;
        if($this->isMCA())
          $maxTotal = 1050;
        else if($this->semesters[$j]<3)
          $maxTotal = 755;
        else if(($this->semesters[$j]==8||$this->semesters[$j]==10)&&$this->isFinalYear($j))
          $maxTotal = 750;
        else
          $maxTotal = 900;
        $this->percentage[$j]=number_format((float)(($this->total[$j]*100)/$maxTotal), 2, '.', '');
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

		private function getToken(){
			$token = "";
			$lastTokenInfo = file_get_contents(self::TOKEN_FILE);
			if(empty($lastTokenInfo))
				$token = $this->getAndCasheToken();
			else{
				$lastTokenInfo = explode("###", $lastTokenInfo);
				if($lastTokenInfo[0]+7200000 > round(microtime(true) * 1000))
					$token = $lastTokenInfo[1];
				else
					$token = $this->getAndCasheToken();
			}
			return $token;
		}

		private function getAndCasheToken(){
			$token = $this->getTokenFromVTU();
			file_put_contents(self::TOKEN_FILE, round(microtime(true) * 1000)."###".$token);
			return $token;
		}

		private function getTokenFromVTU(){
			$token = NULL;
			$ch = curl_init(self::BASE_URL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			curl_setopt($ch, CURLOPT_TIMEOUT, 45);
			$html = curl_exec($ch);
			if(curl_errno($ch)==0){
				//Finding token
				if (preg_match_all("/1f0a-B9BB_7e826562\" value=\".+?\">/", $html, $matches))
					$token = substr($matches[0][0], 27, -2);
			}
			curl_close($ch);
			return $token;
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
