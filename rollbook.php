<?php

$department = $_REQUEST['Department'];
$grade = $_REQUEST['Grade'];
$class = $_REQUEST['Class'];

if (!isset($department) || $department == '' || !isset($grade) || $grade == ''
    || !isset($class) || $class == '') {
	$output = array(
        "result" => "fail"
    );
    echo json_encode( $output );
    return;
}

// for demo
if ($department == "1000") {
    $output = array(
        "result" => "success",
        "srchdate" => '2017-12-20', 
        "teacher" => 'Hans', 
        "students" => array(),
    );

    for ($i=0;$i<4;$i++){
        $output['students'][$i]['StudentNo'] = ($i+1);
        if($i == 0) $output['students'][$i]['StudentName'] = "James";
        if($i == 1) $output['students'][$i]['StudentName'] = "Mattew";
        if($i == 2) $output['students'][$i]['StudentName'] = "Sam";
        if($i == 3) $output['students'][$i]['StudentName'] = "David";

        $output['students'][$i]['Attend'] = 'y';
        $output['students'][$i]['Talent'] = '0';
        $output['students'][$i]['QT'] = 'n';
        $output['students'][$i]['AddOptDept'] = '0';
        $output['students'][$i]['RollbookMemo'] = '';
    
        $summary = "";
        if($output['students'][$i]['Attend']=='n') $summary .= "결석";
        else $summary .= "출석";
        $summary .= ",포인트 ".$output['students'][$i]['Talent']."개";
        if($output['students'][$i]['QT']=='y') $summary .= ",어묵O";
        else $summary .= ",어묵X";
        if($output['students'][$i]['AddOptDept']=='0') $summary .= ",추가X";
        else $summary .= ",추가O";
        if($output['students'][$i]['RollbookMemo']=='') $summary .= ",메모X";
        else $summary .= ",메모O";
        $output['students'][$i]['Summary'] = $summary;
        
        $background = "";
        if($i%4 == 0) $background = '#2980B9';
        if($i%4 == 1) $background = '#27AE60';
        if($i%4 == 2) $background = '#9B27AE';
        if($i%4 == 3) $background = '#e67e22';
        $output['students'][$i]['Background'] = $background;
    }
    echo json_encode( $output );
    return;
}

include "../adm/Shares/Db.php";

function getDateForSpecificDayBetweenDates($start, $end, $weekday = 0){
	$weekdays="Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday";
	
	$arr_weekdays=explode(",", $weekdays);
	$weekday = $arr_weekdays[$weekday];
	if(!$weekday)
	    die("Invalid Weekday!");
	
	$start= strtotime("+0 day", strtotime($start) );
	$end= strtotime($end);
	
	$dateArr = array();
	$specific_day = strtotime($weekday, $start);
	while($specific_day <= $end)
	{
	    $dateArr[] = date("Y-m-d", $specific_day);
	    $specific_day = strtotime("+1 weeks", $specific_day);
	}
	return $dateArr;
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$first_day = date("Y-m-d", strtotime('first day of January '.date('Y') ));
$end_day = date('Y-m-d', strtotime(date('Y-m-d')." -".date('w')."days"));
$sundays = getDateForSpecificDayBetweenDates($first_day, $end_day, 0);
$sundays = array_reverse($sundays);

$sundays_count = count($sundays);		
$curdate = "";
if (!isset($_REQUEST['SrchDate']) || $_REQUEST['SrchDate'] == ''){
	if($sundays_count>0) {
		$curdate = $sundays[0];
	} 	
} else {
	$curdate = $_REQUEST['SrchDate'];
	if(!validateDate($curdate) && $sundays_count>0){
		$curdate = $sundays[0];
	}
}

$sql = "SELECT Name FROM Teachers WHERE Department = ".$department." "
	."AND Grade = ".$grade." AND Class = ".$class." ";
$result = $conn -> query($sql);
if ($row = $result -> fetch_assoc()) {
	$teachers_name = $row['Name'];
} else {
	$teachers_name = "";
}

$sql = "SELECT s.No as StudentNo, s.Name as StudentName ";
$sql .= ", IFNULL(r.Attend, '') as Attend ";
$sql .= ", IFNULL(r.QT, 0) as QT ";
$sql .= ", IFNULL(r.Talent, '') as Talent ";
$sql .= ", IFNULL(r.AddOpt, 0) as AddOpt ";
$sql .= ", IFNULL(r.AddOptDept, 0) as AddOptDept ";
$sql .= ", IFNULL(r.Memo, '') as RollbookMemo ";
$sql .= "FROM Students s ";
$sql .= "LEFT JOIN RollBook".date('Y')." r ";
$sql .= "ON r.StudentNo = s.No and r.Date = '".$curdate."' ";
$sql .= "WHERE s.Department = ".$department." AND s.Grade = ".$grade." ";
$sql .= "AND s.Class = ".$class." ";
$sql .= "AND r.Attend is not null ";
$result = $conn -> query($sql);

$output = array(
    "result" => "success",
	"srchdate" => $curdate, 
	"teacher" => $teachers_name, 
    "students" => array(),
);

$i = 0;
while ($row = $result -> fetch_assoc()) {
    $output['students'][$i]['StudentNo'] = $row['StudentNo'];
    $output['students'][$i]['StudentName'] = $row['StudentName'];
    $output['students'][$i]['Attend'] = $row['Attend'];
    $output['students'][$i]['Talent'] = $row['Talent'];
    $output['students'][$i]['QT'] = $row['QT'];
    $output['students'][$i]['AddOptDept'] = $row['AddOptDept'];
    $output['students'][$i]['RollbookMemo'] = $row['RollbookMemo'];

    $summary = "";
    if($row['Attend']=='n') $summary .= "결석";
    else $summary .= "출석";
    $summary .= ",포인트 ".$row['Talent']."개";
    if($row['QT']=='y') $summary .= ",어묵O";
    else $summary .= ",어묵X";
    if($row['AddOptDept']=='0') $summary .= ",추가X";
    else $summary .= ",추가O";
    if($row['RollbookMemo']=='') $summary .= ",메모X";
    else $summary .= ",메모O";
    $output['students'][$i]['Summary'] = $summary;
    
    $background = "";
    if($i%4 == 0) $background = '#2980B9';
	if($i%4 == 1) $background = '#27AE60';
	if($i%4 == 2) $background = '#9B27AE';
    if($i%4 == 3) $background = '#e67e22';
    $output['students'][$i]['Background'] = $background;

    $i++;
}

echo json_encode( $output );
$conn -> close();
?>



