<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Uključivanje PHPMailer autoloadera
require 'vendor/autoload.php';

// Provjera da li je korisnik prijavljen i očekuje unos 2FA koda
if (!isset($_SESSION['twofa_pending']) || !isset($_SESSION['email'])) {
    // Ako korisnik nije prijavljen ili nije označen za unos 2FA koda, preusmjeri ga na stranicu za prijavu
    header("Location: login.php");
    exit();
}

// Provera da li je korisnik kliknuo na dugme za slanje 2FA koda
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unesenog 2FA koda iz forme
    $twofa_code = $_POST["twofa_code"];

    // Provjera ispravnosti 2FA koda
    if ($twofa_code === $_SESSION['twofa_code']) {
        // Ispravan 2FA kod, označite korisnika kao prijavljenog i preusmjerite ga na početnu stranicu
        unset($_SESSION['twofa_pending']); // Uklonite označavanje korisnika kao očekujućeg 2FA koraka

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

        // Napravite SQL upit za dohvaćanje korisničkih podataka na temelju emaila iz sesije
        $email = $_SESSION['email'];
        $sql = "SELECT * FROM Korisnici WHERE Email='$email'";

        // Izvršite upit
        $result = mysqli_query($conn, $sql);

        // Provjerite da li postoji rezultat
        if (mysqli_num_rows($result) == 1) {
            // Ako postoji, dohvatite korisničke podatke
            $row = mysqli_fetch_assoc($result);

            // Postavite identifikaciju korisnika u sesiju
            $_SESSION['id'] = $row['ID_korisnika'];
            $_SESSION['ime'] = $row['Ime'];
            $_SESSION['prezime'] = $row['Prezime'];

            // Preusmjerite korisnika na početnu stranicu
            header("Location: landing.php");
            exit();
        } else {
            // Ako ne postoji rezultat, prikažite poruku o grešci ili poduzmite odgovarajuće radnje
            echo "Greška pri dohvaćanju korisničkih podataka.";
        }
    } else {
        // Neispravan 2FA kod, prikažite poruku o grešci
        $error_message = "Neispravan 2FA kod. Molimo pokušajte ponovo.";
    }
}

// Slanje emaila s 2FA kodom
try {
    $mail = new PHPMailer(true);

    // Konfiguracija SMTP servera
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Promijenite u svoj SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'klinikabosna@gmail.com'; // Promijenite u svoj email
    $mail->Password = 'fgawglsgznwnbbdo'; // Promijenite u svoju lozinku
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Postavljanje informacija o primatelju, pošiljatelju i sadržaju emaila
    $mail->setFrom('klinikabosna@gmail.com', 'Klinika Bosna'); // Promijenite u svoj email i ime
    $mail->addAddress($_SESSION['email']); // Dodajte primatelja, ovdje koristimo email iz sesije
    $mail->Subject = '2FA Kod'; // Naslov emaila

    // Generiranje novog 2FA koda
    function generateRandomCode($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUWXYZ';
        $code = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $max)];
        }
        return $code;
    }

    $twofa_code = generateRandomCode(); // Ovdje zamijenite funkcijom koja generira slučajne kodove

    // Postavljanje 2FA koda u sesiju
    $_SESSION['twofa_code'] = $twofa_code;

    $mail->Body = 'Vaš 2FA kod je: ' . $twofa_code; // Sadržaj emaila

    // Slanje emaila
    $mail->send();
    echo '';
} catch (Exception $e) {
    echo 'Poruka o grešci prilikom slanja emaila: ', $mail->ErrorInfo;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="2fa-style.css">
    <title>Unos 2FA koda</title>
    <link rel="icon" href="img/favicon.png">
</head>
<body>
    <div class="container">
    <br>
    <div class="row">
        <div class="col-lg-5 col-md-7 mx-auto my-auto">
            <div class="card">
                <div class="card-body px-lg-5 py-lg-5 text-center">
                    <img src="img/shield.png" class="rounded-circle avatar-lg img-thumbnail mb-4" alt="profile-image">
                    <h2 class="text-info">2FA Sigurnost</h2>
                    <p class="mb-4">Email s 2FA kodom je poslan. Provjerite svoj inbox.</p>
					
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <?php if (isset($error_message)) { ?>
                        <p style="color: red;"><?php echo $error_message; ?></p>
                    <?php } ?>
                    <div class="mb-3">
                        <label for="twofa_code" class="form-label">Kod:</label>
                        <input type="text" class="form-control" id="twofa_code" name="twofa_code">
                    </div>
                    <div class="mb-3">
                        <input type="submit" class="btn btn-primary w-100" value="Potvrdi">
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-tuZV3ta2uuP5H6k9SRmK+95CzKqL1W7/CGpv+aCrt2GhcYb+l0lWywpZ+9v+dAIf" crossorigin="anonymous"></script>
</body>
</html>
