<?php
include('cfg.php');
session_start();

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

// Obsługa usuwania z koszyka
if (isset($_POST['action']) && $_POST['action'] == 'remove') {
    $product_id = (int)$_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Obsługa aktualizacji ilości
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Inicjalizacja koszyka
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Obsługa dodawania do koszyka
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Sprawdź czy produkt już jest w koszyku
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Przekieruj z powrotem do sklepu aby uniknąć ponownego wysłania formularza
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Obsługa filtrowania po kategorii
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Pobieranie kategorii
$categories_html = '<div class="categories"><h2>Kategorie</h2><ul>';
$categories_html .= '<li><a href="shop.php" ' . ($selected_category == 0 ? 'class="active"' : '') . '>Wszystkie</a></li>';

// Pobierz główne kategorie
$query_main_categories = "SELECT * FROM categories WHERE parent_id = 0";
$result_main = mysqli_query($link, $query_main_categories);

while($main_category = mysqli_fetch_assoc($result_main)) {
    $categories_html .= '<li>
        <a href="shop.php?category=' . $main_category['id'] . '" ' . 
        ($selected_category == $main_category['id'] ? 'class="active"' : '') . '>' . 
        htmlspecialchars($main_category['name']) . '</a>';
    
    // Pobierz podkategorie
    $query_sub = "SELECT * FROM categories WHERE parent_id = " . $main_category['id'];
    $result_sub = mysqli_query($link, $query_sub);
    
    if(mysqli_num_rows($result_sub) > 0) {
        $categories_html .= '<ul>';
        while($sub_category = mysqli_fetch_assoc($result_sub)) {
            $categories_html .= '<li>
                <a href="shop.php?category=' . $sub_category['id'] . '" ' . 
                ($selected_category == $sub_category['id'] ? 'class="active"' : '') . '>' . 
                htmlspecialchars($sub_category['name']) . '</a>
            </li>';
        }
        $categories_html .= '</ul>';
    }
    
    $categories_html .= '</li>';
}

$categories_html .= '</ul></div>';

// Pobieranie produktów z filtrowaniem po kategorii
$query_products = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";

if ($selected_category > 0) {
    // Pobierz wszystkie podkategorie wybranej kategorii
    $subcategories = array($selected_category);
    $query_subs = "SELECT id FROM categories WHERE parent_id = $selected_category";
    $result_subs = mysqli_query($link, $query_subs);
    while ($sub = mysqli_fetch_assoc($result_subs)) {
        $subcategories[] = $sub['id'];
    }
    
    $query_products .= " AND p.category_id IN (" . implode(',', $subcategories) . ")";
}

$query_products .= " ORDER BY p.title ASC";
$result_products = mysqli_query($link, $query_products);

// Wyświetlanie produktów
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sklep - Komputer moją pasją</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/kolorujtlo.js"></script>
    <script src="js/timedate.js"></script>
    <script src="js/main.js"></script>
</head>
<body onload="startclock()">
    <!-- Nagłówek strony -->
    <header class="header">
        <h1>Komputer moją pasją</h1>
        <nav class="nav-container">
            <div class="nav-links">
                <?php echo PobierzNawigacje($link); ?>
            </div>
        </nav>
    </header>

    <!-- Zawartość strony w kontenerze content -->
    <div class="content">
        <?php echo $categories_html; ?>
        
        <div class="products">
            <h2>Produkty</h2>
            <div class="products-grid">
                <?php if(mysqli_num_rows($result_products) > 0): ?>
                    <?php while($product = mysqli_fetch_assoc($result_products)): ?>
                        <?php $gross_price = $product['net_price'] * (1 + ($product['vat_rate']/100)); ?>
                        <div class="product-card image-wrap">
                            <?php if(!empty($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="category">Kategoria: <?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p class="price">Cena: <?php echo number_format($gross_price, 2); ?> zł</p>
                                <form method="post" action="shop.php<?php echo $selected_category ? '?category='.$selected_category : ''; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                    <button type="submit" <?php echo ($product['stock_quantity'] < 1 ? 'disabled' : ''); ?>>
                                        Dodaj do koszyka
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Brak produktów w wybranej kategorii.</p>
                        <a href="shop.php" class="back-to-all">Pokaż wszystkie produkty</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Koszyk -->
        <div class="cart">
            <h2>Koszyk</h2>
            <?php if(empty($_SESSION['cart'])): ?>
                <p>Koszyk jest pusty</p>
            <?php else: ?>
                <?php 
                $total = 0;
                foreach($_SESSION['cart'] as $product_id => $quantity):
                    $query_cart = "SELECT * FROM products WHERE id = $product_id";
                    $result_cart = mysqli_query($link, $query_cart);
                    $product = mysqli_fetch_assoc($result_cart);
                    
                    if($product):
                        $gross_price = $product['net_price'] * (1 + ($product['vat_rate']/100));
                        $subtotal = $gross_price * $quantity;
                        $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <h4><?php echo htmlspecialchars($product['title']); ?></h4>
                        <div class="quantity-control">
                            <form method="post" class="quantity-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" 
                                       min="0" max="<?php echo $product['stock_quantity']; ?>">
                                <button type="submit" class="update-btn">Aktualizuj</button>
                            </form>
                        </div>
                        <p>Cena: <?php echo number_format($subtotal, 2); ?> zł</p>
                        <form method="post" class="remove-form">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <button type="submit" class="remove-btn">Usuń</button>
                        </form>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
                <div class="cart-total">
                    <h3>Suma: <?php echo number_format($total, 2); ?> zł</h3>
                </div>
                <form method="post" action="payment.php">
                    <input type="hidden" name="total" value="<?php echo $total; ?>">
                    <button type="submit" class="checkout-btn">Przejdź do płatności</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stopka strony -->
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