<?php
session_start();
include('cfg.php');

// Statyczny adres e-mail do wysyłania wiadomości
define('STATIC_EMAIL', 'admin@example.com');

/**
 * Wyświetla formularz kontaktowy
 * 
 * @return string Kod HTML formularza kontaktowego
 */
function PokazKontakt() {
    // Tworzenie formularza kontaktowego z zabezpieczeniami XSS
    $wynik = '
    <div class="content">
        <h2>Formularz kontaktowy</h2>
        <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
            <div class="form-group">
                <label for="email">Twój email:</label><br>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="temat">Temat:</label><br>
                <input type="text" name="temat" id="temat" required>
            </div>
            
            <div class="form-group">
                <label for="tresc">Treść wiadomości:</label><br>
                <textarea name="tresc" id="tresc" rows="5" required></textarea>
            </div>
            
            <input type="submit" name="wyslij" value="Wyślij wiadomość">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Wysyła email z formularza kontaktowego
 * 
 * @return void
 */
function WyslijMailKontakt() {
    // Sprawdzenie czy wszystkie pola są wypełnione
    if (empty($_POST['temat']) || empty($_POST['tresc']) || empty($_POST['email'])) {
        echo '<p class="error">[nie_wypelniles_pola]</p>';
        echo PokazKontakt();
        return;
    }

    // Zabezpieczenie danych przed atakiem
    $mail['subject'] = htmlspecialchars($_POST['temat']);
    $mail['body'] = htmlspecialchars($_POST['tresc']);
    $mail['sender'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $mail['recipient'] = STATIC_EMAIL; // Użycie statycznego adresu e-mail

    // Przygotowanie nagłówków emaila
    $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
    $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit\n";
    $header .= "X-Sender: " . $mail['sender'] . "\n";
    $header .= "X-Mailer: PHP/mail 1.2\n";
    $header .= "X-Priority: 3\n";
    $header .= "Return-Path: <" . $mail['sender'] . ">\n";

    // Wysłanie emaila i obsługa wyniku
    if(mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
        echo '<p class="success">[wiadomosc_wyslana]</p>';
    } else {
        echo '<p class="error">[blad_wysylania]</p>';
        echo PokazKontakt();
    }
}

// Wywołanie funkcji do wysyłania maila, jeśli formularz został wysłany
if (isset($_POST['wyslij'])) {
    WyslijMailKontakt();
}

// Funkcja do pobierania nawigacji z bazy danych
function PobierzNawigacje($link) {
    $query = "SELECT id, page_title FROM page_list WHERE status = 1 ORDER BY id";
    $result = mysqli_query($link, $query);
    $nawigacja = '';

    while ($row = mysqli_fetch_assoc($result)) {
        // Generowanie linku z parametrem id
        $nawigacja .= '<a href="index.php?id=' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['page_title']) . '</a> ';
    }

    // Dodanie linków do Kontakt i Sklep
    $nawigacja .= '<a href="contact.php">Kontakt</a> ';
    $nawigacja .= '<a href="shop.php">Sklep</a> ';

    return $nawigacja;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt - Komputer Moja Pasja</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <h1>Komputer Moja Pasja</h1>
        <nav class="nav-container">
            <div class="nav-links">
                <?php echo PobierzNawigacje($link); ?>
            </div>
        </nav>
    </header>

    <main>
        <div class="content">
            <?php echo PokazKontakt(); ?>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div id="zegarek"></div>
            <div id="data"></div>
            <div class="footer-content">
            <p>&copy; 2024 Komputer Moja Pasja. Wszelkie prawa zastrzeżone.</p>
        </div>
    </footer>
</body>
</html> 