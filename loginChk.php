<?php

include "adm/Shares/Db.php";

if ($_POST['cmd'] == "login") {
	$id = $_POST['id'];
	$pwd = $_POST['pw'];
	
	$sql = "SELECT u.No, u.Name, u.Level, u.Department, u.Grade, u.Class, d.Name as DepartmentName, g.Name as GradeName ";
	$sql .= "FROM Users u ";
	$sql .= "INNER JOIN Department d ON d.No = u.Department ";
	$sql .= "LEFT JOIN Grade g ON g.No = u.Grade ";
	$sql .= "WHERE ID = ? ";
	$sql .= "AND Pwd = AES_ENCRYPT(?,?) ";
	$sql .= "AND Level = 10 ";
	//echo $sql;
	
	$no = 0;
	$uname = "";
	$level = 0;
	$grade = 0;
	$classno = 0;

	$stmt =  $conn->stmt_init();
	if ($stmt->prepare($sql)) {
		$stmt->bind_param("sss", $id, $pwd, $keyname);
	    $stmt->execute();
	    $stmt->bind_result($no, $uname, $level, $department, $grade, $classno, $departmentname, $gradename);
		$stmt->fetch();
		
		if($no > 0){
			$return['Result'] = true;
			session_start();
			$_SESSION['UserNo'] = $no;
			$_SESSION['UserId'] = $id;
			$_SESSION['UserName'] = $uname;
			$_SESSION['Level'] = $level;
			$_SESSION['Department'] = $department;
			$_SESSION['Grade'] = $grade;
			$_SESSION['Class'] = $classno;
			$_SESSION['DepartmentName'] = $departmentname;
			$_SESSION['GradeName'] = $gradename;
	    } else {
	    	$return['Result'] = false;
	    }
	    $stmt->close();
	}
	print json_encode($return);
}
$conn -> close();
?>
