<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

function processImage($imageName){

	if(strpos(strtolower($imageName), ".png")) {
		$img = imagecreatefrompng($imageName);
	} else if(strpos(strtolower($imageName), ".jpg") || strpos(strtolower($imageName), ".jpeg")) {
		$img = imagecreatefromjpeg($imageName);
	} if(strpos(strtolower($imageName), ".gif")) {
		$img = imagecreatefromgif($imageName);
	}

	imagealphablending($img, false);
	imagesavealpha($img, true);

	$iw = imagesx($img);
	$ih = imagesy($img);

	//$borderSize = 30;

	$colors = [];

	for($y=0; $y<$ih; $y++){
		for($x=0; $x<$iw; $x++){
			
			$rgb = imagecolorat($img, $x, $y);
			
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = ($rgb >> 0) & 0xFF;
			
			/*$rgb2 = 0;
			$rgb2 |= (floor($r/10)*10)<<16;
			$rgb2 |= (floor($g/10)*10)<<8;
			$rgb2 |= (floor($b/10)*10)<<0;
			
			$nx = $x/$iw;
			$ny = $y/$ih;
			
			$vx = 1-4*pow(abs($nx-.5), 2);
			$vy = 1-4*pow(abs($ny-.5), 2);
			
			$val = $vx*$vy;*/
			
			$maxval = max($r, $g, $b);
			$minval = min($r, $g, $b);
			$val = $maxval-$minval;
			
			if(!isSet($colors[$rgb])) $colors[$rgb] = $val;
			//if(!isSet($colors[$rgb2])) $colors[$rgb2] = $val;
			//else $colors[$rgb2] += $val;
			
			/*$cx = max(min($x, $iw-$borderSize), $borderSize);
			$cy = max(min($y, $ih-$borderSize), $borderSize);
			
			$dx = abs($x-$cx)/$borderSize;
			$dy = abs($y-$cy)/$borderSize;
			$d = sqrt($dx*$dx+$dy*$dy);
			
			if($d>0){
				
				$d = min(pow($d, 2), 1);
				
				imagesetpixel($img, $x, $y, imagecolorallocatealpha($img, $r, $g, $b, $d*127));
				
			}*/
			
		}
	}

	$max = 0;
	$val = 0;

	foreach($colors as $k=>$v){
		if($v>$max){
			$max = $v;
			$val = $k;
		}
	}
	
	$r = ($val >> 16) & 0xFF;
	$g = ($val >> 8) & 0xFF;
	$b = ($val >> 0) & 0xFF;
	
	$hr = dechex($r); if(strlen($hr)==1) $hr = '0'.$hr;
	$hg = dechex($g); if(strlen($hg)==1) $hg = '0'.$hg;
	$hb = dechex($b); if(strlen($hb)==1) $hb = '0'.$hb;
	
	$av = ($r+$g+$b)/3;
	$background = '#'.$hr.$hg.$hb;
	$color = $av>128?'black':'white';

//	imagepng($img, $imageName.'_edit.png');
	
	return ['background'=>$background, 'color'=>$color];
	
}

?>