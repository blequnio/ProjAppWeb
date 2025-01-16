<?php
session_start();
include('cfg.php');

// Sprawdzenie, czy koszyk jest pusty
if (empty($_SESSION['cart'])) {
    header('Location: shop.php'); // Przekierowanie do sklepu, jeśli koszyk jest pusty
    exit();
}

// Obliczanie całkowitej kwoty
$total = 0;
$products = [];
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $query_cart = "SELECT * FROM products WHERE id = $product_id";
    $result_cart = mysqli_query($link, $query_cart);
    $product = mysqli_fetch_assoc($result_cart);
    
    if ($product) {
        $gross_price = $product['net_price'] * (1 + ($product['vat_rate'] / 100));
        $subtotal = $gross_price * $quantity;
        $total += $subtotal;
        $products[] = [
            'title' => htmlspecialchars($product['title']),
            'quantity' => $quantity,
            'gross_price' => number_format($gross_price, 2),
            'subtotal' => number_format($subtotal, 2)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność - Komputer Moja Pasja</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-container">
                <div class="nav-links">
                    <a href="shop.php" class="back-btn">Powrót do sklepu</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="content">
            <h2>Podsumowanie zamówienia</h2>
            <table>
                <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Ilość</th>
                        <th>Cena jednostkowa (z VAT)</th>
                        <th>Łączna cena</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['title']; ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td><?php echo $product['gross_price']; ?> zł</td>
                            <td><?php echo $product['subtotal']; ?> zł</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Łączna kwota do zapłaty: <?php echo number_format($total, 2); ?> zł</h3>

            <h3>Dane do płatności</h3>
            <form method="post" action="process_payment.php"> <!-- Formularz do przetwarzania płatności -->
                <label for="name">Imię i nazwisko:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="address">Adres:</label>
                <input type="text" id="address" name="address" required>

                <input type="hidden" name="total" value="<?php echo $total; ?>"> <!-- Przekazanie całkowitej kwoty -->
                <button type="submit" class="checkout-btn">Złóż zamówienie</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Komputer Moja Pasja. Wszelkie prawa zastrzeżone.</p>
        </div>
    </footer>
</body>
</html> 