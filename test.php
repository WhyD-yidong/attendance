<?php
    echo date("l");
	echo date("Y.m.d");
	echo getcwd() . "\n";
	
	$file = 'people.txt';
	$person = "John Smith\n";
	file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
?>