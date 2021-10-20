<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

use Dompdf\Dompdf;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once('php/db.php');
require_once('php/mail.php');

require_once('lib/dompdf/autoload.inc.php');

require_once('lib/phpMailer/src/Exception.php');
require_once('lib/phpMailer/src/PHPMailer.php');
require_once('lib/phpMailer/src/SMTP.php');

$db = DB_CONNECT();

if($_SERVER['REQUEST_METHOD']==='POST') {
	
	if(!isSet($_GET['uid'])){
		
		$uid = (isSet($_SESSION['user']) ? $_SESSION['user']['username'] : session_id()) . '_' . uniqid();
		$sessionId = session_id();
		
		$stmt = $db->prepare("insert into Wish(uid, sessionId, number, date_created) values (?, ?, ?, NOW())");
		$stmt->bind_param("ssi", $uid, $sessionId, $_POST['bday']);
		$stmt->execute();
		$stmt->close();
		
	} else {
		
		$uid = $_GET['uid'];
		
		$stmt = $db->prepare("select userId, sessionId from Wish where uid=?");
		$stmt->bind_param("s", $uid);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		
		$row = $res->fetch_assoc();
		
		if(!$row){
			die('Not found');
		} else if(!( ( isSet($_SESSION['user']) && $row['userId']==$_SESSION['user']['id'] ) || $row['sessionId']==session_id() )) {
			die('Forbidden');
		}
		
	}
	
	$previewText = $_POST['textMode']=='auto'?($_POST['for'].', '.$_POST['from'].' ti přeje všechno nejlepší k '.$_POST['bday'].'. narozeninám!'):$_POST['wishText'];
	$userId = isSet($_SESSION['user'])?$_SESSION['user']['id']:null;
	
	$stmt = $db->prepare("update Wish set userId=?, preview_text=?, lastEdited=NOW() where uid=?");
	$stmt->bind_param("sss", $userId, $previewText, $uid);
	$stmt->execute();
	$stmt->close();
	
	function esc($txt){
		$txt = str_replace("\\", "\\\\", $txt);
		$txt = str_replace("\"", "\\\"", $txt);
		return $txt;
	}
	
	$json = '{'.
			'"bday":"'.$_POST['bday'].'"'.
			',"textMode":"'.$_POST['textMode'].'"'.
			',"for":"'.esc($_POST['for']).'"'.
			',"from":"'.esc($_POST['from']).'"'.
			',"wishText":"'.esc($_POST['wishText']).'"'.
			',"categories":"'.$_POST['categories'].'"'.
			',"infoMode":"'.$_POST['infoMode'].'"'.
			',"infoList":"'.$_POST['infoList'].'"'.
			',"infoCount":"'.$_POST['infoCount'].'"'.
			',"randomInfoList":"'.$_POST['randomInfoList'].'"'.
			'}';
	
	file_put_contents('generated/json/'.$uid.'.json', $json);
	
	function imgb64($src){
		return 'data:image/png;base64,'.base64_encode(file_get_contents($src));
	}

	$dochead = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>@page {margin: 0px;} body { margin: 0px; font-family: DejaVu Sans, sans-serif; }</style>';

	$docbody = '<div style="padding-left:20px;background: #f3eee3; height: 100%;page-break-after: always;">';
	$docbody .= '	<div style="width:150px;"><img src="'.imgb64('res/cake.png').'"></img></div>';
	if($_POST['textMode']=='auto') {
		$docbody .= '	<div style="font-size:36px;font-weight:bold;">'.htmlspecialchars($_POST['for']).',</div>
				<div style="font-size:28px;">'.htmlspecialchars($_POST['from']).' ti přeje všechno nejlepší k <b>'.htmlspecialchars($_POST['bday']).'</b>. narozeninám!</div>';
	} else if($_POST['textMode']=='custom'){
		$docbody .= '	<div style="font-size:28px;">'.htmlspecialchars($_POST['wishText']).'</div>';
	}
	$docbody .= '	<div style="font-size:28px;">Na dalších stranách najdeš zajímavosti k číslu tvých narozenin!</div>';
	$docbody .= '</div>';
	
	$idList = '';
	if($_POST['infoMode']=='list'){
		$idList = $_POST['infoList'];
	} else if($_POST['infoMode']=='random'){
		$idList = $_POST['randomInfoList'];
	}
	
	$stmt = $db->prepare("select id, content, background, color, link, imgSrc, imgAttrib from NumberInfo where id in (".$idList.") order by field(id, ".$idList.")");
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();
	
	while($row = $res->fetch_assoc()){
		
		$img_src = $row['imgSrc'];
		
		$docbody .= '<div style="background: '.($row['background']??'white').'; padding: 10px;height:100%;page-break-after: always;overflow:hidden;">
				<p style="color: '.($row['color']??'black').';font-size:28px;">'.$row['content'].'</p>
				<a style="color: '.($row['color']??'black').';font-size:24px;" href="'.$row['link'].'">'.$row['link'].'</a>
				<br>'.
				(($img_src&&strlen(trim($img_src))>0) ? '<img src="'.imgb64($img_src).'" style="width: 100%;"></img>' : '').
				'<p style="color: '.($row['color']??'black').';font-style:italic;font-size:16px;">'.$row['imgAttrib'].'</p>'.
			'</div>';
			
	}
	
	$loc = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	$loc = substr($loc, 0, strrpos($loc, '/'));
	
	$docbody .= '<div style="background:#f3eee3;padding:10px;height:100%;page-break-after:always;overflow:hidden;">
					<div style="width:150px;"><img src="'.imgb64('res/cake.png').'"></img></div>
					<p style="color: black;font-size:28px;">Přání pomohl vytvořit web Narozeninová přání.</p>
					<p style="color: black;">
						<br>Chcete svému blízkému udělat radost něčím netradičním?
						<br>Popřejte mu formou přání zaslaného v den narozenin.
						<ul>
							<li>Přání si zde sestavíte z různých ftipných i seriózních zajímavostí.</li>
							<li>Vybrané zajímavosti se číselně pojí s oslavencovým věkem.</li>
							<li>Po registraci také můžete přispět do sdíleného seznamu vlastní zajímavostí.</li>
							<li>Můžete odeslání přání naplánovat dopředu a pustit to z hlavy.</li>
						</ul>
						Je to opravdu jednoduché :)
						<br><a style="text-decoration:underline;" href="http://'.$loc.'">VYTVOŘIT PŘÁNÍ</a>
					</p>
				</div>';
	
	$html = "<!doctype html><html><head>".$dochead."</head><body>".$docbody."</body></html>";
	
	$pdf = new Dompdf();
	$pdf->loadHtml($html);
	$pdf->render();
	
	$docname = 'generated/pdf/'.$uid.'.pdf';

	file_put_contents($docname, $pdf->output());

	echo '{"uid":"'.$uid.'"}';
	
}

$db->close();

?>