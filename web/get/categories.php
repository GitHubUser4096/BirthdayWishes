<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

require_once('../php/db.php');

$db = DB_CONNECT();

$stmt = $db->prepare("select distinct Category.name from InfoCat inner join Category on Category.id=InfoCat.catId inner join NumberInfo on NumberInfo.id=InfoCat.infoId where NumberInfo.state='approved'");
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

echo '[';
$firstRow = true;
while($row = $res->fetch_assoc()){
	if(!$firstRow) echo ', ';
	else $firstRow = false;
	echo '{';
	$firstEntry = true;
	foreach($row as $key=>$val){
		if(!$firstEntry) echo ', ';
		else $firstEntry = false;
		echo '"'.$key.'":"'.$val.'"';
	}
	echo '}';
}
echo ']';

$db->close();

?>