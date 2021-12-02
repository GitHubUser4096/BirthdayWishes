<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

require_once('../php/db.php');

$db = DB_CONNECT();

if(isSet($_GET['id'])){
	
	$id = $_GET['id'];
	
	$usrId = isSet($_SESSION['user'])?$_SESSION['user']['id']:0;
	
	$stmt = $db->prepare("select id, number, content, color, background, link, imgSrc, imgAttrib, createdBy, createdTime, state from NumberInfo where id=? and (state='approved' or createdBy=?)");
	$stmt->bind_param('ii', $id, $usrId);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	
} else {
	
	$bday = $_GET['bday'];
	$catList = $_GET['categories'];

	$catList = explode(',', $catList);
	$cats = "";
	$firstRow = true;
	foreach($catList as $cat){
		if($firstRow) $firstRow = false;
		else $cats .= ',';
		$cats .= "'".$db->real_escape_string($cat)."'";
	}

	$usrId = isSet($_SESSION['user'])?$_SESSION['user']['id']:0;

	$stmt = $db->prepare("select distinct NumberInfo.id, number, content, color, background, link, imgSrc, imgAttrib, createdBy, createdTime, state from InfoCat inner join NumberInfo on InfoCat.infoId=NumberInfo.id inner join Category on InfoCat.catId=Category.id where number=? and Category.name in (".$cats.") and (state='approved' or createdBy=?)");
	$stmt->bind_param('ii', $bday, $usrId);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	
}

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
		$val = str_replace("\\", "\\\\", $val);
		$val = str_replace("\n", "\\n", $val);
		$val = str_replace("\r", "", $val);
		// $val = str_replace(" ", "&nbsp;", $val);
		echo '"'.$key.'":"'.$val.'"';
	}
	echo '}';
}
echo ']';

$db->close();

?>