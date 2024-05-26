<?php
session_start();

// Provjera da li je korisnik već unio ispravan kod za resetiranje lozinke
if (!isset($_SESSION['reset_code']) || empty($_SESSION['reset_code'])) {
    // Ako nije, preusmjerite korisnika na stranicu za unos koda
    header("Location: password-recovery.php");
    exit();
}


// Provjera da li je korisnik kliknuo na dugme za potvrdu koda
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unesenog koda iz forme
    $entered_code = $_POST["entered_code"];

    // Provjera je li uneseni kod jednak kodu u sesiji
    if ($entered_code === $_SESSION['reset_code']) {
        // Ispravan kod, preusmjerite korisnika na stranicu za promjenu lozinke
        header("Location: new-password.php");
        exit();
    } else {
        // Neispravan kod, prikažite poruku o grešci
        $error_message = "Neispravan kod. Molimo pokušajte ponovo.";
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
    <link rel="stylesheet" href="2fa-style.css">
    <title>Unos koda</title>
    <link rel="icon" href="img/favicon.png">
</head>
<body>
    <div class="container">
        <br>
        <div class="row">
            <div class="col-lg-5 col-md-7 mx-auto my-auto">
                <div class="card">
                    <div class="card-body px-lg-5 py-lg-5 text-center">
                        <img src="img/code.gif" class="rounded-circle avatar-lg img-thumbnail mb-4" alt="profile-image">
                        <h2 class="text-info">Unos koda</h2>
                        <p class="mb-4">Molimo unesite 2FA kod koji ste primili putem emaila.</p>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <?php if (isset($error_message)) { ?>
                                <p style="color: red;"><?php echo $error_message; ?></p>
                            <?php } ?>
                            <div class="mb-3">
                                <label for="entered_code" class="form-label">Kod:</label>
                                <input type="text" class="form-control" id="entered_code" name="entered_code" required>
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
