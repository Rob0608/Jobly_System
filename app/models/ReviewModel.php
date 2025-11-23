<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ReviewModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
    }

    // Insert new review
    public function insertReview($data)
    {
        return $this->db->table('reviews')->insert($data);
    }

    // Get all reviews with anonymous names
    public function getAllReviews()
    {
        $sql = "SELECT id, user_type, user_name, rating, comment, created_at 
                FROM reviews 
                ORDER BY created_at DESC";
        $query = $this->db->raw($sql);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Anonymize names
        foreach ($results as &$review) {
            $review['user_name'] = $this->anonymizeName($review['user_name']);
        }
        
        return $results;
    }

    // Get average rating
    public function getAverageRating()
    {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                FROM reviews";
        $query = $this->db->raw($sql);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Get rating distribution
    public function getRatingDistribution()
    {
        $sql = "SELECT rating, COUNT(*) as count 
                FROM reviews 
                GROUP BY rating 
                ORDER BY rating DESC";
        $query = $this->db->raw($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if user already reviewed
    public function hasUserReviewed($userId, $userType)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM reviews 
                WHERE user_id = :user_id AND user_type = :user_type";
        $query = $this->db->raw($sql, [
            ':user_id' => $userId,
            ':user_type' => $userType
        ]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Anonymize name: show first 2 letters, rest asterisks
    private function anonymizeName($name)
    {
        $name = trim($name);
        if (strlen($name) <= 2) {
            return $name;
        }
        
        $firstTwo = mb_substr($name, 0, 2);
        $remaining = mb_strlen($name) - 2;
        $asterisks = str_repeat('*', $remaining);
        
        return $firstTwo . $asterisks;
    }
}
