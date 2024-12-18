<?php
    $dbhost = 'localhost'; 
    $dbuser = 'root';
    $dbpass = '';
    $baza = 'moja_strona';

    // Dane logowania do panelu admina
    $login = 'admin';
    $pass = 'admin123';

    $nr_indeksu = '168061'; 
    $nrGrupy = 'isi4';

    $link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);
    
    if (!$link) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }
?>