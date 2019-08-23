<?php

include "db.php";

//create pdo array
$pdo_array=array("$old_name", "$new_name");
try {
	//pdo connection
    $pdo = new PDO("mysql:unix_socket=$db_sock;dbname=$db_name", $db_user, $db_pass);
    //pdo query
    $sql = "UPDATE $db_table SET region = REPLACE (region, '$old_name', '$new_name') WHERE region  LIKE '%$old_name%'";
    $stm = $pdo->prepare($sql);
    $stm->execute($pdo_array);
    $pdo = null;
}
catch(PDOException $e){
}
?>
