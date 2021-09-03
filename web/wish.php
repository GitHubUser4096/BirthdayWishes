<?php
session_start();
require_once('php/db.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$db = DB_CONNECT();

if(!isSet($_SESSION['wish'])){
	die('400 - Bad request');
}

$wish = $_SESSION['wish'];

function imgb64($src){
	return 'data:image/png;base64,'.base64_encode(file_get_contents($src));
}

$html = '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>@page {margin: 0px;} body { margin: 0px; font-family: DejaVu Sans, sans-serif; }</style></head>';

$html .= '<div style="padding-left:20px;background: #f3eee3; height: 100%;page-break-after: always;">
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
	
	if(strpos(strtolower($img_src), ".png")) {
		$img = imagecreatefrompng($img_src);
	} else if(strpos(strtolower($img_src), ".jpg") || str_contains(strtolower($img_src), ".jpeg")) {
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
	$color = '#'.$hr.$hg.$hb;
	
	$html .= '<div style="background: '.$color.'; padding: 10px;height:100%;page-break-after: always;">
			<p style="color: white;">'.$row['content'].'</p>
			<a style="color: white;" href="'.$row['link'].'">'.$row['link'].'</a>
			<br>
			<img src="'.imgb64($img_src).'" style="width: 100%;"></img>
		</div>';
		
}

$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->render();
file_put_contents('generated/wish.pdf', $pdf->output());

?>
<!doctype html>
<html>
	
	<head>
		
		<title>Přání</title>
		
		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		
	</head>
	
	<body>
		
		<?php include('php/titlebar.php'); ?>
		
		<embed src="generated/wish.pdf" style="position:absolute;width:100%;height:calc(100% - 80px);">
		</embed>
		
	</body>
	
</html>
<?php
$db->close();
?>