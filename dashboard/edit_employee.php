<?php
include 'check_admin_when_click_dashboard.php'; // Inkluzija baze i autentifikacije

header('Content-Type: application/json'); // Ovo osigurava da je odgovor u JSON formatu

// Provjera da li je zahtjev POST i da li su svi potrebni podaci prisutni
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'], $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['position'], $_POST['status'], $_POST['department'], $_POST['location'])) {
    // Povezivanje sa bazom podataka
    $conn = new mysqli($servername, $username, $password, $database);

    // Provjera konekcije
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    $id = $_POST['id'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $status = $_POST['status'];
    $department = $_POST['department'];
    $location = $_POST['location'];

    // Pripremanje SQL upita za ažuriranje podataka
    $sql = "UPDATE Osoblje SET Ime = ?, Prezime = ?, Email = ?, Pozicija = ?, status_zaposlenog = ?, ID_odjela = ?, ID_lokacije = ? WHERE ID_osoblja = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssiiii", $name, $surname, $email, $position, $status, $department, $location, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Podaci o zaposlenom su uspješno ažurirani.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Greška pri ažuriranju podataka: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Greška pri pripremi upita: ' . $conn->error]);
    }
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Nisu prosleđeni svi potrebni podaci.']);
}
?>
