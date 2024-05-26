<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Greška prilikom povezivanja na bazu podataka: ' . $conn->connect_error]);
    exit();
}

$ID_doktora = isset($_POST['ID_doktora']) ? (int)$_POST['ID_doktora'] : 0;
$Ocjena = isset($_POST['Ocjena']) ? (int)$_POST['Ocjena'] : 0;
$Komentar = isset($_POST['Komentar']) ? $_POST['Komentar'] : '';
$Datum = date('Y-m-d');

$sql = "INSERT INTO Recenzije (ID_doktora, Ocjena, Komentar, Datum) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $ID_doktora, $Ocjena, $Komentar, $Datum);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Recenzija uspješno dodana.']);
} else {
    echo json_encode(['error' => 'Greška prilikom dodavanja recenzije: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
