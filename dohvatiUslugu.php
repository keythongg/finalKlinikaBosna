<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Povezivanje s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ID_odjela = isset($_GET['ID_odjela']) ? (int)$_GET['ID_odjela'] : 0;

$sql = "SELECT ID_usluge, Naziv_usluge FROM usluge WHERE ID_odjela = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ID_odjela);
$stmt->execute();
$result = $stmt->get_result();

$services = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'ID' => $row['ID_usluge'],
            'Naziv' => $row['Naziv_usluge'],
        ];
    }
}

$stmt->close();
$conn->close();

echo json_encode($services);
?>
