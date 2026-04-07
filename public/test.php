<?php
	$token = bin2hex(random_bytes(32));
	$_SESSION['csrfTokens'][$token] = time();

?>