<?php
session_start();



// Povezivanje sa bazom podataka
$servername = "localhost";
$username = "admin";
$password = "admin";
$database = "klinika_bosna";

// Povezivanje na bazu podataka
$conn = mysqli_connect($servername, $username, $password, $database);

// Provjera konekcije
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Inicijalizacija ili provjera broja pokušaja prijave
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$current_time = time();
$lockout_time = 1 * 60; //  minuta u sekundama

// Ako je broj pokušaja veći od dopuštenog i prošlo je manje od 15 minuta od zadnjeg pokušaja
if ($_SESSION['login_attempts'] > 5 && ($current_time - $_SESSION['last_attempt_time']) < $lockout_time) {
    die('Previše neuspješnih pokušaja prijave. Molimo pokušajte ponovno kasnije.');
}

// Provjera da li je korisnik kliknuo na dugme za prijavu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unetih podataka iz forme
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    // Priprema SQL upita
    $stmt = $conn->prepare("SELECT * FROM Korisnici WHERE Email = ?");
    $stmt->bind_param("s", $email);
    
    // Izvršavanje SQL upita
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Korisnik je pronađen, provjerite šifru
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            // Šifra je tačna
            if ($row['twofa_enabled'] == 1) {
                // Ako je omogućen 2FA, preusmjeri korisnika na stranicu za unos 2FA koda
                $_SESSION['twofa_pending'] = true;
                $_SESSION['email'] = $email;
                header("Location: unos_2fa.php");
                exit();
            } else {
                // Ako nije omogućen 2FA, prijavi korisnika i preusmjeri ga na početnu stranicu
                $_SESSION['id'] = $row['ID_korisnika'];
                $_SESSION['email'] = $row['Email'];
                $_SESSION['ime'] = $row['Ime'];
                $_SESSION['prezime'] = $row['Prezime'];
                // Resetiraj broj pokušaja prijave nakon uspješne prijave
                $_SESSION['login_attempts'] = 0;
                header("Location: landing.php");
                exit();
            }
        } else {
            // Prikazivanje poruke o neuspješnoj prijavi
            echo '<div class="alert alert-danger" role="alert">Pogrešno korisničko ime ili lozinka.</div>';
            // Povećaj broj pokušaja prijave i postavi vrijeme zadnjeg pokušaja
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = $current_time;
        }
    } else {
        // Prikazivanje poruke o neuspješnoj prijavi
        echo '<div class="alert alert-danger" role="alert">Pogrešno korisničko ime ili lozinka.</div>';
        // Povećaj broj pokušaja prijave i postavi vrijeme zadnjeg pokušaja
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = $current_time;
    }

    // Zatvaranje izjave
    $stmt->close();
}

// Provjera kolačića za "Zapamti me" prilikom učitavanja stranice
if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
    // Ako postoje kolačići, izbrišite ih
    setcookie('email', '', time() - 3600, "/");
    setcookie('password', '', time() - 3600, "/");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Login forma</title>
    <link rel="icon" href="img/favicon.png">
</head>
<body>

    <!-- Main Container -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <!-- Login Container -->
        <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #1b1b42;">
            <a href="landing.php">
                <div class="featured-image mb-3">
                    <img src="img\logo-bg.png" class="img-fluid" style="width: 250px;">
                </div>
            </a>
        </div>

        <!-- Right Box -->
        <div class="col-md-6 right-box">
            <div class="row align-items-center">
                <div class="header-text mb-4">
                    <h2>Prijava na servis</h2>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" placeholder="Email adresa" name="email">
                    </div>
                    <div class="input-group mb-1">
                        <input type="password" class="form-control form-control-lg bg-light fs-6" placeholder="Lozinka" name="password">
                    </div>
                    <div class="input-group mb-3 d-flex justify-content-between">
                         <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                            <label for="rememberMe" class="form-check-label text-secondary"><small>Zapamti me</small></label>
                        </div>
                        <div class="forgot">
                             <small><a href="password-recovery.php">Zaboravili ste lozinku?</a></small>
                        </div>
                    </div>
                    <div class="input-group mb-1">
                        <button class="btn btn-lg btn-primary w-100 fs-6">Prijavi se</button>
                    </div>
                </form>
                <div class="row">
                    <small>Nemate račun? <a href="registracija.php">Registrirajte se</a></small>
                </div>
            </div>
        </div> 

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-tuZV3ta2uuP5H6k9SRmK+95CzKqL1W7/CGpv+aCrt2GhcYb+l0lWywpZ+9v+dAIf" crossorigin="anonymous"></script>

</body>
</html>
