<?php
include('cfg.php');
include('showpage.php');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// Pobierz ID strony z parametru GET
$id = $_GET['id'] ?? 1; // Domyślnie strona główna (id=1)
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
                <td><a href="index.php?id=1">Strona główna</a></td>
                <td><a href="index.php?id=2">Popularni Twórcy</a></td>
                <td><a href="index.php?id=3">Gry</a></td>
                <td><a href="index.php?id=4">Praca na komputerze</a></td>
                <td><a href="index.php?id=5">Kontakt</a></td>
                <td><a href="index.php?id=6">Filmy</a></td>
            </tr>
        </table>
    </div>

    <?php echo PokazPodstrone($id); ?>

    <div class="footer">
        <div id="zegarek"></div>
        <div id="data"></div>
        <p>&copy; 2024 Komputer moją pasją</p>
    </div>
    
</body>
</html>