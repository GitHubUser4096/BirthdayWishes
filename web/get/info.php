<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

require_once('../php/db.php');

$db = DB_CONNECT();

$bday = $_GET['bday'];
$catList = $_GET['categories'];

$catList = explode(',', $catList);
$cats = "";
$firstRow = true;
foreach($catList as $cat){
	if($firstRow) $firstRow = false;
	else $cats .= ',';
	$cats .= "'".$cat."'";
}

$stmt = $db->prepare("select distinct NumberInfo.id, number, content, color, background, link, imgSrc, imgAttrib, createdBy, createdTime, state from InfoCat inner join NumberInfo on InfoCat.infoId=NumberInfo.id inner join Category on InfoCat.catId=Category.id where number=? and Category.name in (".$cats.") and state='approved'");
$stmt->bind_param('i', $_GET['bday']);
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
		$v = str_replace("\n", " ", $val);
		$v = str_replace("\r", "", $v);
		echo '"'.$key.'":"'.$v.'"';
	}
	echo '}';
}
echo ']';

$db->close();

?>