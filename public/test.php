<?php
	$token = bin2hex(random_bytes(32));
	$_SESSION['csrfTokens'][$token] = time();
  print_r($_SESSION['csrfTokens']);
  foreach ($_SESSION['csrfTokens'] as $key => $value) {echo "<br>test:{$key} //  value:{$value}";};

?>