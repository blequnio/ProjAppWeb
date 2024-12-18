<?php
// Funkcja wyświetlająca formularz kontaktowy
function PokazKontakt() {
    $wynik = '
    <div class="content">
        <h2>Formularz kontaktowy</h2>
        <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
            <div class="form-group">
                <label for="email">Twój email:</label><br>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="temat">Temat:</label><br>
                <input type="text" name="temat" required>
            </div>
            
            <div class="form-group">
                <label for="tresc">Treść wiadomości:</label><br>
                <textarea name="tresc" rows="5" required></textarea>
            </div>
            
            <input type="submit" name="wyslij" value="Wyślij wiadomość">
        </form>
    </div>';
    
    return $wynik;
}

// Funkcja wysyłająca maila z formularza kontaktowego
function WyslijMailKontakt($odbiorca) {
    if (empty($_POST['temat']) || empty($_POST['tresc']) || empty($_POST['email'])) {
        echo '[nie_wypelniles_pola]';
        echo PokazKontakt(); // ponowne wywołanie formularza
    } else {
        $mail['subject'] = $_POST['temat'];
        $mail['body'] = $_POST['tresc'];
        $mail['sender'] = $_POST['email'];
        $mail['recipient'] = $odbiorca;

        $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
        $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit\n";
        $header .= "X-Sender: " . $mail['sender'] . "\n";
        $header .= "X-Mailer: PHP/mail 1.2\n";
        $header .= "X-Priority: 3\n";
        $header .= "Return-Path: <" . $mail['sender'] . ">\n";

        if(mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
            echo '[wiadomosc_wyslana]';
        } else {
            echo '[blad_wysylania]';
            echo PokazKontakt();
        }
    }
}

// Funkcja przypominająca hasło
function PrzypomnijHaslo() {
    global $login, $pass; // pobieramy dane z cfg.php
    
    $wynik = '
    <div class="content">
        <h2>Przypomnienie hasła</h2>
        <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
            <div class="form-group">
                <label for="email">Podaj email administratora:</label><br>
                <input type="email" name="email" required>
            </div>
            <input type="submit" name="przypomnij" value="Przypomnij hasło">
        </form>
    </div>';
    
    if(isset($_POST['przypomnij'])) {
        $odbiorca = $_POST['email'];
        
        $mail['subject'] = 'Przypomnienie hasła do panelu administracyjnego';
        $mail['body'] = "Login: $login\nHasło: $pass";
        $mail['sender'] = 'admin@example.com';
        $mail['recipient'] = $odbiorca;

        $header = "From: System przypominania hasła <" . $mail['sender'] . ">\n";
        $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit\n";
        $header .= "X-Sender: " . $mail['sender'] . "\n";
        $header .= "X-Mailer: PHP/mail 1.2\n";
        $header .= "X-Priority: 3\n";
        $header .= "Return-Path: <" . $mail['sender'] . ">\n";

        if(mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
            $wynik .= '<p class="success">Hasło zostało wysłane na podany adres email.</p>';
        } else {
            $wynik .= '<p class="error">Wystąpił błąd podczas wysyłania hasła.</p>';
        }
    }
    
    return $wynik;
}
?> 