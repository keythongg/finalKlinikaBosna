<?php
include 'check_admin_when_click_dashboard.php';

$id = $_POST['id'];
$name = $_POST['name'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

$sql = "UPDATE Odjel SET Naziv_odjela = '$name' WHERE ID_odjela = $id";
$response = [];
if ($conn->query($sql) === TRUE) {
    $response['success'] = true;
    $response['message'] = "Odjel uspješno ažuriran.";
} else {
    $response['success'] = false;
    $response['message'] = "Greška prilikom ažuriranja odjela: " . $conn->error;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
