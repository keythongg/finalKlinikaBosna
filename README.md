# Dokumentacija za Sigurnost Web Aplikacije Klinika Bosna

Ova dokumentacija se fokusira na sigurnosne aspekte ključnih dijelova aplikacije Klinika Bosna, uključujući datoteke `profil.php`, `registracija.php`, `login.php`, `unos_2fa.php`, `password-recovery.php`, `new-passowrd.php` i `kod_password.php`. Svaka datoteka će biti detaljno objašnjena kako bi se osiguralo da su sigurnosni mehanizmi jasni i dobro implementirani.



## 1. **registracija.php**

**Opis:**
Ova datoteka omogućava registraciju novih korisnika.

### Sigurnosne mjere:

1. **Povezivanje sa bazom podataka:**

    ```php
    $servername = "localhost";
    $username = "username";
    $password = "password";
    $database = "ime-baze-podataka";

    $conn = mysqli_connect($servername, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    ```

2. **Pokretanje sesije:**

    ```php
    session_start();
    ```

3. **Unos i validacija podataka:**
    - Prikupljanje i validacija korisničkih podataka, uključujući provjeru formata email adrese i snagu lozinke. Metoda **POST** koristi se za slanje podataka na server radi obrade. Kada se koristi u formi, podaci se šalju u tijelu HTTP zahtjeva, što znači da nisu vidljivi u URL-u, čime se povećava sigurnost osjetljivih informacija poput lozinki. POST metoda također omogućava slanje većih količina podataka u usporedbi s GET metodom, što je korisno za složenije forme i datoteke.

[![image.png](https://i.postimg.cc/mZY377BC/image.png)](https://postimg.cc/QVMW39Bd)

    Kod prikazan ispod pokazuje kako se koriste prepared statements za sigurnu interakciju s bazom podataka, što smanjuje rizik od SQL injekcija:
    - **prepare** metoda se koristi za pripremu SQL upita. Ovo pomaže u sprječavanju SQL injekcija jer upit nije izravno interpoliran sa korisničkim unosima.
    - **bind_param** metoda veže varijable na odgovarajuće pozicije u upitu. Tipovi parametara se specificiraju koristeći formatne kodove (npr. s za string).
    - **execute** metoda izvršava pripremljeni upit sa vezanim parametrima.

```php
   // Postavljanje varijabli za čuvanje unesenih podataka
$ime = $prezime = $email = $lozinka = $datum_rodjenja = "";

// Provjera da li je korisnik kliknuo na dugme za registraciju
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dobijanje unesenih podataka iz forme
    $_SESSION['ime'] = $_POST["ime"];
    $_SESSION['prezime'] = $_POST["prezime"];
    $_SESSION['email'] = $_POST["email"];
    $_SESSION['datum_rodjenja'] = $_POST["datum_rodjenja"];
    
    $ime = $_SESSION['ime'];
    $prezime = $_SESSION['prezime'];
    $email = $_SESSION['email'];
    $lozinka = $_POST["password"];
    $datum_rodjenja = $_SESSION['datum_rodjenja'];

    // Provjera ispravnosti formata emaila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger" role="alert">Neispravan format emaila.</div>';
    } else if (strlen($lozinka) < 8 || !preg_match('/[0-9]/', $lozinka) || !preg_match('/\W/', $lozinka)) {
        echo '<div class="alert alert-danger" role="alert">Lozinka mora imati minimum 8 znakova, uključujući brojeve i specijalne znakove.</div>';
        $lozinka = "";
    } else {
        // Priprema SQL upita za provjeru emaila
        $stmt = $conn->prepare("SELECT * FROM Korisnici WHERE Email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<div class="alert alert-danger" role="alert">Korisnik s tim emailom već postoji.</div>';
        } else {
            // Hashovanje lozinke
            $hashed_password = password_hash($lozinka, PASSWORD_DEFAULT);
            // Priprema SQL upita za unos korisnika
            $stmt = $conn->prepare("INSERT INTO Korisnici (Ime, Prezime, Email, Password, Datum_rodjenja, Tip_korisnika) VALUES (?, ?, ?, ?, ?, 'obični korisnik')");
            $stmt->bind_param("sssss", $ime, $prezime, $email, $hashed_password, $datum_rodjenja);
            
            if ($stmt->execute()) {
                echo '<div class="alert alert-success" role="alert">Uspješno ste se registrovali.</div>';
                session_unset(); // Prazni sve sesija varijable
                sleep(3);
                header("Location: login.php");
                exit();
            } else {
                echo '<div class="alert alert-danger" role="alert">Greška prilikom registracije: ' . $stmt->error . '</div>';
            }
        }
        $stmt->close();
    }
}
```

## 2. **login.php**

**Opis:**
Ova datoteka omogućava prijavu korisnika na aplikaciju.
[![image.png](https://i.postimg.cc/FzDSfg34/image.png)](https://postimg.cc/5QQyTzs7)

### Sigurnosne mjere:
1. **Ograničavanje pokušaja prijave:**
    - Implementacija mehanizma za ograničavanje broja pokušaja prijave kako bi se spriječili brute force napadi.

    ```php
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
    }

    $current_time = time();
    $lockout_time = 1 * 60; //  minuta u sekundama

    if ($_SESSION['login_attempts'] > 5 && ($current_time - $_SESSION['last_attempt_time']) < $lockout_time) {
        die('Previše neuspješnih pokušaja prijave. Molimo pokušajte ponovno kasnije.');
    }
    ```

2. **Validacija korisničkih podataka:**
    - Provjera unesenih podataka i validacija lozinke.

    ```php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT * FROM Korisnici WHERE Email = ?");
        $stmt->bind_param("s", $email);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                if ($row['twofa_enabled'] == 1) {
                    $_SESSION['twofa_pending'] = true;
                    $_SESSION['email'] = $email;
                    header("Location: unos_2fa.php");
                    exit();
                } else {
                    $_SESSION['id'] = $row['ID_korisnika'];
                    $_SESSION['email'] = $row['Email'];
                    $_SESSION['ime'] = $row['Ime'];
                    $_SESSION['prezime'] = $row['Prezime'];
                    $_SESSION['login_attempts'] = 0;
                    header("Location: landing.php");
                    exit();
                }
            } else {
                echo '<div class="alert alert-danger" role="alert">Pogrešno korisničko ime ili lozinka.</div>';
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = $current_time;
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Pogrešno korisničko ime ili lozinka.</div>';
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = $current_time;
        }

        $stmt->close();
    }

    if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
        setcookie('email', '', time() - 3600, "/");
        setcookie('password', '', time() - 3600, "/");
    }
    ```
    
## 3. **profil.php**

**Opis:**
Ova datoteka omogućava korisnicima pregled i ažuriranje njihovog profila.
[![image.png](https://i.postimg.cc/0y6SQSNT/image.png)](https://postimg.cc/xX28pcM5)
### Sigurnosne mjere:


1. **Sesija i autentifikacija:**
    - Provjerava se da li je korisnik prijavljen pomoću sesije. Ako korisnik nije prijavljen, preusmjerava se na stranicu za prijavu.

    ```php
    session_start();
    if (!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }
    ```


2. **Dohvatanje i ažuriranje korisničkih podataka:**
    - Korisnički podaci se dohvaćaju iz baze i prikazuju na formi za ažuriranje.

    ```php
    $user_id = $_SESSION['id'];
    $sql_get_user_data = "SELECT * FROM Korisnici WHERE ID_korisnika='$user_id'";
    $result_user_data = mysqli_query($conn, $sql_get_user_data);
    $row = mysqli_fetch_assoc($result_user_data);
    ```

3. **Ažuriranje profila:**
    - Kada korisnik podnese formu za ažuriranje, podaci se validiraju i ažuriraju u bazi.
    - **Validacija lozinke:** Provjerava se dužina lozinke, prisustvo brojeva i specijalnih znakova.

    ```php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $telefon = $_POST['telefon'];
        $twofa_enabled = isset($_POST['twofa_enabled']) ? 1 : 0;

        if (!empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
            if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
                $_SESSION['error_message'] = 'Oba polja za novu šifru moraju biti popunjena.';
            } else {
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                $password_errors = [];
                if (strlen($new_password) < 8) {
                    $password_errors[] = 'Šifra mora imati najmanje 8 znakova.';
                }
                if (!preg_match('/[0-9]/', $new_password)) {
                    $password_errors[] = 'Šifra mora sadržavati barem jedan broj.';
                }
                if (!preg_match('/\\W/', $new_password)) {
                    $password_errors[] = 'Šifra mora sadržavati barem jedan specijalni znak.';
                }

                $sql_last_passwords = "SELECT Password FROM passwordhistory WHERE ID_korisnika='$user_id' ORDER BY ID DESC LIMIT 4";
                $result_last_passwords = mysqli_query($conn, $sql_last_passwords);
                if (!$result_last_passwords) {
                    die('Query failed: ' . mysqli_error($conn));
                }
                $last_passwords = array();
                while ($row_password = mysqli_fetch_assoc($result_last_passwords)) {
                    $last_passwords[] = $row_password['Password'];
                }

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
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql_update_password = "UPDATE Korisnici SET Password='$hashed_password' WHERE ID_korisnika='$user_id'";
                    if (mysqli_query($conn, $sql_update_password)) {
                        $_SESSION['success_message'] = 'Šifra uspešno ažurirana!';
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

        $sql_update_profile = "UPDATE Korisnici SET Ime='$username', Email='$email', Telefon='$telefon', twofa_enabled='$twofa_enabled' WHERE ID_korisnika='$user_id'";
        if (mysqli_query($conn, $sql_update_profile) && empty($_SESSION['error_message'])) {
            $_SESSION['success_message'] = 'Profil uspešno ažuriran!';
            $_SESSION['ime'] = $username;
        } else {
            $_SESSION['error_message'] = 'Greška prilikom ažuriranja profila: ' . mysqli_error($conn);
        }

        $result_user_data = mysqli_query($conn, $sql_get_user_data);
        $row = mysqli_fetch_assoc($result_user_data);
    }
    ```

## 4. **unos_2fa.php**

**Opis:**
Ova datoteka omogućava unos 2FA koda za dodatnu sigurnost prilikom prijave.
[![image.png](https://i.postimg.cc/hGXN2crF/image.png)](https://postimg.cc/2qfH6pMT)
### Sigurnosne mjere:


1. **Provjera 2FA statusa:**
    - Provjerava se da li je korisnik prijavljen i očekuje unos 2FA koda.

    ```php
    session_start();
    if (!isset($_SESSION['twofa_pending']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
    }
    ```

2. **Validacija 2FA koda:**
    - Provjerava se da li je uneseni 2FA kod ispravan.

    ```php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $twofa_code = $_POST["twofa_code"];
    if ($twofa_code === $_SESSION['twofa_code']) {
        unset($_SESSION['twofa_pending']);
        $email = $_SESSION['email'];
        $sql = "SELECT * FROM Korisnici WHERE Email='$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['id'] = $row['ID_korisnika'];
            $_SESSION['ime'] = $row['Ime'];
            $_SESSION['prezime'] = $row['Prezime'];
            header("Location: landing.php");
            exit();
        } else {
            echo "Greška pri dohvaćanju korisničkih podataka.";
        }
    } else {
        $error_message = "Neispravan 2FA kod. Molimo pokušajte ponovo.";
        }
    }
    ```

3. **Slanje 2FA koda putem emaila:**

``` php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email-sa-kojeg-se-šalje-email';
    $mail->Password = 'kod (ako je preko gmaila treba uključiti 2FA na gmailu i kreirati app-password i taj kreirani passowrd zalijepiti ovdje )';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('klinikabosna@gmail.com', 'Klinika Bosna');
    $mail->addAddress($_SESSION['email']);
    $mail->Subject = '2FA Kod';

    function generateRandomCode($length = 6) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUWXYZ';
        $code = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $max)];
        }
        return $code;
    }

    $twofa_code = generateRandomCode();
    $_SESSION['twofa_code'] = $twofa_code;
    $mail->Body = 'Vaš 2FA kod je: ' . $twofa_code;
    $mail->send();
} catch (Exception $e) {
    echo 'Poruka o grešci prilikom slanja emaila: ', $mail->ErrorInfo;
}
```

## 5. **password-recovery.php**

**Opis:**
Ova datoteka omogućava korisnicima da zatraže kod za resetiranje lozinke putem emaila.

### Sigurnosne mjere:

1. **Validacija email adrese:**
    - Provjerava se da li uneseni email postoji u bazi podataka.

    ```php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $sql = "SELECT * FROM Korisnici WHERE Email='$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'klinikabosna@gmail.com';
                $mail->Password = 'kod (ako je preko gmaila treba uključiti 2FA na gmailu i kreirati app-password i taj kreirani passowrd zalijepiti ovdje )';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('klinikabosna@gmail.com', 'Klinika Bosna');
                $mail->addAddress($email);
                $mail->Subject = 'Resetiranje lozinke';

                function generatePasswordCode($length = 6) {
                    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $code = '';
                    $max = strlen($characters) - 1;
                    for ($i = 0; $i < $length; $i++) {
                        $code .= $characters[random_int(0, $max)];
                    }
                    return $code;
                }

                $reset_code = generatePasswordCode();
                $_SESSION['reset_code'] = $reset_code;
                $_SESSION['reset_email'] = $email;
                $mail->Body = 'Vaš kod za resetiranje lozinke je: ' . $reset_code;
                $mail->send();
                header("Location: kod_password.php");
                exit();
            } catch (Exception $e) {
                echo 'Poruka o grešci prilikom slanja emaila: ', $mail->ErrorInfo;
            }
        } else {
            $error_message = "Uneseni email nije pronađen u našoj bazi.";
        }
    }
    ```

## 6. **kod_password.php**

**Opis:**
Ova datoteka omogućava korisnicima da unesu kod za resetiranje lozinke koji su primili putem emaila.

### Sigurnosne mjere:


1. **Validacija koda:**
    - Provjerava se da li je uneseni kod ispravan i odgovara li kodu poslanom na email.

    ```php
    session_start();
    if (!isset($_SESSION['reset_code']) || empty($_SESSION['reset_code'])) {
        header("Location: password-recovery.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $entered_code = $_POST["entered_code"];
        if ($entered_code === $_SESSION['reset_code']) {
            header("Location: new-password.php");
            exit();
        } else {
            $error_message = "Neispravan kod. Molimo pokušajte ponovo.";
        }
    }
    ```

## 7. **new-password.php**

**Opis:**
Ova datoteka omogućava korisnicima da unesu novu lozinku nakon što su unijeli ispravan kod za resetiranje lozinke.
[![image.png](https://i.postimg.cc/fRRxnbpP/image.png)](https://postimg.cc/9rs4ycWP)
### Sigurnosne mjere:

1. **Validacija lozinke:**
    - Provjerava se da li su unijete lozinke iste i da li ispunjavaju sigurnosne kriterije.

    ```php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];
            $sql = "UPDATE Korisnici SET Password='$hashed_password' WHERE Email='$email'";
            if (mysqli_query($conn, $sql)) {
                $success_message = "Uspješno ste promijenili šifru.";
            } else {
                $error_message = "Greška pri promjeni šifre: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Nove šifre se ne podudaraju. Molimo pokušajte ponovo.";
        }
    }
    ```

## Zaključak

Implementacija sigurnosnih mjera u ovim datotekama osigurava da su korisnički podaci zaštićeni i da je aplikacija otporna na uobičajene napade poput SQL injekcija, brute force napada i neautorizovanog pristupa. 


