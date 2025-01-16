<?php
class Cart {
    public function __construct() {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function addToCart($product_id, $quantity = 1) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    
    public function removeFromCart($product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            // Resetujemy indeksy tablicy
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
    
    public function updateQuantity($product_id, $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            $this->removeFromCart($product_id);
        }
    }
    
    public function showCart($db) {
        if (empty($_SESSION['cart'])) {
            return ['items' => [], 'total' => 0];
        }
        
        $items = [];
        $total = 0;
        
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql = "SELECT * FROM products WHERE id = ? LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $gross_price = $product['net_price'] * (1 + ($product['vat_rate']/100));
                $subtotal = $gross_price * $quantity;
                
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'gross_price' => $gross_price,
                    'subtotal' => $subtotal
                ];
                
                $total += $subtotal;
            }
        }
        
        return [
            'items' => $items,
            'total' => $total
        ];
    }
} 