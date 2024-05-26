<?php
include 'db_connection.php'; // Include your database connection file

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$sql = "DELETE FROM Odjel WHERE ID_odjela = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Odjel je uspješno izbrisan."]);
} else {
    echo json_encode(["success" => false, "message" => "Greška prilikom brisanja odjela."]);
}

$stmt->close();
$conn->close();
?>
