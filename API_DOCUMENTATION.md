# Review System REST API Documentation

## Base URL
```
http://localhost/LavaLust-Final
```

## Authentication
All API endpoints (except `/api/health`) require authentication using an API key.

**Header Required:**
```
X-API-Key: lavalust-job-portal-2025
```

---

## Endpoints

### 1. Health Check
Check if the API is running and get a list of available endpoints.

**Endpoint:** `GET /api/health`  
**Authentication:** Not required

**cURL Example:**
```bash
curl http://localhost/LavaLust-Final/api/health
```

**JavaScript Fetch Example:**
```javascript
fetch('http://localhost/LavaLust-Final/api/health')
  .then(response => response.json())
  .then(data => console.log(data));
```

**Response:**
```json
{
  "status": "ok",
  "message": "Review API is running",
  "endpoints": {
    "GET /api/health": "API health check",
    "GET /api/reviews": "Get all reviews (requires API key)",
    "GET /api/reviews/stats": "Get review statistics (requires API key)",
    "POST /api/reviews/submit": "Submit a new review (requires API key)",
    "GET /api/reviews/check": "Check if user has reviewed (requires API key)"
  }
}
```

---

### 2. Get All Reviews
Retrieve all reviews with anonymized user names.

**Endpoint:** `GET /api/reviews`  
**Authentication:** Required

**cURL Example:**
```bash
curl -H "X-API-Key: lavalust-job-portal-2025" \
     http://localhost/LavaLust-Final/api/reviews
```

**JavaScript Fetch Example:**
```javascript
fetch('http://localhost/LavaLust-Final/api/reviews', {
  headers: {
    'X-API-Key': 'lavalust-job-portal-2025'
  }
})
  .then(response => response.json())
  .then(data => {
    console.log(`Total reviews: ${data.count}`);
    data.reviews.forEach(review => {
      console.log(`${review.user_name} gave ${review.rating} stars`);
    });
  });
```

**PHP Example:**
```php
<?php
$ch = curl_init('http://localhost/LavaLust-Final/api/reviews');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: lavalust-job-portal-2025'
]);
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

echo "Total reviews: " . $data['count'] . "\n";
foreach ($data['reviews'] as $review) {
    echo $review['user_name'] . " - " . $review['rating'] . " stars\n";
}
?>
```

**Response:**
```json
{
  "count": 10,
  "reviews": [
    {
      "id": "1",
      "user_id": "5",
      "user_type": "applicant",
      "user_name": "Jo***",
      "rating": "5",
      "comment": "Great platform!",
      "created_at": "2025-01-15 10:30:00"
    },
    {
      "id": "2",
      "user_id": "3",
      "user_type": "employer",
      "user_name": "Ac***",
      "rating": "4",
      "comment": null,
      "created_at": "2025-01-15 11:45:00"
    }
  ]
}
```

---

### 3. Get Review Statistics
Get average rating, total reviews, and rating distribution.

**Endpoint:** `GET /api/reviews/stats`  
**Authentication:** Required

**cURL Example:**
```bash
curl -H "X-API-Key: lavalust-job-portal-2025" \
     http://localhost/LavaLust-Final/api/reviews/stats
```

**JavaScript Fetch Example:**
```javascript
fetch('http://localhost/LavaLust-Final/api/reviews/stats', {
  headers: {
    'X-API-Key': 'lavalust-job-portal-2025'
  }
})
  .then(response => response.json())
  .then(data => {
    console.log(`Average rating: ${data.average_rating}/5`);
    console.log(`Total reviews: ${data.total_reviews}`);
    console.log('Distribution:', data.distribution);
  });
```

**Response:**
```json
{
  "average_rating": 4.3,
  "total_reviews": 25,
  "distribution": {
    "5": 12,
    "4": 8,
    "3": 3,
    "2": 1,
    "1": 1
  }
}
```

---

### 4. Submit a Review
Submit a new review from a user.

**Endpoint:** `POST /api/reviews/submit`  
**Authentication:** Required  
**Content-Type:** `application/json`

**Request Body:**
```json
{
  "user_id": 123,
  "user_type": "applicant",
  "user_name": "John Doe",
  "rating": 5,
  "comment": "Excellent job portal!"
}
```

**Field Validations:**
- `user_id` (integer, required): User's ID
- `user_type` (string, required): Must be "applicant" or "employer"
- `user_name` (string, required): User's full name
- `rating` (integer, required): Must be between 1 and 5
- `comment` (string, optional): Review comment

**cURL Example:**
```bash
curl -X POST http://localhost/LavaLust-Final/api/reviews/submit \
  -H "X-API-Key: lavalust-job-portal-2025" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "user_type": "applicant",
    "user_name": "John Doe",
    "rating": 5,
    "comment": "Excellent job portal!"
  }'
```

**JavaScript Fetch Example:**
```javascript
fetch('http://localhost/LavaLust-Final/api/reviews/submit', {
  method: 'POST',
  headers: {
    'X-API-Key': 'lavalust-job-portal-2025',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    user_id: 123,
    user_type: 'applicant',
    user_name: 'John Doe',
    rating: 5,
    comment: 'Excellent job portal!'
  })
})
  .then(response => response.json())
  .then(data => console.log(data.message));
```

**PHP Example:**
```php
<?php
$data = [
    'user_id' => 123,
    'user_type' => 'applicant',
    'user_name' => 'John Doe',
    'rating' => 5,
    'comment' => 'Excellent job portal!'
];

$ch = curl_init('http://localhost/LavaLust-Final/api/reviews/submit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: lavalust-job-portal-2025',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

echo $result['message'];
?>
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Review submitted successfully",
  "review_id": 26
}
```

**Error Response - Already Reviewed (409 Conflict):**
```json
{
  "success": false,
  "message": "You have already submitted a review"
}
```

**Error Response - Invalid Rating (400 Bad Request):**
```json
{
  "success": false,
  "message": "Rating must be between 1 and 5"
}
```

---

### 5. Check if User Has Reviewed
Check if a specific user has already submitted a review.

**Endpoint:** `GET /api/reviews/check`  
**Authentication:** Required  
**Query Parameters:**
- `user_id` (integer, required): User's ID
- `user_type` (string, required): "applicant" or "employer"

**cURL Example:**
```bash
curl -H "X-API-Key: lavalust-job-portal-2025" \
     "http://localhost/LavaLust-Final/api/reviews/check?user_id=123&user_type=applicant"
```

**JavaScript Fetch Example:**
```javascript
const userId = 123;
const userType = 'applicant';

fetch(`http://localhost/LavaLust-Final/api/reviews/check?user_id=${userId}&user_type=${userType}`, {
  headers: {
    'X-API-Key': 'lavalust-job-portal-2025'
  }
})
  .then(response => response.json())
  .then(data => {
    if (data.has_reviewed) {
      console.log('User has already reviewed');
    } else {
      console.log('User can submit a review');
    }
  });
```

**Response - User has reviewed:**
```json
{
  "has_reviewed": true
}
```

**Response - User has not reviewed:**
```json
{
  "has_reviewed": false
}
```

---

## Error Responses

### 401 Unauthorized
Missing or invalid API key.

```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing API key"
}
```

### 400 Bad Request
Missing required fields or invalid data.

```json
{
  "success": false,
  "message": "Missing required fields: user_id, user_type, user_name, rating"
}
```

### 500 Internal Server Error
Server-side error occurred.

```json
{
  "success": false,
  "message": "Error message here"
}
```

---

## CORS Support
The API includes CORS headers to allow cross-origin requests:
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, X-API-Key`

OPTIONS requests are handled automatically for preflight checks.

---

## Testing with Postman

### Setup
1. Open Postman
2. Create a new Collection: "Review System API"
3. Add environment variable:
   - `base_url`: `http://localhost/LavaLust-Final`
   - `api_key`: `lavalust-job-portal-2025`

### Request Examples

**Health Check:**
- Method: GET
- URL: `{{base_url}}/api/health`
- Headers: None

**Get Reviews:**
- Method: GET
- URL: `{{base_url}}/api/reviews`
- Headers:
  - `X-API-Key`: `{{api_key}}`

**Get Stats:**
- Method: GET
- URL: `{{base_url}}/api/reviews/stats`
- Headers:
  - `X-API-Key`: `{{api_key}}`

**Submit Review:**
- Method: POST
- URL: `{{base_url}}/api/reviews/submit`
- Headers:
  - `X-API-Key`: `{{api_key}}`
  - `Content-Type`: `application/json`
- Body (raw JSON):
```json
{
  "user_id": 123,
  "user_type": "applicant",
  "user_name": "Test User",
  "rating": 5,
  "comment": "Great platform!"
}
```

**Check Review:**
- Method: GET
- URL: `{{base_url}}/api/reviews/check?user_id=123&user_type=applicant`
- Headers:
  - `X-API-Key`: `{{api_key}}`

---

## Rate Limiting
Currently, there is no rate limiting implemented. Consider adding rate limiting for production use.

## Security Notes
1. **API Key Security**: Store the API key securely. Don't commit it to public repositories.
2. **HTTPS**: In production, always use HTTPS to encrypt API communications.
3. **Input Validation**: All inputs are validated server-side.
4. **SQL Injection Prevention**: Using parameterized queries via LavaLust's database class.

---

## Integration Examples

### Mobile App (React Native)
```javascript
const API_BASE = 'http://localhost/LavaLust-Final/api';
const API_KEY = 'lavalust-job-portal-2025';

async function submitReview(userId, userType, userName, rating, comment) {
  const response = await fetch(`${API_BASE}/reviews/submit`, {
    method: 'POST',
    headers: {
      'X-API-Key': API_KEY,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      user_id: userId,
      user_type: userType,
      user_name: userName,
      rating,
      comment
    })
  });
  
  return await response.json();
}
```

### External Dashboard (Vue.js)
```javascript
export default {
  data() {
    return {
      reviews: [],
      stats: {}
    }
  },
  async mounted() {
    await this.loadReviews();
    await this.loadStats();
  },
  methods: {
    async loadReviews() {
      const response = await fetch('http://localhost/LavaLust-Final/api/reviews', {
        headers: { 'X-API-Key': 'lavalust-job-portal-2025' }
      });
      const data = await response.json();
      this.reviews = data.reviews;
    },
    async loadStats() {
      const response = await fetch('http://localhost/LavaLust-Final/api/reviews/stats', {
        headers: { 'X-API-Key': 'lavalust-job-portal-2025' }
      });
      this.stats = await response.json();
    }
  }
}
```

---

## Changelog

### Version 1.0.0 (2025-01-15)
- Initial API release
- 5 endpoints: health, reviews, stats, submit, check
- API key authentication
- CORS support
- Anonymous name display
- Rating distribution stats
