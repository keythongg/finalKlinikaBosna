<?php
include 'check_admin_when_click_dashboard.php';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kartonId = $_POST['id'];

    $sql = "DELETE FROM Medicinski_karton WHERE ID_kartona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $kartonId);

    if ($stmt->execute()) {
        echo "Karton je uspješno obrisan.";
    } else {
        echo "Greška prilikom brisanja kartona: " . $stmt->error;
    }
}

$conn->close();
?>
