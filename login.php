<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="kr">
<head>
  <meta charset="utf-8">
  <title>평촌드림교회 유아부</title>
  <meta name="description" content="">
  <meta name="author" content="mac">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	
  <!-- jquery mobile -->	
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
</head>

<body>
	<div style="display:;padding: 60px 50px 50px 50px;">
		<span style="text-align: center;"><h2>평촌드림교회 유아부</h2></span>
		<span style="text-align: center;"><h2>교사보고서</h2></span>
		<label for="userid">아이디</label>
		<input type="text" name="userid" id="userid" value="">
		<label for="userpw">비밀번호</label>
		<input type="password" name="userpw" id="userpw" value="">
		<p id="errmsg" style="color: red;">* ID, PW는 행정팀에 문의 바랍니다. *</p>
		<a href="#dlg-invalid-credentials" data-rel="popup" data-transition="pop" data-position-to="window" id="btn-submit" class="ui-btn ui-btn-b ui-corner-all mc-top-margin-1-5">로그인</a>
	</div>
</body>
<script>

$("#btn-submit").click(function() {
	if (Validate() == false)
		return;

	$("#errmsg").text("");
	$.ajax({
		type : 'post',
		url : 'loginChk.php',
		dataType : 'json',
		data : {
			cmd : 'login',
			id : $("#userid").val(),
			pw : $("#userpw").val()
		},
		success : function(data) {
			if (data.Result === true) {
				window.location.href = "rollbook.php";
			} else {
				$("#errmsg").text("잘못된 아이디 또는 비밀번호 정보입니다. 다시 시도하세요.");
			}
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$("#errmsg").text("에러! 행정팀에 문의 하세요..");
		}
	});
});

function Validate() {
	if ($("#userid").val() == '') {
		$("#errmsg").text("아이디를 입력해 주세요!");
		return false;
	} else {
		$("#errmsg").text("");
	}

	if ($("#userpw").val() == '') {
		$("#errmsg").text("비밀번호를 입력해 주세요!");
		return false;
	} else {
		$("#errmsg").text("");
	}
	return true;
}

</script>
</html>
