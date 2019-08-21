<?php

include "../console/db.php";

//prevent GET requests to this script
if($_SERVER['REQUEST_METHOD'] == 'GET'){
	echo "No GET allowed.";
    exit();
}
//get POST variables
$post_nr = $_POST['post_nr'];
$regions = $_POST['regions'];
$board = $_POST['board'];
//only numbers allowed in post_nr
$post_nr = preg_replace("/[^0-9]/i","", $post_nr);
if (!strlen($post_nr)){
	die("post_nr is empty");
}
//list for flags
$flaglist = file('flag_list_api2.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$regionsArray = explode('||', $regions);
foreach ($regionsArray as $region) {
	if (!in_array($region, $flaglist)) {
		$regions = "empty, or there were errors. Re-set your flags.";
	}
}
//strip to be sure
$regions = strip_tags($regions, '');
//create pdo array
$pdo_array=array("$post_nr", "$board", "$regions");
try {
	//pdo connection
    $pdo = new PDO("mysql:unix_socket=$db_sock;dbname=$db_name", $db_user, $db_pass);
    //pdo query
    $sql = "INSERT INTO $db_table(post_nr, board, region) VALUES (?, ?, ?)";
    $stm = $pdo->prepare($sql);
    $stm->execute($pdo_array);
    $pdo = null;
}
catch(PDOException $e){
}
?>
