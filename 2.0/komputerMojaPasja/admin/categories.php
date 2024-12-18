<?php
/**
 * System zarządzania kategoriami
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.0
 */

session_start();
include('../cfg.php');

// Dodanie arkuszy stylów
echo '<link rel="stylesheet" href="../css/admin.css" type="text/css">';
echo '<link rel="stylesheet" href="../css/categories.css" type="text/css">';

/**
 * Klasa zarządzająca kategoriami
 */
class CategoryManager {
    private $db;
    
    /**
     * Konstruktor klasy
     * 
     * @param mysqli $db Połączenie z bazą danych
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Wyświetla formularz dodawania kategorii
     * 
     * @return string Kod HTML formularza
     */
    public function DodajKategorie() {
        // Pobierz listę kategorii głównych dla selecta
        $query = "SELECT id, name FROM categories WHERE parent_id = 0";
        $result = mysqli_query($this->db, $query);
        
        $wynik = '
        <div class="dodaj-kategorie">
            <h2>Dodaj nową kategorię</h2>
            <form method="post">
                <label for="cat_name">Nazwa kategorii:</label><br>
                <input type="text" name="cat_name" id="cat_name" required><br>
                
                <label for="parent_id">Kategoria nadrzędna:</label><br>
                <select name="parent_id" id="parent_id">
                    <option value="0">Brak (kategoria główna)</option>';
        
        while($row = mysqli_fetch_array($result)) {
            $wynik .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
        }
        
        $wynik .= '</select><br>
                <input type="submit" name="dodaj_kat" value="Dodaj kategorię">
            </form>
        </div>';
        
        return $wynik;
    }
    
    /**
     * Usuwa kategorię
     * 
     * @param int $id ID kategorii do usunięcia
     * @return string Komunikat o wyniku operacji
     */
    public function UsunKategorie($id) {
        $id = mysqli_real_escape_string($this->db, $id);
        
        // Sprawdź czy kategoria ma podkategorie
        $check_query = "SELECT COUNT(*) as count FROM categories WHERE parent_id = '$id'";
        $check_result = mysqli_query($this->db, $check_query);
        $row = mysqli_fetch_array($check_result);
        
        if($row['count'] > 0) {
            return '<p class="error">Nie można usunąć kategorii, która ma podkategorie!</p>';
        }
        
        $query = "DELETE FROM categories WHERE id = '$id' LIMIT 1";
        $result = mysqli_query($this->db, $query);
        
        if($result) {
            return '<p class="success">Kategoria została usunięta!</p>';
        } else {
            return '<p class="error">Błąd podczas usuwania kategorii: ' . mysqli_error($this->db) . '</p>';
        }
    }
    
    /**
     * Wyświetla formularz edycji kategorii
     * 
     * @param int $id ID edytowanej kategorii
     * @return string Kod HTML formularza
     */
    public function EdytujKategorie($id) {
        $id = mysqli_real_escape_string($this->db, $id);
        
        $query = "SELECT * FROM categories WHERE id='$id' LIMIT 1";
        $result = mysqli_query($this->db, $query);
        $category = mysqli_fetch_array($result);
        
        // Pobierz listę możliwych kategorii nadrzędnych
        $parent_query = "SELECT id, name FROM categories WHERE id != '$id' AND parent_id = 0";
        $parent_result = mysqli_query($this->db, $parent_query);
        
        $wynik = '
        <div class="edytuj-kategorie">
            <h2>Edytuj kategorię</h2>
            <form method="post">
                <input type="hidden" name="cat_id" value="' . $id . '">
                <label for="cat_name">Nazwa kategorii:</label><br>
                <input type="text" name="cat_name" id="cat_name" value="' . htmlspecialchars($category['name']) . '" required><br>
                
                <label for="parent_id">Kategoria nadrzędna:</label><br>
                <select name="parent_id" id="parent_id">
                    <option value="0" ' . ($category['parent_id'] == 0 ? 'selected' : '') . '>Brak (kategoria główna)</option>';
        
        while($row = mysqli_fetch_array($parent_result)) {
            $selected = ($category['parent_id'] == $row['id']) ? 'selected' : '';
            $wynik .= '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
        }
        
        $wynik .= '</select><br>
                <input type="submit" name="zapisz_kat" value="Zapisz zmiany">
            </form>
        </div>';
        
        return $wynik;
    }
    
    /**
     * Wyświetla drzewo kategorii
     * 
     * @return string Kod HTML z listą kategorii
     */
    public function PokazKategorie() {
        // Pobierz kategorie główne
        $query = "SELECT * FROM categories WHERE parent_id = 0 ORDER BY name";
        $result = mysqli_query($this->db, $query);
        
        $wynik = '<div class="kategorie-lista">
                  <h2>Lista kategorii</h2>
                  <div class="tree-view">';
        
        // Wyświetl kategorie główne
        while($parent = mysqli_fetch_array($result)) {
            $wynik .= '<div class="category-item">
                        <div class="category-header">
                            <span class="category-name">' . htmlspecialchars($parent['name']) . '</span>
                            <div class="category-actions">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="cat_id" value="' . $parent['id'] . '">
                                    <input type="submit" name="edytuj_kat" value="Edytuj">
                                    <input type="submit" name="usun_kat" value="Usuń" 
                                           onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">
                                </form>
                            </div>
                        </div>';
            
            // Pobierz i wyświetl podkategorie
            $sub_query = "SELECT * FROM categories WHERE parent_id = '{$parent['id']}' ORDER BY name";
            $sub_result = mysqli_query($this->db, $sub_query);
            
            if(mysqli_num_rows($sub_result) > 0) {
                $wynik .= '<div class="subcategories">';
                while($child = mysqli_fetch_array($sub_result)) {
                    $wynik .= '<div class="category-item">
                                <div class="category-header">
                                    <span class="category-name">→ ' . htmlspecialchars($child['name']) . '</span>
                                    <div class="category-actions">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="cat_id" value="' . $child['id'] . '">
                                            <input type="submit" name="edytuj_kat" value="Edytuj">
                                            <input type="submit" name="usun_kat" value="Usuń" 
                                                   onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">
                                        </form>
                                    </div>
                                </div>
                              </div>';
                }
                $wynik .= '</div>';
            }
            
            $wynik .= '</div>';
        }
        
        $wynik .= '</div></div>';
        return $wynik;
    }
}

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['zalogowany'])) {
    header('Location: admin.php');
    exit();
}

// Utworzenie instancji managera kategorii
$categoryManager = new CategoryManager($link);

// Obsługa formularzy
if(isset($_POST['dodaj_kat'])) {
    $name = mysqli_real_escape_string($link, $_POST['cat_name']);
    $parent_id = (int)$_POST['parent_id'];
    
    $query = "INSERT INTO categories (name, parent_id) VALUES ('$name', '$parent_id')";
    if(mysqli_query($link, $query)) {
        echo '<p class="success">Kategoria została dodana!</p>';
    } else {
        echo '<p class="error">Błąd podczas dodawania kategorii!</p>';
    }
}

if(isset($_POST['zapisz_kat'])) {
    $id = (int)$_POST['cat_id'];
    $name = mysqli_real_escape_string($link, $_POST['cat_name']);
    $parent_id = (int)$_POST['parent_id'];
    
    $query = "UPDATE categories SET name='$name', parent_id='$parent_id' WHERE id='$id' LIMIT 1";
    if(mysqli_query($link, $query)) {
        echo '<p class="success">Zmiany zostały zapisane!</p>';
    } else {
        echo '<p class="error">Błąd podczas zapisywania zmian!</p>';
    }
}

if(isset($_POST['usun_kat'])) {
    echo $categoryManager->UsunKategorie($_POST['cat_id']);
}

// Wyświetlenie interfejsu
echo '<div class="admin-panel">';
echo '<h1>Zarządzanie kategoriami</h1>';

// Link powrotu do panelu admina
echo '<p><a href="admin.php">← Powrót do panelu administracyjnego</a></p>';

if(isset($_POST['edytuj_kat'])) {
    echo $categoryManager->EdytujKategorie($_POST['cat_id']);
} else {
    echo $categoryManager->DodajKategorie();
}

echo $categoryManager->PokazKategorie();
echo '</div>';
?> 