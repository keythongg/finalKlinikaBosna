<?php
// Povezivanje sa bazom podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

$conn = mysqli_connect($servername, $username, $password, $database);

// Provera konekcije
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Provera da li je korisnik prijavljen
if (isset($_SESSION['id'])) {
    // Prvo brisanje podataka iz tabele PasswordHistory
    $sql_delete_history = "DELETE FROM passwordhistory WHERE ID_korisnika='$user_id'";
    if (mysqli_query($conn, $sql_delete_history)) {
        // Zatim brisanje zapisa iz tabele Osoblje
        $sql_delete_osoblje = "DELETE FROM osoblje WHERE ID_korisnika='$user_id'";
        if (mysqli_query($conn, $sql_delete_osoblje)) {
            // Nakon što su obrisani zavisni podaci, briše se korisnik
            $sql_delete_user = "DELETE FROM korisnici WHERE ID_korisnika='$user_id'";
            if (mysqli_query($conn, $sql_delete_user)) {
                // Postavi poruku pre uništavanja sesije
                $_SESSION['account_deleted'] = "Uspješno ste izbrisali račun.";
                
                // Unset sve sesije i odjavi korisnika
                session_unset();
                session_destroy();
                
                // Redirect na stranicu za prijavu
                header("Location: login.php");
                exit();
            } else {
                echo "Greška prilikom brisanja naloga: " . mysqli_error($conn);
            }
        } else {
            echo "Greška prilikom brisanja zapisa iz tabele osoblje: " . mysqli_error($conn);
        }
    } else {
        echo "Greška prilikom brisanja istorije šifara: " . mysqli_error($conn);
    }
}
?>
