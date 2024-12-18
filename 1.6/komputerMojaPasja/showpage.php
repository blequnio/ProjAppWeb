<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function PokazPodstrone($id) {
    global $link;
    
    if (!$link) {
        return "Błąd połączenia z bazą danych: " . mysqli_connect_error();
    }
    
    $id_clear = htmlspecialchars($id);
    $id_clear = mysqli_real_escape_string($link, $id_clear);
    
    $query = "SELECT * FROM page_list WHERE id='$id_clear' LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if (!$result) {
        return "Błąd zapytania: " . mysqli_error($link);
    }
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        if($row['status'] == 1) {
            return $row['page_content'];
        } else {
            return '<div class="content">[Ta strona nie jest aktywna]</div>';
        }
    } else {
        return '<div class="content">
                    <h2>Strona nie została znaleziona</h2>
                    <p>Przepraszamy, ale strona o ID ' . $id_clear . ' nie istnieje.</p>
                </div>';
    }
}
?>