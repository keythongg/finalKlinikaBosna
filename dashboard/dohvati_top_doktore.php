<?php
// Postavke za povezivanje na bazu podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// SQL upit za povlačenje podataka o top doktorima
$sql = "
    SELECT
        Osoblje.ID_osoblja,
        Osoblje.Ime,
        Osoblje.Prezime,
        Osoblje.slika,
        Odjel.Naziv_odjela,
        Lokacija.Naziv AS Lokacija,
        COUNT(Recenzije.ID_recenzije) AS BrojRecenzija,
        AVG(Recenzije.Ocjena) AS ProsjecnaOcjena
    FROM
        Osoblje
    LEFT JOIN Recenzije ON Osoblje.ID_osoblja = Recenzije.ID_doktora
    LEFT JOIN Odjel ON Osoblje.ID_odjela = Odjel.ID_odjela
    LEFT JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije
    WHERE Recenzije.ID_recenzije IS NOT NULL
    GROUP BY
        Osoblje.ID_osoblja
    ORDER BY
        ProsjecnaOcjena DESC, BrojRecenzija DESC
    LIMIT 5;
";

$result = $conn->query($sql);

$topDoctors = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $topDoctors[] = $row;
    }
} else {
    echo json_encode([]);
    exit();
}

// Vraćanje rezultata kao JSON
echo json_encode($topDoctors);

// Zatvaranje veze s bazom podataka
$conn->close();
?>
