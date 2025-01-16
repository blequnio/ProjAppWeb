<?php
include('cfg.php');
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Obsługa usuwania z koszyka
if (isset($_POST['action']) && $_POST['action'] == 'remove') {
    $product_id = (int)$_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
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
}

// Pobierz produkty z koszyka
$cart_items = array();
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id IN ($ids_string)";
    
    $result = mysqli_query($link, $query);
    
    while ($product = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $gross_price = $product['net_price'] * (1 + ($product['vat_rate']/100));
        $subtotal = $gross_price * $quantity;
        
        $cart_items[] = array(
            'product' => $product,
            'quantity' => $quantity,
            'gross_price' => $gross_price,
            'subtotal' => $subtotal
        );
        
        $total += $subtotal;
    }
}
?>

<div class="cart-content">
    <h2>Koszyk</h2>
    
    <?php if (!empty($cart_items)): ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <?php if(!empty($item['product']['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($item['product']['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product']['title']); ?>">
                    <?php endif; ?>
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['product']['title']); ?></h3>
                        <p>Cena: <?php echo number_format($item['gross_price'], 2); ?> zł</p>
                        <form method="post" class="quantity-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                   min="0" max="<?php echo $item['product']['stock_quantity']; ?>"
                                   onchange="this.form.submit()">
                        </form>
                        <p>Suma: <?php echo number_format($item['subtotal'], 2); ?> zł</p>
                        <form method="post" class="remove-form">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                            <button type="submit">Usuń</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="cart-total">
                <h3>Suma całkowita: <?php echo number_format($total, 2); ?> zł</h3>
            </div>
        </div>
    <?php else: ?>
        <p>Twój koszyk jest pusty.</p>
    <?php endif; ?>
</div> 