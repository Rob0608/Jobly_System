<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ReviewController extends Controller
{
    private function ensureLoggedIn()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in'])) {
            redirect('login');
            exit;
        }
    }

    // Submit review
    public function submit()
    {
        $this->ensureLoggedIn();
        $this->call->model('ReviewModel');

        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if ($rating < 1 || $rating > 5) {
            $_SESSION['error'] = 'Please select a rating between 1 and 5 stars.';
            $this->redirectBack();
            return;
        }

        $userType = $_SESSION['role'] ?? '';
        $userId = null;
        $userName = '';

        if ($userType === 'applicant') {
            $userId = $_SESSION['applicant_id'] ?? null;
            $userName = $_SESSION['applicant_name'] ?? 'Anonymous';
        } elseif ($userType === 'employer') {
            $userId = $_SESSION['company_id'] ?? null;
            $userName = $_SESSION['company_name'] ?? 'Anonymous';
        } else {
            $_SESSION['error'] = 'Invalid user type.';
            $this->redirectBack();
            return;
        }

        if (!$userId) {
            $_SESSION['error'] = 'User ID not found.';
            $this->redirectBack();
            return;
        }

        // Check if user already reviewed
        if ($this->ReviewModel->hasUserReviewed($userId, $userType)) {
            $_SESSION['error'] = 'You have already submitted a review.';
            $this->redirectBack();
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
            $_SESSION['success'] = 'Thank you for your review!';
        } else {
            $_SESSION['error'] = 'Failed to submit review. Please try again.';
        }

        $this->redirectBack();
    }

    private function redirectBack()
    {
        $role = $_SESSION['role'] ?? '';
        if ($role === 'applicant') {
            redirect('applicant/dashboard?section=reviews');
        } elseif ($role === 'employer') {
            redirect('company/employer?tab=reviews');
        } else {
            redirect('login');
        }
    }
}
