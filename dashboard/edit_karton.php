<?php
include 'check_admin_when_click_dashboard.php';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kartonId = $_POST['id'];
    $adresa = $_POST['adresa'];
    $glavne_tegobe = $_POST['glavne_tegobe'];
    $fizikalni_pregled = $_POST['fizikalni_pregled'];
    $laboratorijski_nalazi = $_POST['laboratorijski_nalazi'];
    $terapija = $_POST['terapija'];
    $dijagnoza = $_POST['dijagnoza'];
    $preporuka = $_POST['preporuka'];

    $sql = "UPDATE Medicinski_karton SET Adresa = ?, Glavne_tegobe = ?, Fizikalni_pregled = ?, Laboratorijski_nalazi = ?, Terapija = ?, Dijagnoza = ?, Preporuka = ? WHERE ID_kartona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $adresa, $glavne_tegobe, $fizikalni_pregled, $laboratorijski_nalazi, $terapija, $dijagnoza, $preporuka, $kartonId);

    if ($stmt->execute()) {
        echo "Karton je uspješno ažuriran.";
    } else {
        echo "Greška prilikom ažuriranja kartona: " . $stmt->error;
    }
}

$conn->close();
?>
