<?php

/**
 * PLEASE READ THE README TO FIND OUT WHY THIS CODE LOOKS SO HORRIBLE!
 * THANKS!
 */

// Functions and start-up code

function getLetter($stringes) {
	$query = mysql_query("SELECT letter FROM captcha_somesite WHERE universalcode = '".mysql_real_escape_string($stringes)."' ");
	if(mysql_num_rows($query) == 1) {
		$row = mysql_fetch_array($query);
		if(strpos($row['letter'], "+"))
		{
			$letter = str_replace("+", "", $row['letter']);
			return $letter;
		} else {
			return $row['letter'];
		}
	} else {
		return "$";
	}
}

mysql_connect("localhost", "username", "password");
mysql_select_db("dbname");

// Train the application by inserting data and declaring whats inside it
$imagesArr = array(
    array("data-recognition-db/10144_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/13798_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/14633_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/7003_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/12257_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/9310_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/8609_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays"),
    array("data-recognition-db/6582_perserv_email.png", "type-here-the-emailaddress-you-see-inside-the-image-i-removed-it-because-of-gdpr-nowadays")
);

// Legenda
print("Legenda:<BR>
- een $ geeft weer dat het karakter nog niet in de database bekend is<BR>
- Er wordt in iedere regel een vergelijking gemaakt met de nieuwe waarde en de bekende waarde in de database (ter controle)<BR>");

foreach($imagesArr as $imageElement)
{
	print("<BR><HR><BR>");
	$imgSrc = $imageElement[0];
	$imgValue = $imageElement[1];
	
	$image = imagecreatefrompng($imgSrc);
	$file = getimagesize($imgSrc);
	$y = 0;
	$xMax = $file[0];
	$yMax = $file[1];
	$arr = array();

	// Zet het plaatje als x,y posities en zijn waarde in een array
	for($y = 0; $y < $yMax; $y++)
	{
		for($x = 0; $x < $xMax; $x++)
		{
			$color = imagecolorat($image, $x, $y);
			$arr[$x][$y] = $color;
		}
	}

	// Doorzoek de array op lege kolommen, dus of een Y door de hele array 0 is
	$beginKolom = 0;
	$eindKolom = -1;
	$allesNulVanafKolom = 0;
	$allesNulTotKolom = 0;
	$gevonden = array();

	for($xx = 0; $xx < $xMax; $xx++)
	{
		$allesNul = true;
		
		// Loop één hele Y (kolom) door
		for($yy = 0; $yy < $yMax; $yy++)
		{
			if($arr[$xx][$yy] == 1) {
				$allesNul = false;
			}
		}
		
		if($allesNul)
		{
			//lege kolom
			for($yy = 0; $yy < $yMax; $yy++)
			{
				$arr[$xx][$yy] = "<font color=red>0</font>";
			}
			
			if($allesNulVanafKolom != 0 && $allesNulTotKolom != 0)
			{
				// Er zijn weer 0-en gevonden, ga nu de voorgaande kolommen doorzoeken
				$gevonden[] = $allesNulVanafKolom."-".$allesNulTotKolom;
				$allesNulVanafKolom = 0;
				$allesNulTotKolom = 0;
			}
		}
		else
		{
			if($allesNulVanafKolom == 0) {
				//begin kolom - iedere keer resetten naar 0 als de check klaar is
				$allesNulVanafKolom = $xx;
			}
			elseif($allesNulVanafKolom == $xx-1) {
				// dit is de volgende kolom waar tevens 1-en in zitten
				$allesNulTotKolom = $xx;
			}	
			elseif($allesNulTotKolom == $xx-1) {
				// alle extra kolommen met 1-en
				$allesNulTotKolom = $xx;
			}
		}
	}

	// Geef de getallen van de kleurwaarden weer alsof het een plaatje voorstelt
	for($y = 0; $y < $yMax; $y++)
	{
		for($x = 0; $x < $xMax; $x++)
		{
			//echo $arr[$x][$y];
		}
		//echo "<BR>";
	}

	// Onderaan: doorzoek de database voor de bijbehorende letter
	$letterPos = 0;
	$decodedTotal = 0;
	print('Decoding picture: <img src="'.$imgSrc.'"> ...');
	print('<table border=1><tr><td width="50">In datab:</td><td width="50">Moet zijn:</td><td>ASCII-code:</td></tr>');
	print("\n");
	foreach($gevonden as $g)
	{
		$g = explode("-", $g);
		$start = $g[0];
		$eind = $g[1];
		$stringes = "";
		
		for($i = $start; $i <= $eind; $i++) {
			for($j = 0; $j < $yMax; $j++) {
				$stringes .= $arr[$i][$j];
				//echo $arr[$i][$j];
			}
			//echo " ";
		}
		
		print('<tr><td>'.getLetter($stringes).'</td><td>'.$imgValue[$letterPos].'</td><td>'.$stringes.'</td></tr>');
		if(getLetter($stringes) == $imgValue[$letterPos])
		{
			$decodedTotal++;
		} elseif($_GET['add'] == "y") {
			$q = mysql_query("INSERT INTO captcha_somesite (letter, universalcode) VALUES ('".$imgValue[$letterPos]."', '".$stringes."') ");
			if(!$q) {
				$q = mysql_query("INSERT INTO captcha_somesite (letter, universalcode) VALUES ('".$imgValue[$letterPos]."+', '".$stringes."') ");
			}
		}
		$letterPos++;
	}
	print("</table>\n");
	
	// Print stats per plaatje
	$successrate = 100 / count($gevonden) * $decodedTotal;
	if($successrate == 100)
	{
		print("<font color=green>Decoded 100% OK</font><BR>");
	} else {
		print("<font color=red>Decoded ".round($successrate, 2)."% OK</font><BR>");
	}
}

// Pretty cool for 2007 huh? I believe it was PHP4 or PHP5 at that time with xHTML and jQuery at their very beginning.. That time of the era ;-)