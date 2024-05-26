<?php
include 'check_admin_when_click_dashboard.php';

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Preuzimanje podataka iz obrasca
$id = $_POST['id'];
$staff = $_POST['staff'];
$service = $_POST['service'];
$date = $_POST['date'];
$time = $_POST['time'];
$status = $_POST['status'];
$user = $_POST['user'];
$location = $_POST['location'];
$note = $_POST['note'];

// Izvršavanje upita za ažuriranje termina u bazi podataka
$sql = "UPDATE Termini SET ID_osoblja='$staff', ID_usluge='$service', Datum='$date', Vrijeme='$time', Status='$status', ID_korisnika='$user', ID_lokacije='$location', Napomena='$note' WHERE ID_termina=$id";
if ($conn->query($sql) === TRUE) {
    $sql = "
        SELECT Termini.ID_termina, Termini.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Termini.ID_usluge, Usluge.Naziv_usluge, Termini.Datum, Termini.Vrijeme, Termini.Status, Termini.Napomena, Termini.ID_korisnika, Korisnici.Ime as KorisnikIme, Korisnici.Prezime as KorisnikPrezime, Termini.ID_lokacije, Lokacija.Naziv AS Naziv_lokacije
        FROM Termini
        LEFT JOIN Osoblje ON Termini.ID_osoblja = Osoblje.ID_osoblja
        LEFT JOIN Usluge ON Termini.ID_usluge = Usluge.ID_usluge
        LEFT JOIN Korisnici ON Termini.ID_korisnika = Korisnici.ID_korisnika
        LEFT JOIN Lokacija ON Termini.ID_lokacije = Lokacija.ID_lokacije
        WHERE Termini.ID_termina = $id
    ";
    $result = $conn->query($sql);
    $appointment = $result->fetch_assoc();

    // Dohvaćanje statistike
    $sql = "SELECT COUNT(*) AS total FROM Termini";
    $total_appointments = $conn->query($sql)->fetch_assoc()['total'];

    $sql = "SELECT COUNT(*) AS pending FROM Termini WHERE Status = 'pending'";
    $pending_appointments = $conn->query($sql)->fetch_assoc()['pending'];

    $sql = "SELECT COUNT(*) AS approved FROM Termini WHERE Status = 'approved'";
    $approved_appointments = $conn->query($sql)->fetch_assoc()['approved'];

    echo json_encode([
        'success' => true,
        'message' => 'Termin je uspješno ažuriran.',
        'appointment' => $appointment,
        'total_appointments' => $total_appointments,
        'pending_appointments' => $pending_appointments,
        'approved_appointments' => $approved_appointments
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Greška prilikom ažuriranja termina: ' . $conn->error]);
}

$conn->close();
?>
