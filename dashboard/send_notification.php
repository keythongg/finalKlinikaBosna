<?php
// Povezivanje s bazom podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Dohvaćanje podataka o terminu i pacijentu
$appointmentId = $_POST['id'];
$status = $_POST['status'];

$sql = "SELECT Termini.*, Korisnici.Email, Korisnici.ID_korisnika, Usluge.Naziv_usluge, Osoblje.Ime AS ImeDoktora, Osoblje.Prezime AS PrezimeDoktora, Lokacija.Naziv AS NazivLokacije 
        FROM Termini
        LEFT JOIN Korisnici ON Termini.ID_korisnika = Korisnici.ID_korisnika
        LEFT JOIN Usluge ON Termini.ID_usluge = Usluge.ID_usluge
        LEFT JOIN Osoblje ON Termini.ID_osoblja = Osoblje.ID_osoblja
        LEFT JOIN Lokacija ON Termini.ID_lokacije = Lokacija.ID_lokacije
        WHERE Termini.ID_termina = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $appointment = $result->fetch_assoc();

    $userId = $appointment['ID_korisnika'];
    $formattedDate = date('F j, Y', strtotime($appointment['Datum']));
    $time = date('H:i', strtotime($appointment['Vrijeme']));

    if ($status === 'approved') {
        $message = "
            <div style='font-family: Arial, sans-serif;'>
                <p style='color: #333; font-size: 16px;'>Poštovani,</p>
                <p style='font-size: 16px; color: #555;'>
                    Vaš termin za uslugu <strong>" . htmlspecialchars($appointment['Naziv_usluge']) . "</strong> je zakazan za <strong>" . htmlspecialchars($formattedDate) . "</strong> u <strong>" . htmlspecialchars($time) . "</strong>.
                </p>
                <p style='font-size: 16px; color: #555;'>
                    Kod doktora <strong>" . htmlspecialchars($appointment['ImeDoktora']) . " " . htmlspecialchars($appointment['PrezimeDoktora']) . "</strong> na lokaciji <strong>" . htmlspecialchars($appointment['NazivLokacije']) . "</strong>.
                </p>
                <p style='font-size: 16px; color: #555;'>
                    Hvala Vam što koristite naše usluge.
                </p>
            </div>";
    } elseif ($status === 'canceled') {
        $message = "
            <div style='font-family: Arial, sans-serif;'>
                <p style='color: #333; font-size: 16px;'>Poštovani,</p>
                <p style='font-size: 16px; color: #555;'>
                    Vaš termin za uslugu <strong>" . htmlspecialchars($appointment['Naziv_usluge']) . "</strong> zakazan za <strong>" . htmlspecialchars($formattedDate) . "</strong> u <strong>" . htmlspecialchars($time) . "</strong> je nažalost otkazan.
                </p>
                <p style='font-size: 16px; color: #555;'>
                    Molimo Vas da se obratite našoj podršci za dodatne informacije ili zakazivanje novog termina.
                </p>
                <p style='font-size: 16px; color: #555;'>
                    Hvala Vam što koristite naše usluge.
                </p>
            </div>";
    } else {
        echo "Nepodržan status termina.";
        exit;
    }

    // Unos obavijesti u bazu podataka
    $sql = "INSERT INTO Obavijesti (ID_korisnika, Poruka) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $message);

    if ($stmt->execute()) {
        echo "Obavijest je uspješno poslana.";
    } else {
        echo "Greška prilikom slanja obavijesti.";
    }

    $stmt->close();
} else {
    echo "Termin nije pronađen.";
}

$conn->close();
?>
