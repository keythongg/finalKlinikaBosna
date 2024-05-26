<?php
include 'dashboard/db_connection.php'; // Uključite vašu datoteku za povezivanje s bazom podataka

session_start();

header('Content-Type: application/json'); // Set the header to return JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $ID_korisnika = $_SESSION['user_id'];
    $ID_doktora = $_POST['ID_doktora'];
    $Ocjena = $_POST['Ocjena'];
    $Komentar = $_POST['Komentar'];
    $Datum = date('Y-m-d H:i:s'); // Trenutni datum i vrijeme

    // Provjerite je li korisnik već recenzirao ovog doktora
    $sql = "SELECT * FROM recenzije WHERE ID_korisnika = ? AND ID_doktora = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $ID_korisnika, $ID_doktora);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["message" => "Već ste recenzirali ovog doktora."]);
    } else {
        $sql = "INSERT INTO recenzije (ID_korisnika, ID_doktora, Ocjena, Komentar, Datum) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $ID_korisnika, $ID_doktora, $Ocjena, $Komentar, $Datum);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Recenzija uspješno poslana!"]);
        } else {
            echo json_encode(["message" => "Došlo je do greške prilikom slanja recenzije.", "error" => $stmt->error]);
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["message" => "Niste prijavljeni ili je došlo do greške."]);
}
?>
