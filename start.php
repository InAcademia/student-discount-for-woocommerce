<?php
require 'inacademia.php';
$_SESSION['inacademia_referrer'] = @$_SERVER['HTTP_REFERER'];

// $_SESSION['inacademia_validated'] = True;
inacademia_authenticate();

header('Location: ' . $_SESSION['inacademia_referrer'], true);
