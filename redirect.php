<?php
require 'inacademia.php';
inacademia_authenticate();

header('Location: ' . $_SESSION['inacademia_referrer'], true);
