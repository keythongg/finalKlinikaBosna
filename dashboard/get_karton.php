<?php
include 'check_admin_when_click_dashboard.php';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $kartonId = $_GET['id'];

    $sql = "
        SELECT mk.*, p.Ime AS Ime_pacijenta, p.Prezime AS Prezime_pacijenta 
        FROM Medicinski_karton mk
        LEFT JOIN Pacijent p ON mk.ID_pacijenta = p.ID_pacijenta
        WHERE mk.ID_kartona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $kartonId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $karton = $result->fetch_assoc();
        echo json_encode($karton);
    } else {
        echo "Karton nije pronađen.";
    }
}

$conn->close();
?>
