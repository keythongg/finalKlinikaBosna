<?php
session_start();

// Postavke za povezivanje na bazu podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Dohvatanje korisničkog ID-a iz sesije
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    die("Greška: Korisnički ID nije dostupan.");
}

// Dohvatanje podataka iz POST zahteva
$service = $_POST['service'];
$doctor = $_POST['doctor'];
$date = $_POST['date'];
$time = $_POST['time'];
$message = $_POST['message'];

// Sanitizacija ulaznih podataka
$service = (int) $service;
$doctor = (int) $doctor;
$date = $conn->real_escape_string($date);
$time = $conn->real_escape_string($time);
$message = $conn->real_escape_string($message);

// SQL upit za umetanje termina
$sql = "INSERT INTO Termini (ID_usluge, ID_osoblja, Datum, Vrijeme, Napomena, ID_korisnika, Status) 
        VALUES ($service, $doctor, '$date', '$time', '$message', $user_id, 'pending')";

if ($conn->query($sql) === TRUE) {
    echo "Termin uspješno zakazan! Uskoro ćemo Vas kontaktirati. Pratite < Obavijesti > na našoj web aplikaciji.";
} else {
    echo "Greška: " . $conn->error;
}

// Zatvaranje veze s bazom podataka
$conn->close();
?>
