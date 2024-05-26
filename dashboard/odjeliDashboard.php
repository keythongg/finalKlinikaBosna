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

// Dohvati odjele iz baze podataka
$departments = [];
$sql = "SELECT * FROM Odjel";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
} else {
    echo "Nema odjela.";
}

// Dohvati lokacije iz baze podataka
$locations = [];
$sql = "SELECT * FROM Lokacija";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
} else {
    echo "Nema lokacija.";
}

// Number of services to display per page
$limit = 10;

// Current page number
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch services with limit and offset
$services = [];
$sql = "
    SELECT Usluge.*, Odjel.Naziv_odjela 
    FROM Usluge
    LEFT JOIN Odjel ON Usluge.ID_odjela = Odjel.ID_odjela
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
} else {
    echo "Nema usluga.";
}

// Fetch total number of services
$sql = "SELECT COUNT(*) AS total FROM Usluge";
$totalServices = $conn->query($sql)->fetch_assoc()['total'];
$totalPages = ceil($totalServices / $limit);

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
    <title>Odjeli</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        .table-responsive {
            margin-top: 20px;
            
        }
        .table thead th {
            padding: 8px;
            background-color: #C2EFFF;
            border-bottom: 2px solid #ADE9FF;
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
        .btn-primary {
            background-color: #0ABEFF;
            border-color: #0ABEFF;
        }
        .btn-primary:hover {
            background-color: #47CEFF;
            border-color: #47CEFF;
        }
        .btn-warning {
            background-color: transparent;
            border-color: transparent;
        }
        .btn-warning:hover {
            background-color: #FFC099;
            border-color: transparent;
        }
        .btn-danger {
            color: black;
            background-color: transparent;
            border-color: transparent;
        }
        .btn-danger:hover {
            color: black;
            background-color: #FA9F9E;
            border-color: transparent;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 500;
            color: #0ABEFF;
        }
        .card-text {
    font-size: 2rem;
    color: #040F16;
    margin-bottom: 20px;
    font-weight: 600; /* Add a bit of weight for better emphasis */
    line-height: 1.5; /* Improve readability */
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1); /* Subtle text shadow for depth */
    padding: 10px; /* Add padding for better spacing */
    background: linear-gradient(45deg, #f3f4f6, #eaecef); /* Light gradient background */
    border-radius: 10px; /* Smooth rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow for a card-like feel */
    transition: transform 0.2s; /* Smooth transition for hover effect */
}

.card-text:hover {
    transform: scale(1.02); /* Slightly enlarge on hover for a modern effect */
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
        .sticky-bottom {
            margin-top: auto;
        }
        .text-right {
    text-align: right;
}
.capitalize {
        text-transform: capitalize;
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
                            <a href="odjeliDashboard.php" class="nav-link active" aria-current="page">
                                <i class="fa-regular fa-building"></i> Odjeli
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
                            <a href="terminiDashboard.php" class="nav-link text-dark">
                                <i class="fas fa-calendar-check"></i> Termini
                            </a>
                        </li>
                        <li>
                            <a href="odjeliDashboard.php" class="nav-link active" aria-current="page">
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
            <?php if ($tip_korisnika == 'admin' || $tip_korisnika == 'glavni doktor' ) { ?>
            <div class="col-12 main-content">
                <!-- Main Content -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Odjeli</h1>
                    <!-- Button trigger modal -->
                   
                   
                </div>
                <div class="row">
    <div class="col-md-4">
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Ukupno odjela</h5>
                <p class="card-text"><?= count($departments) ?></p>
                <div class="text-right">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                        Dodaj odjel
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php if ($tip_korisnika == 'admin') { ?>
    <div class="col-md">
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Ukupno lokacija</h5>
                <p class="card-text"><?= count($locations) ?></p>
                <div class="text-right">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                        Dodaj lokaciju
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="col-md">
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Ukupno usluga</h5>
                <p class="card-text"><?= $totalServices ?></p>
                <div class="text-right">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        Dodaj uslugu
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


                <!-- Tabele za odjele, lokacije i usluge -->
                <div class="table-responsive">
                    <!-- Odjeli -->
                    <h3>Odjeli</h3>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Naziv odjela</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $department): ?>
                            <tr id="department-<?= $department['ID_odjela'] ?>">
                                <td class="name"><?= htmlspecialchars($department['Naziv_odjela']) ?></td>
                                <td>
                                    <button onclick="editDepartment(<?= $department['ID_odjela'] ?>)" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi informacije odjela">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="deleteDepartment(<?= $department['ID_odjela'] ?>)" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši odjel">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($departments)): ?>
                            <tr>
                                <td colspan="2">Nema podataka o odjelima.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($tip_korisnika == 'admin' ) { ?>
                <div class="table-responsive">
                    <!-- Lokacije -->
                    <h3>Lokacije</h3>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Naziv lokacije</th>
                                <th>Adresa</th>
                                <th>Kontakt telefon</th>
                                <th>Email</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $location): ?>
                            <tr id="location-<?= $location['ID_lokacije'] ?>">
                                <td class="name"><?= htmlspecialchars($location['Naziv']) ?></td>
                                <td class="address"><?= htmlspecialchars($location['Adresa']) ?></td>
                                <td class="phone"><?= htmlspecialchars($location['Kontakt_telefon']) ?></td>
                                <td class="email"><?= htmlspecialchars($location['Email']) ?></td>
                                <td>
                                    <button onclick="editLocation(<?= $location['ID_lokacije'] ?>)" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi informacije lokacije">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="deleteLocation(<?= $location['ID_lokacije'] ?>)" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši lokaciju">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($locations)): ?>
                            <tr>
                                <td colspan="5">Nema podataka o lokacijama.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
                <div class="table-responsive">
            <!-- Usluge -->
            <h3>Usluge</h3>
            <table class="table table-striped table-sm" id="services-table">
                <thead>
                    <tr>
                        <th>Naziv usluge</th>
                        <th>Opis</th>
                        <th>Cijena</th>
                        <th>Odjel</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody id="services-tbody">
                    <?php foreach ($services as $service): ?>
                    <tr id="service-<?= $service['ID_usluge'] ?>">
                        <td class="name"><?= htmlspecialchars($service['Naziv_usluge']) ?></td>
                        <td class="description"><?= htmlspecialchars($service['Opis']) ?></td>
                        <td class="price"><?= htmlspecialchars($service['Cijena']) ?></td>
                        <td class="department"><?= htmlspecialchars($service['Naziv_odjela']) ?></td>
                        <td>
                            <button onclick="editService(<?= $service['ID_usluge'] ?>)" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi informacije usluge">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                            <button onclick="deleteService(<?= $service['ID_usluge'] ?>)" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši uslugu">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="5">Nema podataka o uslugama.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="text-center my-3">
            <button id="show-more-btn" class="btn btn-primary" onclick="loadMoreServices(<?= $page + 1 ?>)">Prikaži više</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal za dodavanje odjela -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Dodaj novi odjel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDepartmentForm" action="add_department.php" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Naziv odjela:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal za dodavanje lokacije -->
    <div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLocationModalLabel">Dodaj novu lokaciju</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addLocationForm" action="add_location.php" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Naziv lokacije:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresa:</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Kontakt telefon:</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal za dodavanje usluge -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">Dodaj novu uslugu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addServiceForm" action="add_service.php" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Naziv usluge:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Opis:</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Cijena:</label>
                            <input type="text" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Odjel:</label>
                            <select class="form-select" id="department" name="department" required>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= $department['ID_odjela'] ?>"><?= htmlspecialchars($department['Naziv_odjela']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Modal za uređivanje odjela -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDepartmentModalLabel">Uredi Odjel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDepartmentForm">
                    <input type="hidden" id="edit-department-id" name="id">
                    <div class="mb-3">
                        <label for="edit-department-name" class="form-label">Naziv odjela:</label>
                        <input type="text" class="form-control" id="edit-department-name" name="name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal za uređivanje lokacije -->
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLocationModalLabel">Uredi Lokaciju</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLocationForm">
                    <input type="hidden" id="edit-location-id" name="id">
                    <div class="mb-3">
                        <label for="edit-location-name" class="form-label">Naziv lokacije:</label>
                        <input type="text" class="form-control" id="edit-location-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-location-address" class="form-label">Adresa:</label>
                        <input type="text" class="form-control" id="edit-location-address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-location-phone" class="form-label">Kontakt telefon:</label>
                        <input type="text" class="form-control" id="edit-location-phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-location-email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="edit-location-email" name="email" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal za uređivanje usluge -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">Uredi Uslugu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm">
                    <input type="hidden" id="edit-service-id" name="id">
                    <div class="mb-3">
                        <label for="edit-service-name" class="form-label">Naziv usluge:</label>
                        <input type="text" class="form-control" id="edit-service-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-service-description" class="form-label">Opis:</label>
                        <textarea class="form-control" id="edit-service-description" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-service-price" class="form-label">Cijena:</label>
                        <input type="text" class="form-control" id="edit-service-price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-service-department" class="form-label">Odjel:</label>
                        <select class="form-select" id="edit-service-department" name="department" required>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?= $department['ID_odjela'] ?>"><?= htmlspecialchars($department['Naziv_odjela']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<script>
    // Funkcije za otvaranje modala za uređivanje s popunjenim podacima
    function editDepartment(id) {
        const row = document.getElementById('department-' + id);
        const name = row.querySelector('.name').textContent.trim();
        
        document.getElementById('edit-department-id').value = id;
        document.getElementById('edit-department-name').value = name;
        
        $('#editDepartmentModal').modal('show');
    }

    function editLocation(id) {
        const row = document.getElementById('location-' + id);
        const name = row.querySelector('.name').textContent.trim();
        const address = row.querySelector('.address').textContent.trim();
        const phone = row.querySelector('.phone').textContent.trim();
        const email = row.querySelector('.email').textContent.trim();
        
        document.getElementById('edit-location-id').value = id;
        document.getElementById('edit-location-name').value = name;
        document.getElementById('edit-location-address').value = address;
        document.getElementById('edit-location-phone').value = phone;
        document.getElementById('edit-location-email').value = email;
        
        $('#editLocationModal').modal('show');
    }

    function editService(id) {
    const row = document.getElementById('service-' + id);
    if (!row) {
        console.error('No service row found for ID:', id);
        return;
    }

    const nameElem = row.querySelector(".name");
    const descriptionElem = row.querySelector(".description");
    const priceElem = row.querySelector(".price");
    const departmentElem = row.querySelector(".department");

    if (!nameElem || !descriptionElem || !priceElem || !departmentElem) {
        console.error('Some elements are missing for service ID:', id);
        return;
    }

    const name = nameElem.textContent.trim();
    const description = descriptionElem.textContent.trim();
    const price = priceElem.textContent.trim();
    const departmentName = departmentElem.textContent.trim(); // Get the department name

    document.getElementById('edit-service-id').value = id;
    document.getElementById('edit-service-name').value = name;
    document.getElementById('edit-service-description').value = description;
    document.getElementById('edit-service-price').value = price;

    const departmentSelect = document.getElementById('edit-service-department');
    const options = departmentSelect.options;
    
    // Loop through options and set the selected attribute
    for (let i = 0; i < options.length; i++) {
        if (options[i].text === departmentName) {
            options[i].selected = true;
            break;
        }
    }

    $('#editServiceModal').modal('show');
}


    // Funkcije za slanje podataka putem AJAX-a i ažuriranje tabele u realnom vremenu
    document.getElementById('editDepartmentForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('edit_department.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            $('#editDepartmentModal').modal('hide');
            if (data.success) {
                const id = document.getElementById('edit-department-id').value;
                const row = document.getElementById('department-' + id);
                row.querySelector('.name').textContent = formData.get('name');
                showToast(data.message, true);
            } else {
                showToast(data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast("Došlo je do greške u komunikaciji.", false);
        });
    });

    document.getElementById('editLocationForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('edit_location.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            $('#editLocationModal').modal('hide');
            if (data.success) {
                const id = document.getElementById('edit-location-id').value;
                const row = document.getElementById('location-' + id);
                row.querySelector('.name').textContent = formData.get('name');
                row.querySelector('.address').textContent = formData.get('address');
                row.querySelector('.phone').textContent = formData.get('phone');
                row.querySelector('.email').textContent = formData.get('email');
                showToast(data.message, true);
            } else {
                showToast(data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast("Došlo je do greške u komunikaciji.", false);
        });
    });

    document.getElementById('editServiceForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('edit_service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            $('#editServiceModal').modal('hide');
            if (data.success) {
                const id = document.getElementById('edit-service-id').value;
                const row = document.getElementById('service-' + id);
                row.querySelector('.name').textContent = formData.get('name');
                row.querySelector('.description').textContent = formData.get('description');
                row.querySelector('.price').textContent = formData.get('price');
                const departmentSelect = document.getElementById('edit-service-department');
                const selectedDepartment = departmentSelect.options[departmentSelect.selectedIndex].textContent;
                row.querySelector('.department').textContent = selectedDepartment;
                showToast(data.message, true);
            } else {
                showToast(data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast("Došlo je do greške u komunikaciji.", false);
        });
    });


// Function to delete a service
function deleteService(id) {
    if (confirm('Da li ste sigurni da želite izbrisati uslugu?')) {
        fetch('delete_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('service-' + id);
                row.remove();
                showToast('Usluga je uspješno izbrisana.', true);
            } else {
                showToast('Greška prilikom brisanja: ' + data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Greška prilikom komunikacije s poslužiteljem.', false);
        });
    }
}

// Function to delete a location
function deleteLocation(id) {
    if (confirm('Da li ste sigurni da želite izbrisati lokaciju?')) {
        fetch('delete_location.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('location-' + id);
                row.remove();
                showToast('Lokacija je uspješno izbrisana.', true);
            } else {
                showToast('Greška prilikom brisanja: ' + data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Greška prilikom komunikacije s poslužiteljem.', false);
        });
    }
}

// Function to delete a department
function deleteDepartment(id) {
    if (confirm('Da li ste sigurni da želite izbrisati odjel?')) {
        fetch('delete_department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('department-' + id);
                row.remove();
                showToast('Odjel je uspješno izbrisan.', true);
            } else {
                showToast('Greška prilikom brisanja: ' + data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Greška prilikom komunikacije s poslužiteljem.', false);
        });
    }
}

// LOAD MORE ZA USLUGE

function loadMoreServices(page) {
            const url = `odjeliDashboard.php?page=${page}`;

            fetch(url)
                .then(response => response.text())
                .then(data => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const newRows = doc.querySelectorAll('#services-tbody tr');
                    const tbody = document.getElementById('services-tbody');

                    newRows.forEach(row => {
                        tbody.appendChild(row);
                    });

                    const newButton = doc.getElementById('show-more-btn');
                    if (newButton) {
                        document.getElementById('show-more-btn').replaceWith(newButton);
                    } else {
                        document.getElementById('show-more-btn').remove();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

// Function to show toast messages
function showToast(message, success) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white ${success ? 'bg-success' : 'bg-danger'}`;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
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

// Function to add a new department
document.getElementById('addDepartmentForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('add_department.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        $('#addDepartmentModal').modal('hide');
        if (data.success) {
            location.reload();
            showToast(data.message, true);
        } else {
            showToast(data.message, false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});

// Function to add a new location
document.getElementById('addLocationForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('add_location.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        $('#addLocationModal').modal('hide');
        if (data.success) {
            location.reload();
            showToast(data.message, true);
        } else {
            showToast(data.message, false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});

// Function to add a new service
document.getElementById('addServiceForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('add_service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        $('#addServiceModal').modal('hide');
        if (data.success) {
            location.reload();
            showToast(data.message, true);
        } else {
            showToast(data.message, false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});





</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>