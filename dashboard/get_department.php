<?php
include 'check_admin_when_click_dashboard.php';

$id = $_GET['id'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

$sql = "SELECT * FROM Odjel WHERE ID_odjela = $id";
$result = $conn->query($sql);

$response = [];
if ($result && $result->num_rows > 0) {
    $response['success'] = true;
    $response['department'] = $result->fetch_assoc();
} else {
    $response['success'] = false;
    $response['message'] = "Greška prilikom dohvaćanja podataka odjela.";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
