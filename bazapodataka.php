<?php
// Povezivanje sa bazom podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Povezivanje na bazu podataka
$conn = mysqli_connect($servername, $username, $password, $database);

// Provera konekcije
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Provera da li je korisnik kliknuo na dugme za registraciju
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unetih podataka iz forme
    $ime = $_POST["ime"];
    $prezime = $_POST["prezime"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $datum_rodjenja = $_POST["datum_rodjenja"];

    // SQL upit za unošenje podataka u bazu
    $sql = "INSERT INTO Korisnici (Ime, Prezime, Email, Password, Datum_rodjenja, Tip_korisnika) VALUES ('$ime', '$prezime', '$email', '$password', '$datum_rodjenja', 'pacijent')";

    // Izvršavanje SQL upita
    if (mysqli_query($conn, $sql)) {
        echo "Uspešno ste se registrovali.";
    } else {
        echo "Greška prilikom registracije: " . mysqli_error($conn);
    }
}



/////////////////////////////// ZA PROVJERU KORISNIKA TIP ////////////////////////////////////////



$sql = "SELECT Tip_korisnika FROM Korisnici WHERE Email = '$email'";

$result = mysqli_query($conn, $sql);

if ($result) {
    // Ako ima rezultata, dohvatite tip korisnika
    $row = mysqli_fetch_assoc($result);
    $ulogaKorisnika = $row['Tip_korisnika'];
} else {
    // Ako nema rezultata ili se dogodila greška, postavite ulogu korisnika na nešto pretpostavljeno
    $ulogaKorisnika = "nepoznato";
}












?>



