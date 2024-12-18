<?php
/**
 * Plik konfiguracyjny zawierający podstawowe ustawienia strony
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.0
 */

// Konfiguracja bazy danych
$dbhost = 'localhost'; 
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

// Dane logowania do panelu administracyjnego
$login = 'admin';
$pass = 'admin123';

// Informacje o autorze
$nr_indeksu = '168061'; 
$nrGrupy = 'isi4';

// Nawiązanie połączenia z bazą danych
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);

// Sprawdzenie połączenia
if (!$link) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

// Ustawienie kodowania znaków
mysqli_set_charset($link, "utf8");
?>