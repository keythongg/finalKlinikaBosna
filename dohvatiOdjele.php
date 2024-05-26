<?php
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM odjel";
$result = $conn->query($sql);

$odjeli = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $odjeli[] = array(
            'ID' => $row['ID_odjela'],
            'Naziv' => $row['Naziv_odjela']
        );
    }
}

echo json_encode($odjeli);

$conn->close();
?>
