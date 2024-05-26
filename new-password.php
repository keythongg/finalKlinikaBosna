<?php
session_start();

// Postavljanje početne vrijednosti za poruku o uspjehu i grešci
$success_message = "";
$error_message = "";

// Provera da li je korisnik kliknuo na dugme za promenu šifre
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unesenih šifri iz forme
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Provjera da li su obe šifre iste
    if ($new_password === $confirm_password) {
        // Šifre su iste, promijenite šifru u bazi podataka

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

        // Priprema upita za promenu šifre
        $email = $_SESSION['reset_email'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hashiramo šifru
        $sql = "UPDATE Korisnici SET Password='$hashed_password' WHERE Email='$email'";

        // Izvršavanje upita
        if (mysqli_query($conn, $sql)) {
            // Uspješno promenjena šifra, prikaži poruku o uspjehu
            $success_message = "Uspješno ste promijenili šifru.";
        } else {
            // Greška pri promeni šifre, prikaži poruku o grešci
            $error_message = "Greška pri promeni šifre: " . mysqli_error($conn);
        }
    } else {
        // Šifre se ne podudaraju, prikaži poruku o grešci
        $error_message = "Nove šifre se ne podudaraju. Molimo pokušajte ponovo.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="2fa-style.css">
    <title>Nova šifra</title>
    <link rel="icon" href="img/favicon.png">
	
	<style>
		.show-password {
    position: relative;
    
    font-size: 12px;
    color: blue; 
    cursor: pointer;
    z-index: 1; 
	 font-weight: normal;
	 
}

input[type="password"] {
    padding-right: 20px; 
    z-index: 0; 
}


	</style>
</head>
<body>
    <div class="container">
        <br>
        <div class="row">
            <div class="col-lg-5 col-md-7 mx-auto my-auto">
                <div class="card">
                    <div class="card-body px-lg-5 py-lg-5 text-center">
                        <img src="img/shield.png" class="rounded-circle avatar-lg img-thumbnail mb-4" alt="profile-image">
						
                        <h2 class="text-info">Nova šifra</h2>
                        <p class="mb-4">Unesite novu šifru i potvrdite je.</p>
                        <?php if (!empty($success_message)) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                            <script>
                                // Automatsko preusmjeravanje na login nakon 3 sekunde
                                setTimeout(function() {
                                    window.location.href = "login.php";
                                }, 3000);
                            </script>
                        <?php } ?>
                        <?php if (isset($error_message)) { ?>
                            <p style="color: red;"><?php echo $error_message; ?></p>
                        <?php } ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <div class="mb-3">
								<a href="#" id="show-password-toggle" class="show-password">Show password</a><br><br>
                                 <label for="new_password">Nova šifra:</label><br>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label" >Potvrdi novu šifru:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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
	
	<script>
	
	var showPasswordToggle = document.getElementById('show-password-toggle');
    var passwordInputs = document.querySelectorAll('input[type="password"]');

    showPasswordToggle.addEventListener('click', function(event) {
        event.preventDefault(); // Spriječava preusmjeravanje prilikom klika na link

        passwordInputs.forEach(function(input) {
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        });

        // Promjena teksta linka iz "Show password" u "Hide password" i obrnuto
        if (showPasswordToggle.textContent === 'Show password') {
            showPasswordToggle.textContent = 'Hide password';
        } else {
            showPasswordToggle.textContent = 'Show password';
        }
    });
	
	</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-tuZV3ta2uuP5H6k9SRmK+95CzKqL1W7/CGpv+aCrt2GhcYb+l0lWywpZ+9v+dAIf" crossorigin="anonymous"></script>
</body>
</html>
