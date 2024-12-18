<?php
/**
 * System zarządzania produktami
 * 
 * @author Łukasz Tomaszewicz
 * @version 2.0
 */

session_start();
include('../cfg.php');

// Dodanie arkuszy stylów
echo '<link rel="stylesheet" href="../css/admin.css" type="text/css">';
echo '<link rel="stylesheet" href="../css/products.css" type="text/css">';

/**
 * Klasa zarządzająca produktami
 */
class ProductManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Sprawdza dostępność produktu
     */
    private function checkAvailability($product) {
        if ($product['status'] == 'unavailable') {
            return 'unavailable';
        }
        
        if ($product['stock_quantity'] <= 0) {
            return 'out_of_stock';
        }
        
        if ($product['expiration_date'] && strtotime($product['expiration_date']) < time()) {
            return 'expired';
        }
        
        return 'available';
    }
    
    /**
     * Wyświetla formularz dodawania produktu
     */
    public function DodajProdukt() {
        // Pobierz listę kategorii
        $query = "SELECT id, name FROM categories ORDER BY name";
        $result = mysqli_query($this->db, $query);
        
        $wynik = '
        <div class="dodaj-produkt">
            <h2>Dodaj nowy produkt</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Nazwa produktu:</label>
                    <input type="text" name="title" id="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Opis:</label>
                    <textarea name="description" id="description" rows="5" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price_net">Cena netto:</label>
                        <input type="number" name="price_net" id="price_net" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="vat_rate">VAT (%):</label>
                        <input type="number" name="vat_rate" id="vat_rate" value="23">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="stock_quantity">Ilość w magazynie:</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="available">Dostępny</option>
                            <option value="unavailable">Niedostępny</option>
                            <option value="hidden">Ukryty</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategoria:</label>
                    <select name="category_id" id="category_id" required>
                        <option value="">Wybierz kategorię</option>';
        
        while($row = mysqli_fetch_array($result)) {
            $wynik .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
        }
        
        $wynik .= '</select>
                </div>
                
                <div class="form-group">
                    <label for="dimensions">Wymiary (dł. x szer. x wys. cm):</label>
                    <input type="text" name="dimensions" id="dimensions" placeholder="np. 20x15x10">
                </div>
                
                <div class="form-group">
                    <label for="expiration_date">Data wygaśnięcia:</label>
                    <input type="date" name="expiration_date" id="expiration_date">
                </div>
                
                <div class="form-group">
                    <label for="image">Zdjęcie produktu:</label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>
                
                <input type="submit" name="dodaj_produkt" value="Dodaj produkt">
            </form>
        </div>';
        
        return $wynik;
    }
    
    /**
     * Wyświetla listę produktów
     */
    public function PokazProdukty() {
        $query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 ORDER BY p.created_at DESC";
        $result = mysqli_query($this->db, $query);
        
        $wynik = '<div class="lista-produktow">
                  <h2>Lista produktów</h2>
                  <table>
                    <tr>
                        <th>ID</th>
                        <th>Zdjęcie</th>
                        <th>Nazwa</th>
                        <th>Cena netto</th>
                        <th>VAT</th>
                        <th>Stan</th>
                        <th>Kategoria</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>';
        
        while($row = mysqli_fetch_array($result)) {
            $availability = $this->checkAvailability($row);
            $status_class = 'status-' . $availability;
            
            $wynik .= '<tr>
                        <td>' . $row['id'] . '</td>
                        <td>' . ($row['image_url'] ? '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['title']) . '" class="product-thumb">' : 'brak zdjęcia') . '</td>
                        <td>' . htmlspecialchars($row['title']) . '</td>
                        <td>' . number_format($row['price_net'], 2) . ' zł</td>
                        <td>' . $row['vat_rate'] . '%</td>
                        <td>' . $row['stock_quantity'] . ' szt.</td>
                        <td>' . htmlspecialchars($row['category_name']) . '</td>
                        <td class="' . $status_class . '">' . $availability . '</td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="product_id" value="' . $row['id'] . '">
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
     * Edytuje produkt
     */
    public function EdytujProdukt($id) {
        $id = mysqli_real_escape_string($this->db, $id);
        $query = "SELECT * FROM products WHERE id = '$id' LIMIT 1";
        $result = mysqli_query($this->db, $query);
        $product = mysqli_fetch_array($result);
        
        // Pobierz listę kategorii
        $cat_query = "SELECT id, name FROM categories ORDER BY name";
        $cat_result = mysqli_query($this->db, $cat_query);
        
        $wynik = '
        <div class="edytuj-produkt">
            <h2>Edytuj produkt</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="' . $id . '">
                
                <div class="form-group">
                    <label for="title">Nazwa produktu:</label>
                    <input type="text" name="title" id="title" value="' . htmlspecialchars($product['title']) . '" required>
                </div>
                
                <!-- Podobne pola jak w formularzu dodawania, ale z wartościami -->
                
                <input type="submit" name="zapisz_produkt" value="Zapisz zmiany">
            </form>
        </div>';
        
        return $wynik;
    }
    
    /**
     * Usuwa produkt
     */
    public function UsunProdukt($id) {
        $id = mysqli_real_escape_string($this->db, $id);
        $query = "DELETE FROM products WHERE id = '$id' LIMIT 1";
        
        if(mysqli_query($this->db, $query)) {
            return '<p class="success">Produkt został usunięty!</p>';
        } else {
            return '<p class="error">Błąd podczas usuwania produktu: ' . mysqli_error($this->db) . '</p>';
        }
    }
}

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['zalogowany'])) {
    header('Location: admin.php');
    exit();
}

// Utworzenie instancji managera produktów
$productManager = new ProductManager($link);

// Obsługa formularzy
if(isset($_POST['dodaj_produkt'])) {
    // Obsługa dodawania produktu
}

if(isset($_POST['zapisz_produkt'])) {
    // Obsługa zapisywania zmian
}

if(isset($_POST['usun_produkt'])) {
    echo $productManager->UsunProdukt($_POST['product_id']);
}

// Wyświetlenie interfejsu
echo '<div class="admin-panel">';
echo '<h1>Zarządzanie produktami</h1>';

// Link powrotu do panelu admina
echo '<p><a href="admin.php">← Powrót do panelu administracyjnego</a></p>';

if(isset($_POST['edytuj_produkt'])) {
    echo $productManager->EdytujProdukt($_POST['product_id']);
} else {
    echo $productManager->DodajProdukt();
}

echo $productManager->PokazProdukty();
echo '</div>';
?> 