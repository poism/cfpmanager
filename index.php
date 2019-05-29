<!DOCTYPE html>
<html>
<head>
	<!-- Compiled and minified CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
	<!-- Compiled and minified JavaScript -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>



<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REMOTE_ADDR'] !== "FIXME" || !isset($_REQUEST['FIXME'])) {
	echo "<h1>Nothing to see here...</h1>";
	die();
}

require_once("cloudflare.php");
$CF = new CloudFlare();

function getSubmitResults() {
	global $CF;
	if (count($_POST) == 0) return "No Results Yet";

	$CF->setHostKey("FIXME");

	if (isset($_POST['action'])) {
		$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
		return $CF->submitAction($action,$_POST);
	}
	
}


function createForm($id) {
	global $CF;
	
	if (!isset($CF->actions[$id])){
		echo '<h4>Error: '.$id.' not defined.</h4>'; 
		return false; //fail
	}

	$title=isset($CF->actions[$id]['title']) ? $CF->actions[$id]['title'] : '';
	$description=isset($CF->actions[$id]['description']) ? $CF->actions[$id]['description'] : '';
	$required=isset($CF->actions[$id]['required']) ? $CF->actions[$id]['required'] : [];
	$optional=isset($CF->actions[$id]['optional']) ? $CF->actions[$id]['optional'] : [];
	$fields=array_merge($required,$optional);

	echo '<div class="row" id="'.$id.'">';
	if ($title) echo '<h4>'.$title.'</h4>';
	if ($description) echo '<p>'.$description.'</p>';

	echo '<form id="'.$id.'_form" class="col s12" method="POST"><div class="row">';

	foreach ($fields as $k) {
		$type=$CF->fields[$k]['type'];
		$label=$CF->fields[$k]['label'];
		$tooltip=isset($CF->fields[$k]['tooltip']) ? $CF->fields[$k]['tooltip'] : '';
		$validateStr=isset($required[$k]) ? 'validate' : '';
		echo '<div class="input-field col s12">';
		if ($tooltip) {
			echo '<input name="'.$k.'" type="'.$type.'" data-tooltip="'.$tooltip.'" data-position="bottom" class="tooltipped '.$validateStr.'">';
		}
		else {
			echo '<input name="'.$k.'" type="'.$type.'" class=" '.$validateStr.'">';
		}
		echo '<label for="'.$k.'">'.$label.'</label>';
		echo '</div>';
	}
	echo '<div class="input-submit col s12"><button class="btn waves-effect waves-light" type="submit" name="action" value="'.$id.'">Submit</button></div>';
	echo '</div></form></div>';
}

?>

	<div id="main" class="card"> 

	<div class="card-content">
		<h2>Cloudflare Hosting Provider Manager</h2>
		<ul class="collapsible" id="results">
			<li><div style="padding:1em;">
				<pre>
<?php 
	print_r(getSubmitResults()); 
?>
				</pre>
			</div></li>
		</ul>
	</div>


	<div class="card-tabs">
		<ul id="tabselect" class="tabs tabs-fixed-width">
			<?php
				$c=0;
				foreach ($CF->actions as $k=>$v) {
					echo '<li class="tab"><a href="#'.$k.'" class="'.($c==0 ? "active" : "").'">'.$v["title"].'</a></li>';
					$c++;
				}
			?>
		</ul>
	</div>
	<div class="card-content">
		<?php
			foreach ($CF->actions as $k=>$v) {
				createForm($k);
			}
		?>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var tooltips = M.Tooltip.init(document.querySelectorAll('.tooltipped'), {});
			var tabselect = M.Tabs.init(document.getElementById('tabselect'),{});
		});
	</script>

</body>
</html>
