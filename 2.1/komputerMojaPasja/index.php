<?php
session_start();
include('cfg.php');

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

// Pobieranie zawartości strony na podstawie ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT page_content FROM page_list WHERE id = $id AND status = 1";
    $result = mysqli_query($link, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $content = $row['page_content'];
    } else {
        $content = '<p>Strona nie została znaleziona.</p>';
    }
} else {
    // Domyślna strona (np. strona główna)
    $query = "SELECT page_content FROM page_list WHERE id = 1 AND status = 1";
    $result = mysqli_query($link, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $content = $row['page_content'];
    } else {
        $content = '<p>Witaj na stronie głównej!</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komputer Moja Pasja</title>
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
            <?php echo $content; ?>
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