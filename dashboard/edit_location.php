<?php
include 'check_admin_when_click_dashboard.php';

$id = $_POST['id'];
$name = $_POST['name'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$email = $_POST['email'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

$sql = "UPDATE Lokacija SET Naziv = '$name', Adresa = '$address', Kontakt_telefon = '$phone', Email = '$email' WHERE ID_lokacije = $id";
$response = [];
if ($conn->query($sql) === TRUE) {
    $response['success'] = true;
    $response['message'] = "Lokacija uspješno ažurirana.";
} else {
    $response['success'] = false;
    $response['message'] = "Greška prilikom ažuriranja lokacije: " . $conn->error;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
