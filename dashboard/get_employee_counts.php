<?php

include 'check_admin_zaposlenici.php';

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Dohvati brojeve zaposlenih
$sql = "SELECT COUNT(*) AS total FROM Osoblje";
$total_employees = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) AS active FROM Osoblje WHERE status_zaposlenog = 1";
$active_employees = $conn->query($sql)->fetch_assoc()['active'];

$sql = "SELECT COUNT(*) AS inactive FROM Osoblje WHERE status_zaposlenog = 0";
$inactive_employees = $conn->query($sql)->fetch_assoc()['inactive'];

// Vraćanje podataka kao JSON
echo json_encode([
    'total' => $total_employees,
    'active' => $active_employees,
    'inactive' => $inactive_employees
]);

// Zatvori vezu s bazom podataka
$conn->close();
?>
