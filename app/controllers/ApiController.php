<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        $this->call->model('ReviewModel');
    }

    private function validateApiKey()
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        $validKey = 'lavalust-job-portal-2025';
        
        if ($apiKey !== $validKey) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized: Invalid API key'
            ]);
            exit;
        }
    }

    // GET /api/reviews - Get all reviews
    public function getReviews()
    {
        $this->validateApiKey();
        
        try {
            $reviews = $this->ReviewModel->getAllReviews();
            
            echo json_encode([
                'success' => true,
                'count' => count($reviews),
                'data' => $reviews
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching reviews: ' . $e->getMessage()
            ]);
        }
    }

    // GET /api/reviews/stats - Get rating statistics
    public function getStats()
    {
        $this->validateApiKey();
        
        try {
            $stats = $this->ReviewModel->getAverageRating();
            $distribution = $this->ReviewModel->getRatingDistribution();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'average_rating' => round($stats['avg_rating'] ?? 0, 2),
                    'total_reviews' => (int)($stats['total_reviews'] ?? 0),
                    'distribution' => $distribution
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ]);
        }
    }

    // POST /api/reviews/submit - Submit new review
    public function submitReview()
    {
        $this->validateApiKey();
        
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid JSON format'
                ]);
                return;
            }
            
            $rating = (int)($input['rating'] ?? 0);
            $comment = trim($input['comment'] ?? '');
            $userId = (int)($input['user_id'] ?? 0);
            $userType = $input['user_type'] ?? '';
            $userName = trim($input['user_name'] ?? '');
            
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Rating must be between 1 and 5 stars'
                ]);
                return;
            }
            
            // Validate user data
            if (!$userId || !in_array($userType, ['applicant', 'employer']) || empty($userName)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid user data. Required: user_id, user_type (applicant/employer), user_name'
                ]);
                return;
            }
            
            // Check if already reviewed
            if ($this->ReviewModel->hasUserReviewed($userId, $userType)) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'message' => 'User has already submitted a review'
                ]);
                return;
            }
            
            // Insert review
            $data = [
                'user_id' => $userId,
                'user_type' => $userType,
                'user_name' => $userName,
                'rating' => $rating,
                'comment' => $comment ?: null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if ($this->ReviewModel->insertReview($data)) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Review submitted successfully',
                    'data' => [
                        'rating' => $rating,
                        'submitted_at' => $data['created_at']
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to submit review'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error submitting review: ' . $e->getMessage()
            ]);
        }
    }

    // GET /api/reviews/check - Check if user has reviewed
    public function checkReview()
    {
        $this->validateApiKey();
        
        $userId = (int)($_GET['user_id'] ?? 0);
        $userType = $_GET['user_type'] ?? '';
        
        if (!$userId || !in_array($userType, ['applicant', 'employer'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid parameters. Required: user_id, user_type'
            ]);
            return;
        }
        
        try {
            $hasReviewed = $this->ReviewModel->hasUserReviewed($userId, $userType);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'has_reviewed' => $hasReviewed
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error checking review status: ' . $e->getMessage()
            ]);
        }
    }

    // GET /api/health - API health check
    public function health()
    {
        echo json_encode([
            'success' => true,
            'message' => 'API is running',
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoints' => [
                'GET /api/reviews',
                'GET /api/reviews/stats',
                'POST /api/reviews/submit',
                'GET /api/reviews/check',
                'GET /api/health'
            ]
        ]);
    }
}
