<?php
include 'db_connection.php'; // Include your database connection file

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$sql = "DELETE FROM Lokacija WHERE ID_lokacije = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Lokacija je uspješno izbrisana."]);
} else {
    echo json_encode(["success" => false, "message" => "Greška prilikom brisanja lokacije."]);
}

$stmt->close();
$conn->close();
?>
