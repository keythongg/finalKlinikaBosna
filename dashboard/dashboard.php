<?php

include 'check_admin_when_click_dashboard.php';

// Ponovno uspostavljanje veze s bazom podataka
$conn = new mysqli($servername, $username, $password, $database);

// Provjera veze s bazom podataka
if ($conn->connect_error) {
    die("Greška prilikom povezivanja na bazu podataka: " . $conn->connect_error);
}

// Dohvati osoblje iz baze podataka
$employees = [];
$sql = "
    SELECT Osoblje.ID_osoblja, Osoblje.Ime, Osoblje.Prezime, Osoblje.Pozicija, Osoblje.Email, 
           Osoblje.status_zaposlenog, Osoblje.ID_odjela, Odjel.Naziv_odjela, Osoblje.ID_lokacije, Lokacija.Naziv AS Naziv_lokacije
    FROM Osoblje
    LEFT JOIN Odjel ON Osoblje.ID_odjela = Odjel.ID_odjela
    LEFT JOIN Lokacija ON Osoblje.ID_lokacije = Lokacija.ID_lokacije
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
} else {
    echo "Nema zaposlenih.";
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

$welcome_message = "<p style='font-family: Poppins, sans-serif; color: #222529; font-size: 18px;'>Dobrodošao, <span style='color: #29ADB2;'>$ime</span>!</p>";

// Dohvati korisnike iz baze podataka
$sql = "SELECT * FROM Korisnici";
$result = $conn->query($sql);
$users = [];
if ($result && $result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_role'])) {
    // Preuzimanje podataka iz obrasca
    $korisnik_id = $_POST['korisnik_id'];
    $nova_uloga = $_POST['nova_uloga'];

    // Izvršavanje upita za ažuriranje uloge korisnika u bazi podataka
    $sql = "UPDATE Korisnici SET Tip_korisnika='$nova_uloga' WHERE ID_korisnika=$korisnik_id";
    if ($conn->query($sql) === TRUE) {
        // Ako je ažuriranje uspješno, preusmjeri korisnika na istu stranicu
        header("Location: dashboard/dashboard.php");
        exit();
    } else {
        echo "Greška prilikom izvršavanja upita: " . $conn->error;
    }
}

$sql = "SELECT COUNT(*) AS total FROM Osoblje";
$total_employees = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) AS active FROM Osoblje WHERE status_zaposlenog = 1";
$active_employees = $conn->query($sql)->fetch_assoc()['active'];

$sql = "SELECT COUNT(*) AS inactive FROM Osoblje WHERE status_zaposlenog = 0";
$inactive_employees = $conn->query($sql)->fetch_assoc()['inactive'];

$sql = "SELECT ID_odjela, Naziv_odjela FROM Odjel";
$result = $conn->query($sql);
$odjeli = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $odjeli[] = $row;
    }
}

$sql = "SELECT ID_lokacije, Naziv FROM Lokacija";
$result = $conn->query($sql);
$lokacije = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lokacije[] = $row;
    }
}

// Dohvati ukupni broj pacijenata
$sql = "SELECT COUNT(*) AS total_pacijenti FROM pacijent";
$result = $conn->query($sql);
$total_pacijenti = $result->fetch_assoc()['total_pacijenti'];

// Dohvati ukupni broj zaposlenika
$sql = "SELECT COUNT(*) AS total_zaposlenih FROM osoblje";
$result = $conn->query($sql);
$total_zaposlenih = $result->fetch_assoc()['total_zaposlenih'];

// Dohvati ukupni broj termina
$sql = "SELECT COUNT(*) AS total_termina FROM termini";
$result = $conn->query($sql);
$total_termina = $result->fetch_assoc()['total_termina'];

// Dohvati sve godine iz baze podataka
$godine = [];
$sql = "SELECT DISTINCT YEAR(Datum) as godina FROM termini ORDER BY godina DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $godine[] = $row['godina'];
    }
}

// Dohvati sve lokacije iz baze podataka
$lokacije = [];
$sql = "SELECT ID_lokacije, Naziv FROM Lokacija";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lokacije[] = $row;
    }
}

// Dohvati podatke za odabranu godinu i lokaciju
$odabrana_godina = isset($_GET['godina']) ? $_GET['godina'] : $godine[0];
$odabrana_lokacija = isset($_GET['lokacija']) ? $_GET['lokacija'] : $lokacije[0]['ID_lokacije'];

$terminiPoMjesecima = [];
for ($i = 1; $i <= 12; $i++) {
    $sql = "SELECT COUNT(*) AS broj_termina FROM termini WHERE MONTH(Datum) = $i AND YEAR(Datum) = $odabrana_godina AND ID_lokacije = $odabrana_lokacija AND Status = 'completed'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $terminiPoMjesecima[] = $row['broj_termina'];
}



// Dohvati broj zaposlenih po odjelima
$sql = "
    SELECT Odjel.Naziv_odjela, COUNT(Osoblje.ID_osoblja) AS broj_zaposlenih
    FROM Osoblje
    LEFT JOIN Odjel ON Osoblje.ID_odjela = Odjel.ID_odjela
    GROUP BY Odjel.Naziv_odjela
";
$result = $conn->query($sql);

$departments = [];
$employee_counts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['Naziv_odjela'];
        $employee_counts[] = $row['broj_zaposlenih'];
    }
}


// Dohvati ukupnu zaradu iz baze podataka
$sql = "
    SELECT 
        SUM(u.Cijena) AS UkupnaZarada
    FROM 
        termini t
    JOIN 
        usluge u ON t.ID_usluge = u.ID_usluge
    WHERE 
        t.Status = 'completed';
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ukupnaZarada = $row['UkupnaZarada'];
} else {
    $ukupnaZarada = 0;
}


// Dohvaćanje godine iz GET parametara ili postavljanje na trenutnu godinu
$godina = isset($_GET['godina']) ? $_GET['godina'] : date('Y');
$lokacija = isset($_GET['lokacija']) ? $_GET['lokacija'] : $lokacije[0]['ID_lokacije'];

// Dohvati zaradu po mjesecima za određenu godinu i lokaciju iz baze podataka
$sql = "
    SELECT 
        MONTH(t.Datum) AS Mjesec,
        SUM(u.Cijena) AS MjesecnaZarada
    FROM 
        termini t
    JOIN 
        usluge u ON t.ID_usluge = u.ID_usluge
    WHERE 
        t.Status = 'completed' AND YEAR(t.Datum) = $godina AND t.ID_lokacije = $lokacija
    GROUP BY 
        MONTH(t.Datum)
    ORDER BY 
        Mjesec;
";
$result = $conn->query($sql);

$mjeseci = array_fill(1, 12, 0); // Inicijalizacija zarade za svaki mjesec na 0

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mjeseci[(int)$row['Mjesec']] = $row['MjesecnaZarada'];
    }
}







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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


    

    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" type="image/png" href="../img/favicon.png">

    <title>Dashboard</title>


    <style>
        

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        .table tbody tr {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
        }
        .table tbody td {
            vertical-align: middle;
        }
        .btn {
            border-radius: 4px;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .card-title {
    font-size: 1rem;
    color: #6c757d;
    padding-bottom: 13px;
}

.card-text {
    font-size: 1.5rem;
    font-weight: bold;
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
        .sidebar a {
            color: #fff;
        }
        .sidebar li {
            color: #90908E;
        }
        .sidebar a:hover {
            background-color: #D7D7D6;
        }
        .nav-pills .nav-link {
            margin-bottom: 1rem;
            padding: 10px 20px;
            border-radius: 30px;
        }
        .nav-pills .nav-link.active {
            background-color: #0ABEFF !important;
            color: white !important;
            border-radius: 50px;
        }
        .nav-pills .nav-link i {
            margin-right: 0.5rem;
        }
        .capitalize {
        text-transform: capitalize;
    }

    .icon-container {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    background-color: #f1f1f1;
}

.select-container {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            display: flex;
            gap: 10px;
        }
        .year-select, .location-select {
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            background-color: #fff;
        }
        .chart-size {
    height: 300px; 
    max-height: 432px; 
}

.ui-state-highlight {
    height: 2.5rem;
    line-height: 1.2rem;
    background-color: #f0f0f0;
    border: 1px dashed #ccc;
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
                <hr>
                <br>
                <ul class="nav nav-pills flex-column mb-auto">
                <?php if ($tip_korisnika == 'admin') { ?>
                        <li>
                            <a href="dashboard.php" class="nav-link active" aria-current="page">
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
                            <a href="terminiDashboard.php" class="nav-link text-dark">
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
                            <a href="dashboard.php" class="nav-link active" aria-current="page">
                                <i class="fa-solid fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="pacijentiDashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-hospital-user"></i> Pacijenti
                            </a>
                        </li>
                        <li>
                            <a href="terminiDashboard.php" class="nav-link text-dark">
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
                    <li>
                        <a href="#" class="nav-link text-dark">
                            <i class="fas fa-cog"></i> Postavke
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-dark">
                            <i class="fas fa-question-circle"></i> Pomoć
                        </a>
                    </li>
                    <li>
                        <a href="../odjava.php" class="nav-link text-dark"> 
                            <i class="fas fa-sign-out-alt"></i> Odjavi se
                        </a>
                    </li>
                </ul>
                
            </div>

            <?php if ($tip_korisnika == 'admin') { ?>
            <div class="col-12 col-md-9 col-lg-10 main-content">
                <!-- Main Content -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <!-- Button trigger modal -->
                    </div>



                    <div class="row sortable">
                <div class="col-md-3 mb-4 ">
                    <div class="card shadow-sm p-3 bg-white rounded">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon-container me-3">
                                    <i class="fa-solid fa-user-injured fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Ukupno Pacijenata</h5>
                                    <h3 class="card-text" id="totalPatients"><?php echo $total_pacijenti; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4 ">
                    <div class="card shadow-sm p-3 bg-white rounded">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon-container me-3">
                                    <i class="fa-solid fa-users fa-2x text-success"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Ukupno zaposlenih</h5>
                                    <h3 class="card-text" id="totalEmployees"><?php echo $total_zaposlenih; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4 ">
                    <div class="card shadow-sm p-3 bg-white rounded">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon-container me-3">
                                    <i class="fa-solid fa-calendar-check fa-2x text-info"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Ukupno termina</h5>
                                    <h3 class="card-text" id="totalAppointments"><?php echo $total_termina; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4 ">
                    <div class="card shadow-sm p-3 bg-white rounded">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon-container me-3">
                                    <i class="fa-solid fa-dollar-sign fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Ukupna Zarada</h5>
                                    <h3 class="card-text" id="totalEarnings"><?php echo 'BAM ' . number_format($ukupnaZarada, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- -->

            <div class="row sortable">


            <div class="col-md-7 ">
                <div class="card mb-4 ">
                    <div class="card-body">
                        <h5 class="card-title">Termini</h5>
                        <div class="select-container">
                                    <select id="godinaSelect" class="year-select" onchange="promijeniParametre()">
                                        <?php foreach ($godine as $godina): ?>
                                            <option value="<?php echo $godina; ?>" <?php echo $godina == $odabrana_godina ? 'selected' : ''; ?>>
                                                <?php echo $godina; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select id="lokacijaSelect" class="location-select" onchange="promijeniParametre()">
                                        <?php foreach ($lokacije as $lokacija): ?>
                                            <option value="<?php echo $lokacija['ID_lokacije']; ?>" <?php echo $lokacija['ID_lokacije'] == $odabrana_lokacija ? 'selected' : ''; ?>>
                                                <?php echo $lokacija['Naziv']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                        <canvas id="patientChart"></canvas>
                    </div>
                </div>
            </div>


                <div class="col-md-5 " >
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Top Odjeli</h5>
                            <canvas id="departmentsChart" class="chart-size"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <!-- -->

            <div class="row sortable">

                <div class="col-md-5 ">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Zarada</h5>
                          
                            <canvas id="zaradaChart"></canvas>
                         
                        </div>
                    </div>
                </div>

                <div class="col-md-7 ">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Top Doktori</h5>
                            <div id="topDoctorsList"></div>
                        </div>
                    </div>
                </div>

                       


            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


          <script>
        function promijeniParametre() {
            const odabranaGodina = document.getElementById('godinaSelect').value;
            const odabranaLokacija = document.getElementById('lokacijaSelect').value;
            window.location.href = `dashboard.php?godina=${odabranaGodina}&lokacija=${odabranaLokacija}`;
        }

        // PHP niz pretvorimo u JavaScript niz
        const terminiPoMjesecima = <?php echo json_encode($terminiPoMjesecima); ?>;
        const mjeseci = <?php echo json_encode(array_values($mjeseci)); ?>;
        
        // Konfiguracija grafikona za termine
        const configTermini = {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Broj Termina',
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    data: terminiPoMjesecima,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        stacked: true
                    },
                    y: {
                        beginAtZero: true,
                        stacked: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                }
            }
        };

        // Konfiguracija grafikona za zaradu
        const configZarada = {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Zarada po mjesecima',
                    data: mjeseci,
                    backgroundColor: 'rgba(108, 190, 125, 0.6)',
                    borderColor: 'rgba(108, 190, 125, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                }
            }
        };

        // Kreiranje grafikona kada se stranica učita
        window.addEventListener('load', function() {
            const ctxTermini = document.getElementById('patientChart').getContext('2d');
            const ctxZarada = document.getElementById('zaradaChart').getContext('2d');
            new Chart(ctxTermini, configTermini);
            new Chart(ctxZarada, configZarada);
        });


        // TOP ODJELI

        document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('departmentsChart').getContext('2d');

    // Pretvaranje PHP podataka u JavaScript
    const departments = <?php echo json_encode($departments); ?>;
    const employeeCounts = <?php echo json_encode($employee_counts); ?>;

    const data = {
        labels: departments,
        datasets: [{
            data: employeeCounts,
            backgroundColor: [
                'rgba(108, 190, 125, 1)',  // Medium Green 
                'rgba(51, 82, 251, 1)',     //  Medium Blue
                'rgba(134, 199, 253, 1)',  // Light Blue
                'rgba(241, 201, 77, 1)',  // Medium Yellow
                'rgba(153, 102, 255, 1)', // Light Purple
                'rgba(255, 159, 243, 1)', // Light Pink
                'rgba(255, 99, 132, 1)',  // Medium Red
                'rgba(75, 192, 192, 1)',  // Medium Green
                'rgba(255, 205, 86, 1)',  // Medium Yellow
                'rgba(255, 99, 132, 1)',  // Medium Red
                'rgba(153, 102, 255, 1)', // Medium Purple
                'rgba(255, 159, 243, 1)'  // Medium Pink
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',    // Dark Blue
                'rgba(75, 192, 192, 1)',    // Dark Green
                'rgba(255, 205, 86, 1)',    // Dark Yellow
                'rgba(255, 99, 132, 1)',    // Dark Red
                'rgba(153, 102, 255, 1)',   // Dark Purple
                'rgba(255, 159, 243, 1)',   // Dark Pink
                'rgba(54, 162, 235, 0.8)',  // Medium-Dark Blue
                'rgba(75, 192, 192, 0.8)',  // Medium-Dark Green
                'rgba(255, 205, 86, 0.8)',  // Medium-Dark Yellow
                'rgba(255, 99, 132, 0.8)',  // Medium-Dark Red
                'rgba(153, 102, 255, 0.8)', // Medium-Dark Purple
                'rgba(255, 159, 243, 0.8)'  // Medium-Dark Pink
            ],
            borderWidth: 0
        }]
    };

    const config = {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                    },
                },
                title: {
                    display: true,
                    text: 'Broj zaposlenih po odjelima'
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                }
            }
        },
    };

    new Chart(ctx, config);
});

// DOKTORI TOP
document.addEventListener('DOMContentLoaded', function() {
    fetch('dohvati_top_doktore.php')
        .then(response => response.json())
        .then(data => {
            const topDoctorsList = document.getElementById('topDoctorsList');
            topDoctorsList.innerHTML = '';

            data.forEach((doctor, index) => {
                const starCount = Math.round(doctor.ProsjecnaOcjena);
                const stars = '★'.repeat(starCount) + '☆'.repeat(5 - starCount);

                topDoctorsList.innerHTML += `
                <div class="doctor-entry">
                                <div class="doctor-rank">#${index + 1}</div>
                                <div class="doctor-image">
                                    <img src="${doctor.slika}" alt="${doctor.Ime} ${doctor.Prezime}">
                                </div>
                                <div class="doctor-info">
                                    <div class="doctor-details">
                                        <div class="doctor-name">Dr. ${doctor.Ime} ${doctor.Prezime}</div>
                                        <div class="doctor-department">${doctor.Naziv_odjela}</div>
                                        <div class="doctor-location">${doctor.Lokacija}</div>
                                    </div>
                                </div>
                                <div class="doctor-reviews">${doctor.BrojRecenzija} Reviews</div>
                                <div class="doctor-rating">${stars}</div>
                            </div>
                `;
            });
        })
        .catch(error => console.error('Error:', error));
});

// CSS za stiliziranje
const style = document.createElement('style');
style.innerHTML = `
.doctor-entry {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            
            border-radius: 5px;
            white-space: nowrap;
        }
        .doctor-rank {
            margin-right: 15px;
            color: #666;
            font-size: 16px;
            flex: 0 0 40px;
        }
        .doctor-image img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }
        .doctor-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .doctor-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .doctor-name {
            font-size: 16px;
            color: #666;
            flex: 1;
            margin-right: 20px;
        }
        .doctor-department {
            font-size: 16px;
            color: #666;
            margin-right: 40px;
            flex: 0 0 150px; /* prilagodite širinu prema potrebi */
            text-align: left;
        }
        .doctor-location {
            font-size: 16px;
            color: #999;
            margin-right: 20px;
            flex: 0 0 100px; /* prilagodite širinu prema potrebi */
            text-align: left;
        }
        .doctor-reviews {
            margin-left: 20px;
            font-size: 16px;
            color: #666;
            flex: 0 0 100px;
            text-align: center;
        }
        .doctor-rating {
            font-size: 20px;
            color: gold;
            flex: 0 0 100px;
            text-align: center;
        }
`;
document.head.appendChild(style);

// SORTABLE ZA DINAMIČKO POMJERANJE KARTICA
$(document).ready(function() {
    $(".sortable").sortable({
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        tolerance: "pointer"
    });
    $(".sortable").disableSelection();
});


                </script>
            </div>
        </div>
    </div>



    </script>
   
</body>
</html><?php }