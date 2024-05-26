<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Greška prilikom povezivanja na bazu podataka: ' . $conn->connect_error]);
    exit();
}

$ID_odjela = isset($_GET['ID_odjela']) ? (int)$_GET['ID_odjela'] : 0;

$sql = "SELECT Osoblje.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Lokacija.Naziv AS Lokacija 
        FROM Osoblje 
        JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije
        WHERE (Osoblje.Pozicija IN ('doktor', 'glavni doktor')) 
        AND (Osoblje.ID_odjela = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ID_odjela);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = [
            'ID' => $row['ID_osoblja'],
            'Ime' => $row['Ime'],
            'Prezime' => $row['Prezime'],
            'Lokacija' => $row['Lokacija']
        ];
    }
}

$stmt->close();
$conn->close();

echo json_encode($doctors);
?>
