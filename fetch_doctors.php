<?php
// fetch_doctors.php
include 'dashboard/db_connection.php'; // Uključite vašu datoteku za povezivanje s bazom podataka

$sql = "SELECT Osoblje.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Lokacija.Naziv AS Lokacija 
        FROM Osoblje 
        JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije 
        WHERE Osoblje.Pozicija = 'doktor' OR Osoblje.Pozicija = 'glavni doktor'";
$result = $conn->query($sql);

$options = '';
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $options .= "<option value='{$row['ID_osoblja']}'>{$row['Ime']} {$row['Prezime']} ({$row['Lokacija']})</option>";
    }
}

echo $options;
$conn->close();
?>
