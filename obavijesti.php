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

$user_id = $_SESSION['id'];

// Dohvaćanje obavijesti za trenutnog korisnika
$sql = "SELECT * FROM Obavijesti WHERE ID_korisnika = ? ORDER BY Datum DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
$conn->close();

// Dodavanje koda za navbar iz landing.php
$welcome_message = "<p class='mb-0'>Nisi logiran!</p>";
$loginLink = '<li><a href="login.php">Prijava</a></li>';
$registerLink = '<li><a href="registracija.php">Registracija</a></li>';
$dashboard_link = '';

if (isset($_SESSION['ime']) && isset($_SESSION['prezime'])) {
    $ime = $_SESSION['ime'];
    $prezime = $_SESSION['prezime'];

    // Ponovno uspostavljanje veze s bazom podataka za dohvaćanje korisničkih informacija
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
    }

    $sql = "SELECT ID_korisnika, Tip_korisnika FROM Korisnici WHERE Ime = '$ime' AND Prezime = '$prezime'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row["ID_korisnika"];
        $_SESSION['user_id'] = $user_id;
        $tip_korisnika = $row["Tip_korisnika"];

        $welcome_message = "<p style='font-family: Poppins, sans-serif; color: #222529; font-size: 18px;'>Dobrodošao, <span style='color: #29ADB2;'>$ime $prezime</span>!</p>";

        if ($tip_korisnika == 'admin' || $tip_korisnika == 'glavni doktor' || $tip_korisnika == 'glavni medicinski tehničar/sestra') {
            $dashboard_link = '<li><a href="dashboard/dashboard.php">Dashboard</a></li>';
        }
    }
    $conn->close();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Obavijesti</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="stil-landing.css">
        <link rel="stylesheet" href="navstyle.css">
        <link rel="stylesheet" href="icofont.css">




    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        .container {
            margin-top: 50px;
        }
        .notification-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .notification-card h5 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .notification-card p {
            margin: 0;
            font-size: 14px;
        }
        .notification-card .date {
            font-size: 12px;
            color: gray;
        }
        .notification-card .badge-new {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
        .notification-card .btn-group {
            position: absolute;
            top: 20px;
            right: 20px;
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
                                            <li><a href="landing.php">Početna</a>
                                            </li>
                                            <li class="active"><a href="obavijesti.php">Obavijesti </a></li>
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










    <div class="container">
        <h3 class="mb-4">Obavijesti</h3>
        <?php if (empty($notifications)): ?>
            <p>Nema novih obavijesti.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card" id="notification-<?= $notification['ID_obavijesti'] ?>">
                    <h5><?= htmlspecialchars_decode($notification['Poruka']) ?></h5>
                    <p class="date"><?= htmlspecialchars($notification['Datum']) ?></p>
                    <?php if (!$notification['Procitano']): ?>
                        <span class="badge-new">Nova</span>
                    <?php endif; ?>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="markAsRead(<?= $notification['ID_obavijesti'] ?>)">Označi kao pročitano</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(<?= $notification['ID_obavijesti'] ?>)">Izbriši</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function markAsRead(notificationId) {
            $.post('mark_notification_read.php', { id: notificationId }, function(response) {
                if (response.success) {
                    $('#notification-' + notificationId + ' .badge-new').remove();
                } else {
                    alert('Greška prilikom označavanja obavijesti kao pročitane.');
                }
            }, 'json');
        }

        function deleteNotification(notificationId) {
            if (confirm('Da li ste sigurni da želite izbrisati ovu obavijest?')) {
                $.post('delete_notification.php', { id: notificationId }, function(response) {
                    if (response.success) {
                        $('#notification-' + notificationId).remove();
                    } else {
                        alert('Greška prilikom brisanja obavijesti.');
                    }
                }, 'json');
            }
        }
    </script>
</body>
</html>
