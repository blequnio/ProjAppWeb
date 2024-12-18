<?php
/**
 * Panel administracyjny strony
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.0
 */

session_start();
include('../cfg.php');

// Dodanie arkusza stylów
echo '<link rel="stylesheet" href="../css/admin.css" type="text/css">';

/**
 * Wyświetla formularz logowania do panelu administracyjnego
 * 
 * @return string Kod HTML formularza logowania
 */
function FormularzLogowania() {
    $wynik = '
    <div class="logowanie">
        <h1>Panel administracyjny</h1>
        <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
            <label for="login">Login:</label><br>
            <input type="text" name="login" id="login" required><br>
            <label for="pass">Hasło:</label><br>
            <input type="password" name="pass" id="pass" required><br>
            <input type="submit" name="logowanie" value="Zaloguj">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Wyświetla listę wszystkich podstron z możliwością edycji i usuwania
 * 
 * @return string Kod HTML tabeli z listą podstron
 */
function ListaPodstron() {
    global $link;
    
    // Pobranie listy wszystkich podstron
    $query = "SELECT * FROM page_list ORDER BY id ASC";
    $result = mysqli_query($link, $query);
    
    $wynik = '<div class="lista-podstron">
              <h2>Lista podstron</h2>
              <table>
              <tr>
                <th>ID</th>
                <th>Tytuł podstrony</th>
                <th>Akcje</th>
              </tr>';
              
    while($row = mysqli_fetch_array($result)) {
        $wynik .= '<tr>
                    <td>' . htmlspecialchars($row['id']) . '</td>
                    <td>' . htmlspecialchars($row['page_title']) . '</td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">
                            <input type="submit" name="edytuj" value="Edytuj">
                            <input type="submit" name="usun" value="Usuń" 
                                   onclick="return confirm(\'Czy na pewno chcesz usunąć tę podstronę?\')">
                        </form>
                    </td>
                   </tr>';
    }
    $wynik .= '</table></div>';
    return $wynik;
}

/**
 * Wyświetla formularz edycji podstrony
 * 
 * @param int $id ID edytowanej podstrony
 * @return string Kod HTML formularza edycji
 */
function EdytujPodstrone($id) {
    global $link;
    
    // Zabezpieczenie parametru ID
    $id = mysqli_real_escape_string($link, $id);
    
    $query = "SELECT * FROM page_list WHERE id='$id' LIMIT 1";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    
    $wynik = '
    <div class="edycja-podstrony">
        <h2>Edycja podstrony</h2>
        <form method="post">
            <input type="hidden" name="id" value="' . htmlspecialchars($id) . '">
            <label for="page_title">Tytuł podstrony:</label><br>
            <input type="text" name="page_title" id="page_title" value="' . htmlspecialchars($row['page_title']) . '" required><br>
            <label for="page_content">Treść podstrony:</label><br>
            <textarea name="page_content" id="page_content" rows="20" cols="60" required>' . htmlspecialchars($row['page_content']) . '</textarea><br>
            <label>
                <input type="checkbox" name="status" value="1" ' . ($row['status'] == 1 ? 'checked' : '') . '>
                Strona aktywna
            </label><br>
            <input type="submit" name="zapisz" value="Zapisz zmiany">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Wyświetla formularz dodawania nowej podstrony
 * 
 * @return string Kod HTML formularza dodawania
 */
function DodajNowaPodstrone() {
    $wynik = '
    <div class="dodaj-podstrone">
        <h2>Dodaj nową podstronę</h2>
        <form method="post">
            <label for="new_page_title">Tytuł podstrony:</label><br>
            <input type="text" name="new_page_title" id="new_page_title" required><br>
            <label for="new_page_content">Treść podstrony:</label><br>
            <textarea name="new_page_content" id="new_page_content" rows="20" cols="60" required></textarea><br>
            <label>
                <input type="checkbox" name="new_status" value="1" checked>
                Strona aktywna
            </label><br>
            <input type="submit" name="dodaj" value="Dodaj podstronę">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Usuwa podstronę o podanym ID
 * 
 * @param int $id ID usuwanej podstrony
 * @return string Komunikat o wyniku operacji
 */
function UsunPodstrone($id) {
    global $link;
    
    // Zabezpieczenie parametru ID
    $id = mysqli_real_escape_string($link, $id);
    
    $query = "DELETE FROM page_list WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if($result) {
        return '<p class="success">Podstrona została usunięta!</p>';
    } else {
        return '<p class="error">Błąd podczas usuwania podstrony: ' . mysqli_error($link) . '</p>';
    }
}

// Obsługa logowania
if (isset($_POST['logowanie'])) {
    if ($_POST['login'] === $login && $_POST['pass'] === $pass) {
        $_SESSION['zalogowany'] = true;
    } else {
        echo '<p class="error">Błędny login lub hasło!</p>';
    }
}

// Obsługa wylogowania
if (isset($_POST['wyloguj'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Główna logika panelu administracyjnego
if (isset($_SESSION['zalogowany'])) {
    echo '<div class="admin-panel">';
    
    // Przycisk wylogowania
    echo '<form method="post" style="float: right;">
            <input type="submit" name="wyloguj" value="Wyloguj">
          </form>';
    
    // Przycisk dodawania nowej podstrony
    echo '<form method="post" style="margin-bottom: 20px;">
            <input type="submit" name="dodaj_nowa" value="Dodaj nową podstronę">
            <a href="categories.php" class="button">Zarządzaj kategoriami</a>
            <a href="products.php" class="button">Zarządzaj produktami</a>
          </form>';
    
    // Obsługa formularzy
    if (isset($_POST['dodaj_nowa'])) {
        echo DodajNowaPodstrone();
    } elseif (isset($_POST['edytuj'])) {
        echo EdytujPodstrone($_POST['id']);
    } else {
        echo ListaPodstron();
    }
    
    // Obsługa dodawania nowej podstrony
    if (isset($_POST['dodaj'])) {
        $title = mysqli_real_escape_string($link, $_POST['new_page_title']);
        $content = mysqli_real_escape_string($link, $_POST['new_page_content']);
        $status = isset($_POST['new_status']) ? 1 : 0;
        
        $query = "INSERT INTO page_list (page_title, page_content, status) 
                 VALUES ('$title', '$content', '$status')";
                 
        if(mysqli_query($link, $query)) {
            echo '<p class="success">Nowa podstrona została dodana!</p>';
            echo ListaPodstron();
        } else {
            echo '<p class="error">Błąd podczas dodawania podstrony: ' . mysqli_error($link) . '</p>';
        }
    }
    
    // Obsługa usuwania podstrony
    if (isset($_POST['usun'])) {
        echo UsunPodstrone($_POST['id']);
        echo ListaPodstron();
    }
    
    // Obsługa zapisywania zmian
    if (isset($_POST['zapisz'])) {
        $id = mysqli_real_escape_string($link, $_POST['id']);
        $title = mysqli_real_escape_string($link, $_POST['page_title']);
        $content = mysqli_real_escape_string($link, $_POST['page_content']);
        $status = isset($_POST['status']) ? 1 : 0;
        
        $query = "UPDATE page_list SET 
                 page_title='$title', 
                 page_content='$content', 
                 status='$status' 
                 WHERE id='$id' LIMIT 1";
                 
        if(mysqli_query($link, $query)) {
            echo '<p class="success">Zmiany zostały zapisane!</p>';
            echo ListaPodstron();
        } else {
            echo '<p class="error">Błąd podczas zapisywania zmian!</p>';
        }
    }
    
    echo '</div>';
} else {
    // Wyświetlenie formularza logowania
    echo FormularzLogowania();
}
?>
