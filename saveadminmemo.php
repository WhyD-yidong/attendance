<?php
include "../adm/Shares/Db.php";

//header('Content-Type: application/json; charset=UTF-8');

if(!in_array('application/json',explode(';',$_SERVER['CONTENT_TYPE']))){
    echo json_encode(array('result_code' => '400'));
    exit;
}

$body_json = file_get_contents("php://input"); 
$body = json_decode( $body_json, TRUE ); 

// echo var_dump($body);
// echo "userno: ".$body['userno']."<br/>";
// echo "userid: ".$body['userid']."<br/>";
// echo "curdate: ".$body['curdate']."<br/>";
// echo "memotoadmin: ".$body['memotoadmin']."<br/>";

$userno = $body['userno'];
$userid = $body['userid'];
$curdate = $body['srchdate'];
$adm_memo = $body['memotoadmin'];

if ($userno > 0 && $adm_memo != "") {
    $sql = "SELECT count(No) as cnt ";
    $sql .= "FROM Memo ";
    $sql .= "WHERE Date = '".addslashes($curdate)."' AND UserNo = ".$userno." ";
    
    //echo "$sql <br/>";
    $cnt = 0;
    $result = $conn -> query($sql);
    if ($result -> num_rows > 0) {
        $data = $result -> fetch_assoc();
        if (!empty($data)) {
            $cnt = intval($data['cnt']);
        }
    }
    
    $memo_title = $userid."\'s memo"; 
    if ($cnt > 0){
        $sql = "UPDATE Memo ";
        $sql .= "SET Title = '".$memo_title."', Content = '".addslashes($adm_memo)."' ";
        $sql .= ", UpdateDate = now() ";
        $sql .= "WHERE Date = '".addslashes($curdate)."' AND UserNo = ".$userno." ";
    }else {
        $sql = "INSERT INTO Memo (Date, UserNo, Title, Content, Department) ";
        $sql .= "VALUES ('".addslashes($curdate)."', ".$userno.", '".$memo_title."', '".addslashes($adm_memo)."', ".$_SESSION['Department']." ) ";
    }
    
    // echo "$sql <br/>";		
    // if ($conn->query($sql) === TRUE) {
    //     //echo "New record created successfully<br/>";
    // } else {
    //     //echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    //     $error = "저장에러(#2)! 관리자에게 문의하세요!";
    // }			
    echo json_encode(array('result_code' => '200'));
}
else echo json_encode(array('result_code' => '400'));

$conn->close();
?>