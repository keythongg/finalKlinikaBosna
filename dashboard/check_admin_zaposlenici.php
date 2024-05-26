<?php
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

// Provjera je li korisnik prijavljen i ima li tip korisnika "admin"
session_start();
if (isset($_SESSION['ime']) && isset($_SESSION['prezime'])) {
    $ime = $_SESSION['ime'];
    $prezime = $_SESSION['prezime'];

    // Pošalji upit za dohvaćanje tipa korisnika iz baze podataka
    $sql = "SELECT Tip_korisnika FROM Korisnici WHERE Ime = '$ime' AND Prezime = '$prezime'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tip_korisnika = $row["Tip_korisnika"];

        // Provjeri tip korisnika i dozvoli pristup samo adminima
        if ($tip_korisnika != 'admin') {
            // Korisnik nema ovlasti za pristup ovoj stranici, preusmjeri ga na error_dashboard.php
            header("Location: error_dashboard.php");
            exit();
        }
    } else {
        // Ako se korisnik ne pronađe u bazi, preusmjeri ga na error_dashboard.php
        header("Location: error_dashboard.php");
        exit();
    }
} else {
    // Korisnik nije prijavljen, preusmjeri ga na login.php
    header("Location: login.php");
    exit();
}

// Zatvori vezu s bazom podataka
$conn->close();
?>
