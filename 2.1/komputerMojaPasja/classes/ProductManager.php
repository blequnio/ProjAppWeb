<?php
class ProductManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function addProduct($data) {
        $title = mysqli_real_escape_string($this->db, $data['title']);
        $description = mysqli_real_escape_string($this->db, $data['description']);
        $net_price = floatval($data['net_price']);
        $vat_rate = floatval($data['vat_rate']);
        $stock_quantity = intval($data['stock_quantity']);
        $category_id = intval($data['category_id']);
        $availability_status = mysqli_real_escape_string($this->db, $data['availability_status']);
        $dimensions = mysqli_real_escape_string($this->db, $data['dimensions']);
        $image_url = isset($data['image_url']) ? mysqli_real_escape_string($this->db, $data['image_url']) : '';
        
        $query = "INSERT INTO products (
            title, 
            description, 
            net_price, 
            vat_rate, 
            stock_quantity, 
            category_id, 
            availability_status, 
            dimensions, 
            image_url
        ) VALUES (
            '$title', 
            '$description', 
            $net_price, 
            $vat_rate, 
            $stock_quantity, 
            $category_id, 
            '$availability_status', 
            '$dimensions', 
            '$image_url'
        )";
        
        return mysqli_query($this->db, $query);
    }
    
    public function updateProduct($id, $data) {
        $id = intval($id);
        $title = mysqli_real_escape_string($this->db, $data['title']);
        $description = mysqli_real_escape_string($this->db, $data['description']);
        $net_price = floatval($data['net_price']);
        $vat_rate = floatval($data['vat_rate']);
        $stock_quantity = intval($data['stock_quantity']);
        $category_id = intval($data['category_id']);
        $availability_status = mysqli_real_escape_string($this->db, $data['availability_status']);
        $dimensions = mysqli_real_escape_string($this->db, $data['dimensions']);
        
        $query = "UPDATE products SET 
            title = '$title',
            description = '$description',
            net_price = $net_price,
            vat_rate = $vat_rate,
            stock_quantity = $stock_quantity,
            category_id = $category_id,
            availability_status = '$availability_status',
            dimensions = '$dimensions'";
        
        // Dodaj aktualizację obrazka tylko jeśli został przesłany nowy
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            $image_url = mysqli_real_escape_string($this->db, $data['image_url']);
            $query .= ", image_url = '$image_url'";
        }
        
        $query .= " WHERE id = $id";
        
        return mysqli_query($this->db, $query);
    }
    
    public function deleteProduct($id) {
        $sql = "DELETE FROM products WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function editProduct($id, $data) {
        $sql = "UPDATE products SET 
                title = ?, description = ?, expiry_date = ?, 
                net_price = ?, vat_rate = ?, stock_quantity = ?,
                availability_status = ?, category_id = ?, 
                dimensions = ?, image_url = ?, modified_at = CURRENT_TIMESTAMP
                WHERE id = ? LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['expiry_date'],
            $data['net_price'],
            $data['vat_rate'],
            $data['stock_quantity'],
            $data['availability_status'],
            $data['category_id'],
            $data['dimensions'],
            $data['image_url'],
            $id
        ]);
    }
    
    public function showProducts() {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.availability_status = 'available' 
                AND p.stock_quantity > 0 
                AND (p.expiry_date IS NULL OR p.expiry_date > CURRENT_DATE)
                LIMIT 100";
                
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
} 