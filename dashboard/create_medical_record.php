<?php
include 'check_admin_when_click_dashboard.php';

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ID_pacijenta = $_POST['ID_pacijenta'];
    $Adresa = $_POST['Adresa'];
    $Glavne_tegobe = $_POST['Glavne_tegobe'];
    $Fizikalni_pregled = $_POST['Fizikalni_pregled'];
    $Laboratorijski_nalazi = $_POST['Laboratorijski_nalazi'];
    $Terapija = $_POST['Terapija'];
    $Dijagnoza = $_POST['Dijagnoza'];
    $Preporuka = $_POST['Preporuka'];
    $Datum_kreiranja = date('Y-m-d H:i:s');

    $sql = "INSERT INTO medicinski_karton (ID_pacijenta, Adresa, Glavne_tegobe, Fizikalni_pregled, Laboratorijski_nalazi, Terapija, Dijagnoza, Preporuka, Datum_kreiranja)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $ID_pacijenta, $Adresa, $Glavne_tegobe, $Fizikalni_pregled, $Laboratorijski_nalazi, $Terapija, $Dijagnoza, $Preporuka, $Datum_kreiranja);
    
    if ($stmt->execute()) {
        echo 'Medicinski karton uspješno kreiran.';
    } else {
        echo 'Greška prilikom kreiranja medicinskog kartona: ' . $stmt->error;
    }

    $stmt->close();
} else {
    echo 'Nevažeći zahtjev.';
}

$conn->close();
?>
