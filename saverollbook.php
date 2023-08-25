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
// echo "department: ".$body['department']."<br/>";
// echo "grade: ".$body['grade']."<br/>";
// echo "classno: ".$body['classno']."<br/>";
// echo "srchdate: ".$body['srchdate']."<br/>";
// echo "students: ".$body['students']."<br/>";

$curdate = $body['srchdate'];
foreach ($body['students'] as $student) {
    // echo "StudentNo: ".$student['StudentNo']."<br/>";
    // echo "StudentName: ".$student['StudentName']."<br/>";
    // echo "Attend: ".$student['Attend']."<br/>";
    // echo "QT: ".$student['QT']."<br/>";
    // echo "Talent: ".$student['Talent']."<br/>";
    // echo "AddOptDept: ".$student['AddOptDept']."<br/>";
    // echo "RollbookMemo: ".$student['RollbookMemo']."<br/>";

    $mem = $student['StudentNo'];
    $mem_at = $student['Attend'];
    $mem_qt = $student['QT'];
    $mem_talent = $student['Talent'];
    $mem_at_opt_dept = $student['AddOptDept'];
    if($mem_at_opt_dept!='0') $mem_opt = 'y';
    else $mem_opt = 'n';
    $mem_memo = $student['RollbookMemo'];
    
    $sql = "SELECT count(No) as cnt ";
    $sql .= "FROM RollBook".date('Y')." ";
    $sql .= "WHERE Date = '".addslashes($curdate)."' AND StudentNo = ".addslashes($mem)."";
    
    // echo "$sql <br/>";
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
        $sql .= ", Talent = '".addslashes($mem_talent)."' ";
        $sql .= ", AddOpt = '".addslashes($mem_opt)."', AddOptDept = '".addslashes($mem_at_opt_dept)."' ";
        if ($mem_memo != "") {
            $sql .= ", Memo = '".addslashes($mem_memo)."' ";
        }
        $sql .= ", UpdateDate = now() ";
        $sql .= "WHERE Date = '".addslashes($curdate)."' AND StudentNo = ".addslashes($mem)." ";		
    }else {
        $sql = "INSERT INTO RollBook".date('Y')." (Date, StudentNo, Attend, QT, Talent, AddOpt, AddOptDept, Memo) ";
        $sql .= "VALUES ('".addslashes($curdate)."', ".addslashes($mem)." ";
        $sql .= ", '".addslashes($mem_at)."', '".addslashes($mem_qt)."', '".addslashes($mem_talent)."' ";
        $sql .= ", '".addslashes($mem_opt)."', '".addslashes($mem_at_opt_dept)."' ";
        if ($mem_memo != "") {
            $sql .= ", '".addslashes($mem_memo)."') ";
        } else {
            $sql .= ", NULL) ";
        }
    }
    
    // echo "$sql <br/>";					
    // if ($conn->query($sql) === TRUE) {
    //     //echo "New record created successfully<br/>";
    // } else {
    //     //echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    //     $error = "저장에러(#1)! 관리자에게 문의하세요!";
    // }	
    // echo "==================<br/>";	
}
echo json_encode(array('result_code' => '200'));

$conn->close();
?>