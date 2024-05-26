<?php
include 'check_admin_when_click_dashboard.php';

$id = $_POST['id'];
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$department = $_POST['department'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

$sql = "UPDATE Usluge SET Naziv_usluge = '$name', Opis = '$description', Cijena = '$price', ID_odjela = '$department' WHERE ID_usluge = $id";
$response = [];
if ($conn->query($sql) === TRUE) {
    $response['success'] = true;
    $response['message'] = "Usluga uspješno ažurirana.";
} else {
    $response['success'] = false;
    $response['message'] = "Greška prilikom ažuriranja usluge: " . $conn->error;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
