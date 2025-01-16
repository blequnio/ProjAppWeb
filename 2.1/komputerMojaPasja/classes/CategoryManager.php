<?php
class CategoryManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function addCategory($name, $parent_id = 0) {
        $sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $parent_id]);
    }
    
    public function deleteCategory($id) {
        // Najpierw sprawdzamy czy kategoria ma podkategorie
        $sql = "SELECT COUNT(*) FROM categories WHERE parent_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Nie możemy usunąć kategorii z podkategoriami
        }
        
        $sql = "DELETE FROM categories WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function editCategory($id, $name, $parent_id = null) {
        if ($parent_id !== null) {
            $sql = "UPDATE categories SET name = ?, parent_id = ? WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $parent_id, $id]);
        } else {
            $sql = "UPDATE categories SET name = ? WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $id]);
        }
    }
    
    public function showCategories() {
        // Pobieramy główne kategorie
        $sql = "SELECT * FROM categories WHERE parent_id = 0 LIMIT 100";
        $mainCategories = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        $output = '<ul class="categories-list">';
        foreach ($mainCategories as $main) {
            $output .= "<li>{$main['name']}";
            
            // Pobieramy podkategorie
            $sql = "SELECT * FROM categories WHERE parent_id = ? LIMIT 50";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$main['id']]);
            $subCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($subCategories)) {
                $output .= '<ul>';
                foreach ($subCategories as $sub) {
                    $output .= "<li>{$sub['name']}</li>";
                }
                $output .= '</ul>';
            }
            
            $output .= '</li>';
        }
        $output .= '</ul>';
        
        return $output;
    }
} 