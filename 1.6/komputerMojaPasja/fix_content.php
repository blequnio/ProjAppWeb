<?php
include('cfg.php');

// Pobierz wszystkie strony
$query = "SELECT * FROM page_list";
$result = mysqli_query($link, $query);

while($row = mysqli_fetch_array($result)) {
    $content = $row['page_content'];
    
    // Wyciągnij tylko zawartość z div class="content"
    if(preg_match('/<div class="content">(.*?)<\/div>/s', $content, $matches)) {
        $clean_content = $matches[0];
        
        // Zaktualizuj zawartość w bazie
        $clean_content = mysqli_real_escape_string($link, $clean_content);
        $update_query = "UPDATE page_list SET page_content = '$clean_content' WHERE id = {$row['id']}";
        
        if(mysqli_query($link, $update_query)) {
            echo "Zaktualizowano treść strony o ID: {$row['id']}<br>";
        } else {
            echo "Błąd podczas aktualizacji strony o ID: {$row['id']}<br>";
        }
    }
}

echo "Zakończono czyszczenie zawartości stron.";
?> 