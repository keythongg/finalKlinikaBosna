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

// Dohvati podatke o pacijentima iz baze podataka
$patients = [];
$sql = "
    SELECT Pacijent.ID_pacijenta, Pacijent.Ime, Pacijent.Prezime, Pacijent.Datum_rodjenja, Pacijent.Telefon, Pacijent.Email, Lokacija.Naziv AS Lokacija
    FROM Pacijent
    LEFT JOIN Lokacija ON Pacijent.ID_lokacije = Lokacija.ID_lokacije
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['Datum_rodjenja'] = date('F j, Y', strtotime($row['Datum_rodjenja'])); // Promjena formata datuma
        $patients[] = $row;
    }
} else {
    echo "Nema pacijenata.";
}

// Statistika pacijenata
$total_patients = count($patients);

$cazin_patients = 0;
$bihac_patients = 0;
$sarajevo_patients = 0;

foreach ($patients as $patient) {
    if ($patient['Lokacija'] == 'Cazin') {
        $cazin_patients++;
    } elseif ($patient['Lokacija'] == 'Bihać') {
        $bihac_patients++;
    } elseif ($patient['Lokacija'] == 'Sarajevo') {
        $sarajevo_patients++;
    }
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

// Dohvati medicinske kartone iz baze podataka
$kartoni = [];
$sql = "
    SELECT Medicinski_karton.ID_kartona, Pacijent.Ime, Pacijent.Prezime, Medicinski_karton.Datum_kreiranja
    FROM Medicinski_karton
    LEFT JOIN Pacijent ON Medicinski_karton.ID_pacijenta = Pacijent.ID_pacijenta
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['Datum_kreiranja'] = date('F j, Y', strtotime($row['Datum_kreiranja'])); // Promjena formata datuma
        $kartoni[] = $row;
    }
} else {
    echo "Nema medicinskih kartona.";
}
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
    <title>Pacijenti Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        .table-responsive { margin-top: 20px; border-radius: 20px; }
        .table thead th { padding: 8px; background-color: #C2EFFF; border-bottom: 2px solid #ADE9FF; text-align: left; }
        .table tbody tr { background-color: #fff; border-bottom: 1px solid #dee2e6; }
        .table tbody tr:hover { background-color: #f1f3f5; }
        .table tbody td { vertical-align: middle; }
        .btn { border-radius: 4px; }
        .btn-primary{ background-color:#0ABEFF; border-color:#0ABEFF; }
        .btn-primary:hover{ background-color:#47CEFF; border-color:#47CEFF; }
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
        .card-title { font-size: 1.25rem; font-weight: 500; color: #0ABEFF; }
        .card-text { font-size: 2rem; color: #040F16; margin-bottom: 20px; font-weight: 600; line-height: 1.5; padding: 10px; background: linear-gradient(45deg, #f3f4f6, #eaecef); border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: transform 0.2s; }
        .card-text:hover { transform: scale(1.02); }
        .sidebar a { color: #fff; }
        .sidebar li { color: #90908E; }
        .sidebar a:hover { background-color: #D7D7D6; }
        .nav-pills .nav-link { margin-bottom: 1rem; padding: 10px 20px; border-radius: 30px; }
        .nav-pills .nav-link.active { background-color: #0ABEFF !important; color: white !important; border-radius: 50px; }
        .nav-pills .nav-link i { margin-right: 0.5rem; }
        .capitalize { text-transform: capitalize; }
        .btn { border-radius: 4px; }
        .btn-primary{ background-color:#0ABEFF; border-color:#0ABEFF; }
        .btn-primary:hover{ background-color:#47CEFF; border-color:#47CEFF; }
        .btn-warning { background-color: transparent; border-color: transparent; }
        .btn-warning:hover { background-color: #FFC099; border-color: transparent; }
        .btn-danger { color: black; background-color: transparent; border-color: transparent; }
        .btn-danger:hover { color: black; background-color:#FA9F9E; border-color: transparent; }
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
                            <a href="dashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="zaposleniciDashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-users"></i>Zaposlenici
                            </a>
                        </li>
                        <li>
                            <a href="pacijentiDashboard.php" class="nav-link active" aria-current="page">
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
                            <a href="dashboard.php" class="nav-link text-dark">
                                <i class="fa-solid fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="pacijentiDashboard.php" class="nav-link active" aria-current="page">
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
                <li><a href="#" class="nav-link text-dark"><i class="fas fa-cog"></i> Postavke</a></li>
                <li><a href="#" class="nav-link text-dark"><i class="fas fa-question-circle"></i> Pomoć</a></li>
                <li><a href="../odjava.php" class="nav-link text-dark"><i class="fas fa-sign-out-alt"></i> Odjavi se</a></li>
            </ul>
        </div>



        <div class="col-12 col-md-9 col-lg-10 main-content">
            <!-- Main Content -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pacijenti</h1>
            </div>



            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Ukupno pacijenata</h5>
                            <p class="card-text"><?= $total_patients ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Pacijenati u Cazinu</h5>
                            <p class="card-text"><?= $cazin_patients ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Pacijenati u Bihaću</h5>
                            <p class="card-text"><?= $bihac_patients ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Pacijenati u Sarajevu</h5>
                            <p class="card-text"><?= $sarajevo_patients ?></p>
                        </div>
                    </div>
                </div>
            </div>     



            <!-- Filter -->
            <h5 class="card-title">Filter</h5>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
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

                    <!-- Dropdown za filtriranje po imenu pacijenta -->
                    <div class="input-group mb-3 me-3" style="width: 320px;">
                        <label class="input-group-text" for="nameFilter">Ime</label>
                        <input type="text" class="form-control" id="nameFilter" placeholder="Pretraži po imenu">
                    </div>

                    <!-- Dropdown za filtriranje po prezimenu pacijenta -->
                    <div class="input-group mb-3 me-3" style="width: 320px;">
                        <label class="input-group-text" for="surnameFilter">Prezime</label>
                        <input type="text" class="form-control" id="surnameFilter" placeholder="Pretraži po prezimenu">
                    </div>
                </div>
            </div>

            <!-- Tabela sa pacijentima -->
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID pacijenta</th>
                            <th>Ime</th>
                            <th>Prezime</th>
                            <th>Datum rođenja</th>
                            <th>Telefon</th>
                            <th>Email</th>
                            <th>Lokacija</th>
                        </tr>
                    </thead>
                    <tbody id="patientTableBody">
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?= htmlspecialchars($patient['ID_pacijenta']) ?></td>
                                <td><?= htmlspecialchars($patient['Ime']) ?></td>
                                <td><?= htmlspecialchars($patient['Prezime']) ?></td>
                                <td><?= htmlspecialchars($patient['Datum_rodjenja']) ?></td>
                                <td><?= htmlspecialchars($patient['Telefon']) ?></td>
                                <td><?= htmlspecialchars($patient['Email']) ?></td>
                                <td><?= htmlspecialchars($patient['Lokacija']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            

<!-- Modal for Creating Medical Record -->
<div class="modal fade" id="createMedicalRecordModal" tabindex="-1" aria-labelledby="createMedicalRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMedicalRecordModalLabel">Kreiraj medicinski karton</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createMedicalRecordForm">
                    <div class="mb-3">
                        <label for="patientSelect" class="form-label">Odaberi pacijenta</label>
                        <select class="form-select" id="patientSelect" name="ID_pacijenta" required>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['ID_pacijenta'] ?>"><?= $patient['Ime'] . ' ' . $patient['Prezime'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adresa" class="form-label">Adresa</label>
                        <input type="text" class="form-control" id="adresa" name="Adresa" required>
                    </div>
                    <div class="mb-3">
                        <label for="glavneTegobe" class="form-label">Glavne tegobe</label>
                        <textarea class="form-control" id="glavneTegobe" name="Glavne_tegobe" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fizikalniPregled" class="form-label">Fizikalni pregled</label>
                        <textarea class="form-control" id="fizikalniPregled" name="Fizikalni_pregled" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="laboratorijskiNalazi" class="form-label">Laboratorijski nalazi</label>
                        <textarea class="form-control" id="laboratorijskiNalazi" name="Laboratorijski_nalazi" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="terapija" class="form-label">Terapija</label>
                        <textarea class="form-control" id="terapija" name="Terapija" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="dijagnoza" class="form-label">Dijagnoza</label>
                        <textarea class="form-control" id="dijagnoza" name="Dijagnoza" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="preporuka" class="form-label">Preporuka</label>
                        <textarea class="form-control" id="preporuka" name="Preporuka" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Sačuvaj</button>
                </form>
            </div>
        </div>
    </div>
</div>


<br><br><br><br>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
<h5 class="card-title">Medicinski kartoni</h5>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMedicalRecordModal">
        Kreiraj medicinski karton
    </button>
            </div>

<!-- Tabela sa medicinskim kartonima -->
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID kartona</th>
                <th>Ime</th>
                <th>Prezime</th>
                <th>Datum kreiranja</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody id="kartonTableBody">
            <?php foreach ($kartoni as $karton): ?>
                <tr>
                    <td><?= htmlspecialchars($karton['ID_kartona']) ?></td>
                    <td><?= htmlspecialchars($karton['Ime']) ?></td>
                    <td><?= htmlspecialchars($karton['Prezime']) ?></td>
                    <td><?= htmlspecialchars($karton['Datum_kreiranja']) ?></td>
                    <td>
                
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" title="Pregledaj karton" data-bs-target="#viewKartonModal" data-id="<?= $karton['ID_kartona'] ?>"><i class="fa-regular fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" title="Uredi karton" data-bs-target="#editKartonModal" data-id="<?= $karton['ID_kartona'] ?>"><i class="fa-regular fa-pen-to-square"></i></button>
                        <button class="btn btn-sm btn-danger" title="Izbriši karton" onclick="deleteKarton(<?= $karton['ID_kartona'] ?>)"><i class="fa-regular fa-trash-can"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


        </div>
        
    </div>

</div>


<!-- Modal za pregled medicinskog kartona -->
<div class="modal fade" id="viewKartonModal" tabindex="-1" aria-labelledby="viewKartonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewKartonModalLabel">Pregled medicinskog kartona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewKartonContent">
                <div class="container">
                    <div class="row mb-3">
                        <div class="col">
                            <strong>ID kartona:</strong> <span id="viewIDKartona"></span>
                        </div>
                       
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Ime pacijenta:</strong> <span id="viewIme"></span>
                        </div>
                        <div class="col">
                            <strong>Prezime pacijenta:</strong> <span id="viewPrezime"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Adresa:</strong> <span id="viewAdresa"></span>
                        </div>
                        <div class="col">
                            <strong>Datum kreiranja:</strong> <span id="viewDatumKreiranja"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Glavne tegobe:</strong>
                            <p id="viewGlavneTegobe"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Fizikalni pregled:</strong>
                            <p id="viewFizikalniPregled"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Laboratorijski nalazi:</strong>
                            <p id="viewLaboratorijskiNalazi"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Terapija:</strong>
                            <p id="viewTerapija"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Dijagnoza:</strong>
                            <p id="viewDijagnoza"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <strong>Preporuka:</strong>
                            <p id="viewPreporuka"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
                <button type="button" class="btn btn-primary" id="downloadPDF">Preuzmi PDF</button>
                <button type="button" class="btn btn-primary" id="printKarton">Printaj</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal za uređivanje medicinskog kartona -->
<div class="modal fade" id="editKartonModal" tabindex="-1" aria-labelledby="editKartonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKartonModalLabel">Uredi medicinski karton</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Ovdje će biti forma za uređivanje medicinskog kartona -->
                <form id="editKartonForm">
                    <input type="hidden" name="id" id="editKartonId">
                    <div class="mb-3">
                        <label for="editAdresa" class="form-label">Adresa</label>
                        <input type="text" class="form-control" id="editAdresa" name="adresa" required>
                    </div>
                    <div class="mb-3">
                        <label for="editGlavneTegobe" class="form-label">Glavne tegobe</label>
                        <textarea class="form-control" id="editGlavneTegobe" name="glavne_tegobe" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editFizikalniPregled" class="form-label">Fizikalni pregled</label>
                        <textarea class="form-control" id="editFizikalniPregled" name="fizikalni_pregled" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editLaboratorijskiNalazi" class="form-label">Laboratorijski nalazi</label>
                        <textarea class="form-control" id="editLaboratorijskiNalazi" name="laboratorijski_nalazi" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editTerapija" class="form-label">Terapija</label>
                        <textarea class="form-control" id="editTerapija" name="terapija" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editDijagnoza" class="form-label">Dijagnoza</label>
                        <textarea class="form-control" id="editDijagnoza" name="dijagnoza" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editPreporuka" class="form-label">Preporuka</label>
                        <textarea class="form-control" id="editPreporuka" name="preporuka" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Sačuvaj promjene</button>
                </form>
            </div>
        </div>
    </div>
</div>





<script>
// Filtriranje tabele pacijenata
document.getElementById('locationFilter').addEventListener('change', function() {
        filterTable();
    });
    document.getElementById('nameFilter').addEventListener('input', function() {
        filterTable();
    });
    document.getElementById('surnameFilter').addEventListener('input', function() {
        filterTable();
    });

    function filterTable() {
        var selectedLocation = document.getElementById('locationFilter').value.toLowerCase();
        var nameFilter = document.getElementById('nameFilter').value.toLowerCase();
        var surnameFilter = document.getElementById('surnameFilter').value.toLowerCase();
        var rows = document.querySelectorAll('#patientTableBody tr');

        rows.forEach(function(row) {
            var location = row.cells[6].textContent.toLowerCase();
            var name = row.cells[1].textContent.toLowerCase();
            var surname = row.cells[2].textContent.toLowerCase();

            var locationMatch = (selectedLocation === "all" || location === selectedLocation);
            var nameMatch = (name.includes(nameFilter));
            var surnameMatch = (surname.includes(surnameFilter));

            if (locationMatch && nameMatch && surnameMatch) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }


    document.getElementById('createMedicalRecordForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    var formData = new FormData(this);

    fetch('create_medical_record.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        if (data.includes('uspješno kreiran')) {
            location.reload(); // Reload the page to see the new record
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});



// Pregled medicinskog kartona
$('#viewKartonModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); 
    var kartonId = button.data('id'); 

    var modal = $(this);
    $.ajax({
        url: 'get_karton.php',
        type: 'GET',
        data: { id: kartonId },
        success: function(response) {
            try {
                var karton = JSON.parse(response);
                if (karton.error) {
                    alert(karton.error);
                } else {
                    modal.find('#viewIDKartona').text(karton.ID_kartona);
                    modal.find('#viewIme').text(karton.Ime_pacijenta);
                    modal.find('#viewPrezime').text(karton.Prezime_pacijenta);
                    modal.find('#viewAdresa').text(karton.Adresa);
                    modal.find('#viewGlavneTegobe').text(karton.Glavne_tegobe);
                    modal.find('#viewFizikalniPregled').text(karton.Fizikalni_pregled);
                    modal.find('#viewLaboratorijskiNalazi').text(karton.Laboratorijski_nalazi);
                    modal.find('#viewTerapija').text(karton.Terapija);
                    modal.find('#viewDijagnoza').text(karton.Dijagnoza);
                    modal.find('#viewPreporuka').text(karton.Preporuka);
                    modal.find('#viewDatumKreiranja').text(karton.Datum_kreiranja);
                    
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                alert('Greška pri učitavanju podataka.');
            }
        }
    });
});

document.getElementById('downloadPDF').addEventListener('click', function() {
    var element = document.getElementById('viewKartonContent');
    html2pdf().from(element).save();
});

document.getElementById('printKarton').addEventListener('click', function() {
    var printContents = document.getElementById('viewKartonContent').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
});


// Uređivanje medicinskog kartona
$('#editKartonModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var kartonId = button.data('id');

    var modal = $(this);
    $.ajax({
        url: 'get_karton.php',
        type: 'GET',
        data: { id: kartonId },
        success: function(response) {
            var karton = JSON.parse(response);
            modal.find('#editKartonId').val(karton.ID_kartona);
            modal.find('#editIme').val(karton.Ime);
            modal.find('#editPrezime').val(karton.Prezime);
            modal.find('#editAdresa').val(karton.Adresa);
            modal.find('#editAdresa').val(karton.Adresa);
            modal.find('#editGlavneTegobe').val(karton.Glavne_tegobe);
            modal.find('#editFizikalniPregled').val(karton.Fizikalni_pregled);
            modal.find('#editLaboratorijskiNalazi').val(karton.Laboratorijski_nalazi);
            modal.find('#editTerapija').val(karton.Terapija);
            modal.find('#editDijagnoza').val(karton.Dijagnoza);
            modal.find('#editPreporuka').val(karton.Preporuka);
        }
    });
});

$('#editKartonForm').on('submit', function(event) {
    event.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
        url: 'edit_karton.php',
        type: 'POST',
        data: formData,
        success: function(response) {
            $('#editKartonModal').modal('hide');
            location.reload();
        }
    });
});


// Brisanje medicinskog kartona
function deleteKarton(kartonId) {
    if (confirm('Da li ste sigurni da želite obrisati ovaj medicinski karton?')) {
        $.ajax({
            url: 'delete_karton.php',
            type: 'POST',
            data: { id: kartonId },
            success: function(response) {
                location.reload();
            }
        });
    }
}


</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

</body>
</html>
