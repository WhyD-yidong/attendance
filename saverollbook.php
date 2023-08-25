<?php

session_start();
if (!isset($_SESSION['UserId']) || $_SESSION['UserId'] == '' || intval($_SESSION['Level']) != 10) {
	header('Location: login.php');
	exit();
}

include "adm/Shares/Db.php";

$activity = $_REQUEST["activity"];
$curdate = $_REQUEST["curdate"];
$st_idx = $_REQUEST["st_idx"];
$teachers_name = $_REQUEST["teachername"];

//echo "$activity $curdate $st_idx <br/>";

if(($activity == "add" || $activity == "modify") && $st_idx > 0) {
	$i = 1;
	$sql = "";
	$error = "";
	while ($i<=$st_idx) {
		$mem = $_REQUEST['mem_'.$i];
		if (intval($mem)<1) continue;
		
		$mem_at = $_REQUEST['mem_'.$i.'_at'];
		$mem_qt = $_REQUEST['mem_'.$i.'_qt'];
		$mem_qtpoint = $_REQUEST['mem_'.$i.'_qtpoint'];
		$mem_talent = $_REQUEST['mem_'.$i.'_talent'];
		
		$mem_opt = 0;
		$mem_at_opt = 0;
		$mem_at_opt_dept = 0;
		$mem_nat_opt = 0;
		if($_REQUEST['mem_'.$i.'_add'] == "y") {
			if($mem_at == "y"){
				if (isset($_REQUEST['mem_'.$i.'_at_opt'])) 
					$mem_at_opt = intval($_REQUEST['mem_'.$i.'_at_opt']);
				if ($mem_at_opt == 1 && isset($_REQUEST['mem_'.$i.'_at_opt_dept']))
					$mem_at_opt_dept = intval($_REQUEST['mem_'.$i.'_at_opt_dept']);
				$mem_opt = $mem_at_opt;
			} else if($mem_at == "n"){
				if (isset($_REQUEST['mem_'.$i.'_nat_opt']))
					$mem_nat_opt = intval($_REQUEST['mem_'.$i.'_nat_opt']);
				$mem_opt = $mem_nat_opt;
			}
		}
	
		$mem_memo = "";
		if (isset($_REQUEST['mem_'.$i.'_memo']))
			$mem_memo = $_REQUEST['mem_'.$i.'_memo'];
		
		//echo "$i $mem $mem_at, $mem_talent, $mem_opt, $mem_at_opt_dept, $mem_memo <br/>";
		
		$sql = "SELECT count(No) as cnt ";
		$sql .= "FROM RollBook".date('Y')." ";
		$sql .= "WHERE Date = '".addslashes($curdate)."' AND StudentNo = ".addslashes($mem)."";
		
		//echo "$sql <br/>";
		$cnt = 0;
		$result = $conn -> query($sql);
		if ($result -> num_rows > 0) {
			$data = $result -> fetch_assoc();
			if (!empty($data)) {
				$cnt = intval($data['cnt']);
			}
		}
		
		if ($cnt > 0){
			$sql = "UPDATE RollBook".date('Y')." ";
			$sql .= "SET Attend = '".addslashes($mem_at)."', QT = '".addslashes($mem_qt)."' ";
			$sql .= ", QTPoint = '".addslashes($mem_qtpoint)."' ";
			$sql .= ", Talent = '".addslashes($mem_talent)."' ";
			$sql .= ", AddOpt = '".addslashes($mem_opt)."', AddOptDept = '".addslashes($mem_at_opt_dept)."' ";
			if ($mem_memo != "") {
				$sql .= ", Memo = '".addslashes($mem_memo)."' ";
			}
			$sql .= ", UpdateDate = now() ";
			$sql .= "WHERE Date = '".addslashes($curdate)."' AND StudentNo = ".addslashes($mem)." ";		
		}else {
			$sql = "INSERT INTO RollBook".date('Y')." (Date, StudentNo, Attend, QT, QTPoint, Talent, AddOpt, AddOptDept, Memo) ";
			$sql .= "VALUES ('".addslashes($curdate)."', ".addslashes($mem)." ";
			$sql .= ", '".addslashes($mem_at)."', '".addslashes($mem_qt)."', '".addslashes($mem_qtpoint)."', '".addslashes($mem_talent)."' ";
			$sql .= ", '".addslashes($mem_opt)."', '".addslashes($mem_at_opt_dept)."' ";
			if ($mem_memo != "") {
				$sql .= ", '".addslashes($mem_memo)."') ";
			} else {
				$sql .= ", NULL) ";
			}
		}
		
		//echo "$sql <br/>";					
		if ($conn->query($sql) === TRUE) {
		    //echo "New record created successfully<br/>";
		} else {
		    //echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
		    $error = "저장에러(#1)! 관리자에게 문의하세요!";
		}			
		
		$i = $i + 1;
	}	

	$adm_memo = $_REQUEST['adm_memo'];
	echo "$adm_memo <br/>";
	if ($adm_memo != "") {
		$sql = "SELECT count(No) as cnt ";
		$sql .= "FROM Memo ";
		$sql .= "WHERE Date = '".addslashes($curdate)."' AND UserNo = ".$_SESSION['UserNo']." ";
		
		//echo "$sql <br/>";
		$cnt = 0;
		$result = $conn -> query($sql);
		if ($result -> num_rows > 0) {
			$data = $result -> fetch_assoc();
			if (!empty($data)) {
				$cnt = intval($data['cnt']);
			}
		}
		
		$memo_title = $_SESSION['UserName']."(".$teachers_name.")샘의 메모"; 
		if ($cnt > 0){
			$sql = "UPDATE Memo ";
			$sql .= "SET Title = '".$memo_title."', Content = '".addslashes($adm_memo)."' ";
			$sql .= ", UpdateDate = now() ";
			$sql .= "WHERE Date = '".addslashes($curdate)."' AND UserNo = ".$_SESSION['UserNo']." ";
		}else {
			$sql = "INSERT INTO Memo (Date, UserNo, Title, Content, Department) ";
			$sql .= "VALUES ('".addslashes($curdate)."', ".$_SESSION['UserNo'].", '".$memo_title."', '".addslashes($adm_memo)."', ".$_SESSION['Department']." ) ";
		}
		
		//echo "$sql <br/>";		
		if ($conn->query($sql) === TRUE) {
		    //echo "New record created successfully<br/>";
		} else {
		    //echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
		    $error = "저장에러(#2)! 관리자에게 문의하세요!";
		}			
	}
	
	if($error!="") {
		echo "<script>alert('저장에러(#3)! 관리자에게 문의하세요!')</script>";
	} else {
		echo "<script>alert('저장 하였습니다.');</script>";
	}
} else {
	echo "<script>alert('저장에러(#3)! 관리자에게 문의하세요!')</script>";
}

$conn->close();
?>
