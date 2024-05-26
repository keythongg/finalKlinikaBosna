<?php
// Povezivanje sa bazom podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Povezivanje na bazu podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Dohvati ID osoblja iz GET parametra
$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;

// Dohvati lokaciju osoblja
$sql = "SELECT ID_lokacije FROM Osoblje WHERE ID_osoblja = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

$response = ['success' => false];
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response = [
        'success' => true,
        'location_id' => $row['ID_lokacije']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
