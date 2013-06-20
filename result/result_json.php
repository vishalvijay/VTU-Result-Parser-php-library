<?php

	require_once __DIR__ . '/VTUResultParser.php';
	
	$result;
	
	function showResult(){
		global $result;
		if($result->errorValue==-1){
			$response["name"] = $result->name;
			$response["usn"] = $result->usn;
			$response["result"] = $result->result;
			$response["percentage"] = $result->percentage;
			$response["total"] = $result->total;
			$response["semesters"] =$result->semesters;
			$response["mark"]=$result->markInTable;
			$response["message"]="success";
		} else{
			$response["message"]=$result->getError();
		}
		echo json_encode($response);
	}
	
	if (isset($_POST["usn"])&&isset($_POST["resultType"])){
		global $result;
		$result=new VTUResultParser($_POST["resultType"]);
		$result->requestResult($_POST["usn"]);
		showResult();
	}else
		echo "<br/>Access denied.<br/>";
?>