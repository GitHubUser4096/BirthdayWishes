<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

require_once('php/db.php');

$db = DB_CONNECT();

$stmt = $db->prepare("select * from Wish where (mail_sent=1 or DATEDIFF(NOW(), lastEdited)>(select value from Config where name='wishAccessTime')) and deleted=0");
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

echo 'Cleaning up...<br>';

while($row = $res->fetch_assoc()){
	
	echo 'Deleting '.$row['uid'].' '.$row['preview_text'].'... ';
	
	unlink('generated/pdf/'.$row['uid'].'.pdf');
	
	$stmt = $db->prepare('update Wish set deleted=1 where id=?');
	$stmt->bind_param('i', $row['id']);
	$stmt->execute();
	$stmt->close();
	
	echo 'Done<br>';
	
}

echo 'Cleanup done';

$db->close();

?>