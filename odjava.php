<?php
session_start();
session_unset(); // Ukloni sve podatke sesije
session_destroy(); // Uništi sesiju
header("Location: landing.php"); // Preusmjeri na stranicu za prijavu nakon odjave
exit();
?>
