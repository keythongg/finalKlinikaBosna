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

// Pokretanje sesije ako već nije pokrenuta
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definiranje defaultne poruke dobrodošlice
$welcome_message = "<p class='mb-0'>Nisi logiran!</p>";

// Definisanje promenljivih za stavke navigacije
$loginLink = '<li><a href="login.php">Prijava</a></li>';
$registerLink = '<li><a href="registracija.php">Registracija</a></li>';

// Provjera da li je korisnik prijavljen
$imePrezime = '';
$email = '';
$phone = '';
$dashboard_link = '';
if (isset($_SESSION['id'])) {
    $loginLink = ''; // sakrij prijavu ako je korisnik prijavljen
    $registerLink = ''; // sakrij registraciju ako je korisnik prijavljen

    $ime = $_SESSION['ime'];
    $prezime = $_SESSION['prezime'];

    // Pošalji upit za dohvaćanje korisničkog ID-a i tipa korisnika
    $sql = "SELECT ID_korisnika, Tip_korisnika, Email, Telefon FROM Korisnici WHERE Ime = '$ime' AND Prezime = '$prezime'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row["ID_korisnika"];
        $_SESSION['user_id'] = $user_id; // Pohranite korisnički ID u sesiju
        $tip_korisnika = $row["Tip_korisnika"];
        $email = $row["Email"];
        $phone = $row["Telefon"];
        $imePrezime = "$ime $prezime";

        $welcome_message = "<p style='font-family: Poppins, sans-serif; color: #222529; font-size: 18px;'>Dobrodošao, <span style='color: #29ADB2;'>$ime $prezime</span>!</p>";

        // Postavi odgovarajući link za "Dashboard"
        if ($tip_korisnika == 'admin' || $tip_korisnika == 'glavni doktor' || $tip_korisnika == 'glavni medicinski tehničar/sestra' || $tip_korisnika == 'doktor') {
            $dashboard_link = '<li><a href="dashboard/dashboard.php">Dashboard</a></li>';
        }
    }
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
// Zatvori vezu s bazom podataka
$conn->close();
?>




    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="stil-landing.css">
        <link rel="stylesheet" href="navstyle.css">
        <link rel="stylesheet" href="icofont.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">






        <!-- Bootstrap CSS potreban za zakazivanje termina - ne radi bez toga-->
        <link rel="stylesheet" href="css/bootstrap.min.css">




        <title>Klinika Bosna - Dobrodošli!</title>
        <link rel="icon" href="img/favicon.png">


        <style>
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
                <div class="container-lg">
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
                        <div class="welcome-message text-center">
                            <!-- Dodana klasa text-center -->
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
                <div class="container-lg">
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
                                            <li class="active"><a href="#">Početna</a>
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

                                            <li><a>Korisnik <i class="icofont-rounded-down"></i></a >
											<ul class="dropdown">
                                            <?php if (!isset($_SESSION['id'])) { ?>
                <li><a href="login.php">Prijava</a></li>
                <li><a href="registracija.php">Registracija</a></li>
            <?php } ?>
                                            <li><a href="profil.php">Profil</a></li>
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
                                    <a href="#appointment" class="btn">Zakaži termin</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ End Header Inner -->
        </header>
        <!-- End Header Area -->





        <div class="container-lg">

            <section>
                <div class="container prvi">
                    <div class="row">
                        <div class="col d-flex justify-content-center align-items-start flex-column pl-2 prvi-tekst">Klinika Bosna
                            <br> Pružatelj vrhunskih usluga!
                            <p>Najbolja skrb za vaše zdravlje - Broj 1 Klinika u Bosni i Hercegovini</p>
                        </div>
                        <div class="col p-5 d-flex justify-content-end"><img src="img\klinik.png" alt="" class="prvi-slika"></div>
                    </div>
                </div>
            </section>
</div>

<div class="container-lg">
            <section>
                <div class="row">
                    <div class="col d-flex justify-content-center align-items-center flex-column">
                        <h1>Upoznajte naše stručnjake!</h1>
                        <h5>Tim vrsnih doktora</h5></div>
                </div>
                <div class="row galerija mt-5 g-3">
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d1.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Amar Hadžić</h3>
                            <p><i>Kardiolog iz Cazina</i></p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d2.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Faris Džafić</h3>
                            <p><i>Kardiolog iz Cazina</i></p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d3.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Haris Smajić</h3>
                            <p><i>Neurolog iz Cazina</i></p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src="img\d4.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Aldin Mustafić</h3>
                            <p><i>Neurolog iz Sarajeva</i></p>
                        </div>
                    </div>
                </div>

                <div class="row galerija mt-5 g-3">
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d5.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Selma Kovačević</h3>
                            <p><i>Kardiolog iz Bihaća</i></p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d6.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Adnan Mujić</h3>
                            <p><i>Kardiolog iz Sarajeva</i></p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d7.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Muhamed Hodžić</h3>
                            <p><i>Neurolog iz Sarajeva</i></p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center align-items-center flex-column py-3 px-1 doki"><img src=" img\d8.png" alt="">
                        <div class="galerija-tekst">
                            <h3>Dr. Emina Bajrić</h3>
                            <p><i>Kardiolog iz Sarajeva</i></p>
                        </div>
                    </div>
                </div>
            </section>
</div>
        



            <br>
            <br>








            <!-- ZAKAZIVANJE TERMINA DIO -->


    <!-- Start Appointment -->
    <section id="appointment" class="appointment">
        <div class="container-lg">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Zakažite termin!</h2>
                        <img src="img/section-img.png" alt="#">
                        <p>Ispunite polja koja se nalaze ispod i čekajte naš odgovor.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12">
                    <form class="form" id="appointmentForm">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <input name="name" type="text" placeholder="Ime i Prezime" value="<?php echo htmlspecialchars($imePrezime); ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <input name="email" type="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <input name="phone" type="text" placeholder="Telefon" value="<?php echo htmlspecialchars($phone); ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <select class="form-control wide velahavlemeni" id="departmentSelect" name="department" required>
                                        <option selected disabled hidden>Odjel</option>
                                        <!-- Opcije će biti dodane putem JavaScripta -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <select class="form-control wide velahavlemeni" id="serviceSelect" name="service" required>
                                        <option selected disabled hidden>Usluga</option>
                                        <!-- Opcije će biti dodane putem JavaScripta -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <select class="form-control wide velahavlemeni" id="doctorSelect" name="doctor" required>
                                        <option selected disabled hidden>Doktori</option>
                                        <!-- Opcije će biti dodane putem JavaScripta -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="form-group">
                                    <input type="time" class="form-control" id="time" name="time" required>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-12">
                                <div class="form-group">
                                    <textarea name="message" placeholder="Poruka..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-5 col-md-4 col-12">
                                <div class="form-group">
                                    <div class="get-quote">
                                        <button id="scheduleButton" type="submit" class="btn">Zakaži Termin</button>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="appointment-image">
                        <img src="img/contact-img.png" alt="#">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Appointment -->

<!-- Modal za prikaz poruka -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Poruka</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="messageModalBody">Molimo popunite sva polja.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
      </div>
    </div>
  </div>
</div>

<br><br><br><br><br><br><br><br>
<div class="recenzija-background">
<section id="recenzija-section">
 
        <section id="recenzija" class="recenzija">
            <h2>Ostavite Recenziju</h2>
            <form id="reviewForm" method="POST" action="submit_review.php">
                <div class="form-group">
                    <label for="reviewDoctorSelect"><i class="fas fa-user-md"></i> Doktor:</label>
                    <select id="reviewDoctorSelect" name="ID_doktora" required>
                        <?php
                        include 'dashboard/db_connection.php'; // Uključite vašu datoteku za povezivanje s bazom podataka

                        $sql = "SELECT Osoblje.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Lokacija.Naziv AS Lokacija 
                                FROM Osoblje 
                                JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije 
                                WHERE Osoblje.Pozicija = 'doktor' OR Osoblje.Pozicija = 'glavni doktor'";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['ID_osoblja']}'>{$row['Ime']} {$row['Prezime']} ({$row['Lokacija']})</option>";
                            }
                        }

                        $conn->close();
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rating"><i class="fas fa-star"></i> Ocjena (1-5):</label>
                    <input type="number" id="rating" name="Ocjena" min="1" max="5" required>
                </div>
                <div class="form-group">
                    <label for="comment"><i class="fas fa-comments"></i> Komentar:</label>
                    <textarea id="comment" name="Komentar" rows="4" required></textarea>
                </div>
                <button type="submit"><i class="fas fa-paper-plane send-icon"></i> Pošalji recenziju</button>
            </form>
        </section>
    
</section></div>


<!-- Toast Container -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11;">
        <!-- Toasts će biti dinamički dodani ovdje -->
    </div>
</div>






 <!-- Footer -->


    <br><br><br><br><br><br><br><br>

            <footer class="text-white pt-3 pb-3" style="background-color: #07070f;">

            <div class="container-lg">
            <div class="row">
            <div class="col-md-3">
    <img src="img/253.png" alt="Logo Klinike Bosna" width="253" height="182">
</div>

                <div class="col-md-3">
                    <h5>Linkovi</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Usluge</a></li>
                        <li><a href="#" class="text-white">O nama</a></li>
                        <li><a href="#" class="text-white">Cjenovnik</a></li>
                        <li><a href="#" class="text-white">Blog</a></li>
                        <li><a href="#" class="text-white">Kontakt</a></li>
                    </ul>
                </div>
            
                <div class="col-md-3">
                    <h5>Kontakt</h5>
                    <p>Adresa: Trg Alije Izetbegovića, Cazin</p>
                    <p>Adresa: Ceravačka brda 63, Bihać</p>
                    <p>Adresa: Ulica Obala Kulina bana, Sarajevo</p>
                    <p>Email: info@klinikabosna.ba</p>
                    <p>Telefon: +387 123 456</p>
                </div>
                <div class="col-md-3">
                    <h5>Pratite nas</h5>
                    <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <footer class="text-white" style="background-color: #67adb2; text-align: center; height: 3vh; display: flex; flex-direction: column; justify-content: space-between;">
    <p style="color: white;">&copy; 2024 Sva prava pridržana</p>
</footer>





<style> 

#recenzija-section {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    position: relative;

}

.recenzija-background {
    background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
    padding: 50px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1;

}

.recenzija button .send-icon {
    color: #fff; 
    margin-right: 8px;
}
.recenzija {
    background-color: rgba(255, 255, 255, 0.9);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    min-width: 700px;
    margin: 30px auto;
    font-family: 'Poppins', sans-serif;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.recenzija:before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background-size: cover;
    background-position: center;
    opacity: 0.1;
    z-index: 0;
    transform: rotate(30deg);
}

.recenzija h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
    z-index: 1;
    position: relative;
}

.recenzija form {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 1;
    position: relative;
}

.recenzija .form-group {
    width: 100%;
    margin-bottom: 15px;
}

.recenzija label {
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
    display: block;
    font-size: 16px;
    text-align: left;
}

.recenzija select,
.recenzija input[type="number"],
.recenzija textarea {
    width: 100%;
    padding: 12px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
}

.recenzija button {
    background-color: #0ABEFF;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.recenzija button:hover {
    background-color: #009fd1;
}

.recenzija i {
    margin-right: 8px;
    color: #555;
}

.recenzija .form-group label i {
    color: #0ABEFF;
    margin-right: 5px;
}



.footer {
    background-color: #333;
    color: #fff;
    font-size: 16px;
}

.footer h4 {
    margin-bottom: 15px;
    font-size: 18px;
}

.footer p, .footer a {
    color: #fff;

    margin-bottom: 30px;
    font-size: 22px;
}

.footer a {
    text-decoration: none;
}

.footer ul {
    list-style: none;
    padding: 0;
}

.footer ul li a {
    transition: color 0.2s;
}

.footer ul li a:hover {
    color: #aaa;
}

.footer-section {
    padding-left: 20px;
}

.footer .bi {
    font-size: 24px;
    margin-right: 10px;
    color: #fff;
}

.footer .bi:hover {
    color: #aaa;
}

.container-fluid {
    width: 100%;
    padding:0px;

}
</style>


        </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let departmentSelect = document.getElementById('departmentSelect');
    let serviceSelect = document.getElementById('serviceSelect');
    let doctorSelect = document.getElementById('doctorSelect');
    let reviewDoctorSelect = document.getElementById('reviewDoctorSelect');

    // Dohvati odjele
    if (departmentSelect) {
        fetch('dohvatiOdjele.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(department => {
                    let option = document.createElement('option');
                    option.value = department.ID;
                    option.textContent = department.Naziv;
                    departmentSelect.appendChild(option);
                });
            });
    }

    // Dohvati usluge po odjelu
    if (serviceSelect) {
        departmentSelect.addEventListener('change', function() {
            const departmentID = this.value;

            fetch(`dohvatiUslugu.php?ID_odjela=${departmentID}`)
                .then(response => response.json())
                .then(data => {
                    serviceSelect.innerHTML = '<option selected disabled hidden>Usluga</option>';
                    data.forEach(service => {
                        let option = document.createElement('option');
                        option.value = service.ID;
                        option.textContent = service.Naziv;
                        serviceSelect.appendChild(option);
                    });
                });
        });
    }

    // Dohvati doktore po odjelu
    if (doctorSelect) {
        departmentSelect.addEventListener('change', function() {
            const departmentID = this.value;

            fetch(`doktoriBazaPodataka.php?ID_odjela=${departmentID}`)
                .then(response => response.json())
                .then(data => {
                    doctorSelect.innerHTML = '<option selected disabled hidden>Doktori</option>';
                    data.forEach(doctor => {
                        let option = document.createElement('option');
                        option.value = doctor.ID;
                        option.textContent = `${doctor.Ime} ${doctor.Prezime} (${doctor.Lokacija})`;
                        doctorSelect.appendChild(option);
                    });
                });
        });
    }

    // Dohvati sve doktore za recenzije
    if (reviewDoctorSelect) {
        fetch('fetch_doctors.php')
            .then(response => response.text())
            .then(data => {
                reviewDoctorSelect.innerHTML = data;
            })
            .catch(error => console.error('Error fetching doctors:', error));
    }

    // Slanje recenzije
    document.getElementById('reviewForm').addEventListener('submit', function(event) {
        event.preventDefault();
        submitReview();
    });

    function submitReview() {
        const form = document.getElementById('reviewForm');
        const formData = new FormData(form);

        fetch('submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message, data.message.includes('uspješno poslana'));
            if (data.message.includes('uspješno poslana')) {
                form.reset();
            }
        })
        .catch(error => {
            console.error('Error submitting review:', error);
            showToast('Došlo je do greške prilikom slanja recenzije.', false);
        });
    }

    // Slanje termina
    document.getElementById('scheduleButton').addEventListener('click', function (event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append('name', document.querySelector('input[name="name"]').value);
        formData.append('email', document.querySelector('input[name="email"]').value);
        formData.append('phone', document.querySelector('input[name="phone"]').value);
        formData.append('department', document.getElementById('departmentSelect').value);
        formData.append('service', document.getElementById('serviceSelect').value);
        formData.append('doctor', document.getElementById('doctorSelect').value);
        formData.append('date', document.getElementById('date').value);
        formData.append('time', document.getElementById('time').value);
        formData.append('message', document.querySelector('textarea[name="message"]').value);

        fetch('process_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            showToast(data.includes('uspješno zakazan') ? 'Termin uspješno zakazan!' : 'Došlo je do greške prilikom zakazivanja termina.', data.includes('uspješno zakazan'));
            if (data.includes('uspješno zakazan')) {
                document.getElementById('appointmentForm').reset();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Došlo je do greške prilikom zakazivanja termina.', false);
        });
    });

    // Funkcija za prikaz toast obavijesti
    function showToast(message, success = true) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white ${success ? 'bg-success' : 'bg-danger'} border-0`;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '1050'; // Ensure the toast is on top of other elements
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        document.getElementById('toastContainer').appendChild(toast);
        const toastBootstrap = new bootstrap.Toast(toast);
        toastBootstrap.show();
        setTimeout(() => {
            toastBootstrap.hide();
            toast.remove();
        }, 3000);
    }
});





</script>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>