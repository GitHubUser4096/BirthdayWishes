<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

require_once('php/db.php');

$db = DB_CONNECT();

$stmt = $db->prepare("select * from Wish where (mail_sent=1 or (mail_date is null and DATEDIFF(NOW(), lastEdited)>(select value from Config where name='wishAccessTime'))) and deleted=0;");
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

echo 'Cleaning up...<br>';

$i = 0;

while($row = $res->fetch_assoc()){

	unlink('generated/pdf/'.$row['uid'].'.pdf');

	$stmt = $db->prepare('update Wish set deleted=1 where id=?');
	$stmt->bind_param('i', $row['id']);
	$stmt->execute();
	$stmt->close();

	$i++;

}

echo 'Cleaned up '.$i.' wishes';

$db->close();

?>
