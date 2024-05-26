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

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Provjera novih obavijesti za trenutnog korisnika
$novih_obavijesti = false;
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $sql = "SELECT COUNT(*) AS novih FROM obavijesti WHERE Procitano = 0 AND ID_korisnika = $user_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['novih'] > 0) {
            $novih_obavijesti = true;
        }
    }
}

$user_id = $_SESSION['id'];
// Resetovanje poruka na početku svakog zahteva koji doseže ovaj deo koda
$_SESSION['success_message'] = '';
$_SESSION['error_message'] = '';

// Get current user data to use in the form and to check the user type
$sql_get_user_data = "SELECT * FROM Korisnici WHERE ID_korisnika='$user_id'";
$result_user_data = mysqli_query($conn, $sql_get_user_data);
$row = mysqli_fetch_assoc($result_user_data);

$dashboard_link = '';
if ($row) {
    $tip_korisnika = trim($row["Tip_korisnika"]); // Uklanja prazne znakove

    if ($tip_korisnika == 'admin' || $tip_korisnika == 'glavni doktor' || $tip_korisnika == 'glavni medicinski tehničar/sestra') {
        $dashboard_link = '<li><a href="dashboard/dashboard.php">Dashboard</a></li>';
    }
}

// Provera da li je korisnik kliknuo na dugme za ažuriranje profila
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $twofa_enabled = isset($_POST['twofa_enabled']) ? 1 : 0;

    // Provera da li je korisnik uneo novu šifru
    if (!empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
        if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $_SESSION['error_message'] = 'Oba polja za novu šifru moraju biti popunjena.';
        } else {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Validacija nove šifre samo ako je korisnik uneo novu šifru
            $password_errors = [];
            if (strlen($new_password) < 8) {
                $password_errors[] = 'Šifra mora imati najmanje 8 znakova.';
            }
            if (!preg_match('/[0-9]/', $new_password)) {
                $password_errors[] = 'Šifra mora sadržavati barem jedan broj.';
            }
            if (!preg_match('/\W/', $new_password)) {
                $password_errors[] = 'Šifra mora sadržavati barem jedan specijalni znak.';
            }

            // Provera protiv istorije šifara
            $sql_last_passwords = "SELECT Password FROM passwordhistory WHERE ID_korisnika='$user_id' ORDER BY ID DESC LIMIT 4";
            $result_last_passwords = mysqli_query($conn, $sql_last_passwords);
            if (!$result_last_passwords) {
                die('Query failed: ' . mysqli_error($conn));
            }
            $last_passwords = array();
            while ($row_password = mysqli_fetch_assoc($result_last_passwords)) {
                $last_passwords[] = $row_password['Password'];
            }

            // Proveri da li je nova šifra jedna od poslednjih 4 korištene šifre (hashovane)
            foreach ($last_passwords as $last_password) {
                if (password_verify($new_password, $last_password)) {
                    $password_errors[] = 'Nova šifra ne može biti jedna od posljednjih 4 korištene šifre.';
                    break;
                }
            }

            if ($new_password !== $confirm_password) {
                $password_errors[] = 'Nova šifra se ne podudara s potvrdom nove šifre.';
            }

            if (count($password_errors) > 0) {
                $_SESSION['error_message'] = implode(" ", $password_errors);
            } else {
                // Ako nema grešaka, ažuriraj šifru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_password = "UPDATE Korisnici SET Password='$hashed_password' WHERE ID_korisnika='$user_id'";
                if (mysqli_query($conn, $sql_update_password)) {
                    $_SESSION['success_message'] = 'Šifra uspešno ažurirana!';
                    
                    // Dodaj novu šifru u istoriju šifara
                    $sql_insert_password_history = "INSERT INTO passwordhistory (ID_korisnika, Password, CreatedAt) VALUES ('$user_id', '$hashed_password', NOW())";
                    if (!mysqli_query($conn, $sql_insert_password_history)) {
                        $_SESSION['error_message'] = 'Greška prilikom dodavanja šifre u istoriju: ' . mysqli_error($conn);
                    }
                } else {
                    $_SESSION['error_message'] = 'Greška prilikom ažuriranja šifre: ' . mysqli_error($conn);
                }
            }
        }
    }

    // Ažuriranje ostalih informacija profila
    $sql_update_profile = "UPDATE Korisnici SET Ime='$username', Email='$email', Telefon='$telefon', twofa_enabled='$twofa_enabled' WHERE ID_korisnika='$user_id'";
    if (mysqli_query($conn, $sql_update_profile) && empty($_SESSION['error_message'])) {
        $_SESSION['success_message'] = 'Profil uspešno ažuriran!';
        
        // Update session variables
        $_SESSION['ime'] = $username;
    } else {
        $_SESSION['error_message'] = 'Greška prilikom ažuriranja profila:<br> Provjerite da li Vaša šifra se sastoji od:<br> - 8 karaktera<br> - Sadrži bar 1 specijalni znak<br> - Sadrži bar 1 broj' . mysqli_error($conn);
    }

    // Refresh user data after update
    $result_user_data = mysqli_query($conn, $sql_get_user_data);
    $row = mysqli_fetch_assoc($result_user_data);
}

if (isset($_SESSION['ime']) && isset($_SESSION['prezime'])) {
    $ime = $_SESSION['ime'];
    $prezime = $_SESSION['prezime'];
    $welcome_message = "<p style='font-family: Poppins, sans-serif; color: #222529; font-size: 18px;'>Dobrodošao, <span style='color: #29ADB2;'>$ime $prezime</span>!</p>";
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="img/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="navstyle.css">
    <link rel="stylesheet" href="icofont.css">
    <link rel="stylesheet" href="profile-style.css">
    <title>Profil</title>

	<style>
		.show-password {
    position: relative;
    margin-left: 220px; /* Pomjeramo element unazad kako bi bio iznad input polja */
    font-size: 12px;
    color: blue; /* Promijenite boju po potrebi */
    cursor: pointer;
    z-index: 1; /* Postavljamo visok z-index kako bi bio iznad input polja */
	 font-weight: normal;
}

input[type="password"] {
    padding-right: 60px; /* Osigurava dovoljno mjesta za tekst "Prikaži šifru" */
    z-index: 0; /* Postavljamo niski z-index kako bi bio ispod teksta "Prikaži šifru" */
}


/* Stil za dugme "Obriši nalog" */
#delete-account {
    min-width: 150px; /* Minimalna širina dugmeta */
    margin-top: 10px; /* Razmak između dugmeta i forme */
}

/* Prilagođeni stil za formu */
.form-floating {
    margin-bottom: 15px; /* Razmak između polja forme */
}

/* Stil za fleksibilni kontejner koji sadrži formu i dugme */
.d-flex {
    flex-wrap: wrap; /* Omogućava zamotavanje elemenata unutar kontejnera */
}
.notification-dot {
        height: 12px;
        width: 12px;
        background-color: #E3170A;
        border-radius: 50%;
        display: inline-block;
        margin-left: 5px;
        position: relative;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.5);
            opacity: 0.5;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

	</style>

</head>
<body>

		<!-- Header Area -->
<header class="header">
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-5 col-12">
                    <!-- Contact -->
                    <ul class="top-link">
                        <li><a href="#">O nama</a></li>
                        <li><a href="#">Doktori</a></li>
                        <li><a href="#">Kontakt</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                    <!-- End Contact -->
                </div>
                <div class="col-lg-6 col-md-7 col-12">
                    <!-- Top Contact -->
                    <ul class="top-contact">
                        <li><i class="bi bi-telephone-fill"></i>+387 60 301 2288</li>
                        <li><i class="bi bi-envelope-fill"></i><a href="mailto:klinikabosna@gmail.com">štatrebaš@klinikabosna.ba</a></li>
                    </ul>
                    <!-- End Top Contact -->
                </div>
				<!-- Welcome message -->
    <div class="welcome-message text-center"> <!-- Dodana klasa text-center -->
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php echo $welcome_message; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End Welcome message -->
            </div>
			
        </div>
		
    </div>
    <!-- End Topbar -->
    
    

<!-- End Header Area -->



<!-- End Header Area -->
			
			<!-- Header Inner -->
			<div class="header-inner">
				<div class="container">
					<div class="inner">
						<div class="row">
							<div class="col-lg-3 col-md-3 col-12">
								<!-- Start Logo -->
								<div class="logo">
									<a href="landing.php"><img src="img\logo-transparent.png" alt="#"></a>
								</div>
								<!-- End Logo -->
								<!-- Mobile Nav -->
								<div class="mobile-nav"></div>
								<!-- End Mobile Nav -->
							</div>
							<div class="col-lg-7 col-md-9 col-12">
								<!-- Main Menu -->
								<div class="main-menu">
									<nav class="navigation">
										<ul class="nav menu">
											<li><a href="landing.php">Početna</a>
											</li>
											<li><a href="obavijesti.php">Obavijesti 
                                            <?php if ($novih_obavijesti): ?>
                                            <span class="notification-dot"></span>
                                        <?php endif; ?>
                                            </a></li>
											<li><a href="#">Doktori</a></li>

											</li>
											<li><a href="#">Kontakt</a>
											</li>
											
											<li class="active"><a>Korisnik <i class="icofont-rounded-down"></i></a >
											<ul class="dropdown">
                                            <?php if (!isset($_SESSION['id'])) { ?>
                <li><a href="login.php">Prijava</a></li>
                <li><a href="registracija.php">Registracija</a></li>
            <?php } ?>
													
													<li><a href="odjava.php">Odjavi se</a></li>
									
												</ul>
												
												<!-- <li><a href="#">Dashboard</a> -->
												<?php echo $dashboard_link; ?>
									
											</li>
												
										</ul>
										
										
										
									</nav>
								</div>
								<!--/ End Main Menu -->
							</div>
							<div class="col-lg-2 col-12">
								<div class="get-quote">
									<a href="landing.php" class="btn">Zakaži termin</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--/ End Header Inner -->
		</header>
		<!-- End Header Area -->

<br>
<br>



<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050"></div>

<div class="container">
        <div class="row">
            <div class="col-lg-16">
                

                <!-- Forma za uređivanje profila -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <h3>Edit profile</h3>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Ime" value="<?php echo $row['Ime']; ?>">
                        <label for="username">Ime</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo $row['Email']; ?>">
                        <label for="email">Email</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nova šifra">
                        <label for="new_password">Nova šifra</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Potvrdi novu šifru">
                        <label for="confirm_password">Potvrdi novu šifru</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="telefon" name="telefon" placeholder="Telefon" value="<?php echo $row['Telefon']; ?>">
                        <label for="telefon">Telefon</label>
                    </div>
                    <div class="mb-3">
                        <label for="twofa_enabled" class="form-label">Omogući 2-Factor Authentication</label>
                        <input type="checkbox" id="twofa_enabled" name="twofa_enabled" <?php if ($row['twofa_enabled'] == 1) echo 'checked'; ?>>
                    </div>

                    <button type="button" onclick="goBack()" class="btn" style="background-color: #b7b8b8; color: white;">Nazad</button>
                    <button type="submit" class="btn" style="background-color: #67adb2; color: white;">Ažuriraj profil</button>
               <!-- <button type="button" class="btn btn-danger align-self-end mt-lg-0 mt-3" id="delete-account">Obriši nalog</button> -->
                </form>
                
            </div>
           

<!-- Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAccountModalLabel">Potvrda Brisanja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Da li ste sigurni da želite trajno obrisati vaš nalog? Ova akcija ne može biti poništena.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Odustani</button>
        <button type="button" class="btn btn-danger" id="confirmDelete" style="background-color: gray;">Obriši nalog</button>
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

    function goBack() {
        window.history.back();
    }

</script>


<!-- Toast Container -->
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050"></div>

<script>


function showToast(message, success = true) {
    var toastHTML = `<div class="toast align-items-center text-white ${success ? 'bg-success' : 'bg-danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="7000">
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>`;

    var toastContainer = document.getElementById('toast-container');
    toastContainer.innerHTML += toastHTML;
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl);
    });
    toastList.forEach(toast => toast.show());
}

<?php
if ($_SESSION['success_message']) {
    echo "showToast('".$_SESSION['success_message']."', true);";
}
if ($_SESSION['error_message']) {
    echo "showToast('".$_SESSION['error_message']."', false);";
}
?>


    // DELETE ACCOUNT BUTTON
    document.getElementById('delete-account').addEventListener('click', function() {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    deleteModal.show();
});

document.getElementById('confirmDelete').addEventListener('click', function() {
    window.location.href = 'delete_account.php';
});




</script>






</body>
</html>
