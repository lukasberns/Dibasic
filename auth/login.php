<?php

// login.php
// pseudo-secure login form
// does a lot for a secure login, but you should still use HTTPS
// real authentication is done in loginn.php

$goto = isset($_GET['goto']) ? addslashes($_GET['goto']) : '../';

// if logged in
// redirect to $goto immediately

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Dibasic: Login</title>
	<link rel="stylesheet" href="../css/reset.css" type="text/css" charset="utf-8" />
	<link rel="stylesheet" href="../css/buttons.css" type="text/css" charset="utf-8" />
	<style type="text/css" media="screen">
		html {
			background: #eee;
		}
		
		body {
			font-family: sans-serif;
		}
		
		#login-box {
			width: 360px;
			margin: 100px auto 0;
			padding: 1em 2em;
			border: 1px solid #ccc;
			background: white;
		}
		
		h1 {
			margin: 0.2em 0 0.5em;
			font-size: 2em;
		}
		
		label {
			display: block;
			color: #666;
			font-size: 0.8em;
			margin: 1em 1.5em 0 0;
		}
		
		label input {
			display: block;
			height: 1em;
			padding: 0.5em;
			font-size: 1.25em;
			width: 100%;
		}
		
		#login-button {
			margin: 1em 0;
		}
		
		#powered-by {
			text-align: center;
			font-size: 0.6em;
			color: #aaa;
			margin: 10em 0;
		}
		
		#powered-by a {
			color: #aaa;
			text-decoration: none;
			border-bottom: 1px solid #ccc;
		}
		
		#powered-by a:hover, #powered-by a:focus {
			color: #666;
			border-color: #aaa;
		}
		
		#wrong {
			display: none;
			color: red;
		}
	</style>
	<script src="../js/jquery-1.4.0.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="../js/jshash-2.2/md5-min.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		var focus = null;
		
		$(function() {
			$('#username, #password').keypress(function() {
				$('#wrong').fadeOut();
			}).focus(function() {
				focus = $(this).attr('id');
			});
			
			$('#username').focus();
			
			$('form').submit(function(e) {
				
				var username = $('#username').val();
				var password = $('#password').val();
				
				if (!username || !password) {
					if (!username) {
						$('#username').focus();
					}
					else {
						$('#password').focus();
					}
					return false;
				}
				
				$(this).find('input').attr('disabled', true);
				
				$.post('loginn.php', {
					username: username,
					action: 'spice'
				}, function(spice) {
					if (spice) {
						var h1 = hex_md5(spice.s1 + password);
						var h2 = hex_md5(spice.s2 + password);
						
						var resp = hex_md5(spice.challenge + h1);
						
						$.post('loginn.php', {
							username: username,
							action: 'login',
							challenge: spice.challenge,
							resp: resp,
							h2: h2
						}, function(success) {
							if (success) {
								location.href = '<?=$goto?>';
								// redirect to originally requested page
							}
							else {
								$('form input').removeAttr('disabled');
								$(':password').val('');
								$('#wrong').fadeIn();
								if (focus) {
									$('#'+focus).focus();
								}
							}
						}, 'json');
					}
				}, 'json');
				
				return false;
			});
		});
	</script>
</head>
<body>

<div id="login-box">
	<h1>Login</h1>
	<form action="#">
		<p>
			<label>
				Username: <input type="text" id="username" />
			</label>
			<label>
				Password: <input type="password" id="password" />
			</label>
			<input type="submit" value="Login" id="login-button" />
			<span id="wrong">Sorry, username or password is wrong.</span>
		</p>
	</form>
</div>

<p id="powered-by">Powered by <a href="http://github.com/lukasberns/Dibasic">Dibasic</a></p>

</body>
</html>