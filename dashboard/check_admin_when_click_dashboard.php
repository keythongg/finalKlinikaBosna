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

        // Provjeri tip korisnika i postavi odgovarajući link za "Dashboard"
        if ($tip_korisnika == 'admin' || $tip_korisnika == 'glavni doktor' || $tip_korisnika == 'glavni medicinski tehničar/sestra' || $tip_korisnika == 'doktor') {
            // Postavi link za "Dashboard"
            $dashboard_link = '<li><a href="dashboard/dashboard.php">Dashboard</a></li>';
        } else {
            // Korisnik nema ovlasti za pristup "Dashboardu", preusmjeri ga na error.php
            header("Location: dashboard/error_dashboard.php");
            exit();
        }
    }
} else {
    // Korisnik nije prijavljen, preusmjeri ga na error.php
    header("Location: dashboard/error_dashboard.php");
    exit();
}

// Zatvori vezu s bazom podataka
$conn->close();
?>
