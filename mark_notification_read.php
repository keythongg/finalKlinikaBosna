<?php
// Povezivanje s bazom podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

$notificationId = $_POST['id'];

$sql = "UPDATE Obavijesti SET Procitano = 1 WHERE ID_obavijesti = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $notificationId);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
