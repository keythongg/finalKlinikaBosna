<?php
include 'check_admin_when_click_dashboard.php';

header('Content-Type: application/json');

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$employee_id = $data['id'];

$conn->begin_transaction(); // Započnite transakciju

try {
    // Pronađite ID_korisnika na temelju ID_osoblja
    $sql = "SELECT ID_korisnika FROM Osoblje WHERE ID_osoblja = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        // Obrišite zaposlenika
        $sql = "DELETE FROM Osoblje WHERE ID_osoblja = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        $stmt->close();

        // Obrišite korisnika
        $sql = "DELETE FROM Korisnici WHERE ID_korisnika = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit(); // Potvrdite transakciju
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("No user found for employee ID: $employee_id");
    }
} catch (Exception $e) {
    $conn->rollback(); // Vratite promjene u slučaju greške
    echo json_encode(['success' => false, 'message' => 'Greška prilikom brisanja zaposlenog: ' . $e->getMessage()]);
}

$conn->close();
?>
