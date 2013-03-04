	<!---Version 1.5.8--->
	<style type="text/css">
		.table{
			box-shadow: 5px 5px 5px #000000;
			background: #597280;
			padding: 15px;
			color: white;
			text-align :center;
		}
		.headingRow{
			background: #e7e7e7;
			color: #0e4c6f;
			font-size: 20px;
			padding:10px;
		}
		.headingCell{
			padding: 5px;
		}
		.resultRowFail{
			background: #d7d690;
			color: red;
			font-size: 16px;
			font-weight: bold;
		}
		.resultRowPass{
			background: #d7d690;
			color: green;
			font-size: 16px;
			font-weight: bold;
		}
		.resultCell{
			padding-left: 5px;
			padding-right: 5px;
		}
		.semesterRow{
			background: #2b2c2c;
			font-weight: bold;
		}
		.semesterCell{
		}
		.subheadingRow{
			background: #6d6f6f;
			font-size: 14px;
		}
		.subheadingCell{
			padding-left: 5px;
			padding-right: 5px;
		}
		.subheadingCellLeft{
			text-align :left;
			padding-left: 5px;
		}
		.markRow1{
			background: #0d4b6e;
			font-size: 15px;
		}
		.markRow2{
			background: #0a3b57;
			font-size: 15px;
		}	
		.subjectCell{
			text-align :left;
			padding: 5px;
		}		
		.totalSubjectCell{
			padding: 5px;
			font-weight: bold;
		}
		.resultPassCell{
			color: #04b304;
			font-weight: bold;
			padding: 5px;
		}
		.resultFailCell{
			color: red;
			font-weight: bold;
			padding: 5px;
		}
		.markCell{
			padding: 5px;
		}	
	</style>
<?php

	require_once __DIR__ . '/VTUResultParser.php';
	
	$result;
	
	function showResult(){
		global $result;
		if($result->errorValue==-1){
			echo "
			<table class=\"table\">
				<tr class=\"headingRow\"><th class=\"headingCell\"colspan=\"3\">" . $result->name . "</th><th class=\"headingCell\" colspan=\"2\">" . $result->usn . "</th></tr>";
			if($result->result=="FAIL")
				echo "<tr class=\"resultRowFail\">";
			else
				echo "<tr class=\"resultRowPass\">";
			
			echo "	<td class=\"resultCell\" colspan=\"3\">Result : " .$result->result. "</td>
					<td class=\"resultCell\" colspan=\"1\" >Percentage : " .$result->percentage. "%</td>
					<td class=\"resultCell\" colspan=\"1\" >Total : " .$result->total. "</td>
				</tr>
			";
			for($i=0;$i<sizeof($result->semesters);$i++){
				echo "<tr class=\"semesterRow\">
					<td class=\"semesterCell\" colspan=\"5\">Semester : ".$result->semesters[$i]."</td>
				</tr>";
				echo"<tr class=\"subheadingRow\">
					<td class=\"subheadingCellLeft\">Subject</td>
					<td class=\"subheadingCell\">External</td>
					<td class=\"subheadingCell\">Internal</td>
					<td class=\"subheadingCell\">Total</td>
					<td class=\"subheadingCell\">Result</td>
				</tr>";
				for($j=0;$j<sizeof($result->markInTable[$i]);$j++){
					if($j%2==0)
						echo "<tr class=\"markRow1\">";
					else
						echo "<tr class=\"markRow2\">";
					for($k=0;$k<sizeof($result->markInTable[$i][$j]);$k++){
						if($k==0)
							echo "<td class=\"subjectCell\">".$result->markInTable[$i][$j][$k]."</td>";
						else if($k==3)
							echo "<td class=\"totalSubjectCell\">".$result->markInTable[$i][$j][$k]."</td>";
						else if($k==4){
							if($result->markInTable[$i][$j][$k]=='P')
								echo "<td class=\"resultPassCell\">".$result->markInTable[$i][$j][$k]."</td>";
							else
								echo "<td class=\"resultFailCell\">".$result->markInTable[$i][$j][$k]."</td>";
						}else
							echo "<td class=\"markCell\">".$result->markInTable[$i][$j][$k]."</td>";
					}
					echo "</tr>";
				}
			}
			echo "</table><br/>";
		}else
			echo "<br/>".$result->getError()."(".$result->givenUsn.")"."<br/>";
	}
	
	if (isset($_GET["usn"])&&isset($_GET["resultType"])){
		global $result;
		$result=new VTUResultParser($_GET["resultType"]);
		//$result->setProxy("129.0.255.254:3128");
		$result->requestResult($_GET["usn"]);
		showResult();
	}else
		echo "<br/>Access denied.<br/>";
?>