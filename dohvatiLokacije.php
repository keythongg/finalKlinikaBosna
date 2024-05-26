<?php
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Povezivanje na bazu podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera konekcije
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Dohvati sve lokacije
$sql = "SELECT * FROM lokacija";
$result = $conn->query($sql);

$lokacije = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lokacije[] = array(
            'ID' => $row['ID_lokacije'],
            'Naziv' => $row['Naziv']
        );
    }
}

header('Content-Type: application/json');
echo json_encode($lokacije);

$conn->close();
?>
