<?php
/**
 * Plik zawierający funkcje związane z obsługą formularza kontaktowego i przypominania hasła
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.0
 */

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
 * @param string $odbiorca Adres email odbiorcy
 * @return void
 */
function WyslijMailKontakt($odbiorca) {
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
    $mail['recipient'] = filter_var($odbiorca, FILTER_SANITIZE_EMAIL);

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

/**
 * Obsługuje funkcję przypominania hasła administratora
 * 
 * @return string Kod HTML formularza przypominania hasła i wynik operacji
 */
function PrzypomnijHaslo() {
    global $login, $pass;
    
    // Tworzenie formularza przypominania hasła
    $wynik = '
    <div class="content">
        <h2>Przypomnienie hasła</h2>
        <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
            <div class="form-group">
                <label for="email">Podaj email administratora:</label><br>
                <input type="email" name="email" id="email" required>
            </div>
            <input type="submit" name="przypomnij" value="Przypomnij hasło">
        </form>
    </div>';
    
    // Obsługa wysyłania przypomnienia hasła
    if(isset($_POST['przypomnij'])) {
        $odbiorca = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        $mail['subject'] = 'Przypomnienie hasła do panelu administracyjnego';
        $mail['body'] = "Login: $login\nHasło: $pass";
        $mail['sender'] = 'admin@example.com';
        $mail['recipient'] = $odbiorca;

        // Przygotowanie nagłówków emaila
        $header = "From: System przypominania hasła <" . $mail['sender'] . ">\n";
        $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit\n";
        $header .= "X-Sender: " . $mail['sender'] . "\n";
        $header .= "X-Mailer: PHP/mail 1.2\n";
        $header .= "X-Priority: 3\n";
        $header .= "Return-Path: <" . $mail['sender'] . ">\n";

        // Wysłanie emaila i obsługa wyniku
        if(mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
            $wynik .= '<p class="success">Hasło zostało wysłane na podany adres email.</p>';
        } else {
            $wynik .= '<p class="error">Wystąpił błąd podczas wysyłania hasła.</p>';
        }
    }
    
    return $wynik;
}
?> 