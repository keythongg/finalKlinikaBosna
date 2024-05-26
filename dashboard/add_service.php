<?php

// Postavke za povezivanje na bazu podataka
$servername = "localhost"; // Promijenite ovo u ime vašeg servera ako je potrebno
$username = "admin"; // Promijenite ovo u vaše korisničko ime baze podataka
$password = "admin"; // Promijenite ovo u vašu lozinku baze podataka
$database = "klinika_bosna"; // Promijenite ovo u ime vaše baze podataka

// Uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $department = $_POST['department'];

    $sql = "INSERT INTO Usluge (Naziv_usluge, Opis, Cijena, ID_odjela) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $name, $description, $price, $department);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Usluga je uspješno dodana."]);
    } else {
        echo json_encode(["success" => false, "message" => "Greška prilikom dodavanja usluge."]);
    }

    $stmt->close();
    $conn->close();
}
?>
