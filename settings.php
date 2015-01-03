<?php
include "header.php";
print '<div class="row"><div class="span_3">';
$showmenu = true;
$showparameters = false;
$showupdatesensors  = false;
$showeditswitches = false;
$showeditsensors = false;

if(isset($_POST['parameters'])) {$showmenu=false;$showparameters = true;}
if(isset($_POST['updatesensors'])) {$showmenu=false;$showupdatesensors = true;}
if(isset($_POST['editswitches'])) {$showmenu=false;$showeditswitches = true;}
if(isset($_POST['editsensors'])) {$showmenu=false;$showeditsensors = true;}

if(isset($_POST['logout'])) {session_destroy();$authenticated = false;header("location:settings.php");exit();}

if(isset($_POST['editswitch'])) { 
	$id_switch=($_POST['id_switch']);
	$volgorde=($_POST['volgorde']);
	$sql="update switches set volgorde = '$volgorde' where id_switch = $id_switch";
	if(!$result = $db->query($sql)){ die('There was an error running the query '.$sql.'<br/>[' . $db->error . ']');}
	$showmenu=false;
	$showeditswitches=true;
}
if(isset($_POST['editsensor'])) { 
	$id_sensor=($_POST['id_sensor']);
	$volgorde=($_POST['volgorde']);
	$sql="update sensors set volgorde = '$volgorde' where id_sensor = $id_sensor";
	if(!$result = $db->query($sql)){ die('There was an error running the query '.$sql.'<br/>[' . $db->error . ']');}
	$showmenu=false;
	$showeditsensors=true;
}
if(isset($_POST['upd'])) { 
	$variable=$db->real_escape_string($_POST['variable']);
	$value=$db->real_escape_string($_POST['value']);
	$sql="update settings set value = '$value' where variable like '$variable'";
	if(!$result = $db->query($sql)){ die('There was an error running the query '.$sql.'<br/>[' . $db->error . ']');}
}
if(isset($_POST['add'])) { 
	$variable=$db->real_escape_string($_POST['variable']);
	$value=$db->real_escape_string($_POST['value']);
	$sql="insert into settings (variable, value) VALUES ('$variable', '$value')";
	
	if(!$result = $db->query($sql)){ die('There was an error running the query '.$sql.'<br/>[' . $db->error . ']');}
}
if(isset($_POST['updateswitches'])) { 
	include "history_to_sql.php";
}
if(isset($_SESSION['authenticated'])) {if ($_SESSION['authenticated'] == true) {$authenticated = true;}}
if(!isset($_SESSION['authenticated'])) {
		$error = null;
		if (!empty($_POST['username'])) {
			$username = empty($_POST['username']) ? null : $_POST['username'];
			$password = empty($_POST['password']) ? null : $_POST['password'];
			if ($username == $secretusername && $password == $secretpassword) {
			$_SESSION['authenticated'] = true;
			$authenticated = true;
		} else {
			$error = 'Incorrect username or password';
		}
	}
   echo '<p class="error">'.$error.'</p>';
	}
if($authenticated==true) {
	
//BEGIN AUTHENTICATED STUFF	
if($showmenu==true) {
	echo '
	<form method="post"><input type="submit" name="parameters" value="Parameters" class="abutton"/></form><br/>
	<form method="post"><input type="submit" name="editsensors" value="Edit sensors" class="abutton"/></form><br/>
	<form method="post"><input type="submit" name="editswitches" value="Edit switches" class="abutton"/></form><br/>
	<form method="post"><input type="submit" name="updateswitches" value="Update switches, sensors, history" class="abutton"/></form><br/>
	';
}
if($showparameters==true) {
	echo '<center><table width="400px" style="text-align:center"><tbody>';
	$sql="select variable, value from settings order by variable asc";
	if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
	while($row = $result->fetch_assoc()){
		echo '<form method="post"><tr><td>'.$row['variable'].'</td><td><input type="hidden" name="variable" id="variable" value="'.$row['variable'].'"/><input type="text" name="value" id="value" value="'.$row['value'].'"/></td><td><input type="submit" name="upd" value="update" class="abutton"></td></tr></form>';
	}
	$result->free();
	echo '<form method="post"><tr><td><input type="text" name="variable" id="variable" value=""/></td><td><input type="text" name="value" id="value" value=""/></td><td><input type="submit" name="add" value="add" class="abutton"/></td></tr></form></tbody></table></center>';
}
if($showeditswitches==true) {
	echo '<center><table width="500px" style="text-align:center"><thead><tr><th>id</th><th>Name</th><th>type</th><th>favorite</th><th>order</th></thead><tbody>';
	$sql="select id_switch, name, type, favorite, volgorde from switches order by type asc, volgorde asc, favorite desc, name asc";
	if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
	while($row = $result->fetch_assoc()){
		echo '<form method="post"><tr><td>'.$row['id_switch'].'</td><td>'.$row['name'].'</td><td>'.$row['type'].'</td><td>'.$row['favorite'].'</td><td><input type="hidden" name="id_switch" id="id_switch" value="'.$row['id_switch'].'"/><input type="text" name="volgorde" id="volgorde" value="'.$row['volgorde'].'" size="5"/></td><td><input type="submit" name="editswitch" value="update" class="abutton"></td></tr></form>';
	}
	$result->free();
	echo '</tbody></table></center>';
}
if($showeditsensors==true) {
	echo '<center><table width="500px" style="text-align:center"><thead><tr><th>id</th><th>Name</th><th>type</th><th>favorite</th><th>order</th></thead><tbody>';
	$sql="select id_sensor, name, type, favorite, volgorde from sensors order by volgorde asc, favorite desc, name asc";
	if(!$result = $db->query($sql)){ die('There was an error running the query [' . $db->error . ']');}
	while($row = $result->fetch_assoc()){
		echo '<form method="post"><tr><td>'.$row['id_sensor'].'</td><td>'.$row['name'].'</td><td>'.$row['type'].'</td><td>'.$row['favorite'].'</td><td><input type="hidden" name="id_sensor" id="id_sensor" value="'.$row['id_sensor'].'"/><input type="text" name="volgorde" id="volgorde" value="'.$row['volgorde'].'" size="5"/></td><td><input type="submit" name="editsensor" value="update" class="abutton"></td></tr></form>';
	}
	$result->free();
	echo '</tbody></table></center>';
}

//END AUTHENTICATED STUFF	
if($showmenu==false) {
	print '<br/><br/><br/><form method="post"><input type="submit" name="settings" value="settings" class="abutton"/></form>';
} else {
	print '<br/><br/><br/><form method="post"><input type="submit" name="logout" value="logout" class="abutton"/></form>';
}
} else {
	print '<br/><br/>Log in:<br/><br/>';
	print '<br/><br/>
	<form method="post">
	<label for="username">Username: </label><input type="text" name="username" size="20" /><br/>
	<label for="password">Password: </label><input type="password" name="password" size="20" /><br/>
	<input type="submit" value="login" class="abutton"/><br/>
	</form>';
}
$db->close();
print '</div></div>';
include "footer.php";
?>