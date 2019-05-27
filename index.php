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

if ($_SERVER['REMOTE_ADDR'] !== "FIXME") {
	echo "<h1>INVALID IP</h1>";
	die();
}

function getSubmitResults() {
	if (count($_POST) == 0) return "No Results Yet";

	require_once("cloudflare.php");
	$CF = new CloudFlare("FIXME");
	
	if (@$_POST['action'] == "user_create"){
		return $CF->userCreate($_POST["cloudflare_email"], $_POST["cloudflare_pass"], @$_POST["cloudflare_username"], @$_POST["unique_id"]);
	}
	elseif (@$_POST['action'] == "user_lookup" && isset($_POST["cloudflare_email"])){
		return $CF->userLookup($_POST["cloudflare_email"], @$_POST["unique_id"]);
	}
}

?>

	<div id="main" class="card" style="max-width:700px;margin:0 auto;">

	<div class="card-content">
		<h2>Cloudflare Partner Management</h2>
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
			<li class="tab"><a href="#user_lookup" class="active">User Lookup</a></li>
			<li class="tab"><a href="#user_create">User Create</a></li>
			<li class="tab"><a href="#user_auth">User Auth</a></li>
			<li class="tab"><a href="#zone_lookup">Zone Lookup</a></li>
		</ul>
	</div>
	<div class="card-content">
		<div class="row" id="user_lookup">
			<h4>User Lookup</h4>
			<p>Look a user by 'cloudflare_email' or by a previosly assigned 'unique_id'.</p>
			<form id="user_lookup_form" class="col s12" method="POST">
				<div class="row">
					<div class="input-field col s12">
						<input placeholder="Cloudflare Email" name="cloudflare_email" type="email" class="validate">
						<label for="cloudflare_email">Cloudflare Email</label>
					</div>
					<div class="input-field col s6">
						<input placeholder="Unique ID" name="unique_id" type="text">
						<label for="unique_id">Unique ID (optional)</label>
					</div>
					<div class="input-submit col s12">
						<button class="btn waves-effect waves-light" type="submit" name="action" value="user_lookup">Submit</button>
					</div>
				</div>
			</form>
		</div>

		<div class="row" id="user_create">
			<h4>User Create</h4>
			<p>Create a CloudFlare account for a user. Register you as the host provider.</p>
			<form id="user_create_form" class="col s12" method="POST">
				<div class="row">
					<div class="input-field col s12">
						<input placeholder="Cloudflare Email" name="cloudflare_email" type="email" class="validate">
						<label for="cloudflare_email">Cloudflare Email</label>
					</div>
					<div class="input-field col s12">
						<input name="cloudflare_pass" type="password" class="validate">
						<label for="cloudflare_email">Cloudflare Password</label>
					</div>
					<div class="input-field col s6">
						<input placeholder="Cloudflare Username" name="cloudflare_username" type="text">
						<label for="cloudflare_username">Cloudflare Username (optional)</label>
					</div>
					<div class="input-field col s6">
						<input placeholder="Unique ID" name="unique_id" type="text">
						<label for="unique_id">Unique ID (optional)</label>
					</div>
					<div class="input-submit col s12">
						<button class="btn waves-effect waves-light" type="submit" name="action">Submit</button>
					</div>
				</div>
			</form>
		</div>

		<div class="row" id="user_auth">
			<h4>User Authorize</h4>
			<p>Authorize you as the host provider for an existing user.</p>
			<form id="user_auth_form" class="col s12" method="POST">
				<div class="row">
					<div class="input-field col s12">
						<input placeholder="Cloudflare Email" name="cloudflare_email" type="email" class="validate">
						<label for="cloudflare_email">Cloudflare Email</label>
					</div>
					<div class="input-field col s12">
						<input name="cloudflare_pass" type="password" class="validate">
						<label for="cloudflare_email">Cloudflare Password</label>
					</div>
					<div class="input-field col s6">
						<input placeholder="Unique ID" name="unique_id" type="text">
						<label for="unique_id">Unique ID (optional)</label>
					</div>
					<div class="input-submit col s12">
						<button class="btn waves-effect waves-light" type="submit" name="action" value="user_auth">Submit</button>
					</div>
				</div>
			</form>
		</div>

		<div class="row" id="zone_lookup">
			<h4>Zone Lookup</h4>
			<p>Lookup a Zone by 'user_key' and 'zone_name'.</p>
			<form id="zone_lookup_form" class="col s12" method="POST">
				<div class="row">
					<div class="input-field col s12">
						<input placeholder="Zone Name" name="zone_name" type="text">
						<label for="zone_name">Zone Name</label>
					</div>
					<div class="input-field col s12">
						<input name="user_key" type="password" class="validate">
						<label for="user_key">User Key</label>
					</div>
					<div class="input-submit col s12">
						<button class="btn waves-effect waves-light" type="submit" name="action" value="zone_lookup">Submit</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<script>
		var instance = M.Tabs.init(document.getElementById('tabselect'),{});
	</script>

</body>
</html>
