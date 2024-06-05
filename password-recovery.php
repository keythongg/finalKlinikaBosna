<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Uključivanje PHPMailer autoloadera
require 'vendor/autoload.php';

// Postavljanje početne vrijednosti za poruku o grešci
$error_message = "";

// Provjera da li je korisnik kliknuo na dugme za slanje emaila
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobivanje unesenog emaila iz forme
    $email = $_POST["email"];

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

    // Napravite SQL upit za provjeru da li postoji email u bazi
    $sql = "SELECT * FROM Korisnici WHERE Email='$email'";

    // Izvršite upit
    $result = mysqli_query($conn, $sql);

    // Provjerite da li postoji rezultat
    if (mysqli_num_rows($result) == 1) {
        // Slanje emaila s generiranim kodom za resetiranje lozinke
        try {
            $mail = new PHPMailer(true);

            // Konfiguracija SMTP servera
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Promijenite u svoj SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'klinikabosna@gmail.com'; // Promijenite u svoj email
            $mail->Password = 'sifra'; // Promijenite u svoju lozinku
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Postavljanje informacija o primatelju, pošiljatelju i sadržaju emaila
            $mail->setFrom('klinikabosna@gmail.com', 'Klinika Bosna'); // Promijenite u svoj email i ime
            $mail->addAddress($email); // Dodajte primatelja
            $mail->Subject = 'Resetiranje lozinke'; // Naslov emaila

            // Generiranje koda za resetiranje lozinke
            $reset_code = generatePasswordCode(); // Generiranje slučajnog koda (koristite svoju funkciju)
            $_SESSION['reset_code'] = $reset_code;
            $_SESSION['reset_email'] = $email; // Dodavanje emaila u sesiju
            // Postavljanje tijela poruke s generiranim kodom za resetiranje lozinke
            $mail->Body = 'Vaš kod za resetiranje lozinke je: ' . $reset_code;

            // Slanje emaila
            $mail->send();

            // Preusmjeravanje korisnika na stranicu za provjeru koda
            header("Location: kod_password.php");
            exit();
        } catch (Exception $e) {
            echo 'Poruka o grešci prilikom slanja emaila: ', $mail->ErrorInfo;
        }
    } else {
        // Ako email nije pronađen u bazi, postavi poruku o grešci
        $error_message = "Uneseni email nije pronađen u našoj bazi.";
    }
}

// Funkcija za generiranje slučajnog koda za resetiranje lozinke
function generatePasswordCode($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, $max)];
    }
    return $code;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="img/favicon.png">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <title>Zaboravio sam šifru</title>
	 <style>
        .universal-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            max-height: 30vh; /* Ograničavanje visine slike kako bi se spriječilo preklapanje s ostalim sadržajem */
        }
    </style>
</head>
<body>
    <!-- Main Container -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <!-- GIF ili slika -->
        <div class="container-fluid">
            <img src="img/sifra.gif" alt="GIF" class="universal-image">
        </div>
        <!-- Right Box -->
        <div class="col-md-6 right-box">
            <div class="row align-items-center">
                <div class="header-text mb-4">
                    <h2>Zaboravio sam šifru</h2>
                </div>
                <?php if(isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                <?php if(isset($success_message) && !empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-lg bg-light fs-6" placeholder="Email adresa" name="email" required>
                    </div>
                    <div class="input-group mb-1">
                        <button type="submit" class="btn btn-lg btn-primary w-100 fs-6">Pošalji kod za resetovanje šifre</button>
                    </div>
                </form>
                <div class="row">
                    <small><a href="login.php">Povratak na prijavu</a></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-tuZV3ta2uuP5H6k9SRmK+95CzKqL1W7/CGpv+aCrt2GhcYb+l0lWywpZ+9v+dAIf" crossorigin="anonymous"></script>
</body>



</html>
