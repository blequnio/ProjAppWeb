<?php
/**
 * Plik zawierający funkcję wyświetlania podstron
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Wyświetla zawartość podstrony na podstawie jej ID
 * 
 * @param int $id ID podstrony do wyświetlenia
 * @return string Zawartość podstrony
 */
function PokazPodstrone($id) {
    global $link;
    
    // Sprawdzenie połączenia z bazą
    if (!$link) {
        return "Błąd połączenia z bazą danych: " . mysqli_connect_error();
    }
    
    // Zabezpieczenie parametru ID
    $id_clear = htmlspecialchars($id);
    $id_clear = mysqli_real_escape_string($link, $id_clear);
    
    // Pobranie danych podstrony
    $query = "SELECT * FROM page_list WHERE id='$id_clear' LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if (!$result) {
        return "Błąd zapytania: " . mysqli_error($link);
    }
    
    // Sprawdzenie czy podstrona istnieje
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        
        // Sprawdzenie statusu podstrony
        if($row['status'] == 1) {
            // Specjalna obsługa dla strony kontaktowej
            if($id_clear == 5) {
                ob_start();
                include('contact.php');
                if(isset($_POST['wyslij'])) {
                    WyslijMailKontakt('twoj@email.com');
                } else {
                    echo PokazKontakt();
                }
                return ob_get_clean();
            }
            return $row['page_content'];
        } else {
            return '<div class="content">
                        <h2>Strona nieaktywna</h2>
                        <p>[Ta strona nie jest aktywna]</p>
                    </div>';
        }
    } else {
        return '<div class="content">
                    <h2>Strona nie została znaleziona</h2>
                    <p>Przepraszamy, ale strona o ID ' . $id_clear . ' nie istnieje.</p>
                </div>';
    }
}
?>