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
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "INSERT INTO Lokacija (Naziv, Adresa, Kontakt_telefon, Email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $address, $phone, $email);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Lokacija je uspješno dodana."]);
    } else {
        echo json_encode(["success" => false, "message" => "Greška prilikom dodavanja lokacije."]);
    }

    $stmt->close();
    $conn->close();
}
?>
