<?php
include 'check_admin_when_click_dashboard.php';

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Preuzimanje ID-a termina iz AJAX zahtjeva
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

// Izvršavanje upita za brisanje termina iz baze podataka
$sql = "DELETE FROM Termini WHERE ID_termina=$id";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Greška prilikom brisanja termina: ' . $conn->error]);
}

$conn->close();
?>
