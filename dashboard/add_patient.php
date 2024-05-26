<?php
include 'check_admin_when_click_dashboard.php';

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $appointmentId = $_POST['id'];

    // Dohvati podatke o terminu iz baze podataka
    $sql = "SELECT Termini.ID_termina, Termini.ID_osoblja, Termini.ID_korisnika, Osoblje.ID_lokacije 
            FROM Termini
            LEFT JOIN Osoblje ON Termini.ID_osoblja = Osoblje.ID_osoblja
            WHERE Termini.ID_termina = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        $userId = $appointment['ID_korisnika'];
        $locationId = $appointment['ID_lokacije'];

        // Provjeri da li pacijent već postoji u bazi podataka
        $check_sql = "SELECT COUNT(*) AS count FROM Pacijent WHERE ID_korisnika = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $userId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();
        $check_stmt->close();

        if ($check_row['count'] > 0) {
            echo 'Pacijent već postoji u bazi.';
            exit;
        }

        // Dohvati podatke o korisniku iz baze podataka
        $sql = "SELECT Ime, Prezime, Datum_rodjenja, Telefon, Email FROM Korisnici WHERE ID_korisnika = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $ime = $user['Ime'];
            $prezime = $user['Prezime'];
            $datumRodjenja = !empty($user['Datum_rodjenja']) ? $user['Datum_rodjenja'] : NULL;
            $telefon = !empty($user['Telefon']) ? $user['Telefon'] : NULL;
            $email = !empty($user['Email']) ? $user['Email'] : NULL;

            // Pripremi SQL upit sa odgovarajućim poljima
            $sql = "INSERT INTO Pacijent (Ime, Prezime, ID_korisnika, ID_lokacije";
            $values = "VALUES (?, ?, ?, ?";
            $types = "ssii";
            $params = [$ime, $prezime, $userId, $locationId];

            if ($datumRodjenja) {
                $sql .= ", Datum_rodjenja";
                $values .= ", ?";
                $types .= "s";
                $params[] = $datumRodjenja;
            }
            if ($telefon) {
                $sql .= ", Telefon";
                $values .= ", ?";
                $types .= "s";
                $params[] = $telefon;
            }
            if ($email) {
                $sql .= ", Email";
                $values .= ", ?";
                $types .= "s";
                $params[] = $email;
            }

            $sql .= ") $values)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo 'Pacijent je uspješno dodan.';
            } else {
                echo 'Greška prilikom dodavanja pacijenta: ' . $stmt->error;
            }
        } else {
            echo 'Greška: Korisnik nije pronađen.';
        }
    } else {
        echo 'Greška: Termin nije pronađen.';
    }
} else {
    echo 'Nevažeći zahtjev.';
}

$conn->close();
?>
