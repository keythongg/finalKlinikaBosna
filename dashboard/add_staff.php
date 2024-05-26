<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Povezivanje sa bazom podataka
    $servername = "localhost";
    $username = "admin";
    $password = "admin";
    $dbname = "klinika_bosna";

    // Kreiranje konekcije
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Provjera konekcije
    if ($conn->connect_error) {
        sendResponse(false, "Connection failed: " . $conn->connect_error);
        exit;
    }

    // Priprema podataka
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $location = $_POST['location'];
    $department = $_POST['department'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Hashiranje lozinke
    $status = isset($_POST['status']) ? $_POST['status'] : 0; // Uzimanje statusa, podrazumevano je neaktivan

    // Provjerite postoji li već korisnik s istim emailom
    $checkEmail = "SELECT Email FROM Korisnici WHERE Email = '$email'";
    $result = $conn->query($checkEmail);
    if ($result->num_rows > 0) {
        sendResponse(false, "Korisnik s ovim emailom već postoji.");
        exit;
    }

    // SQL upit za dodavanje korisnika
    $sql = "INSERT INTO Korisnici (Ime, Prezime, Email, Telefon, Password, Tip_korisnika)
            VALUES ('$name', '$surname', '$email', '$phone', '$password', '$position')";

    if ($conn->query($sql) === TRUE) {
        $last_user_id = $conn->insert_id;  // Dohvatanje ID-a novog korisnika

        // SQL upit za dodavanje osoblja
        $sql = "INSERT INTO Osoblje (Ime, Prezime, Pozicija, Kontakt_telefon, Email, ID_lokacije, ID_odjela, ID_korisnika, status_zaposlenog)
                VALUES ('$name', '$surname', '$position', '$phone', '$email', $location, $department, $last_user_id, '$status')";

        if ($conn->query($sql) === TRUE) {
            $last_employee_id = $conn->insert_id;  // Dohvatanje ID-a novog zaposlenika

            // Dohvati podatke o novom zaposleniku
            $sql = "SELECT Osoblje.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Osoblje.Pozicija, Osoblje.Email, 
                           Osoblje.status_zaposlenog, Osoblje.ID_odjela, Odjel.Naziv_odjela, Osoblje.ID_lokacije, Lokacija.Naziv AS Naziv_lokacije
                    FROM Osoblje
                    LEFT JOIN Odjel ON Osoblje.ID_odjela = Odjel.ID_odjela
                    LEFT JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije
                    WHERE Osoblje.ID_osoblja = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $last_employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $new_employee = $result->fetch_assoc();

            sendResponse(true, "Novo osoblje je uspješno dodano!", $new_employee);
        } else {
            sendResponse(false, "Greška pri dodavanju osoblja: " . $conn->error);
        }
    } else {
        sendResponse(false, "Greška pri dodavanju korisnika: " . $conn->error);
    }

    // Zatvaranje konekcije
    $conn->close();
}

function sendResponse($success, $message, $employee = null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'employee' => $employee]);
}
?>
