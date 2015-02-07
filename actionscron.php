<?php
if(!isset($_POST['actionscron'])) {
include "parameters.php";
	$sql="select variable, value from settings order by variable asc";
	if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
	$acceptedips = array();
	while($row = $result->fetch_assoc()){
		if (strpos($row['variable'], 'acceptedip') === 0) { 
			array_push($acceptedips, $row['value']);
		} else {
			$$row['variable'] = $row['value'];
		}
	}
	$result->free();
}
$authenticated=true;
 
//Plak hieronder alles van test.php na testen. 

include "data.php";
include "functions.php";

//Brander schakelen.
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie brander';
if($actie_brander=='yes') {
	echo ' actief</b><br/><br/>';
	$aantalradiatoren = 0;
	if($switchstatus6>$thermometerte4) {$aantalradiatoren = $aantalradiatoren + 1;}
	if($switchstatus7>$thermometerte6) {$aantalradiatoren = $aantalradiatoren + 1;}
	if($switchstatus8>$thermometerte7) {$aantalradiatoren = $aantalradiatoren + 1;}
	if($switchstatus14>$thermometerte5) {$aantalradiatoren = $aantalradiatoren + 1;}
	if($switchstatus15>$thermometerte5) {$aantalradiatoren = $aantalradiatoren + 1;}
	echo 'Temperatuur Eetplaats is '.$thermometerte5.'°C, radiator staat op '.$switchstatus14.'°C.<br/>';
	echo 'Temperatuur Zithoek is '.$thermometerte5.'°C, radiator staat op '.$switchstatus15.'°C.<br/>';
	echo 'Temperatuur Badkamer is '.$thermometerte4.'°C, radiator staat op '.$switchstatus6.'°C.<br/>';
	echo 'Temperatuur Slaapkamer is '.$thermometerte6.'°C, radiator staat op '.$switchstatus7.'°C.<br/>';
	echo 'Temperatuur Slaapkamer Tobi is '.$thermometerte7.'°C, radiator staat op '.$switchstatus8.'°C.<br/>';
	if($aantalradiatoren==1) {
		echo $aantalradiatoren.' radiator heeft warmte nodig<br/>';
	} else {
		echo $aantalradiatoren.' radiatoren hebben warmte nodig<br/>';
	}
	if($switchstatus12=='off') echo 'De brander brandt niet.<br/>'; else echo 'De brander brandt.<br/>';
	if($aantalradiatoren>0 && $switchstatus12=='off') {
			echo "schakel(12, 'on', 'c');sleep(5);schakel(12, 'on', 'd');sleep(5)";
			if(!isset($_POST['showtest'])) schakel(12, 'on', 'c', 'guy@egregius.be', 'yes');sleep(2);schakel(12, 'on', 'd', 'guy@egregius.be', 'yes');sleep(2);
		
	} else if($aantalradiatoren==0 && $switchstatus12=='on'){
		echo "schakel(12, 'off', 'c');sleep(5);schakel(12, 'off', 'd');sleep(5)";
		if(!isset($_POST['showtest'])) schakel(12, 'off', 'c', 'guy@egregius.be', 'yes');sleep(2);schakel(12, 'off', 'd', 'guy@egregius.be', 'yes');sleep(2);
	}
} else {
	echo ' niet actief<br/>';
}
echo '</div>';

//Timer radiatoren living
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie timer radiatoren living';
$tempw = 20;
$tempk = 17;
if($actie_timer_living=='yes' && $switchstatus16=='on'){
	echo ' actief</b><br/><br/>';
	if(in_array(date('N', time()), array(1,2,3,4))) {
		echo 'Vandaag is het een werkdag.<br/>';
		if(time()>(strtotime('18:00')-(($tempw-$thermometerte5)*(($tempw-$thermometerte1)*60))) && (time()<(strtotime('22:00')))) {
			echo 'Het is nu tussen 18:00 en 22:00.<br/>';
			if($switchstatus14<$tempw || $switchstatus15<$tempw) {
				echo "Een van de radiatoren staat kouder dan ".$tempw."°C<br/>";
				echo "radiator(14, ".$tempw.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {radiator(14, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);}
				echo "radiator(15, ".$tempw.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {radiator(15, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);}
			}
		}
	} else if (in_array(date('N', time()), array(5,6,7))) {
		echo 'Vandaag is het weekend.<br/>';
		if(time()>(strtotime('18:00')-(($tempw-$thermometerte5)*(($tempw-$thermometerte1)*60))) && (time()<(strtotime('23:00')))) {
			echo 'Het is nu tussen 18:00 en 23:00.<br/>';
			if($switchstatus14<$tempw || $switchstatus15<$tempw) {
				echo "Een van de radiatoren staat kouder dan ".$tempw."°C<br/>";
				echo "radiator(14, ".$tempw.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {radiator(14, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);}
				echo "radiator(15, ".$tempw.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {radiator(15, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);}
			} else {
				echo "Beide radiatoren staan reeds warmer dan ".$tempw."°C.<br/>";
			}
		}
	} else if(time()>(strtotime('8:00')) && (time()<(strtotime('23:00'))) && ($switchstatus14>$tempk || $switchstatus15>$tempk)) {
			echo 'Manueel hoger gezet, niks doen dus.';
	} else {
		if($switchstatus14>$tempk || $switchstatus15>$tempk) {
			echo "Buiten de daguren en van de radiatoren staat warmer dan ".$tempk." °C<br/> Lager zetten indien niet manueel gezet in de laatste 2 uur. ";
			$laatsteschakel = laatsteschakeltijd(14,null, 'm');
			$laatsteschakel2 = laatsteschakeltijd(15,null, 'm');
			if($laatsteschakel2>$laatsteschakel) $laatsteschakel = $laatsteschakel2;
			if($laatsteschakel['timestamp']<(time()-7200))  {
				echo "radiator(14, ".$tempk.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {radiator(14, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);}
				echo "radiator(15, ".$tempk.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {radiator(15, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);}
			}
		}
	}
} else {
	echo ' niet actief<br/>';
	if($switchstatus14>$tempk || $switchstatus15>$tempk) {
		$laatsteschakel = laatsteschakeltijd(14,null, 'm');
		$laatsteschakel2 = laatsteschakeltijd(15,null, 'm');
		if($laatsteschakel2>$laatsteschakel) $laatsteschakel = $laatsteschakel2;
		if($laatsteschakel['timestamp']<(time()-7200))  {
			echo "radiator(14, ".$tempk.", 'c');sleep(2);<br/>radiator(15, ".$tempk.", 'c');sleep(2)<br/>";
			if(!isset($_POST['showtest'])) {
				radiator(14, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);
				radiator(15, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);
			}
		}
	}
}
echo '</div>';

//Timer radiator badkamer
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie timer radiator badkamer';
$tempw = 24;
$tempk = 18;
if($actie_timer_badkamer=='yes' && $switchstatus16=='on'){
	echo ' actief</b><br/><br/>';
	if(in_array(date('N', time()), array(1,2,3,4,5))) {
		echo 'Vandaag is het een werkdag<br/>';
		if((time()>(strtotime('6:00')-(($tempw-$thermometerte4)*(($tempw-$thermometerte1)*60)))) && (time()<(strtotime('7:30')))) {
			echo 'Tussen 6 en 7:30, tijd voor warmte<br/>';
			if($switchstatus6<$tempk) {
				echo "radiator(6, ".$tempw.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {
					radiator(6, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);
					radiator(6, $tempw, 'd', 'guy@egregius.be', 'yes');sleep(2);
				}
			}
		} 
	} else if(in_array(date('N', time()), array(6,7))) {
		echo 'Vandaag is het weekend<br/>';
		if((time()>(strtotime('7:30')-(($tempw-$thermometerte4)*(($tempw-$thermometerte1)*60)))) && (time()<(strtotime('9:30')))) {
			echo 'Tussen 7:30 en 9:30, tijd voor warmte<br/>';
			if($switchstatus6<$tempw) {
				echo "radiator(6, ".$tempw.", 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) {
					radiator(6, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);
					radiator(6, $tempw, 'd', 'guy@egregius.be', 'yes');sleep(2);
				}
			}
		} else {
			echo 'Geen tijd voor warmte<br/>';
			if($switchstatus6==$tempk) echo 'Radiator staat al op koude temperatuur.<br/>';
				if($switchstatus6>$tempk) {
				echo 'Te warm in de badkamer<br/>';
				$laatsteschakel = laatsteschakeltijd(6,null, 'm');
				echo 'Er werd laatst manueel geschakeld om '.date("j M Y H:i:s",$laatsteschakel['timestamp']).'<br/>';
				if($laatsteschakel['timestamp']<(time()-7200)) {
					echo "radiator(6, ".$tempk.", 'c');sleep(2)<br/>";
					if(!isset($_POST['showtest'])) {
						radiator(6, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);
						radiator(6, $tempk, 'd', 'guy@egregius.be', 'yes');sleep(2);
					}
				}
			}
		}
	}
} else {
	echo ' niet actief<br/>';
	if($switchstatus6>$tempk) {
		echo 'Radiotor ingesteld op '.$switchstatus6.'°C terwijl de actie niet actief is. Manueel geschakeld in de laatste 2 uur?';
		$laatsteschakel = laatsteschakeltijd(6,null, 'm');
		if($laatsteschakel['timestamp']<(time()-7200)) {
			echo "radiator(6, ".$tempk.", 'c');sleep(2)<br/>";
			if(!isset($_POST['showtest'])) {radiator(6, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);radiator(6, $tempk, 'd', 'guy@egregius.be', 'yes');sleep(2);}
		}
	}
}
echo '</div>';

//Timer radiator slaapkamer
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie timer radiator slaapkamer ';
if($actie_timer_slaapkamer=='yes' && $switchstatus16=='on'){
	echo ' actief</b><br/><br/>';
	$tempw = 18;
	$tempk = 8;
	if(time()>(strtotime('22:50')-(($tempw-$thermometerte6)*(($tempw-$thermometerte1)*60))) && (time()<(strtotime('23:00')))) {
		echo 'Tijd voor warmte<br/>';
		if($switchstatus7<$tempw) {
			echo "Radiator 7 verhogen naar ".$tempw." °C.<br/>";
			if(!isset($_POST['showtest'])) radiator(7, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);
		} else {
			echo "Radiator al ingesteld op minstens ".$tempw." °C.<br/>";
		}
	} else {
		echo 'Geen tijd voor warmte<br/>';
		if($switchstatus7>$tempk) {
			$laatsteschakel = laatsteschakeltijd(7,null, 'm');
			if($laatsteschakel['timestamp']<(time()-7200))  {
				echo "radiator(7, ".$tempk.", 'c');sleep(2)<br/>";
			if(!isset($_POST['showtest'])) radiator(7, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);
			}
		}
	}
} else {
	echo ' niet actief<br/>';
	if($switchstatus7>$tempk) {
		$laatsteschakel = laatsteschakeltijd(7,null, 'm');
		if($laatsteschakel['timestamp']<(time()-7200)) {
			echo "radiator(7, ".$tempk.", 'c')<br/>";
			if(!isset($_POST['showtest'])) radiator(7, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);
		}
	}
}
echo '</div>';

//Timer radiator slaapkamer Tobi
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie timer radiator slaapkamer Tobi';
$tempw = 18;
$tempk = 8;
if($actie_timer_slaapkamertobi=='yes' && $switchstatus16=='on'){
	echo ' actief</b><br/><br/>';
	if(date('W', time()) %2 == 0) {
		echo 'Het is een even weeknummer.<br/>';
		if(in_array(date('N', time()), array(3,4,5,6))) {
			echo 'Het is wo, do, vr of za.<br/>';
			if(time()>(strtotime('21:20')-(($tempw-$thermometerte7)*(($tempw-$thermometerte1)*60))) && (time()<(strtotime('21:30')))) {
				echo 'Bijna slaaptijd.<br/>';
				if($switchstatus8<$tempw) {
					echo "radiator(8, ".$tempw.", 'c');sleep(2)<br/>";
					if(!isset($_POST['showtest'])) {radiator(8, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);}
				}
			}
		}
	} else {
		echo 'Het is een onevenen weeknummer.<br/>';
		if(in_array(date('N', time()), array(3,4))) {
			echo 'Het is wo of do.<br/>';
			if(time()>(strtotime('21:20')-(($tempw-$thermometerte7)*(($tempw-$thermometerte1)*60))) && (time()<(strtotime('21:30')))) {
				echo 'Bijna slaaptijd.<br/>';
				if($switchstatus8<$tempw) {
					echo "radiator(8, ".$tempw.", 'c');sleep(2)<br/>";
					if(!isset($_POST['showtest'])) {radiator(8, $tempw, 'c', 'guy@egregius.be', 'yes');sleep(2);}
				}
			}
		}
	} 
} else {
	echo ' niet actief<br/>';
	if($switchstatus8>$tempk) {
		$laatsteschakel = laatsteschakeltijd(8,null, 'm');
		if($laatsteschakel['timestamp']<(time()-7200))  {
			echo "radiator(8, ".$tempk.", 'c');sleep(2)<br/>";
			if(!isset($_POST['showtest'])) radiator(8, $tempk, 'c', 'guy@egregius.be', 'yes');sleep(2);
		}
	}
}
echo '</div>';

//Uitschakelen licht garage
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie timer uitschakelen licht garage';
if($actie_lichtgarage=='yes') {
	echo ' actief</b><br/><br/>';
	if($switchstatus1=='on') {
		if(strtotime($sensortimestamp1)>time()) {$sensor1tijd = laatstesensortijd($sensorid1,null);$sensortimestamp1 = strtotime($sensor1tijd['time']);} else {$sensortimestamp1 = strtotime($sensortimestamp1);}
		if(strtotime($sensortimestamp2)>time()) {$sensor2tijd = laatstesensortijd($sensorid2,null);$sensortimestamp2 = strtotime($sensor2tijd['time']);} else {$sensortimestamp2 = strtotime($sensortimestamp2);}
		if($sensortimestamp1<(time()-200) && $sensortimestamp2<(time()-200)) {
			$laatsteschakel = laatsteschakeltijd(1,null, 'm');
			if($laatsteschakel['timestamp']<(time()-7200) || $laatsteschakel['type']=='off') {
				echo "schakel(1, 'off', 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) schakel(1, 'off', 'c', 'guy@egregius.be', 'yes');sleep(2);
			}
		} 
	}
} else {
	echo ' niet actief<br/>';
}
echo '</div>';

//Schakel Pluto
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie timer Pluto';
if($actie_timer_pluto=='yes'){
	echo ' actief</b><br/><br/>';
	if((time()>(strtotime('11:00'))) && (time()<(strtotime('23:00')))) {
		echo 'Tijd voor Pluto.<br/>';
		if($switchstatus0=='off') {
			echo "schakel(0, 'on', 'c');sleep(2)<br/>";
			if(!isset($_POST['showtest'])) schakel(0, 'on', 'c', 'guy@egregius.be', 'yes');sleep(2);
		} else if ($switchstatus0=='on') {
			echo "Pluto is al actief.<br/>";
		}
	} else {
		echo 'Geen tijd voor Pluto<br/>';
		if($switchstatus0=='on') {
			$laatsteschakel = laatsteschakeltijd(0,null, 'm');
			if($laatsteschakel['timestamp']<(time()-7200))  {
				echo "schakel(0, 'off', 'c');sleep(2)<br/>";
				if(!isset($_POST['showtest'])) schakel(0, 'off', 'c', 'guy@egregius.be', 'yes');sleep(2);
			}
		} else if ($switchstatus0=='off') {
			echo "Pluto is al uitgeschakeld.<br/>";
		}
	}
} else {
	echo ' niet actief<br/>';
	if($switchstatus8>$tempk) {
		$laatsteschakel = laatsteschakeltijd(8,null, 'm');
		if($laatsteschakel['timestamp']<(time()-7200))  {
			echo "schakel(0, 'off', 'c');sleep(2)<br/>";
			if(!isset($_POST['showtest'])) schakel(0, 'off', 'c', 'guy@egregius.be', 'yes');sleep(2);
		}
	}
}
echo '</div>';

//Thuis
echo '<div class="item wide gradient" align="left"><p class="number">3</p><br/>Actie thuis';
if($actie_thuis=='yes'){
	echo ' actief</b><br/><br/>';
	if($switchstatus16=='on') {
		echo 'We zijn thuis<br/>';
	} else {
		echo 'We zijn niet thuis<br/>';
		if($sensorstatus0=='yes') mail ('guy@xafax.be,guy@egregius.be' ,'ROOK gedetecteerd op zolder' ,'ROOK gedetecteerd op zoldere' );
		if($sensorstatus1=='yes') mail ('guy@xafax.be,guy@egregius.be' ,'Poort is geopend' ,'Poort is geopend' );
		if($sensorstatus2=='yes') mail ('guy@xafax.be,guy@egregius.be' ,'Beweging gedetecteerd in garage' ,'Beweging gedetecteerd in garage' );
		if($sensorstatus3=='yes') mail ('guy@xafax.be,guy@egregius.be' ,'ROOK gedetecteerd in de hall' ,'ROOK gedetecteerd in de hall' );
		if($sensorstatus4=='yes') mail ('guy@xafax.be,guy@egregius.be' ,'Bel voordeur ingedrukt' ,'Bel voordeur ingedrukt' );
	}
} else {
	echo ' niet actief<br/>';
	
}
echo '</div>';
if(!isset($_POST['actionscron']) && !isset($_POST['showtest'])) {ob_clean(); $db->close();}

?>