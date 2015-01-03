<?php
include "header.php";

if (isset($_POST['schakel'])) {
	if($authenticated == true) {
		if (isset($_POST['dimlevel'])) {
			if($debug=='yes') {echo '<br/>$_POST = ';print_r($_POST);echo "<br/>sw/dim/".$_POST['switch']."/".$_POST['dimlevel']."<hr>";}
			file_get_contents($jsonurl.'sw/dim/'.$_POST['switch'].'/'.$_POST['dimlevel']);
		} else if (isset($_POST['schakel'])){
			if($debug=='yes') {echo '<br/>$_POST = ';print_r($_POST);echo "<br/>sw/".$_POST['switch']."/".$_POST['schakel']."<hr>";}
			file_get_contents($jsonurl.'sw/'.$_POST['switch'].'/'.$_POST['schakel']);
		}
		
	} else {
		echo '<p class="error">Switching blocked when not logged in</p>';
	}
}
if (isset($_POST['set_temp'])) {
	if($authenticated == true) {
		if($debug=='yes') {echo '<br/>$_POST = ';print_r($_POST);echo "<br>sw/".$_POST['radiator']."/settarget/".$_POST['set_temp']."<hr>";}
		if(isset($_POST['radiator']) && isset($_POST['set_temp']))file_get_contents($jsonurl.'sw/'.$_POST['radiator'].'/settarget/'.$_POST['set_temp']);
	} else {
		echo 'Switching blocked when not logged in';
	}
}
if (isset($_POST['schakelscene'])) {
	if($authenticated == true) {
		if($debug=='yes') {echo '<br/>$_POST = ';print_r($_POST);echo "<br/>gp/".$_POST['scene']."/".$_POST['schakelscene']."<hr>";}
		file_get_contents($jsonurl.'gp/'.$_POST['scene'].'/'.$_POST['schakelscene']);
	} else {
		echo '<p class="error">Switching blocked when not logged in</p>';
	}
}
$data = null;
try {
  $json = file_get_contents($jsonurl.'get-sensors');
  $data = json_decode($json,true);
} catch (Exception $e) {
  echo $e->getMessage();
}
if (!$data) {
  echo "No information available...";
} else {
  
flush();	
if($authenticated == false) { 
print '<div class="row"><div class="span_3"><p class="error">Some information is not available when not logged in</p></div></div>';
} else {
	if($debug=='yes') print_r($data);
}

//---SCHAKELAARS---
$switches =  $data['response']['switches'];
if(!empty($switches)) {
$sql="select id_switch, type, volgorde from switches where type like 'switch' OR type like 'dimmer' order by volgorde asc, favorite desc, name asc";
if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
$group = 0;
?>
<div class='row'><div class='span_1'><div onclick="window.location='index.php'"><h2>Switches</h2></div>
<?php
flush();
echo "<table align='center'><tbody>";
while($row = $result->fetch_assoc()){
	$switchon = "";
	$tdstyle = '';
	if($group != $row['volgorde']) $tdstyle = 'style="border-top:1px solid black"';
	$group = $row['volgorde'];
	if($data['response']['switches'][$row['id_switch']]['status']=="on") {$switchon = "off";} else {$switchon = "on";}
	print '
	<tr ><td align="right" '.$tdstyle.'>'.$data['response']['switches'][$row['id_switch']]['name'].'</td>
	<td width="70px" '.$tdstyle.' ><form method="post" action="#"><input type="hidden" name="switch" value="'.$row['id_switch'].'"/><input type="hidden" name="schakel" value="'.$switchon.'"/>';
	if($row['type']=='switch') {
		print '
		<div class="slider">	
			<input type="checkbox" value="switch'.$row['id_switch'].'" id="switch'.$row['id_switch'].'" name="switch'.$row['id_switch'].'" '; if($switchon=="off") {print 'checked';} print ' onChange="this.form.submit()"/>
			<label for="switch'.$row['id_switch'].'"></label>
		</div>';
	}
	if($row['type']=='dimmer') {
		print '<select name="dimlevel"  class="abutton" onChange="this.form.submit()" style="margin-top:4px; width:80px; ">
		<option '.$data['response']['switches'][$row['id_switch']]['dimlevel'].') selected>'.$data['response']['switches'][$row['id_switch']]['dimlevel'].'</option>
		<option>0</option>
		<option>10</option>
		<option>20</option>
		<option>30</option>
		<option>40</option>
		<option>50</option>
		<option>60</option>
		<option>70</option>
		<option>80</option>
		<option>90</option>
		<option>100</option>
	</select>';
	}
	print '</form></td></tr>';
}
$result->free();
echo "</tbody></table></div>";
}
/* SCENES */
$scenes =  $data['response']['scenes'];
if(!empty($scenes)) {
?>
<div class='span_1'><div onclick='window.location="index.php"'><h2>Scenes</h2></div>
<?php
foreach($scenes as $scene){
	echo '<table width="100%"><thead><tr><th colspan="2">'.$scene['name'].'</th>
	<th width="50px"><form method="post" action="#"><input type="hidden" name="scene" value="'.$scene['id'].'"/><input type="hidden" name="schakelscene" value="on"/><input type="submit" value="ON" class="abutton"/></form></th>
	<th width="50px"><form method="post" action="#"><input type="hidden" name="scene" value="'.$scene['id'].'"/><input type="hidden" name="schakelscene" value="off"/><input type="submit" value="OFF" class="abutton"/></form></th>
	</tr></thead><tbody>';
	$datascene = null;
	$datascenes = null;
	try {
		$jsonscene = file_get_contents($jsonurl.'gp/get/'.$scene['id']);
		$datascenes = json_decode($jsonscene,true);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	if (!$datascenes) {
		echo "No information available...";
	} else {
		foreach($datascenes['response'] as $datascene) {
		print '<tr><td align="right" width="60px">'.$datascene['type'].'&nbsp;&nbsp;</td><td align="left">&nbsp;'.$datascene['name'].'</td><td>'.$datascene['onstatus'].'</td><td>'.$datascene['offstatus'].'</td></tr>';
		}
	}
	echo '</tbody></table>';
}
echo "</div>";
}

//---RADIATORS---
if(!empty($switches)) {
$sql="select id_switch, volgorde from switches where type like 'radiator' order by volgorde asc, favorite desc, name asc";
if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
$group = 0;
?>
<div class='span_1'><div onclick="window.location='index.php'"><h2>Radiators</h2></div>
<?php
flush();
echo "<table align='center'><tbody>";
while($row = $result->fetch_assoc()){
	$tdstyle = '';
	if($group != $row['volgorde']) $tdstyle = 'style="border-top:1px solid black"';
	$group = $row['volgorde'];
	print '<tr><td align="right" '.$tdstyle.'>'.$data['response']['switches'][$row['id_switch']]['name'].'</td>
	<td width="115px" '.$tdstyle.'><form method="post" action="#">
	<input type="hidden" name="radiator" value="'.$row['id_switch'].'"/>
	<select name="set_temp"  class="abutton" onChange="this.form.submit()" style="margin-top:4px">
		<option '.$data['response']['switches'][$row['id_switch']]['tte'].') selected>'.$data['response']['switches'][$row['id_switch']]['tte'].'</option>
		<option>10</option>
		<option>16</option>
		<option>18</option>
		<option>19</option>
		<option>20</option>
		<option>21</option>
		<option>22</option>
	</select>
	</form></td></tr>';
}
$result->free();
echo "</tbody></table></div></div><div class='row'>";
}

//---SENSORS--
$sensors =  $data['response']['kakusensors'];
if(!empty($sensors)) {
?>
<div class='span_1' onclick="window.location='history.php';"><h2>Sensors</h2>
<?php
flush();
$sql="select id_sensor, volgorde from sensors order by volgorde asc, favorite desc, name asc";
if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
$group = 0;
echo '<table align="center" width="100%">';
while($row = $result->fetch_assoc()){
        print '<tr>';
        if($authenticated==true) {
			$name = $data['response']['kakusensors'][$row['id_sensor']]['name'];
			$status = $data['response']['kakusensors'][$row['id_sensor']]['status'];
			$type = $data['response']['kakusensors'][$row['id_sensor']]['type'];
			$time = $data['response']['kakusensors'][$row['id_sensor']]['timestamp'];
			if($status == "yes") {print '<td style="color:#F00; font-weight:bold">'.$name.'</td>';} else {print '<td>'.$name.'</td>';}
        	if($status == "yes") {print '<td style="color:#F00; font-weight:bold">'.$type.'</td>';} else {print '<td>'.$type.'</td>';}
        	if($status == "yes") {print '<td style="color:#F00; font-weight:bold">';} else {print '<td>';}
			if($type=="contact" && $status == "no") { print 'Closed'; }
			else if ($type=="contact" && $status == "yes") { print 'Open'; }
			else if ($type=="motion" && $status == "yes") { print 'Movement'; }
			else if ($type=="motion" && $status == "no") { print ''; }
			else if ($type=="doorbell" && $status == "no") { print ''; }
			else if ($type=="doorbell" && $status == "yes") { print 'Ring'; }
			else if ($type=="smoke" && $status == "no") { print ''; }
			else if ($type=="smoke" && $status == "yes") { print 'SMOKE!'; }
			else print $status;
			print '</td>';
        
			if($status == "yes") {print '<td style="color:#F00; font-weight:bold">'.$time.'</td>';} else {print '<td>'.$time.'</td>';}
		} else {
			print '<td>'.$name.'</td><td>'.$type.'</td><td>status</td><td>time</td>';
		}
		print '</tr>';
}
echo "</table></div>";
}
//--THERMOMETERS--
$thermometers =  $data['response']['thermometers'];
if(!empty($thermometers)) {
?>
<div class="span_1" onclick="window.location='temp.php';"><h2>Temperature</h2>
<?php
flush();
echo "<table width='100%'>";

foreach($thermometers as $thermometer){
	print '<tr><th></th><th>temp<br/>°C</th><th>hum<br/>%</th><th>min<br/>°C</th><th>te-t<br/>&nbsp;</th><th>max<br/>°C</th><th>te+t<br/>&nbsp;</th></tr>';
	print '<tr>';
	print '<td>'.$thermometer['name'].'</td>';
	print '<td>'.$thermometer['te'].'</td>';
	print '<td>'.$thermometer['hu'].'</td>';
	print '<td>'.$thermometer['te-'].'</td>';
	print '<td>'.$thermometer['te-t'].'</td>';
	print '<td>'.$thermometer['te+'].'</td>';
	print '<td>'.$thermometer['te+t'].'</td></tr>';
}
echo "</table></div>";
}
//--RAINMETERS--
$rainmeters =  $data['response']['rainmeters'];
if(!empty($rainmeters)) {
?>
<div class='span_1' onclick="window.location='rain.php';"><h2>Rain</h2>
<?php 
flush();
echo "<table width='100%'>";

foreach($rainmeters as $rainmeter){
	print '<tr><th></th><th>mm</th><th>3h</th></tr>';
	print '<tr>';
	print '<td>'.$rainmeter['name'].'</td>';
	print '<td>'.$rainmeter['mm'].' mm</td>';
	print '<td>'.$rainmeter['3h'].' mm</td></tr>';
}
echo "</table></div>";
}
//--WINDMETERS--
if(isset($data['response']['windmeters']['0']['ws'])) {
$windmeters =  $data['response']['windmeters'];
?>
<div class='span_1' onclick="window.location='wind.php';"><h2>Wind</h2>
<?php
flush();
echo "<table width='100%'>";
foreach($windmeters as $windmeter){
	print '<tr><th>Naam</th><th>ws</th><th>gu</th><th>dir</th><th>ws+</th></tr>';
	print '<tr>';
	print '<td>'.$windmeter['name'].'</td>';
	print '<td>'.$windmeter['ws'].' km/u</td>';
	print '<td>'.$windmeter['gu'].' km/u</td>';
	print '<td>'.$windmeter['dir'].' °</td>';
	print '<td>'.$windmeter['ws+'].' km/u</td></tr>';
}
echo "</table></div></div>";
}
}
$db->close();
include "footer.php";?>