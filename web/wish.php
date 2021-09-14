<?php
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

if(!isSet($_SESSION['wish'])){
	die('400 - Bad request');
}

$wish = $_SESSION['wish'];

$db = DB_CONNECT();

/*if($_SERVER['REQUEST_METHOD']==='POST') {
	
	$address = $_POST['mail'];
	
	$mail = new PHPMailer(true);
	
	$mail->CharSet = "UTF-8";
	//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->isSmtp();
	$mail->Host = MAIL_HOST;
	$mail->SMTPAuth = true;
	$mail->Username = MAIL_USERNAME;
	$mail->Password = MAIL_PASSWORD;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = 12345;
	
	$mail->setFrom(MAIL_FROM, 'Narozeninová přání');
	$mail->addAddress($address);
	
	$mail->addAttachment('generated/'.$_SESSION['docname'].'.pdf');
	
	$mail->isHtml(true);
	$mail->Subject = "Všechno nejlepší k narozeninám!";
	$mail->Body = htmlspecialchars($wish['from']).' ti přeje všechno nejlepší k '.htmlspecialchars($wish['bdayNumber']).' narozeninám!';
	//$mail->Body = $_SESSION['dochtml'];
	
	$mail->send();
	
	$info = "Zpráva odeslána!";
	
}*/

if($_SERVER['REQUEST_METHOD']==='POST') {
	
	if(isSet($_POST['regen'])) {
		$_SESSION['docname'] = null;
	}
	
}

if(!isSet($_SESSION['docname'])||$_SESSION['docname']==null) {

	function imgb64($src){
		return 'data:image/png;base64,'.base64_encode(file_get_contents($src));
	}

	$dochead = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>@page {margin: 0px;} body { margin: 0px; font-family: DejaVu Sans, sans-serif; }</style>';

	$docbody = '<div style="padding-left:20px;background: #f3eee3; height: 100%;page-break-after: always;">
		<div style="width:150px;"><img src="'.imgb64('res/cake.png').'"></img></div>
		<div style="font-size:36px;font-weight:bold;">'.htmlspecialchars($wish['for']).',</div>
		<div style="font-size:28px;">'.htmlspecialchars($wish['from']).' ti přeje všechno nejlepší k <b>'.htmlspecialchars($wish['bdayNumber']).'</b>. narozeninám!</div>
	</div>';
		
	$num = $wish['bdayNumber'];

	$stmt = $db->prepare('select id, content, link, imgSrc from NumberInfo where number=? and approved=true');
	$stmt->bind_param("i", $num);
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();

	$rows = [];

	while($row = $res->fetch_assoc()){
		$rows[count($rows)] = $row;
	}

	$toadd = [];

	if($wish['choose']=='random') {
		shuffle($rows);
		for($i = 0; $i<min(count($rows), $wish['random_count']); $i++){
			$toadd[count($toadd)] = $rows[$i];
		}
	} else if($wish['choose']=='list') {
		foreach($rows as $row) {
			if(in_array($row['id'], $wish['choice'])) {
				$toadd[count($toadd)] = $row;
			}
		}
	}

	foreach($toadd as $row) {
		
		$img_src = $row['imgSrc'];
		
		if($img_src&&strlen(trim($img_src))>0) {
			
			if(strpos(strtolower($img_src), ".png")) {
				$img = imagecreatefrompng($img_src);
			} else if(strpos(strtolower($img_src), ".jpg") || strpos(strtolower($img_src), ".jpeg")) {
				$img = imagecreatefromjpeg($img_src);
			} if(strpos(strtolower($img_src), ".gif")) {
				$img = imagecreatefromgif($img_src);
			}
			
			$iw = imagesx($img);
			$ih = imagesy($img);
			
			$n = $iw*$ih;
			
			$avr = 0;
			$avg = 0;
			$avb = 0;
			
			for($y=0; $y<$ih; $y++){
				for($x=0; $x<$iw; $x++){
					
					$rgb = imagecolorat($img, $x, $y);
					
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = ($rgb >> 0) & 0xFF;
					
					$avr += $r;
					$avg += $g;
					$avb += $b;
					
				}
			}
			
			$avr /= $n;
			$avg /= $n;
			$avb /= $n;
			
			$hr = dechex($avr); if(strlen($hr)==1) $hr = '0'.$hr;
			$hg = dechex($avg); if(strlen($hg)==1) $hg = '0'.$hg;
			$hb = dechex($avb); if(strlen($hb)==1) $hb = '0'.$hb;
			$av = ($avr+$avg+$avb)/3;
			$color = '#'.$hr.$hg.$hb;
			$color2 = $av>128?'black':'white';
			
		} else {
			
			$color = "white";
			$color2 = "black";
			
		}
		
		$docbody .= '<div style="background: '.$color.'; padding: 10px;height:100%;page-break-after: always;overflow:hidden;">
				<p style="color: '.$color2.';">'.$row['content'].'</p>
				<a style="color: '.$color2.';" href="'.$row['link'].'">'.$row['link'].'</a>
				<br>'.
				(($img_src&&strlen(trim($img_src))>0) ? '<img src="'.imgb64($img_src).'" style="width: 100%;"></img>' : '').
			'</div>';
			
	}
	
	$html = "<!doctype html><html><head>".$dochead."</head><body>".$docbody."</body></html>";
	
	$_SESSION['docbody'] = $docbody;

	$pdf = new Dompdf();
	$pdf->loadHtml($html);
	$pdf->render();

	$docname = (isSet($_SESSION['user']) ? $_SESSION['user']['username'] : session_id()) . '_' . date('Ymd_His');

	file_put_contents('generated/'.$docname.'.pdf', $pdf->output());

	$_SESSION['docname'] = $docname;

}

?>
<!doctype html>
<html>
	
	<head>
		
		<title>Přání</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
		<style>
			
			.document {
				position: absolute;
				width: calc(2480px * .3);
				height: calc(3508px * .3);
				margin-left: calc((100% - 2480px * .3) * .5);
			}
			
			.docwrapper {
				position: absolute;
				width: 100%;
				height: calc(100% - 150px);
				overflow-y: auto;
				background: gray;
			}
			
			.bottombar {
				position: absolute;
				width: 100%;
				height: 70px;
				bottom: 0;
				background: #f3eee3;
				overflow: hidden
			}
			
			.btncont {
				margin: 10px;
			}
			
			embed {
				width: 100%;
				height: 100%;
			}
			
		</style>
		
	</head>
	
	<body>
		
		<?php include('php/titlebar.php'); ?>
		
		<div class="docwrapper">
			<div class="document">
				<?php echo $_SESSION['docbody']; ?>
			</div>
			<!--embed id="document" src="generated/<?php echo $_SESSION['docname'] ?>.pdf"></embed-->
		</div>
		
		<div class="bottombar">
			<div class="btncont">
				<!--form method="post">
					E-Mail<input type="text" name="mail"></input><input type="submit" value="Odeslat"></input-->
					<!--?php if(isSet($info)) echo $info; ?-->
				<!--/form-->
				<?php if($wish['choose']=='random') { ?>
					<form style="display: inline;" method="post">
						<input type="submit" class="bigbutton" name="regen" value="Generovat znovu"></input>
					</form>
				<?php } ?>
				<a href="schedule_send.php"><button class="bigbutton">
					Odeslat na E-Mail
				</button></a>
				<a href="generated/<?php echo $_SESSION['docname'] ?>.pdf" download="Narozeninové přání.pdf"><button class="bigbutton">
					Uložit PDF
				</button></a>
			</div>
		</div>
		
	</body>
	
</html>
<?php
$db->close();
?>