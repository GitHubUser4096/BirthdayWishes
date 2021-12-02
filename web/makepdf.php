<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
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
		$txt = str_replace("\n", "\\n", $txt);
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

	$html = '<!doctype html><html>
			<head><style>
				@page {
					margin: 0px;
				}
				body {
					margin: 0px;
					font-family: DejaVu Sans, sans-serif;
				}
				.wrapper {
					position: absolute;
					display: block;
					top: 0px;
					left: 0px;
					right: 0px;
					bottom: 0px;
					overflow: hidden;
					margin: 0px;
					padding: 0px;
					page-break-after:always;
				}
			</style></head>
			<body>';

	for($i = 0; $i<$_POST['numPages']; $i++){
		$html .= '<div class="wrapper"><a target="_blank" href='.$_POST['link'.$i].'><img style="width:100%;" src="'.$_POST['page'.$i].'"></img></a></div>';
	}

	$html .= '</body>
			</html>';

	$pdf = new Dompdf();
	$pdf->loadHtml($html);
	$pdf->setPaper('A4', 'portrait');
	$pdf->render();

	$docname = 'generated/pdf/'.$uid.'.pdf';

	file_put_contents($docname, $pdf->output());

	echo '{"uid":"'.$uid.'"}';

}

$db->close();

?>
