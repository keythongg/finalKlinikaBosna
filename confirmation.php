<?php
$status = $_GET['status'] ?? '';

$message = '';
if ($status === 'success') {
    $message = "Termin je uspješno zakazan!";
} elseif ($status === 'error') {
    $message = "Greška pri zakazivanju termina.";
} elseif ($status === 'potvrđen') {
    $message = "Termin je potvrđen.";
} elseif ($status === 'odbijen') {
    $message = "Termin je odbijen.";
} elseif ($status === 'rescheduled') {
    $message = "Termin je uspješno pomjeren.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Potvrda</title>
</head>
<body>
    <h1>Potvrda</h1>
    <p><?= htmlspecialchars($message) ?></p>
</body>
</html>
