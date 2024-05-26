<?php
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Povezivanje na bazu podataka
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

$doctorID = $_GET['ID_doktor'];

$sql = "SELECT lokacija.Naziv 
        FROM lokacija 
        JOIN osoblje ON lokacija.ID_lokacije = osoblje.ID_lokacije 
        WHERE osoblje.ID_osoblja = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorID);
$stmt->execute();
$result = $stmt->get_result();

$lokacija = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($lokacija);

$stmt->close();
$conn->close();
?>
