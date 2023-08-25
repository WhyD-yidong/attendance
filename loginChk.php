<?php

include "../adm/Shares/Db.php";

header('Content-Type: application/json; charset=UTF-8');

if(!in_array('application/json',explode(';',$_SERVER['CONTENT_TYPE']))){
    echo json_encode(array('result_code' => '400'));
    exit;
}

$body_json = file_get_contents("php://input"); 
$body = json_decode( $body_json, TRUE ); 

// echo var_dump($body);
// echo "userid: ".$body['userid']."<br/>";
// echo "userpw: ".$body['userpw']."<br/>";

$no = 0;
$level = 0;
$department = 0;
$grade = 0;
$classno = 0;
$departmentname = '';
$gradename = '';

$output = array(
    "result_code" => 400,
    "userno" => $no, 
    "level" => $level, 
    "department" => $department, 
    "grade" => $grade, 
    "classno" => $classno, 
    "departmentname" => $departmentname, 
    "gradename" => $gradename 
);

// for demo
if (strtolower($body['userid']) == "demo" && $body['userpw'] == "1234") {
	$output['result_code'] = 200;
	$output['userno'] = 1000;
	$output['level'] = 0;
	$output['department'] = 1000;
	$output['grade'] = 0;
	$output['classno'] = 0;
	$output['departmentname'] = "Guest";
	$output['gradename'] = "";
} else if ($body['userid'] != "" && $body['userpw'] != "") {
	$id = strtolower($body['userid']);
	$pwd = $body['userpw'];
	
	$sql = "SELECT u.No, u.Level, u.Department, u.Grade, u.Class, d.Name as DepartmentName, g.Name as GradeName ";
	$sql .= "FROM Users u ";
	$sql .= "INNER JOIN Department d ON d.No = u.Department ";
	$sql .= "LEFT JOIN Grade g ON g.No = u.Grade ";
	$sql .= "WHERE ID = ? ";
	$sql .= "AND Pwd = AES_ENCRYPT(?,?) ";
	$sql .= "AND Level = 10 ";
	//echo $sql;
	
	$stmt =  $conn->stmt_init();
	if ($stmt->prepare($sql)) {
		$stmt->bind_param("sss", $id, $pwd, $keyname);
	    $stmt->execute();
	    $stmt->bind_result($no, $level, $department, $grade, $classno, $departmentname, $gradename);
		$stmt->fetch();
		
		if($no > 0){
            $output['result_code'] = 200;
			$output['userno'] = $no;
			$output['level'] = $level;
			$output['department'] = $department;
			$output['grade'] = $grade;
			$output['classno'] = $classno;
			$output['departmentname'] = $departmentname;
            $output['gradename'] = $gradename;
	    } else {
            $output['result_code'] = 200;
        }
	    $stmt->close();
	}
}
echo json_encode($output);
$conn -> close();
?>
