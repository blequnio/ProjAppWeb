<?php
/**
 * Panel administracyjny strony
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.1
 */

session_start();
include('../cfg.php');

// Dodanie arkusza stylów
echo '<link rel="stylesheet" href="../css/admin.css" type="text/css">';

// Dodaj na początku pliku:
require_once('../classes/CategoryManager.php');
require_once('../classes/ProductManager.php');

// Inicjalizacja menedżerów
$categoryManager = new CategoryManager($link);
$productManager = new ProductManager($link);

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

/**
 * Wyświetla listę kategorii z możliwością edycji
 */
function ListaKategorii() {
    global $link;
    
    $query = "SELECT * FROM categories ORDER BY parent_id, id";
    $result = mysqli_query($link, $query);
    
    $wynik = '<div class="lista-kategorii">
              <h2>Lista kategorii</h2>
              <table>
              <tr>
                <th>ID</th>
                <th>Nazwa</th>
                <th>Kategoria nadrzędna</th>
                <th>Akcje</th>
              </tr>';
              
    while($row = mysqli_fetch_array($result)) {
        $parent_name = '';
        if($row['parent_id'] > 0) {
            $query_parent = "SELECT name FROM categories WHERE id = " . $row['parent_id'];
            $result_parent = mysqli_query($link, $query_parent);
            $parent = mysqli_fetch_array($result_parent);
            $parent_name = $parent ? $parent['name'] : '';
        }
        
        $wynik .= '<tr>
                    <td>' . htmlspecialchars($row['id']) . '</td>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($parent_name) . '</td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">
                            <input type="submit" name="edytuj_kategorie" value="Edytuj">
                            <input type="submit" name="usun_kategorie" value="Usuń" 
                                   onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">
                        </form>
                    </td>
                   </tr>';
    }
    $wynik .= '</table></div>';
    return $wynik;
}

/**
 * Wyświetla listę produktów z możliwością edycji
 */
function ListaProduktow() {
    global $link;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              ORDER BY p.id";
    $result = mysqli_query($link, $query);
    
    $wynik = '<div class="lista-produktow">
              <h2>Lista produktów</h2>
              <table>
              <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Cena netto</th>
                <th>VAT</th>
                <th>Kategoria</th>
                <th>Status</th>
                <th>Akcje</th>
              </tr>';
              
    while($row = mysqli_fetch_array($result)) {
        $wynik .= '<tr>
                    <td>' . htmlspecialchars($row['id']) . '</td>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . htmlspecialchars($row['net_price']) . ' zł</td>
                    <td>' . htmlspecialchars($row['vat_rate']) . '%</td>
                    <td>' . htmlspecialchars($row['category_name']) . '</td>
                    <td>' . htmlspecialchars($row['availability_status']) . '</td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">
                            <input type="submit" name="edytuj_produkt" value="Edytuj">
                            <input type="submit" name="usun_produkt" value="Usuń" 
                                   onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">
                        </form>
                    </td>
                   </tr>';
    }
    $wynik .= '</table></div>';
    return $wynik;
}

/**
 * Formularz edycji kategorii
 */
function EdytujKategorie($id) {
    global $link;
    
    $id = mysqli_real_escape_string($link, $id);
    $query = "SELECT * FROM categories WHERE id='$id' LIMIT 1";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    
    // Pobierz listę kategorii nadrzędnych
    $query_parents = "SELECT * FROM categories WHERE id != '$id'";
    $result_parents = mysqli_query($link, $query_parents);
    
    $wynik = '
    <div class="edycja-kategorii">
        <h2>Edycja kategorii</h2>
        <form method="post">
            <input type="hidden" name="id" value="' . htmlspecialchars($id) . '">
            <label for="name">Nazwa kategorii:</label><br>
            <input type="text" name="name" id="name" value="' . htmlspecialchars($row['name']) . '" required><br>
            <label for="parent_id">Kategoria nadrzędna:</label><br>
            <select name="parent_id" id="parent_id">
                <option value="0">Brak</option>';
    
    while($parent = mysqli_fetch_array($result_parents)) {
        $selected = ($parent['id'] == $row['parent_id']) ? 'selected' : '';
        $wynik .= '<option value="' . $parent['id'] . '" ' . $selected . '>' . 
                  htmlspecialchars($parent['name']) . '</option>';
    }
    
    $wynik .= '</select><br>
            <input type="submit" name="zapisz_kategorie" value="Zapisz zmiany">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Formularz edycji produktu
 */
function EdytujProdukt($id) {
    global $link;
    
    $id = mysqli_real_escape_string($link, $id);
    $query = "SELECT * FROM products WHERE id='$id' LIMIT 1";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    
    // Pobierz listę kategorii
    $query_categories = "SELECT * FROM categories";
    $result_categories = mysqli_query($link, $query_categories);
    
    $wynik = '
    <div class="edycja-produktu">
        <h2>Edycja produktu</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="' . htmlspecialchars($id) . '">
            <input type="hidden" name="current_image" value="' . htmlspecialchars($row['image_url']) . '">
            
            <label for="title">Tytuł produktu:</label><br>
            <input type="text" name="title" id="title" value="' . htmlspecialchars($row['title']) . '" required><br>
            
            <label for="description">Opis:</label><br>
            <textarea name="description" id="description" rows="5" cols="50">' . 
            htmlspecialchars($row['description']) . '</textarea><br>
            
            <label for="net_price">Cena netto:</label><br>
            <input type="number" step="0.01" name="net_price" id="net_price" 
                   value="' . htmlspecialchars($row['net_price']) . '" required><br>
            
            <label for="vat_rate">Stawka VAT (%):</label><br>
            <input type="number" name="vat_rate" id="vat_rate" 
                   value="' . htmlspecialchars($row['vat_rate']) . '" required><br>
            
            <label for="stock_quantity">Stan magazynowy:</label><br>
            <input type="number" name="stock_quantity" id="stock_quantity" 
                   value="' . htmlspecialchars($row['stock_quantity']) . '" required><br>
            
            <label for="category_id">Kategoria:</label><br>
            <select name="category_id" id="category_id" required>
                <option value="">Wybierz kategorię</option>';
    
    while($category = mysqli_fetch_array($result_categories)) {
        $selected = ($category['id'] == $row['category_id']) ? 'selected' : '';
        $wynik .= '<option value="' . $category['id'] . '" ' . $selected . '>' . 
                  htmlspecialchars($category['name']) . '</option>';
    }
    
    $wynik .= '</select><br>
            
            <label for="availability_status">Status dostępności:</label><br>
            <select name="availability_status" id="availability_status" required>
                <option value="available" ' . ($row['availability_status'] == 'available' ? 'selected' : '') . '>
                    Dostępny
                </option>
                <option value="unavailable" ' . ($row['availability_status'] == 'unavailable' ? 'selected' : '') . '>
                    Niedostępny
                </option>
                <option value="coming_soon" ' . ($row['availability_status'] == 'coming_soon' ? 'selected' : '') . '>
                    Wkrótce dostępny
                </option>
            </select><br>
            
            <label for="dimensions">Wymiary:</label><br>
            <input type="text" name="dimensions" id="dimensions" 
                   value="' . htmlspecialchars($row['dimensions']) . '"><br>
            
            <label for="image">Zdjęcie produktu:</label><br>';
    
    // Wyświetl obecne zdjęcie jeśli istnieje
    if (!empty($row['image_url'])) {
        $wynik .= '<div class="current-image">
                    <img src="../' . htmlspecialchars($row['image_url']) . '" alt="Obecne zdjęcie" style="max-width: 200px;"><br>
                    <small>Obecne zdjęcie</small>
                  </div>';
    }
    
    $wynik .= '<input type="file" name="image" id="image"><br>
            <small>Pozostaw puste, aby zachować obecne zdjęcie</small><br>
            
            <input type="submit" name="zapisz_produkt" value="Zapisz zmiany">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Formularz dodawania nowej kategorii
 */
function DodajNowaKategorie() {
    global $link;
    
    // Pobierz listę kategorii nadrzędnych
    $query = "SELECT * FROM categories";
    $result = mysqli_query($link, $query);
    
    $wynik = '
    <div class="dodaj-kategorie">
        <h2>Dodaj nową kategorię</h2>
        <form method="post">
            <label for="name">Nazwa kategorii:</label><br>
            <input type="text" name="name" id="name" required><br>
            <label for="parent_id">Kategoria nadrzędna:</label><br>
            <select name="parent_id" id="parent_id">
                <option value="0">Brak</option>';
    
    while($category = mysqli_fetch_array($result)) {
        $wynik .= '<option value="' . $category['id'] . '">' . 
                  htmlspecialchars($category['name']) . '</option>';
    }
    
    $wynik .= '</select><br>
            <input type="submit" name="zapisz_nowa_kategorie" value="Dodaj kategorię">
        </form>
    </div>';
    
    return $wynik;
}

/**
 * Formularz dodawania nowego produktu
 */
function DodajNowyProdukt() {
    global $link;
    
    // Pobierz listę kategorii
    $query = "SELECT * FROM categories";
    $result = mysqli_query($link, $query);
    
    $wynik = '
    <div class="dodaj-produkt">
        <h2>Dodaj nowy produkt</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="title">Tytuł produktu:</label><br>
            <input type="text" name="title" id="title" required><br>
            
            <label for="description">Opis:</label><br>
            <textarea name="description" id="description" rows="5" cols="50"></textarea><br>
            
            <label for="net_price">Cena netto:</label><br>
            <input type="number" step="0.01" name="net_price" id="net_price" required><br>
            
            <label for="vat_rate">Stawka VAT (%):</label><br>
            <input type="number" name="vat_rate" id="vat_rate" required><br>
            
            <label for="stock_quantity">Stan magazynowy:</label><br>
            <input type="number" name="stock_quantity" id="stock_quantity" required><br>
            
            <label for="category_id">Kategoria:</label><br>
            <select name="category_id" id="category_id" required>
                <option value="">Wybierz kategorię</option>';
    
    while($category = mysqli_fetch_array($result)) {
        $wynik .= '<option value="' . $category['id'] . '">' . 
                  htmlspecialchars($category['name']) . '</option>';
    }
    
    $wynik .= '</select><br>
            
            <label for="availability_status">Status dostępności:</label><br>
            <select name="availability_status" id="availability_status" required>
                <option value="available">Dostępny</option>
                <option value="unavailable">Niedostępny</option>
                <option value="coming_soon">Wkrótce dostępny</option>
            </select><br>
            
            <label for="dimensions">Wymiary:</label><br>
            <input type="text" name="dimensions" id="dimensions"><br>
            
            <label for="image">Zdjęcie produktu:</label><br>
            <input type="file" name="image" id="image"><br>
            
            <input type="submit" name="zapisz_nowy_produkt" value="Dodaj produkt">
        </form>
    </div>';
    
    return $wynik;
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
    
    // Menu zarządzania
    echo '<div class="admin-menu">
            <form method="post">
                <input type="submit" name="pokaz_strony" value="Zarządzaj stronami">
                <input type="submit" name="pokaz_kategorie" value="Zarządzaj kategoriami">
                <input type="submit" name="pokaz_produkty" value="Zarządzaj produktami">
            </form>
          </div>';
    
    // Obsługa formularzy
    if (isset($_POST['pokaz_strony'])) {
        echo '<div class="section-header">
                <h2>Zarządzanie stronami</h2>
                <form method="post">
                    <input type="submit" name="dodaj_nowa" value="Dodaj nową stronę" class="add-button">
                </form>
              </div>';
        echo ListaPodstron();
    } elseif (isset($_POST['pokaz_kategorie'])) {
        echo '<div class="section-header">
                <h2>Zarządzanie kategoriami</h2>
                <form method="post">
                    <input type="submit" name="dodaj_kategorie" value="Dodaj nową kategorię" class="add-button">
                </form>
              </div>';
        echo ListaKategorii();
    } elseif (isset($_POST['pokaz_produkty'])) {
        echo '<div class="section-header">
                <h2>Zarządzanie produktami</h2>
                <form method="post">
                    <input type="submit" name="dodaj_produkt" value="Dodaj nowy produkt" class="add-button">
                </form>
              </div>';
        echo ListaProduktow();
    } elseif (isset($_POST['dodaj_nowa'])) {
        echo DodajNowaPodstrone();
    } elseif (isset($_POST['dodaj_kategorie'])) {
        echo DodajNowaKategorie();
    } elseif (isset($_POST['dodaj_produkt'])) {
        echo DodajNowyProdukt();
    } elseif (isset($_POST['edytuj'])) {
        echo EdytujPodstrone($_POST['id']);
    } elseif (isset($_POST['usun'])) {
        echo UsunPodstrone($_POST['id']);
        echo ListaPodstron();
    } elseif (isset($_POST['edytuj_kategorie'])) {
        echo EdytujKategorie($_POST['id']);
    } elseif (isset($_POST['edytuj_produkt'])) {
        echo EdytujProdukt($_POST['id']);
    } else {
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
        } else {
            echo '<p class="error">Błąd podczas zapisywania zmian: ' . mysqli_error($link) . '</p>';
        }
        
        echo ListaPodstron();
    }
    
    // Obsługa dodawania nowej strony
    if (isset($_POST['dodaj'])) {
        $title = mysqli_real_escape_string($link, $_POST['new_page_title']);
        $content = mysqli_real_escape_string($link, $_POST['new_page_content']);
        $status = isset($_POST['new_status']) ? 1 : 0;
        
        $query = "INSERT INTO page_list (page_title, page_content, status) 
                 VALUES ('$title', '$content', '$status')";
        
        if(mysqli_query($link, $query)) {
            echo '<p class="success">Nowa strona została dodana!</p>';
        } else {
            echo '<p class="error">Błąd podczas dodawania strony: ' . mysqli_error($link) . '</p>';
        }
        
        echo ListaPodstron();
    }
    
    // W sekcji obsługi formularzy dodaj:
    if (isset($_POST['zapisz_nowa_kategorie'])) {
        $name = $_POST['name'];
        $parent_id = $_POST['parent_id'];
        
        if ($categoryManager->addCategory($name, $parent_id)) {
            echo '<p class="success">Kategoria została dodana!</p>';
        } else {
            echo '<p class="error">Błąd podczas dodawania kategorii!</p>';
        }
        echo ListaKategorii();
    }

    if (isset($_POST['zapisz_nowy_produkt'])) {
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'net_price' => $_POST['net_price'],
            'vat_rate' => $_POST['vat_rate'],
            'stock_quantity' => $_POST['stock_quantity'],
            'availability_status' => $_POST['availability_status'],
            'category_id' => $_POST['category_id'],
            'dimensions' => $_POST['dimensions'],
            'expiry_date' => $_POST['expiry_date'],
            'image_url' => '' // Domyślnie puste
        ];
        
        // Obsługa przesyłania pliku
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../images/products/';
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Zapisz ścieżkę bez '../'
                $data['image_url'] = 'images/products/' . $file_name;
            }
        }
        
        if ($productManager->addProduct($data)) {
            echo '<p class="success">Produkt został dodany!</p>';
        } else {
            echo '<p class="error">Błąd podczas dodawania produktu!</p>';
        }
        echo ListaProduktow();
    }
    
    // Zaktualizuj obsługę zapisywania edytowanego produktu
    if (isset($_POST['zapisz_produkt'])) {
        $id = $_POST['id'];
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'net_price' => $_POST['net_price'],
            'vat_rate' => $_POST['vat_rate'],
            'stock_quantity' => $_POST['stock_quantity'],
            'availability_status' => $_POST['availability_status'],
            'category_id' => $_POST['category_id'],
            'dimensions' => $_POST['dimensions'],
            'image_url' => $_POST['current_image'] // Zachowaj obecne zdjęcie jako domyślne
        ];
        
        // Obsługa przesyłania nowego zdjęcia
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../images/products/';
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Usuń stare zdjęcie jeśli istnieje
                if (!empty($_POST['current_image']) && file_exists('../' . $_POST['current_image'])) {
                    unlink('../' . $_POST['current_image']);
                }
                // Zapisz ścieżkę bez '../'
                $data['image_url'] = 'images/products/' . $file_name;
            }
        }
        
        if ($productManager->editProduct($id, $data)) {
            echo '<p class="success">Produkt został zaktualizowany!</p>';
        } else {
            echo '<p class="error">Błąd podczas aktualizacji produktu!</p>';
        }
        echo ListaProduktow();
    }
    
    echo '</div>';
} else {
    echo FormularzLogowania();
}
?>
