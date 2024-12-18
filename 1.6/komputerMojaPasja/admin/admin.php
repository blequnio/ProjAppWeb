<?php
session_start();
include('../cfg.php');

// Funkcja wyświetlająca formularz logowania
function FormularzLogowania() {
    $wynik = '
    <div class="logowanie">
        <h1>Panel administracyjny</h1>
        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
            <label>Login:</label><br>
            <input type="text" name="login" required><br>
            <label>Hasło:</label><br>
            <input type="password" name="pass" required><br>
            <input type="submit" name="logowanie" value="Zaloguj">
        </form>
    </div>
    ';
    return $wynik;
}

// Funkcja wyświetlająca listę podstron
function ListaPodstron() {
    global $link;
    $query = "SELECT * FROM page_list";
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
                    <td>' . $row['id'] . '</td>
                    <td>' . $row['page_title'] . '</td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="id" value="' . $row['id'] . '">
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

// Funkcja wyświetlająca formularz edycji podstrony
function EdytujPodstrone($id) {
    global $link;
    $query = "SELECT * FROM page_list WHERE id='$id' LIMIT 1";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    
    $wynik = '
    <div class="edycja-podstrony">
        <h2>Edycja podstrony</h2>
        <form method="post">
            <input type="hidden" name="id" value="' . $id . '">
            <label>Tytuł podstrony:</label><br>
            <input type="text" name="page_title" value="' . $row['page_title'] . '" required><br>
            <label>Treść podstrony:</label><br>
            <textarea name="page_content" rows="20" cols="60" required>' . $row['page_content'] . '</textarea><br>
            <label>
                <input type="checkbox" name="status" value="1" ' . ($row['status'] == 1 ? 'checked' : '') . '>
                Strona aktywna
            </label><br>
            <input type="submit" name="zapisz" value="Zapisz zmiany">
        </form>
    </div>';
    
    return $wynik;
}

// Nowa funkcja do dodawania podstrony
function DodajNowaPodstrone() {
    $wynik = '
    <div class="dodaj-podstrone">
        <h2>Dodaj nową podstronę</h2>
        <form method="post">
            <label>Tytuł podstrony:</label><br>
            <input type="text" name="new_page_title" required><br>
            <label>Treść podstrony:</label><br>
            <textarea name="new_page_content" rows="20" cols="60" required></textarea><br>
            <label>
                <input type="checkbox" name="new_status" value="1" checked>
                Strona aktywna
            </label><br>
            <input type="submit" name="dodaj" value="Dodaj podstronę">
        </form>
    </div>';
    
    return $wynik;
}

// Funkcja do usuwania podstrony
function UsunPodstrone($id) {
    global $link;
    $id = mysqli_real_escape_string($link, $id);
    
    $query = "DELETE FROM page_list WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if($result) {
        return '<p style="color: green;">Podstrona została usunięta!</p>';
    } else {
        return '<p style="color: red;">Błąd podczas usuwania podstrony: ' . mysqli_error($link) . '</p>';
    }
}

// Obsługa logowania
if (isset($_POST['logowanie'])) {
    if ($_POST['login'] === $login && $_POST['pass'] === $pass) {
        $_SESSION['zalogowany'] = true;
    } else {
        echo '<p style="color: red;">Błędny login lub hasło!</p>';
    }
}

// Obsługa wylogowania
if (isset($_POST['wyloguj'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Główna logika strony
if (isset($_SESSION['zalogowany'])) {
    echo '<div class="admin-panel">';
    echo '<form method="post" style="float: right;">
            <input type="submit" name="wyloguj" value="Wyloguj">
          </form>';
    
    // Dodanie przycisku "Dodaj nową podstronę"
    echo '<form method="post" style="margin-bottom: 20px;">
            <input type="submit" name="dodaj_nowa" value="Dodaj nową podstronę">
          </form>';
    
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
            echo '<p style="color: green;">Nowa podstrona została dodana!</p>';
            echo ListaPodstron();
        } else {
            echo '<p style="color: red;">Błąd podczas dodawania podstrony: ' . mysqli_error($link) . '</p>';
        }
    }
    
    // Obsługa usuwania (przeniesiona do osobnej funkcji)
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
                 WHERE id='$id'";
                 
        if(mysqli_query($link, $query)) {
            echo '<p style="color: green;">Zmiany zostały zapisane!</p>';
            echo ListaPodstron();
        } else {
            echo '<p style="color: red;">Błąd podczas zapisywania zmian!</p>';
        }
    }
    
    echo '</div>';
} else {
    // Formularz logowania
    echo FormularzLogowania();
}

// Dodanie stylów CSS dla lepszego wyglądu
echo '
<style>
    .admin-panel {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .lista-podstron table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .lista-podstron th, .lista-podstron td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    
    .lista-podstron th {
        background-color: #f5f5f5;
    }
    
    .dodaj-podstrone, .edycja-podstrony {
        margin-top: 20px;
    }
    
    input[type="text"], textarea {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
        box-sizing: border-box;
    }
    
    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin: 5px;
    }
    
    input[type="submit"]:hover {
        background-color: #45a049;
    }
    
    input[name="usun"] {
        background-color: #f44336;
    }
    
    input[name="usun"]:hover {
        background-color: #da190b;
    }
</style>
';
?>
