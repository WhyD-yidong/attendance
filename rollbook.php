<?php
session_start();

if (!isset($_SESSION['UserId']) || $_SESSION['UserId'] == '' || intval($_SESSION['Level']) != 10) {	
	header('Location: login.php');
	exit();
}

include "adm/Shares/Db.php";

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
	//$dateArr[] = date("Y-m-d", $specific_day);
	
	return $dateArr;
}

function validateDate($date) {
	$d = DateTime::createFromFormat('Y-m-d', $date);
	return $d && $d->format('Y-m-d') === $date;
}

$first_day = date("Y-m-d", strtotime('first day of January '.(date('Y') - 1) ));
$end_day = date('Y-m-d', strtotime(date('Y-m-d')." -".date('w')."days"));
$sundays = getDateForSpecificDayBetweenDates($first_day, $end_day, 0);
$sundays = array_reverse($sundays);

$sundays_count = count($sundays);
if($sundays_count==0) {
	$sundays[0] = date("Y-m-d", strtotime('first sunday of '.(date('Y') - 1) ));
	$sundays_count = 1;
}
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

$halfdate = "2021-07-01";

$sql = "SELECT No, Name FROM Department WHERE No <> ".$_SESSION['Department']." AND DGroup > 0 Order By Name ";
$result = $conn -> query($sql);
$depts = array();
while ($row = $result -> fetch_assoc()) {
	$depts[$row['No']] = $row['Name'];
}

$sql = "SELECT t.Name as Name FROM Teachers t "
	."INNER JOIN TeachersTag tg ON tg.TeacherNo = t.No and tg.UseYear = '".date('Y')."' "
	."WHERE tg.Department = ".$_SESSION['Department']." "
	."AND tg.Grade = ".$_SESSION['Grade']." AND tg.Class = ".$_SESSION['Class']." ";
$result = $conn -> query($sql);
if ($row = $result -> fetch_assoc()) {
	$teachers_name = $row['Name'];
} else {
	$teachers_name = "";
}
?>

<!DOCTYPE html>
<html lang="kr">
<head>
  <meta charset="utf-8">
  <title>주일학교 출석부</title>
  <meta name="description" content="">
  <meta name="author" content="mac">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	
  <!-- jquery mobile -->	
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
<style>
/*body {
font-size: 15px; 
}*/
</style>
</head>

<body>
<div data-role="page">

	<div data-role="header">
		<h1><?=$_SESSION['DepartmentName']?>부 <?=$_SESSION['GradeName']?> <?=$_SESSION['Class']?>반 보고서<br/>(<?=$teachers_name?> 선생님)</h1>
	</div>

	<div data-role="main">
		
		<div style="padding: 0px 10px 5px 10px;">
		<select name="SrchDate" id="SrchDate">
			<?php 
			foreach ($sundays as $sunday) {
			?>
			<option value="<?=$sunday?>" <?php if($sunday==$curdate){ echo "selected"; } ?>><?=$sunday?></option>
			<?php 
			}
			?>
		</select>
		</div>
		<form id="rollbook_frm" method="post" data-ajax="false">
		<ul style="padding: 0px 0px 5px 0px; list-style-type:none;">
		<li style="border-bottom: 1px solid black; background-color: white;">
			<div class="ui-grid-c">
				<div class="ui-block-a" style="height:20px;" align="center">
				이름
				</div>
				<div class="ui-block-b" style="height:20px;" align="center">
				출결여부
				</div>
				<div class="ui-block-c" style="height:20px;" align="center">
				누적점수
				</div>
				<div class="ui-block-d" style="height:20px;" align="center">
				기도제목
				</div>
				<!--
				<div class="ui-block-e" style="height:20px;" align="center">
				주간/누적
				</div>
				<!--
				<div class="ui-block-f" style="height:20px;" align="center">
				추가사항
				</div>
				<!--
				<div class="ui-block-f" style="height:20px;" align="center">
				어묵수행
				</div>
				-->
			</div>
		</li>
		<?php
		// $sql = "SELECT s.No as StudentNo, s.Name as StudentName ";
		// $sql .= ", IFNULL(r.Attend, '') as Attend ";
		// $sql .= ", IFNULL(r.QT, 0) as QT ";
		// $sql .= ", IFNULL(r.QTPoint, '') as QTPoint ";
		// $sql .= ", IFNULL(r.Talent, '') as Talent ";
		// $sql .= ", IFNULL(r.AddOpt, 0) as AddOpt ";
		// $sql .= ", IFNULL(r.AddOptDept, 0) as AddOptDept ";
		// $sql .= ", IFNULL(r.Memo, '') as RollbookMemo ";
		// $sql .= "FROM Students s ";
		// $sql .= "INNER JOIN StudentsTag t ";
		// $sql .= "ON t.StudentNo = s.No and t.UseYear = '".date('Y')."' ";
		// $sql .= "LEFT JOIN RollBook".date('Y')." r ";
		// $sql .= "ON r.StudentNo = s.No and r.Date = '".$curdate."' ";
		// $sql .= "WHERE s.Status = 1 AND t.Department = ".$_SESSION['Department']." ";
		// $sql .= "AND t.Grade = ".$_SESSION['Grade']." ";
		// $sql .= "AND t.Class = ".$_SESSION['Class']." ";
		// $sql .= "ORDER BY s.NAME ";

		$sql  = "SELECT s.No as StudentNo ";
		$sql .= "       , s.Name as StudentName ";
		$sql .= "       , IFNULL(r.Attend, '') as Attend ";
		$sql .= "       , IFNULL(r.QT, 0) as QT ";
		$sql .= "       , IFNULL(r.QTPoint, '') as QTPoint ";
		$sql .= "       , IFNULL(r.Talent, '') as Talent ";
		$sql .= "       , IFNULL(r.AddOpt, 0) as AddOpt ";
		$sql .= "       , IFNULL(r.AddOptDept, 0) as AddOptDept ";
		$sql .= "       , IFNULL(r.Memo, '') as RollbookMemo ";
		$sql .= "       , IFNULL(a.TotalPoint, '') as TotalPoint ";
		$sql .= "  FROM Students s ";
		$sql .= " INNER JOIN StudentsTag t ";
		$sql .= "         ON t.StudentNo = s.No and t.UseYear = '".date('Y')."' ";
		$sql .= "  LEFT JOIN RollBook".date('Y')." r ";
		$sql .= "         ON r.StudentNo = s.No and r.Date = '".$curdate."' ";
		$sql .= "  LEFT JOIN (SELECT StudentNo, ";
		$sql .= "                    SUM((CASE WHEN Attend = 'y' THEN 2 ELSE 0 END) + (CASE WHEN QTPoint > 3 THEN 2 WHEN QTPoint > 0 THEN 1 ELSE 0 END) + (CASE WHEN Talent > 0 THEN Talent ELSE 0 END) ) as TotalPoint ";
		$sql .= "               FROM RollBook".date('Y')." ";
		$sql .= "              WHERE `Date` BETWEEN '".$halfdate."' AND '".$curdate."' ";
		$sql .= "              GROUP BY StudentNo) a ";
		$sql .= "         ON a.StudentNo = s.No ";
		$sql .= " WHERE s.Status = 1 ";
		$sql .= "   AND t.Department = ".$_SESSION['Department']." ";
		$sql .= "   AND t.Grade = ".$_SESSION['Grade']." ";
		$sql .= "   AND t.Class = ".$_SESSION['Class']." ";
		$sql .= " ORDER BY s.NAME ";

		// echo "$sql";
		
		$st_idx = 0;
		$result = $conn -> query($sql);
		if ($result -> num_rows > 0) {
			while ($data = $result -> fetch_assoc()){
				$st_idx += 1;
				$studentno = $data['StudentNo'];
				$attend = $data['Attend'];
				$QT = $data['QT'];
				$qtpoint = $data['QTPoint'];
				$talent = $data['Talent'];
				$addopt = intval($data['AddOpt']);
				$addoptdept = intval($data['AddOptDept']);
				$rollbookmemo = $data['RollbookMemo'];
				$totalpoint = $data['TotalPoint'];
				//echo "$studentno $attend $qtpoint $talent $addopt $addoptdept $rollbookmemo"
		?>
		<input type="hidden" id="mem_<?=$st_idx?>" name="mem_<?=$st_idx?>" value="<?=$studentno?>"></input>
		<input type="hidden" id="mem_<?=$st_idx?>_at_org" value="<?=$attend?>"></input>
		<li style="border-bottom: 1px solid black; background-color: white;">
			<div class="ui-grid-c">
				<div class="ui-block-a" style="height:42px;" align="center">
					<div style="font-size: 1.2em;padding-top: 6px;">
					<?=$data['StudentName']?>
					</div>
				</div>
				<div class="ui-block-b" style="height:42px;" align="center">
					<div class="containing-element">
					<!--<label for="mem_<?=$st_idx?>_at" class="ui-hidden-accessible">출결여부:</label>-->
					<select name="mem_<?=$st_idx?>_at" id="mem_<?=$st_idx?>_at" data-role="slider" onchange="showAtOpt('mem_<?=$st_idx?>')" data-mini="true">
						<option value="n" <?php if($attend!="y"){ echo "selected"; } ?>><span style="font-size:10px;">결석</span></option>
						<option value="y" <?php if($attend=="y"){ echo "selected"; } ?>><span style="font-size:10px;">출석</span></option>
					</select>
					</div>
				</div>
				<div class="ui-block-c" style="height:42px;" align="center">
					<div style="font-size: 1.2em;padding-top: 6px;">
					<?php
						echo ($attend=="y" ? 2 : 0)."/".$totalpoint; 
						// echo $talent
					?>
					</div>
				</div>
			</div>
		</li>
		<div id="mem_<?=$st_idx?>_additem" style="display:<?php if($addopt>0){ echo ""; } else { echo "none"; }?>;">
			<div id="mem_<?=$st_idx?>_at_opt" style="display:<?php if($attend=="y"){ echo ""; } else { echo "none"; }?>;padding: 0px 10px 0px 10px;">
				<div class="ui-radio">
					<label for="mem_<?=$st_idx?>_at_opt" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-radio-off">타부서출석</label>
					<input type="radio" name="mem_<?=$st_idx?>_at_opt" id="mem_<?=$st_idx?>_at_opt1" data-enhanced="true" onclick="showOpt1Dept('mem_<?=$st_idx?>')" value="1" <?php if($addopt==1){ echo "checked"; } ?>/>
				</div>
				<fieldset id="mem_<?=$st_idx?>_at_opt_dept_div" data-role="controlgroup" data-type="horizontal" data-mini="true" style="display:<?php if($addopt==1 && $addoptdept>0){ echo ""; } else { echo "none"; }?>;">
				<?php 
				foreach ($depts as $deptno => $deptname) {
				?>
				    <input type="radio" name="mem_<?=$st_idx?>_at_opt_dept" id="mem_<?=$st_idx?>_at_opt_dept_<?=$deptno?>" value="<?=$deptno?>" <?php if($addopt==1 && $addoptdept==$deptno){ echo "checked"; }?>>
				    <label for="mem_<?=$st_idx?>_at_opt_dept_<?=$deptno?>"><?=$deptname?></label>
				<?php 
				}
				?>
				</fieldset>					
				<div class="ui-radio">
					<label for="mem_<?=$st_idx?>_at_opt2" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-radio-off">타교회출석(주보제출)</label>
					<input type="radio" name="mem_<?=$st_idx?>_at_opt" id="mem_<?=$st_idx?>_at_opt2" data-enhanced="true" onclick="showOpt1Dept('mem_<?=$st_idx?>')" value="2" <?php if($addopt==2){ echo "checked"; } ?>>
				</div>
			</div>
			<div id="mem_<?=$st_idx?>_nat_opt" style="display:<?php if($attend=="n"){ echo ""; } else { echo "none"; }?>;padding: 0px 10px 0px 10px;">
				<div class="ui-radio">
					<label for="mem_<?=$st_idx?>_nat_opt1" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-radio-off">장기결석</label>
					<input type="radio" name="mem_<?=$st_idx?>_nat_opt" id="mem_<?=$st_idx?>_nat_opt1" data-enhanced="true" value="1" <?php if($addopt==1){ echo "checked"; } ?>>
				</div>	
				<div class="ui-radio">
					<label for="mem_<?=$st_idx?>_nat_opt2" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-radio-off">병결</label>
					<input type="radio" name="mem_<?=$st_idx?>_nat_opt" id="mem_<?=$st_idx?>_nat_opt2" data-enhanced="true" value="2" <?php if($addopt==2){ echo "checked"; } ?>>
				</div>	
				<div class="ui-radio">
					<label for="mem_<?=$st_idx?>_nat_opt3" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-radio-off">여행</label>
					<input type="radio" name="mem_<?=$st_idx?>_nat_opt" id="mem_<?=$st_idx?>_nat_opt3" data-enhanced="true" value="3" <?php if($addopt==3){ echo "checked"; } ?>>
				</div>
			</div>
			<div style="padding: 0px 10px 0px 10px;">
				<label for="mem_<?=$st_idx?>_memo">메모 사항 (1000자 내외):</label>
				<textarea name="mem_<?=$st_idx?>_memo" id="mem_<?=$st_idx?>_memo" data-enhanced="true" class="ui-input-text ui-shadow-inset ui-body-inherit ui-corner-all"><?=$rollbookmemo?></textarea>
			</div>
		</div>
		<?php
			}
		}
		?>
		</ul>
		<div style="padding: 0px 10px 0px 10px;">
			<?php
			$sql = "SELECT content ";
			$sql .= "FROM Memo ";
			$sql .= "WHERE Date = '".$curdate."' AND UserNo = ".$_SESSION['UserNo']." ";
			
			//echo "$sql <br/>";
			$adm_memo = "";
			$result = $conn -> query($sql);
			if ($result -> num_rows > 0) {
				$data = $result -> fetch_assoc();
				if (!empty($data)) {
					$adm_memo = $data['content'];
				}
			}
			?>
			<label for="adm_memo">행정팀에게 요청 및 메모사항 (1000자 내외):</label>
			<textarea name="adm_memo" id="adm_memo" data-enhanced="true" class="ui-input-text ui-shadow-inset ui-body-inherit ui-corner-all"><?=$adm_memo?></textarea>
		</div>
	   	<input type="hidden" name="activity" value="add"></input>
		<input type="hidden" name="curdate" value="<?=$curdate?>"></input>
		<input type="hidden" name="st_idx" value="<?=$st_idx?>"></input>
		<input type="hidden" name="teachername" value="<?=$teachers_name?>"></input>
		</form>
		<input type="button" id="btn_save" value="저장"></input>
		<input type="button" id="btn_logout" value="로그아웃"></input>
	</div><!-- /content -->
	
</div><!-- /page -->	

<script>
var department = "<?=$_SESSION['Department']?>";

$(document).ready(function() {
	$("#btn_logout").click(function(){
		window.location.href = "login.php";
	});
	$("#SrchDate").change(function(){
		window.location.href = "rollbook.php?SrchDate="+$("#SrchDate option:selected").val();
	});
	
	$("#btn_save").click(function(){
		//$("#rollbook_frm").submit();
		
		$("#rollbook_frm").attr("action", "saverollbook.php");
		$("#rollbook_frm").attr("target", "savefrm");
		$("#rollbook_frm").submit(function(){
			 $("#savefrm").load(function() {
			   //console.log(this);
			   window.location.href = "rollbook.php?SrchDate="+$("#SrchDate option:selected").val();
			});
		}).submit();

	});
});	

function showAddItem (mem_id) {
	if($("#"+mem_id+"_additem").css("display") == "none"){   
		if($("#"+mem_id+"_at option:selected").val() == "y") {
			//resetNatOpt(mem_id,1);
			$("#"+mem_id+"_nat_opt").hide();
			$("#"+mem_id+"_at_opt").show();
		} else {
			//resetAtOpt(mem_id,2);
			$("#"+mem_id+"_at_opt").hide();
			$("#"+mem_id+"_nat_opt").show();
		}
		$('#'+mem_id+"_additem").show();
	} else { 
		if($("#"+mem_id+"_nat_opt").css("display") != "none"){
			resetNatOpt(mem_id,3);
			$("#"+mem_id+"_nat_opt").hide();
		} 
		if($("#"+mem_id+"_at_opt").css("display") != "none"){
			resetAtOpt(mem_id,4);
			$("#"+mem_id+"_at_opt").hide();
		} 
		$('#'+mem_id+"_additem").hide();  
	} 
}
function showAtOpt (mem_id) {
	if($("#"+mem_id+"_additem").css("display") != "none"){   
		if($("#"+mem_id+"_at option:selected").val() == "y") {
			resetNatOpt(mem_id,5);
			$("#"+mem_id+"_nat_opt").hide();
			$("#"+mem_id+"_at_opt").show();
		} else {
			resetAtOpt(mem_id,6);
			$("#"+mem_id+"_at_opt").hide();
			$("#"+mem_id+"_nat_opt").show();
		}
	}
}
function showOpt1Dept(mem_id){
	if($("#"+mem_id+"_at_opt1").prop("checked")) {
		$("#"+mem_id+"_at_opt_dept_div").show();
	} else {
		$("input[name$="+mem_id+"_at_opt_dept").prop("checked",false).checkboxradio("refresh");
		$("#"+mem_id+"_at_opt_dept_div").hide();
	}
}
function resetAtOpt(mem_id,pid){
	$("#"+mem_id+"_at_opt1").prop("checked",false).checkboxradio("refresh");
	$("input[name$="+mem_id+"_at_opt_dept").prop("checked",false).checkboxradio("refresh");
	$("#"+mem_id+"_at_opt_dept_div").hide();
	$("#"+mem_id+"_at_opt2").prop("checked",false).checkboxradio("refresh");
	
	if($("#"+mem_id+"_at_org").val()=="y"){
		if($("#"+mem_id+"_opt_org").val()=="1") {
			$("#"+mem_id+"_at_opt1").prop("checked",true).checkboxradio("refresh");
			$("#"+mem_id+"_at_opt_dept_div").show();
			
			if($("#"+mem_id+"_optdept_org").val()!="0") {
				$("#"+mem_id+"_at_opt_dept_"+$("#"+mem_id+"_optdept_org").val()).prop("checked",true).checkboxradio("refresh");
			}
		}
		else if($("#"+mem_id+"_opt_org").val()=="2") {
			$("#"+mem_id+"_at_opt2").prop("checked",true).checkboxradio("refresh");
		}
	}
}
function resetNatOpt(mem_id,pid){
	$("#"+mem_id+"_nat_opt1").prop("checked",false).checkboxradio("refresh");
	$("#"+mem_id+"_nat_opt2").prop("checked",false).checkboxradio("refresh");
	$("#"+mem_id+"_nat_opt3").prop("checked",false).checkboxradio("refresh");
	
	if($("#"+mem_id+"_at_org").val()=="n"){
		if($("#"+mem_id+"_opt_org").val()=="1") {
			$("#"+mem_id+"_nat_opt1").prop("checked",true).checkboxradio("refresh");
		}
		else if($("#"+mem_id+"_opt_org").val()=="2") {
			$("#"+mem_id+"_nat_opt2").prop("checked",true).checkboxradio("refresh");
		}
		else if($("#"+mem_id+"_opt_org").val()=="3") {
			$("#"+mem_id+"_nat_opt3").prop("checked",true).checkboxradio("refresh");
		}
	}
}
</script>
</body>
</html>
<iframe name="savefrm" id="savefrm" width="0px" height="0px"></iframe>
<?php
$conn -> close();
?>