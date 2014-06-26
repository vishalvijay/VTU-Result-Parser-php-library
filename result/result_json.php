<?php

	require_once __DIR__ . '/VTUResultParser.php';
	
	$result;

  if (!function_exists('http_response_code')){
    function http_response_code($newcode = NULL){
      static $code = 200;
      if($newcode !== NULL){
        header('X-PHP-Response-Code: '.$newcode, true, $newcode);
        if(!headers_sent())
          $code = $newcode;
      }
      return $code;
    }
  }
	
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
			http_response_code(200);
		} else{
			http_response_code(403);
			$response["error"]=$result->getError();
		}
		echo json_encode($response);
	}
	
	if (isset($_GET["usn"])&&isset($_GET["resultType"])){
		global $result;
		$result=new VTUResultParser($_GET["resultType"]);
		$result->requestResult($_GET["usn"]);
		showResult();
	}else{
		http_response_code(401);
		$response["error"]="access_denied";
		echo json_encode($response);
	}
?>
