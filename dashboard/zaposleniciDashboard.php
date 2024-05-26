<?php

include 'check_admin_zaposlenici.php';
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
                <li>
                        <a href="dashboard.php" class="nav-link text-dark">
                            <i class="fa-solid fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="zaposleniciDashboard.php" class="nav-link active" aria-current="page">
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

            <div class="main-content">
                <!-- Main Content -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Zaposlenici</h1>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
    Dodaj zaposlenog
</button>

                </div>

                <div class="row">
                <div class="col-md-4">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Ukupno zaposlenih</h5>
            <p class="card-text" id="totalEmployees"><?= $total_employees ?></p>
        </div>
    </div>
</div>
<div class="col-md">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Aktivni zaposlenici</h5>
            <p class="card-text" id="activeEmployees"><?= $active_employees ?></p>
        </div>
    </div>
</div>
<div class="col-md">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Neaktivni zaposlenici</h5>
            <p class="card-text" id="inactiveEmployees"><?= $inactive_employees ?></p>
        </div>
    </div>
</div>

</div>



<!-- Filtriranje -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

    <!-- Kontejner za sva tri dropdowna -->
    <div class="d-flex">

        <!-- Dropdown za filtriranje po poziciji -->
        <div class="input-group mb-3 me-3" style="width: 320px;">
            <label class="input-group-text" for="positionFilter">Pozicija</label>
            <select class="form-select" id="positionFilter">
                <option value="all">Sve pozicije</option>
                <option value="glavni doktor">Glavni doktor</option>
                <option value="doktor">Doktor</option>
                <option value="glavni medicinski tehničar">Glavni medicinski tehničar/sestra</option>
                <option value="medicinski tehničar">Medicinski tehničar/sestra</option>
            </select>
        </div>

        <!-- Dropdown za filtriranje po lokaciji -->
        <div class="input-group mb-3 me-3" style="width: 320px;">
            <label class="input-group-text" for="locationFilter">Lokacija</label>
            <select class="form-select" id="locationFilter">
                <option value="all">Sve lokacije</option>
                <?php foreach ($lokacije as $lokacija): ?>
                    <option value="<?= htmlspecialchars($lokacija['Naziv']) ?>"><?= htmlspecialchars($lokacija['Naziv']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Dropdown za filtriranje po odjelu -->
        <div class="input-group mb-3 me-3" style="width: 320px;">
            <label class="input-group-text" for="departmentFilter">Odjel</label>
            <select class="form-select" id="departmentFilter">
                <option value="all">Svi odjeli</option>
                <?php foreach ($odjeli as $odjel): ?>
                    <option value="<?= htmlspecialchars($odjel['Naziv_odjela']) ?>"><?= htmlspecialchars($odjel['Naziv_odjela']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
</div>




              <!-- Tabela sa zaposlenima -->
<div class="table-responsive">
<p id="resultCount">Tablica pokazuje    rezultata.</p>
    <table class="table table-striped table-sm" >
        <thead >
            <tr>
                <th>Ime i prezime</th>
                <th>Pozicija</th>
                <th>Email</th>
                <th>Status</th>
                <th>Odjel</th>
                <th>Lokacija</th>
                <th>Akcije</th>
            </tr>
        </thead>
       <tbody>
    <?php foreach ($employees as $employee): ?>
    <tr id="employee-<?= $employee['ID_osoblja'] ?>">
        <td class="name"><?= htmlspecialchars($employee['Ime']) . ' ' . htmlspecialchars($employee['Prezime']) ?></td>
        <td class="position"><?= htmlspecialchars($employee['Pozicija']) ?></td>
        <td class="email"><?= htmlspecialchars($employee['Email']) ?></td>
        <td class="status"><?= $employee['status_zaposlenog'] == 1 ? 'Aktivan' : 'Neaktivan' ?></td>
        <td class="department" data-department-id="<?= htmlspecialchars($employee['ID_odjela']) ?>">
            <?= isset($employee['Naziv_odjela']) ? htmlspecialchars($employee['Naziv_odjela']) : 'N/A' ?>
        </td>
        <td class="location" data-location-id="<?= htmlspecialchars($employee['ID_lokacije']) ?>">
            <?= isset($employee['Naziv_lokacije']) ? htmlspecialchars($employee['Naziv_lokacije']) : 'N/A' ?>
        </td>
        <td>
            <button onclick="editEmployee(<?= $employee['ID_osoblja'] ?>)" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi informacije zaposlenog">
                <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <button onclick="deleteEmployee(<?= $employee['ID_osoblja'] ?>)" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši zaposlenog">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($employees)): ?>
    <tr>
        <td colspan="7">Nema podataka o zaposlenima.</td>
    </tr>
    <?php endif; ?>
</tbody>

    </table>
</div>




            </div>
        </div>
    </div>



    <!DOCTYPE html>
<html lang="en">
<head>
     
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Osoblje</title>
</head>
<body>


<!-- Modal za dodavanje zaposlenog -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Dodaj novog zaposlenog</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" action="add_staff.php" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Ime:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="surname" class="form-label">Prezime:</label>
                        <input type="text" class="form-control" id="surname" name="surname" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefon:</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1">Aktivan</option>
                            <option value="0">Neaktivan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Odjel:</label>
                        <select class="form-select" id="department" name="department" required>
                            <?php foreach ($odjeli as $odjel): ?>
                                <option value="<?= $odjel['ID_odjela'] ?>"><?= htmlspecialchars($odjel['Naziv_odjela']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Pozicija:</label>
                        <select class="form-select" id="position" name="position" required>
                            <option value="glavni doktor">Glavni doktor</option>
                            <option value="doktor">Doktor</option>
                            <option value="glavni medicinski tehničar">Glavni medicinski tehničar/sestra</option>
                            <option value="medicinski tehničar">Medicinski tehničar/sestra</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Lokacija:</label>
                        <select class="form-select" id="location" name="location" required>
                            <?php
                            // PHP kod za dohvaćanje lokacija iz baze
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
                        <label for="password" class="form-label">Lozinka:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
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

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11;">
        <!-- Toasts će biti dinamički dodani ovdje -->
    </div>
</div>

<!-- Modal za uređivanje zaposlenog -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEmployeeModalLabel">Uredi Zaposlenog</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editEmployeeForm">
          <input type="hidden" id="edit-id">
          <div class="mb-3">
            <label for="edit-name" class="form-label">Ime:</label>
            <input type="text" class="form-control" id="edit-name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="edit-surname" class="form-label">Prezime:</label>
            <input type="text" class="form-control" id="edit-surname" name="surname" required>
          </div>
          <div class="mb-3">
            <label for="edit-email" class="form-label">Email:</label>
            <input type="email" class="form-control" id="edit-email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="edit-status" class="form-label">Status:</label>
            <select class="form-select" id="edit-status" name="status" required>
              <option value="1">Aktivan</option>
              <option value="0">Neaktivan</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-department" class="form-label">Odjel:</label>
            <select class="form-select" id="edit-department" name="department" required>
              <?php foreach ($odjeli as $odjel): ?>
                <option value="<?= $odjel['ID_odjela'] ?>"><?= htmlspecialchars($odjel['Naziv_odjela']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-location" class="form-label">Lokacija:</label>
            <select class="form-select" id="edit-location" name="location" required>
              <?php foreach ($lokacije as $lokacija): ?>
                <option value="<?= $lokacija['ID_lokacije'] ?>"><?= htmlspecialchars($lokacija['Naziv']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-position" class="form-label">Pozicija:</label>
            <select class="form-select" id="edit-position" name="position" required>
            <option value="glavni doktor">Glavni doktor</option>
            <option value="doktor">Doktor</option>
            <option value="glavni doktor">Glavni medicinski tehničar/sestra</option>
            <option value="medicinski tehničar">Medicinski tehničar/sestra</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
            <button type="submit" class="btn btn-primary">Sačuvaj Promene</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>





<script>

// Funkcija za prikaz toasta
function showToast(message, success = true) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white ${success ? 'bg-success' : 'bg-danger'} border-0`;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '1050';
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

// Funkcija za dodavanje zaposlenika
document.getElementById('addEmployeeForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Spriječiti standardno slanje forme

    var formData = new FormData(this);

    fetch('add_staff.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Obrada odgovora kao JSON
    .then(data => {
        if (data.success) {
            showToast(data.message, data.success);
            // Osvježi stranicu nakon uspješnog dodavanja zaposlenika
            location.reload();
        } else {
            showToast(data.message, data.success);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});

function resetModalForm() {
    const form = document.getElementById('addEmployeeForm');
    form.reset();
    form.classList.remove('was-validated');
}

function addTableRow(employee) {
    const tableBody = document.querySelector('table tbody');
    const newRow = document.createElement('tr');
    newRow.id = 'employee-' + employee.ID_osoblja;

    newRow.innerHTML = `
        <td class="name">${employee.Ime} ${employee.Prezime}</td>
        <td class="position">${employee.Pozicija}</td>
        <td class="email">${employee.Email}</td>
        <td class="status">${employee.status_zaposlenog == 1 ? 'Aktivan' : 'Neaktivan'}</td>
        <td class="department" data-department-id="${employee.ID_odjela}">${employee.Naziv_odjela || 'N/A'}</td>
        <td class="location" data-location-id="${employee.ID_lokacije}">${employee.Naziv_lokacije || 'N/A'}</td>
        <td>
            <button onclick="editEmployee(${employee.ID_osoblja})" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Uredi informacije zaposlenog">
                <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <button onclick="deleteEmployee(${employee.ID_osoblja})" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Izbriši zaposlenog">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </td>
    `;

    tableBody.appendChild(newRow);
}

// Funkcija za ažuriranje broja zaposlenih
function updateEmployeeCounts() {
    fetch('get_employee_counts.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalEmployees').textContent = data.total;
            document.getElementById('activeEmployees').textContent = data.active;
            document.getElementById('inactiveEmployees').textContent = data.inactive;
        })
        .catch(error => console.error('Error fetching employee counts:', error));
}

// Funkcija za uređivanje zaposlenika
function editEmployee(id) {
    const row = document.getElementById('employee-' + id);
    if (!row) {
        console.error('No employee row found for ID:', id);
        return;
    }

    const nameElem = row.querySelector(".name");
    const emailElem = row.querySelector(".email");
    const positionElem = row.querySelector(".position");
    const statusElem = row.querySelector(".status");
    const departmentElem = row.querySelector(".department");
    const locationElem = row.querySelector(".location");

    // Provjera elemenata
    if (!nameElem || !emailElem || !positionElem || !statusElem || !departmentElem || !locationElem) {
        console.error('Some elements are missing for employee ID:', id);
        return;
    }

    // Dalje izvršavanje
    const names = nameElem.textContent.trim().split(' ');
    const name = names[0];
    const surname = names[1] || '';
    const email = emailElem.textContent.trim();
    const position = positionElem.textContent.trim();
    const status = statusElem.textContent.trim() === 'Aktivan' ? '1' : '0';
    const departmentId = departmentElem.dataset.departmentId;
    const locationId = locationElem.dataset.locationId;

    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-surname').value = surname;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-position').value = position;
    document.getElementById('edit-status').value = status;
    document.getElementById('edit-department').value = departmentId;
    document.getElementById('edit-location').value = locationId;

    // Postavljanje trenutne lokacije i odjela u dropdown
    document.getElementById('edit-location').value = locationId;
    document.getElementById('edit-department').value = departmentId;

    $('#editEmployeeModal').modal('show');
}

// AJAX za uređivanje zaposlenika
document.getElementById('editEmployeeForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Spriječiti standardno slanje forme

    var formData = new FormData(this);
    formData.append('id', document.getElementById('edit-id').value);
    formData.append('status', document.getElementById('edit-status').value); // Dodajte ovu liniju

    fetch('edit_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        $('#editEmployeeModal').modal('hide');
        if (data.success) {
            showToast(data.message, true);
            // Ažuriranje podataka u tabeli
            updateTableRow(document.getElementById('edit-id').value, formData);
            updateEmployeeCounts(); // Ažuriraj brojače zaposlenih
        } else {
            showToast(data.message, false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Došlo je do greške u komunikaciji.", false);
    });
});


function updateTableRow(id, formData) {
    var row = document.getElementById('employee-' + id);
    if (!row) {
        console.error('No row found for ID:', id);
        return;
    }

    row.querySelector(".name").textContent = formData.get('name') + ' ' + formData.get('surname');
    row.querySelector(".position").textContent = formData.get('position');
    row.querySelector(".email").textContent = formData.get('email');

    var statusText = formData.get('status') === '1' ? 'Aktivan' : 'Neaktivan';
    row.querySelector(".status").textContent = statusText;

    // Update department
    var departmentSelect = document.getElementById('edit-department');
    if (departmentSelect) {
        var selectedDepartment = departmentSelect.options[departmentSelect.selectedIndex].textContent;
        row.querySelector(".department").textContent = selectedDepartment;
        row.querySelector(".department").dataset.departmentId = departmentSelect.value;
    }

    // Update location
    var locationSelect = document.getElementById('edit-location');
    if (locationSelect) {
        var selectedLocation = locationSelect.options[locationSelect.selectedIndex].textContent;
        row.querySelector(".location").textContent = selectedLocation;
        row.querySelector(".location").dataset.locationId = locationSelect.value;
    }
}


// Funkcija za brisanje zaposlenika
function deleteEmployee(id) {
    if (confirm('Da li ste sigurni da želite izbrisati zaposlenog?')) {
        fetch('delete_employee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('employee-' + id);
                row.remove();
                alert('Zaposleni je uspješno izbrisan.');
                updateEmployeeCounts(); // Ažuriraj brojače zaposlenih
            } else {
                alert('Greška prilikom brisanja: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Filtriranje zaposlenika
document.getElementById('positionFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('locationFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('departmentFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    var selectedPosition = document.getElementById('positionFilter').value.toLowerCase();
    var selectedLocation = document.getElementById('locationFilter').value.toLowerCase();
    var selectedDepartment = document.getElementById('departmentFilter').value.toLowerCase();
    var rows = document.querySelectorAll('table tbody tr');

    var visibleRowCount = 0;

    rows.forEach(function(row) {
        var position = row.querySelector('.position').textContent.toLowerCase();
        var location = row.querySelector('.location').textContent.toLowerCase();
        var department = row.querySelector('.department').textContent.toLowerCase();

        var positionMatch = (selectedPosition === "all" || position.includes(selectedPosition));
        var locationMatch = (selectedLocation === "all" || location.includes(selectedLocation));
        var departmentMatch = (selectedDepartment === "all" || department.includes(selectedDepartment));

        if (positionMatch && locationMatch && departmentMatch) {
            row.style.display = "";
            visibleRowCount++;
        } else {
            row.style.display = "none";
        }
    });

    document.getElementById('resultCount').textContent = `Tablica pokazuje ${visibleRowCount} rezultata.`;
}
// Poziv funkcije filterTable() prilikom inicijalnog učitavanja stranice kako bi se ispravno postavio broj rezultata
filterTable();
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>