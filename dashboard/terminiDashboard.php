<?php

include 'check_admin_when_click_dashboard.php';
$welcome_message = "<p style='font-family: Poppins, sans-serif; color: #222529; font-size: 18px;'>Dobrodošao, <span style='color: #29ADB2;'>$ime</span>!</p>";

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}


// Dohvati korisnika iz baze podataka na osnovu sesije
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $sql = "SELECT Ime, Prezime, Tip_korisnika FROM Korisnici WHERE ID_korisnika = $user_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $ime = $user_data['Ime'];
        $prezime = $user_data['Prezime'];
        $tip_korisnika = $user_data['Tip_korisnika'];
    } else {
        die("Greška: Korisnik nije pronađen.");
    }
} else {
    die("Greška: Korisnik nije prijavljen.");
}

// Dohvati termine iz baze podataka
$appointments = [];
$sql = "
    SELECT Termini.ID_termina, Termini.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Osoblje.ID_lokacije AS OsobljeLokacija, Lokacija.Naziv AS LokacijaNaziv, Termini.ID_usluge, Usluge.Naziv_usluge, Termini.Datum, Termini.Vrijeme, Termini.Status, Termini.Napomena, Termini.ID_korisnika, Korisnici.Ime as KorisnikIme, Korisnici.Prezime as KorisnikPrezime
    FROM Termini
    LEFT JOIN Osoblje ON Termini.ID_osoblja = Osoblje.ID_osoblja
    LEFT JOIN Usluge ON Termini.ID_usluge = Usluge.ID_usluge
    LEFT JOIN Korisnici ON Termini.ID_korisnika = Korisnici.ID_korisnika
    LEFT JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Formatiranje datuma za input type="date"
        $row['Datum'] = date('Y-m-d', strtotime($row['Datum']));
        // Provjeri i postavi ID_lokacije termina ako nije postavljen
        if (!isset($row['ID_lokacije']) || $row['ID_lokacije'] == '') {
            $row['ID_lokacije'] = $row['OsobljeLokacija'];
        }
        if (!isset($row['Naziv_lokacije']) || $row['Naziv_lokacije'] == '') {
            $row['Naziv_lokacije'] = $row['LokacijaNaziv'];
        }
        $appointments[] = $row;
    }
} else {
    echo "Nema termina.";
}

// Dohvati sve lokacije
$locations = [];
$sql = "SELECT ID_lokacije, Naziv FROM Lokacija";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
}

$doctors = [];
$sql = "SELECT Ime, Prezime FROM osoblje";
$docs = $conn->query($sql);
if ($docs && $docs->num_rows > 0) {
    while ($row = $docs->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Statistika termina
$sql = "SELECT COUNT(*) AS total FROM Termini";
$total_appointments = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) AS pending FROM Termini WHERE Status = 'pending'";
$pending_appointments = $conn->query($sql)->fetch_assoc()['pending'];

$sql = "SELECT COUNT(*) AS approved FROM Termini WHERE Status = 'approved'";
$approved_appointments = $conn->query($sql)->fetch_assoc()['approved'];

// Zatvori vezu s bazom podataka
$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <title>Dashboard</title>
    <style>

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f7fa;
}
.sidebar {
    background-color: #343a40;
    color: #0ABEFF;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 1000;
}

        .main-content { 
            margin-left: 310px; 
            padding: 20px; 
            width: calc(100% - 310px); 
        }

        .table-responsive { margin-top: 20px; border-radius: 20px; }
        .table thead th { padding: 8px; background-color: #C2EFFF; border-bottom: 2px solid #ADE9FF; text-align: left; }
        .table tbody tr { background-color: #fff; border-bottom: 1px solid #dee2e6; }
        .table tbody tr:hover { background-color: #f1f3f5; }
        .table tbody td { vertical-align: middle; }
        .btn { border-radius: 4px; }
        .btn-primary{ background-color:#0ABEFF; border-color:#0ABEFF; }
        .btn-primary:hover{ background-color:#47CEFF; border-color:#47CEFF; }
        .btn-warning { background-color: transparent; border-color: transparent; }
        .btn-warning:hover { background-color: #FFC099; border-color: transparent; }
        .btn-danger { color: black; background-color: transparent; border-color: transparent; }
        .btn-danger:hover { color: black; background-color:#FA9F9E; border-color: transparent; }
        .card-title { font-size: 1.25rem; font-weight: 500; color: #0ABEFF; }
        .card-text { font-size: 2rem; color: #040F16; margin-bottom: 20px; font-weight: 600; line-height: 1.5; padding: 10px; background: linear-gradient(45deg, #f3f4f6, #eaecef); border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: transform 0.2s; }
        .card-text:hover { transform: scale(1.02); }

        .sidebar a { color: #fff; }
        .sidebar li { color: #90908E; }
        .sidebar a:hover { background-color: #D7D7D6; }
        .nav-pills .nav-link { margin-bottom: 1rem; padding: 10px 20px; border-radius: 30px; }
        .nav-pills .nav-link.active { background-color: #0ABEFF !important; color: white !important; border-radius: 50px; }
        .nav-pills .nav-link i { margin-right: 0.5rem; }
        .capitalize {
            text-transform: capitalize;
        }
        .status-pending::before {
            content: '\f017';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            color: #FFC107; /* Žuta boja za pending */
        }
        .status-approved::before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            color: #28A745; /* Zelena boja za approved */
        }
        .status-completed::before {
            content: '\f058';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            color: #17A2B8; /* Plava boja za completed */
        }
        .status-canceled::before {
            content: '\f05e';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            color: #DC3545; /* Crvena boja za canceled */
        }
        .btn-info{
            color: black; background-color: transparent; border-color: transparent; 
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-3 col-lg-2 bg-light sidebar">
            <!-- Sidebar -->
            <div class="py-4 px-3 mb-2 bg-light">
                <div class="media d-flex align-items-center">
                    <a href="../landing.php"><img src="../img/admin.webp" alt="..." width="65" class="mr-3 rounded-circle img-thumbnail shadow-sm"></a>
                    <div class="media-body" style="margin-left: 15px;">
                        <h4 class="m-0"><?php echo $ime, " ", $prezime; ?></h4>
                        <p class="font-weight-light text-muted mb-0 capitalize" ><?php echo $tip_korisnika; ?></p>
                    </div>
                </div>
            </div>
            <hr><br>
            <ul class="nav nav-pills flex-column mb-auto">
            <?php if ($tip_korisnika == 'admin') { ?>
                        <li>
                            <a href="dashboard.php" class="nav-link text-dark" >
                                <i class="fa-solid fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="zaposleniciDashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-users"></i>Zaposlenici
                            </a>
                        </li>
                        <li>
                            <a href="pacijentiDashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-hospital-user"></i> Pacijenti
                            </a>
                        </li>
                        <li>
                            <a href="terminiDashboard.php" class="nav-link active" aria-current="page">
                                <i class="fas fa-calendar-check"></i> Termini
                            </a>
                        </li>
                        <li>
                            <a href="odjeliDashboard.php" class="nav-link text-dark">
                                <i class="fas fa-building"></i> Odjeli
                            </a>
                        </li>
                    <?php } elseif ($tip_korisnika == 'glavni doktor' || $tip_korisnika == 'glavni medicinski tehničar/sestra' || $tip_korisnika == 'doktor') { ?>
                        <li>
                            <a href="dashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="pacijentiDashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-hospital-user"></i> Pacijenti
                            </a>
                        </li>
                        <li>
                            <a href="terminiDashboard.php" class="nav-link active" aria-current="page">
                                <i class="fas fa-calendar-check"></i> Termini
                            </a>
                        </li>
                        <li>
                            <a href="odjeliDashboard.php" class="nav-link text-dark">
                                <i class="fas fa-building"></i> Odjeli
                            </a>
                        </li>
                    <?php } ?>
            </ul>
            <ul class="nav nav-pills flex-column mt-auto mb-2">
                <li><a href="#" class="nav-link text-dark"><i class="fas fa-cog"></i> Postavke</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="fas fa-question-circle"></i> Pomoć</a></li>
                <li><a href="../odjava.php" class="nav-link text-dark"><i class="fas fa-sign-out-alt"></i> Odjavi se</a></li>
            </ul>
        </div>
        <div class="col-12 col-md-9 col-lg-10 main-content">
            <!-- Main Content -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Termini</h1>
 

                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">Dodaj termin</button>
            </div>
            <div class="row">
            <div class="col-md-4">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Ukupan broj termina</h5>
            <p id="total-appointments" class="card-text"><?= $total_appointments ?></p>
        </div>
    </div>
</div>
<div class="col-md">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Termini na čekanju</h5>
            <p id="pending-appointments" class="card-text"><?= $pending_appointments ?></p>
        </div>
    </div>
</div>
<div class="col-md">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Odobreni termini</h5>
            <p id="approved-appointments" class="card-text"><?= $approved_appointments ?></p>
        </div>
    </div>
</div>

            </div>



           


            <h5 class="card-title">Filter</h5>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

<!-- Kontejner za oba dropdowna -->
<div class="d-flex">

    <!-- Dropdown za filtriranje po lokaciji -->
    <div class="input-group mb-3 me-3" style="width: 320px;">
        <label class="input-group-text" for="locationFilter">Lokacija</label>
        <select class="form-select" id="locationFilter">
            <option value="all">Sve lokacije</option>
            <?php foreach ($locations as $location): ?>
                <option value="<?= htmlspecialchars($location['Naziv']) ?>"><?= htmlspecialchars($location['Naziv']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Dropdown za filtriranje po statusu -->
    <div class="input-group mb-3 me-3" style="width: 320px;">
        <label class="input-group-text" for="statusFilter">Status</label>
        <select class="form-select" id="statusFilter">
            <option value="all">Svi statusi</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="completed">Completed</option>
            <option value="canceled">Canceled</option>
        </select>
    </div>

     <!-- Dropdown za filtriranje po doktoru -->
     <div class="input-group mb-3 me-3" style="width: 320px;">
        <label class="input-group-text" for="doctorFilter">Doktori</label>
        <select class="form-select" id="doctorFilter">
            <option value="all">Svi doktori</option>
            <?php foreach ($doctors as $doctor): ?>
        <option value="<?= htmlspecialchars($doctor['Ime'] . ' ' . $doctor['Prezime']) ?>">
            <?= htmlspecialchars($doctor['Ime'] . ' ' . $doctor['Prezime']) ?>
        </option>
    <?php endforeach; ?>
        </select>
    </div>

</div>
</div>





            <!-- Tabela sa terminima -->
            <div class="table-responsive">
            <table class="table table-striped table-sm">
    <thead>
        <tr>
            <th>ID termina</th>
            <th>Ime i prezime osoblja</th>
            <th>Naziv usluge</th>
            <th>Datum</th>
            <th>Vrijeme</th>
            <th>Status</th>
            <th>Ime i prezime korisnika</th>
            <th>Lokacija</th>
            <th>Napomena</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($appointments as $appointment): ?>
        <tr id="appointment-<?= $appointment['ID_termina'] ?>"
            data-staff-name="<?= htmlspecialchars($appointment['Ime'] . ' ' . $appointment['Prezime']) ?>"
            data-service-name="<?= htmlspecialchars($appointment['Naziv_usluge']) ?>"
            data-user-name="<?= htmlspecialchars($appointment['KorisnikIme'] . ' ' . $appointment['KorisnikPrezime']) ?>"
            data-location-name="<?= htmlspecialchars($appointment['Naziv_lokacije']) ?>"
            data-full-note="<?= htmlspecialchars($appointment['Napomena'], ENT_QUOTES, 'UTF-8') ?>">
            <td><?= htmlspecialchars($appointment['ID_termina']) ?></td>
            <td class="staff" data-staff-id="<?= $appointment['ID_osoblja'] ?>"><?= htmlspecialchars($appointment['Ime']) . ' ' . htmlspecialchars($appointment['Prezime']) ?></td>
            <td class="service" data-service-id="<?= $appointment['ID_usluge'] ?>"><?= htmlspecialchars($appointment['Naziv_usluge']) ?></td>
            <td class="date"><?= htmlspecialchars($appointment['Datum']) ?></td>
            <td class="time"><?= htmlspecialchars($appointment['Vrijeme']) ?></td>
            <td class="status status-<?= strtolower(htmlspecialchars($appointment['Status'])) ?>"><?= htmlspecialchars($appointment['Status']) ?></td>
            <td class="user" data-user-id="<?= $appointment['ID_korisnika'] ?>"><?= htmlspecialchars($appointment['KorisnikIme']) . ' ' . htmlspecialchars($appointment['KorisnikPrezime']) ?></td>
            <td class="location" data-location-id="<?= $appointment['ID_lokacije'] ?>"><?= htmlspecialchars($appointment['Naziv_lokacije']) ?></td>
            <td class="note">
                <?php
                    $notePreview = strlen($appointment['Napomena']) > 30 ? substr($appointment['Napomena'], 0, 30) . '...' : $appointment['Napomena'];
                    echo htmlspecialchars($notePreview);
                    if (strlen($appointment['Napomena']) > 30) {
                        echo ' <a href="#" class="text-primary" onclick="showFullNoteModal(\'' . htmlspecialchars(addslashes($appointment['Napomena'])) . '\')">Pročitaj više</a>';
                    }
                ?>
            </td>
            <td>
                <button onclick="editAppointment(<?= $appointment['ID_termina'] ?>)" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi termin">
                    <i class="fa-regular fa-pen-to-square"></i>
                </button>
                <button onclick="deleteAppointment(<?= $appointment['ID_termina'] ?>)" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši termin">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
                <button onclick="sendNotification(<?= $appointment['ID_termina'] ?>)" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Pošalji obavijest">
                    <i class="fa-regular fa-envelope"></i>
                </button>
        <button onclick="addPatient(<?= $appointment['ID_termina'] ?>)" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Dodaj u pacijente">
        <i class="fa-solid fa-plus"></i>
        </button>

            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($appointments)): ?>
    <tr>
        <td colspan="10">Nema zakazanih termina.</td>
    </tr>
    <?php endif; ?>
</tbody>


</table>


            </div>
        </div>
    </div>
</div>
<!-- Modal za dodavanje termina -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAppointmentModalLabel">Dodaj novi termin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAppointmentForm" action="add_appointment.php" method="post">
                    <div class="mb-3">
                        <label for="staff" class="form-label">Osoblje:</label>
                        <select class="form-select" id="staff" name="staff" required>
                            <!-- PHP kod za dohvaćanje osoblja iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_osoblja, Ime, Prezime FROM Osoblje";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_osoblja"] . "'>" . $row["Ime"] . " " . $row["Prezime"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service" class="form-label">Usluga:</label>
                        <select class="form-select" id="service" name="service" required>
                            <!-- PHP kod za dohvaćanje usluga iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_usluge, Naziv_usluge FROM Usluge";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_usluge"] . "'>" . $row["Naziv_usluge"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Datum:</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Vrijeme:</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="user" class="form-label">Korisnik:</label>
                        <select class="form-select" id="user" name="user" required>
                            <!-- PHP kod za dohvaćanje korisnika iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_korisnika, Ime, Prezime FROM Korisnici";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_korisnika"] . "'>" . $row["Ime"] . " " . $row["Prezime"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Lokacija:</label>
                        <select class="form-select" id="location" name="location" required>
                            <!-- PHP kod za dohvaćanje lokacija iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_lokacije, Naziv FROM Lokacija";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_lokacije"] . "'>" . $row["Naziv"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Napomena:</label>
                        <textarea class="form-control" id="note" name="note"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                        <button type="submit" class="btn btn-primary">Sačuvaj promjene</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal za uređivanje termina -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAppointmentModalLabel">Uredi termin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAppointmentForm">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-3">
                        <label for="edit-staff" class="form-label">Osoblje:</label>
                        <select class="form-select" id="edit-staff" name="staff" required>
                            <!-- PHP kod za dohvaćanje osoblja iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_osoblja, Ime, Prezime FROM Osoblje";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_osoblja"] . "'>" . $row["Ime"] . " " . $row["Prezime"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-service" class="form-label">Usluga:</label>
                        <select class="form-select" id="edit-service" name="service" required>
                            <!-- PHP kod za dohvaćanje usluga iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_usluge, Naziv_usluge FROM Usluge";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_usluge"] . "'>" . $row["Naziv_usluge"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-date" class="form-label">Datum:</label>
                        <input type="date" class="form-control" id="edit-date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-time" class="form-label">Vrijeme:</label>
                        <input type="time" class="form-control" id="edit-time" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-status" class="form-label">Status:</label>
                        <select class="form-select" id="edit-status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-user" class="form-label">Korisnik:</label>
                        <select class="form-select" id="edit-user" name="user" required>
                            <!-- PHP kod za dohvaćanje korisnika iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_korisnika, Ime, Prezime FROM Korisnici";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_korisnika"] . "'>" . $row["Ime"] . " " . $row["Prezime"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-location" class="form-label">Lokacija:</label>
                        <select class="form-select" id="edit-location" name="location" required>
                            <!-- PHP kod za dohvaćanje lokacija iz baze -->
                            <?php
                            $conn = new mysqli($servername, $username, $password, $database);
                            $sql = "SELECT ID_lokacije, Naziv FROM Lokacija";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row["ID_lokacije"] . "'>" . $row["Naziv"] . "</option>";
                                }
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-note" class="form-label">Napomena:</label>
                        <textarea class="form-control" id="edit-note" name="note"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                        <button type="submit" class="btn btn-primary">Sačuvaj promjene</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal za prikaz pune napomene -->
<div class="modal fade" id="fullNoteModal" tabindex="-1" aria-labelledby="fullNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullNoteModalLabel">Puna napomena</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fullNoteContent">
                <!-- Ovdje će biti prikazan kompletan tekst napomene -->
            </div>
        </div>
    </div>
</div>


<script>

document.getElementById('addAppointmentForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Spriječiti standardno slanje forme

    var formData = new FormData(this);

    fetch('add_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Obrada odgovora kao JSON
    .then(data => {
        console.log(data); // Add this line to see the server response
        $('#addAppointmentModal').modal('hide'); // Zatvara modal nakon izvršavanja
        $('#addAppointmentModal').on('hidden.bs.modal', function (e) {
            $('.modal-backdrop').remove();  // Uklanjanje overlay-a nakon zatvaranja modala
        });
        showToast(data.message, data.success);
        if (data.success) {
            addTableRow(data.appointment); // Dodaj novi redak u tabelu
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});


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
    document.body.appendChild(toast);
    const toastBootstrap = new bootstrap.Toast(toast);
    toastBootstrap.show();
    setTimeout(() => {
        toastBootstrap.hide();
        toast.remove();
    }, 3000);
}


function deleteAppointment(id) {
    if (confirm('Da li ste sigurni da želite izbrisati termin?')) {
        fetch('delete_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('appointment-' + id);
                row.remove();
                alert('Termin je uspješno izbrisan.');
            } else {
                alert('Greška prilikom brisanja: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
function editAppointment(id) {
    const row = document.getElementById('appointment-' + id);
    if (!row) {
        console.error('No appointment row found for ID:', id);
        return;
    }

    const staffElem = row.querySelector(".staff");
    const serviceElem = row.querySelector(".service");
    const dateElem = row.querySelector(".date");
    const timeElem = row.querySelector(".time");
    const statusElem = row.querySelector(".status");
    const userElem = row.querySelector(".user");
    const locationElem = row.querySelector(".location");
    const noteElem = row.querySelector(".note");

    if (!staffElem || !serviceElem || !dateElem || !timeElem || !statusElem || !userElem || !locationElem || !noteElem) {
        console.error('Some elements are missing for appointment ID:', id);
        return;
    }

    // Retrieve full note text from data attribute
    const fullNoteText = row.getAttribute('data-full-note');

    // Log the data to ensure we are getting the correct values
    console.log('Editing appointment:', {
        id,
        staff: staffElem.dataset.staffId,
        service: serviceElem.dataset.serviceId,
        date: dateElem.textContent.trim(),
        time: timeElem.textContent.trim(),
        status: statusElem.textContent.trim(),
        user: userElem.dataset.userId,
        location: locationElem.dataset.locationId,
        note: fullNoteText
    });

    document.getElementById('edit-id').value = id;
    document.getElementById('edit-staff').value = staffElem.dataset.staffId;
    document.getElementById('edit-service').value = serviceElem.dataset.serviceId;
    document.getElementById('edit-date').value = dateElem.textContent.trim();
    document.getElementById('edit-time').value = timeElem.textContent.trim();
    document.getElementById('edit-status').value = statusElem.textContent.trim();
    document.getElementById('edit-user').value = userElem.dataset.userId;
    document.getElementById('edit-location').value = locationElem.dataset.locationId;
    document.getElementById('edit-note').value = fullNoteText;  // Set the full note text

    $('#editAppointmentModal').modal('show');
}




document.getElementById('editAppointmentForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Spriječiti standardno slanje forme

    var formData = new FormData(this);
    var id = document.getElementById('edit-id').value;
    formData.append('id', id);

    fetch('edit_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Dodaj ovu liniju kako bi vidio odgovor sa servera
        $('#editAppointmentModal').modal('hide');
        if (data.success) {
            showToast(data.message, true);
            updateTableRow(id, data.appointment); // Ažuriraj redak u tabeli
            updateStatistics(data.total_appointments, data.pending_appointments, data.approved_appointments);
        } else {
            showToast(data.message, false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});

function updateTableRow(id, appointment) {
    var row = document.getElementById('appointment-' + id);
    if (!row) {
        console.error('No row found for ID:', id);
        return;
    }

    row.querySelector(".staff").textContent = appointment.Ime + ' ' + appointment.Prezime;
    row.querySelector(".service").textContent = appointment.Naziv_usluge;
    row.querySelector(".date").textContent = appointment.Datum;
    row.querySelector(".time").textContent = appointment.Vrijeme;
    // Ažuriraj status i njegovu klasu
    var statusElem = row.querySelector(".status");
    statusElem.textContent = appointment.Status;
    statusElem.className = 'status status-' + appointment.Status.toLowerCase();
    row.querySelector(".user").textContent = appointment.KorisnikIme + ' ' + appointment.KorisnikPrezime;
    row.querySelector(".location").textContent = appointment.Naziv_lokacije;

    // Ažuriraj napomenu i dodaj gumb "Pročitaj više"
    var noteElem = row.querySelector(".note");
    var fullNote = appointment.Napomena;
    noteElem.setAttribute('data-full-note', fullNote);
    noteElem.innerHTML = fullNote.length > 30 ? 
        fullNote.substring(0, 30) + '... <a href="#" class="view-more-link" data-bs-toggle="modal" data-bs-target="#noteModal-' + id + '">Pročitaj više</a>' : 
        fullNote;

    // Ažuriraj modal s punom napomenom
    var noteModal = document.getElementById('noteModal-' + id);
    if (noteModal) {
        noteModal.querySelector('.modal-body').textContent = fullNote;
    } else {
        const modalHTML = `
            <div class="modal fade" id="noteModal-${id}" tabindex="-1" aria-labelledby="noteModalLabel-${id}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noteModalLabel-${id}">Napomena</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${fullNote}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
}


function updateStatistics(total, pending, approved) {
    document.getElementById('total-appointments').textContent = total;
    document.getElementById('pending-appointments').textContent = pending;
    document.getElementById('approved-appointments').textContent = approved;
}


function addTableRow(appointment) {
    const tableBody = document.querySelector('table tbody');
    const row = document.createElement('tr');
    row.id = 'appointment-' + appointment.ID_termina;
    row.dataset.staffName = appointment.Ime + ' ' + appointment.Prezime;
    row.dataset.serviceName = appointment.Naziv_usluge;
    row.dataset.userName = appointment.KorisnikIme + ' ' + appointment.KorisnikPrezime;
    row.dataset.locationName = appointment.Naziv_lokacije;

    row.innerHTML = `
        <td>${appointment.ID_termina}</td>
        <td class="staff" data-staff-id="${appointment.ID_osoblja}">${appointment.Ime} ${appointment.Prezime}</td>
        <td class="service" data-service-id="${appointment.ID_usluge}">${appointment.Naziv_usluge}</td>
        <td class="date">${appointment.Datum}</td>
        <td class="time">${appointment.Vrijeme}</td>
        <td class="status status-${appointment.Status.toLowerCase()}">${appointment.Status}</td>
        <td class="user" data-user-id="${appointment.ID_korisnika}">${appointment.KorisnikIme} ${appointment.KorisnikPrezime}</td>
        <td class="location" data-location-id="${appointment.ID_lokacije}">${appointment.Naziv_lokacije}</td>
        <td class="note">
            ${appointment.Napomena.length > 30 ? appointment.Napomena.substring(0, 30) + '...' : appointment.Napomena}
            ${appointment.Napomena.length > 30 ? `<button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#noteModal" data-note="${appointment.Napomena}">Pročitaj više</button>` : ''}
        </td>
        <td>
            <button onclick="editAppointment(${appointment.ID_termina})" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi termin">
                <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <button onclick="deleteAppointment(${appointment.ID_termina})" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši termin">
                <i class="fa-regular fa-trash-can"></i>
            </button>
            <button onclick="sendNotification(${appointment.ID_termina})" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Pošalji obavijest">
                <i class="fa-regular fa-envelope"></i>
            </button>
        </td>
    `;

    tableBody.appendChild(row);
}



function sendNotification(appointmentId) {
    const row = document.getElementById('appointment-' + appointmentId);
    const status = row.querySelector('.status').textContent.trim().toLowerCase();

    if (status !== 'approved' && status !== 'canceled') {
        showToast('Obavijest se može poslati samo za odobrene ili odbijene termine.', false);
        return;
    }

    if (confirm('Da li ste sigurni da želite poslati obavijest za ovaj termin?')) {
        fetch('send_notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${appointmentId}&status=${status}`
        })
        .then(response => response.text())
        .then(data => {
            showToast(data, data.includes('uspješno'));
        })
        .catch(error => {
            console.error('Error:', error);
            showToast("Došlo je do greške prilikom slanja obavijesti.", false);
        });
    }
}


// Dodavanje sadržaja napomene u modal
$('#noteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var note = button.data('note'); // Extract info from data-* attributes
    var modal = $(this);
    modal.find('.modal-body').text(note);
});

function showFullNoteModal(noteText) {
    document.getElementById('fullNoteContent').textContent = noteText;
    var fullNoteModal = new bootstrap.Modal(document.getElementById('fullNoteModal'));
    fullNoteModal.show();
}


// FILTRIRANJE

document.getElementById('locationFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('doctorFilter').addEventListener('change', function() {
    filterTable();
});
function filterTable() {
    var selectedLocation = document.getElementById('locationFilter').value.toLowerCase();
    var selectedStatus = document.getElementById('statusFilter').value.toLowerCase();
    var selectedDoctors =document.getElementById('doctorFilter').value.toLowerCase();
    var rows = document.querySelectorAll('table tbody tr');

    rows.forEach(function(row) {
        var location = row.querySelector('.location').textContent.toLowerCase();
        var status = row.querySelector('.status').textContent.toLowerCase();
        var doctor= row.querySelector('.staff').textContent.toLowerCase();

        var locationMatch = (selectedLocation === "all" || location === selectedLocation);
        var statusMatch = (selectedStatus === "all" || status === selectedStatus);
        var doctorMatch = (selectedDoctors === "all" || doctor === selectedDoctors);

        if (locationMatch && statusMatch && doctorMatch) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}



// DODAVANJE AUTOMATSKI LOKACIJE U BAZU PODATAKA KOD TERMINA

document.getElementById('staff').addEventListener('change', function() {
    var staffId = this.value;
    fetch('get_staff_location.php?staff_id=' + staffId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('location').value = data.location_id;
            } else {
                console.error('Greska prilikom dohvaćanja lokacije osoblja');
            }
        });
});

document.getElementById('edit-staff').addEventListener('change', function() {
    var staffId = this.value;
    fetch('get_staff_location.php?staff_id=' + staffId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit-location').value = data.location_id;
            } else {
                console.error('Greska prilikom dohvaćanja lokacije osoblja');
            }
        });
});

function addPatient(appointmentId) {
    const row = document.getElementById('appointment-' + appointmentId);
    const status = row.querySelector('.status').textContent.trim().toLowerCase();

    if (status !== 'completed') {
        showToast('Pacijent može biti dodan samo za završen termin.', false);
        return;
    }

    if (confirm('Da li ste sigurni da želite dodati ovog pacijenta u bazu podataka?')) {
        const params = new URLSearchParams();
        params.append('id', appointmentId);

        fetch('add_patient.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('uspješno dodan')) {
                showToast('Pacijent je uspješno dodan.', true);
            } else {
                showToast('Greška prilikom dodavanja pacijenta: ' + data, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Došlo je do greške prilikom dodavanja pacijenta.', false);
        });
    }
}


</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
