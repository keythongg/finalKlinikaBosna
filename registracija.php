<?php
// Pokretanje sesije
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

// Postavljanje varijabli za čuvanje unesenih podataka
$ime = $prezime = $email = $lozinka = $datum_rodjenja = "";

// Provjera da li je korisnik kliknuo na dugme za registraciju
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unetih podataka iz forme
    $_SESSION['ime'] = $_POST["ime"];
    $_SESSION['prezime'] = $_POST["prezime"];
    $_SESSION['email'] = $_POST["email"];
    $_SESSION['datum_rodjenja'] = $_POST["datum_rodjenja"];
    
    $ime = $_SESSION['ime'];
    $prezime = $_SESSION['prezime'];
    $email = $_SESSION['email'];
    $lozinka = $_POST["password"];
    $datum_rodjenja = $_SESSION['datum_rodjenja'];

    // Provjera ispravnosti formata emaila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger" role="alert">Neispravan format emaila.</div>';
    } else if (strlen($lozinka) < 8 || !preg_match('/[0-9]/', $lozinka) || !preg_match('/\W/', $lozinka)) {
        echo '<div class="alert alert-danger" role="alert">Lozinka mora imati minimum 8 znakova, uključujući brojeve i specijalne znakove.</div>';
        $lozinka = "";
    } else {
        $email_check_query = "SELECT * FROM Korisnici WHERE Email='$email' LIMIT 1";
        $result = mysqli_query($conn, $email_check_query);
        if (mysqli_fetch_assoc($result)) {
            echo '<div class="alert alert-danger" role="alert">Korisnik s tim emailom već postoji.</div>';
        } else {
            // Hashovanje lozinke
            $hashed_password = password_hash($lozinka, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Korisnici (Ime, Prezime, Email, Password, Datum_rodjenja, Tip_korisnika) VALUES ('$ime', '$prezime', '$email', '$hashed_password', '$datum_rodjenja', 'obični korisnik')";
            if (mysqli_query($conn, $sql)) {
                echo '<div class="alert alert-success" role="alert">Uspješno ste se registrovali.</div>';
                session_unset(); // Prazni sve sesija varijable
                sleep(3);
                header("Location: login.php");
                exit();
            } else {
                echo '<div class="alert alert-danger" role="alert">Greška prilikom registracije: ' . mysqli_error($conn) . '</div>';
            }
        }
    }
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
    <title>Register</title>
	<link rel="icon" href="img/favicon.png">
</head>
<body>
    <!----------------------- Main Container -------------------------->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <!----------------------- Login Container -------------------------->
        <!--<div class="row border rounded-5 p-3 bg-white shadow box-area">-->
        <!--------------------------- Left Box ----------------------------->
        <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #1b1b42;">
            <a href="landing.php"><div class="featured-image mb-3">
            <img src="img\logo-bg.png" class="img-fluid" style="width: 250px;">
           </div></a>
       </div> 
        <!-------------------- ------ Right Box ---------------------------->
       <div class="col-md-6 right-box">
          <div class="row align-items-center">
                <div class="header-text mb-4">
                     <h2>Registracija na servis</h2>
                </div>
				<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" placeholder="Ime" name="ime" value="<?php echo isset($_SESSION['ime']) ? $_SESSION['ime'] : ''; ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" placeholder="Prezime" name="prezime" value="<?php echo isset($_SESSION['prezime']) ? $_SESSION['prezime'] : ''; ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-lg bg-light fs-6" placeholder="Email" name="email" required value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control form-control-lg bg-light fs-6" placeholder="Password" name="password" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="date" class="form-control form-control-lg bg-light fs-6" placeholder="Datum rođenja" name="datum_rodjenja" value="<?php echo isset($_SESSION['datum_rodjenja']) ? $_SESSION['datum_rodjenja'] : ''; ?>" required>
                    </div>
                    <div class="input-group mb-3 d-flex justify-content-between">
                        <div class="input-group mb-1">
                            <button class="btn btn-lg btn-primary w-100 fs-6" type="submit">Register</button>
                        </div>
                        <div class="row">
                            <small>Already a member? <a href="login.html">Sign In</a></small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
