<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
/* kod do dynamicznego ładowania stron */
if($_GET['idp'] == '') $strona = 'html/glowna.html';
if($_GET['idp'] == 'popularni_tworcy') $strona = 'html/popularni_tworcy.html';
if($_GET['idp'] == 'gry') $strona = 'html/gry.html';
if($_GET['idp'] == 'praca_na_komputerze') $strona = 'html/praca_na_komputerze.html';
if($_GET['idp'] == 'kontakt') $strona = 'html/kontakt.html';
if($_GET['idp'] == 'filmy') $strona = 'html/filmy.html';

// Zabezpieczenie - sprawdzenie czy plik istnieje
if (!file_exists($strona)) {
    $strona = 'html/glowna.html';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona główna - Komputer moją pasją</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/kolorujtlo.js" type="text/javascript"></script>
    <script src="js/timedate.js" type="text/javascript"></script>
    <script src="js/main.js"></script>
</head>
<body onload="startclock()">
    <div class="header">
        <h1>Komputer moją pasją</h1>
        <h2>Świat technologii w zasięgu ręki</h2>
    </div>
    <div class="menu">
        <table>
            <tr>
                <td><a href="index.php">Strona główna</a></td>
                <td><a href="index.php?idp=popularni_tworcy">Popularni Twórcy</a></td>
                <td><a href="index.php?idp=gry">Gry</a></td>
                <td><a href="index.php?idp=praca_na_komputerze">Praca na komputerze</a></td>
                <td><a href="index.php?idp=kontakt">Kontakt</a></td>
                <td><a href="index.php?idp=filmy">Filmy</a></td>
            </tr>
        </table>
    </div>

    <?php include($strona); ?>

    <div class="footer">
        <div id="zegarek"></div>
        <div id="data"></div>
        <p>&copy; 2024 Komputer moją pasją</p>
    </div>
    
    <?php 
    $nr_indeksu = '168061'; 
    $nrGrupy = 'isi4'; 
    echo 'Autor: Łukasz Tomaszewicz '.$nr_indeksu.' grupa '.$nrGrupy.' <br /><br />'; 
    ?> 
</body>
</html>