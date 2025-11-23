# Job Portal System - Taglish Documentation

## 📖 Overview (Ano ang Ginawa Ko)

Gumawa ako ng isang **complete job portal system** gamit ang LavaLust Framework (PHP MVC) na may mga sumusunod na features:

### 🎯 Main Features:
1. **Google Sign-In Integration** - Para sa applicants, pwedeng mag-login gamit ang Google account
2. **Email Verification System** - 4-digit code verification gamit ang PHPMailer/Gmail SMTP
3. **Review/Rating System** - Users pwedeng mag-rate ng kanilang experience (1-5 stars)
4. **Leaflet Map Integration** - Interactive map para sa company registration
5. **REST API** - Para sa external access sa review system

---

## 🔧 Technologies & Integrations Used

### 1️⃣ **Google OAuth 2.0 Integration** (Google Sign-In)

**Ano ang Google Sign-In?**
- Ito ay isang third-party authentication system kung saan ang users ay pwedeng mag-login gamit ang kanilang Google account.
- Hindi na kailangan gumawa ng bagong password, gamitin lang ang existing Google account.

**Paano ito gumagana?**

```
User → Click "Sign in with Google" 
     → Redirect to Google Login Page
     → Google verifies user credentials
     → Google sends back user info (email, name)
     → System creates/logs in the user
```

**Configuration (config.php):**
```php
// Google OAuth Credentials
$config['google_client_id'] = '1000422010753-ro6eirfnt491jqrg2184l6sdlr2t7bav.apps.googleusercontent.com';
$config['google_client_secret'] = 'REDACTED_GOOGLE_CLIENT_SECRET';
$config['google_redirect_uri'] = 'http://localhost/LavaLust-Final/index.php/auth/google/callback';
```

**Paano kumuha ng Google Credentials:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create new project or select existing
3. Enable "Google+ API"
4. Go to "Credentials" → Create OAuth 2.0 Client ID
5. Set Authorized Redirect URI: `http://localhost/LavaLust-Final/index.php/auth/google/callback`
6. Copy Client ID at Client Secret

**Libraries Used:**
- `google/apiclient` (Composer package: `composer require google/apiclient`)
- Located sa: `vendor/google/apiclient/`

**Controller Implementation:**
- **File:** `app/controllers/SocialAuthController.php`
- **Methods:**
  - `google()` - Starts OAuth flow, redirects to Google
  - `googleCallback()` - Receives Google response, validates token
  - `googleCompleteApplicant()` - Completes registration with additional info (birthdate)

**Flow:**
1. User clicks "Sign in with Google" → `/auth/google`
2. System redirects to Google login page
3. Google validates user → redirects back to `/auth/google/callback`
4. System checks if user exists sa database:
   - **Kung existing user** → Direct login → Dashboard
   - **Kung new user** → Show completion form (name, birthdate) → Save to DB → Login
5. Session is created with user info
6. Last login timestamp updated

**Security Features:**
- State parameter validation (prevents CSRF attacks)
- Email verification check (Google-verified emails only)
- ID token validation
- Age validation (18-60 years old)

**Sample Google Response (ID Token Payload):**
```json
{
  "sub": "1234567890",
  "email": "user@gmail.com",
  "email_verified": true,
  "given_name": "John",
  "family_name": "Doe",
  "picture": "https://..."
}
```

---

### 2️⃣ **PHPMailer + Gmail SMTP** (Email Verification)

**Ano ang Email Verification?**
- Automated email sending system para sa verification codes
- Ginagamit ang Gmail SMTP server para mag-send ng emails
- 4-digit verification code sent to user's email

**Paano ito gumagana?**

```
User registers → System generates 4-digit code (e.g., 1234)
              → Saves code sa database
              → Sends email with code via Gmail SMTP
              → User enters code
              → System verifies code
              → Account activated
```

**Gmail SMTP Configuration:**
```php
// In CompanyController.php & ApplicantController.php
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'robabarintos@gmail.com';      // Your Gmail address
$mail->Password   = 'hbvu mfbf fnea sbak';          // Gmail App Password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption
$mail->Port       = 587;                            // SMTP port
```

**Important Notes:**
- ❌ **Hindi pwede ang regular Gmail password** - Google blocks it for security
- ✅ **Kailangan mag-generate ng App Password**

**Paano kumuha ng Gmail App Password:**
1. Go to your Google Account → [myaccount.google.com](https://myaccount.google.com)
2. Click "Security"
3. Enable "2-Step Verification" (required)
4. Click "App passwords"
5. Select "Mail" and your device
6. Copy the 16-character password (example: `hbvu mfbf fnea sbak`)
7. Use this password sa code (NOT your regular Gmail password)

**Libraries Used:**
- `phpmailer/phpmailer` (Composer package)
- Located sa: `app/third_party/PHPMailer/` or `vendor/phpmailer/`

**Email Template Example:**
```php
$mail->isHTML(true);
$mail->Subject = 'Your Verification Code';
$mail->Body = "
    <h3>Hello, {$name}!</h3>
    <p>Your 4-digit verification code is:</p>
    <h2 style='letter-spacing:5px;'>{$verification_code}</h2>
    <p>Enter this code to activate your account.</p>
";
```

**Verification Code Generation:**
```php
$verification_code = rand(1000, 9999); // Random 4-digit number
```

**Database Storage:**
```sql
-- In applicants or companies table
verification_code VARCHAR(10),
is_verified TINYINT(1) DEFAULT 0
```

**Verification Flow:**
1. User registers → Code generated (e.g., `1234`)
2. Code saved sa database (unverified status)
3. Email sent with code
4. User receives email → enters 4 digits
5. System compares input vs database code
6. Match → Set `is_verified = 1` → Activate account
7. Mismatch → Error message, user tries again

**Error Handling:**
- Email sending errors logged sa error_log
- User shown success message even if email fails (code still valid)
- Alternative: Resend code functionality

---

### 3️⃣ **Review/Rating System** (Custom-built)

**Ano ang Review System?**
- Internal feature kung saan ang users (applicants/employers) ay pwedeng mag-rate ng kanilang experience sa platform
- Hindi ito Google Reviews integration - sariling system natin ito
- Users give 1-5 star rating + optional comment

**Database Schema:**
```sql
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('applicant', 'employer') NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Paano ito gumagana?**

```
User logged in → Goes to "Reviews" section
              → Selects star rating (1-5 stars)
              → Optional: Adds comment
              → Submits review
              → System saves to database
              → Name is anonymized (Jo*** instead of John)
              → Review appears sa dashboard
```

**Models (ReviewModel.php):**
```php
// Insert new review
insertReview($data) 
    → Saves: user_id, user_type, user_name, rating, comment

// Get all reviews with anonymized names
getAllReviews() 
    → Returns: [{user_name: "Jo***", rating: 5, comment: "Great!"}]

// Calculate average rating
getAverageRating() 
    → Returns: {average_rating: 4.5, total_reviews: 25}

// Get rating distribution (how many 5-star, 4-star, etc.)
getRatingDistribution() 
    → Returns: {5: 12, 4: 8, 3: 3, 2: 1, 1: 1}

// Check if user already reviewed
hasUserReviewed($userId, $userType) 
    → Returns: true/false (prevents duplicate reviews)

// Anonymize names
anonymizeName("John Doe") 
    → Returns: "Jo***" (first 2 letters + asterisks)
```

**Controller (ReviewController.php):**
```php
public function submit() {
    // Validate: user must be logged in
    // Validate: rating between 1-5
    // Check: user hasn't reviewed yet
    // Save to database
    // Redirect with success message
}
```

**Star Rating UI (JavaScript):**
```javascript
// Interactive star rating
const stars = document.querySelectorAll('.star');
stars.forEach((star, index) => {
    // Click event - select rating
    star.addEventListener('click', () => {
        selectedRating = index + 1;
        updateStars(selectedRating);
    });
    
    // Hover event - preview rating
    star.addEventListener('mouseenter', () => {
        updateStars(index + 1);
    });
});

function updateStars(rating) {
    stars.forEach((s, i) => {
        s.textContent = i < rating ? '★' : '☆'; // Filled or empty star
    });
}
```

**Features:**
1. **One review per user** - hasUserReviewed() prevents duplicates
2. **Anonymous display** - "John Doe" → "Jo***"
3. **Star rating** - 1 to 5 stars (required)
4. **Optional comment** - Text field (can be empty)
5. **Statistics** - Average rating, total reviews, distribution chart
6. **All dashboards** - Applicant, Employer, Admin can view reviews

**Privacy:**
- User's full name is hidden
- Only first 2 letters shown: "John" → "Jo**", "Alice" → "Al***"
- Prevents personal identification while showing reviews

---

### 4️⃣ **Leaflet Map + OpenStreetMap** (Interactive Maps)

**Ano ang Leaflet?**
- Leaflet is a FREE, open-source JavaScript library for interactive maps
- Alternative sa Google Maps (hindi kailangan ng API key/billing)
- Uses OpenStreetMap data (like Wikipedia of maps - community-driven)

**Paano ito gumagana?**

```
Company registers → Enters address
                  → System searches address via OpenStreetMap Nominatim API
                  → Returns latitude & longitude
                  → Leaflet displays map with marker
                  → User can drag marker to adjust location
                  → Coordinates saved sa database
```

**Configuration (No API Key Needed!):**
```html
<!-- Include Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
```

**Map Initialization:**
```javascript
// Default center (Manila, Philippines)
const defaultCenter = [14.5995, 120.9842];

// Create map
const map = L.map('companyMap').setView(defaultCenter, 13);

// Add OpenStreetMap tiles (FREE)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Add marker
let marker = L.marker(defaultCenter).addTo(map);
```

**Address Search (Nominatim API):**
```javascript
// FREE geocoding API from OpenStreetMap
const query = "Quezon City, Philippines";
const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;

fetch(url)
  .then(response => response.json())
  .then(data => {
    if (data.length > 0) {
      const lat = parseFloat(data[0].lat);
      const lon = parseFloat(data[0].lon);
      
      // Update map and marker
      map.setView([lat, lon], 15);
      marker.setLatLng([lat, lon]);
    }
  });
```

**Server-side Geocoding (PHP):**
```php
// CompanyController.php - Save company with coordinates
$address = $_POST['address'];
$nominatimUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);
$response = file_get_contents($nominatimUrl);
$results = json_decode($response, true);

if (!empty($results)) {
    $latitude = $results[0]['lat'];
    $longitude = $results[0]['lon'];
    
    // Save to database
    $data['latitude'] = $latitude;
    $data['longitude'] = $longitude;
}
```

**Database Storage:**
```sql
-- In companies table
latitude DECIMAL(10, 8),     -- Example: 14.5995
longitude DECIMAL(11, 8),    -- Example: 120.9842
address TEXT
```

**Features:**
1. **Interactive map** - User can click/drag anywhere
2. **Address search** - Type address, auto-find location
3. **Marker placement** - Visual indicator of company location
4. **Zoom controls** - Zoom in/out to see area
5. **Free to use** - No API key, no billing required

**Libraries Used:**
- **Leaflet.js** - Map rendering library
- **OpenStreetMap** - Map data provider (tiles)
- **Nominatim API** - Address geocoding (address → lat/lon)

**Why FREE?**
- OpenStreetMap is community-driven (like Wikipedia)
- Nominatim is official OSM geocoding service
- No commercial restrictions for reasonable use
- Fair use policy: 1 request per second

**Rate Limiting (Important!):**
- Nominatim allows **1 request per second**
- Add delay if making multiple requests
- Use User-Agent header to identify your app:
  ```javascript
  headers: {
    'User-Agent': 'JobPortal/1.0 (your-email@example.com)'
  }
  ```

---

### 5️⃣ **REST API** (Review System API)

**Ano ang REST API?**
- REST API = REpresentational State Transfer API
- Ito ay isang interface para sa external applications na mag-access ng review data
- Uses HTTP methods (GET, POST) and JSON format
- Para sa mobile apps, external dashboards, or third-party integrations

**Paano ito gumagana?**

```
External App (e.g., Mobile App)
    ↓
Sends HTTP Request with API Key
    ↓
Our Server (ApiController.php)
    ↓
Validates API Key → Processes Request → Returns JSON
    ↓
External App receives JSON data
```

**API Configuration:**

**1. API Key Authentication:**
```php
// app/controllers/ApiController.php
private function validateApiKey() {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($apiKey !== 'lavalust-job-portal-2025') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid or missing API key']);
        exit;
    }
}
```

**API Key:** `lavalust-job-portal-2025`

**2. CORS Configuration (Cross-Origin Requests):**
```php
// Allow requests from any domain (for development)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
```

**Ano ang CORS?**
- CORS = Cross-Origin Resource Sharing
- Security feature ng browsers
- Prevents unauthorized websites from accessing your API
- `*` means "allow all origins" (for development)
- For production, change to specific domain: `https://yourdomain.com`

**3. API Routes (routes.php):**
```php
// API Routes
$router->get('/api/health', 'ApiController::health');
$router->get('/api/reviews', 'ApiController::getReviews');
$router->get('/api/reviews/stats', 'ApiController::getStats');
$router->post('/api/reviews/submit', 'ApiController::submitReview');
$router->get('/api/reviews/check', 'ApiController::checkReview');
```

**API Endpoints:**

#### 1. Health Check (No Auth Required)
```bash
GET http://localhost/LavaLust-Final/api/health

Response:
{
  "status": "ok",
  "message": "Review API is running",
  "endpoints": {
    "GET /api/health": "API health check",
    "GET /api/reviews": "Get all reviews",
    ...
  }
}
```

#### 2. Get All Reviews (Auth Required)
```bash
GET http://localhost/LavaLust-Final/api/reviews
Header: X-API-Key: lavalust-job-portal-2025

Response:
{
  "count": 10,
  "reviews": [
    {
      "id": "1",
      "user_name": "Jo***",
      "rating": "5",
      "comment": "Great platform!",
      "created_at": "2025-01-15 10:30:00"
    }
  ]
}
```

#### 3. Get Statistics (Auth Required)
```bash
GET http://localhost/LavaLust-Final/api/reviews/stats
Header: X-API-Key: lavalust-job-portal-2025

Response:
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

#### 4. Submit Review (Auth Required)
```bash
POST http://localhost/LavaLust-Final/api/reviews/submit
Header: X-API-Key: lavalust-job-portal-2025
Header: Content-Type: application/json

Body:
{
  "user_id": 123,
  "user_type": "applicant",
  "user_name": "John Doe",
  "rating": 5,
  "comment": "Excellent!"
}

Success Response (201):
{
  "success": true,
  "message": "Review submitted successfully",
  "review_id": 26
}

Error Response (409):
{
  "success": false,
  "message": "You have already submitted a review"
}
```

#### 5. Check if User Reviewed (Auth Required)
```bash
GET http://localhost/LavaLust-Final/api/reviews/check?user_id=123&user_type=applicant
Header: X-API-Key: lavalust-job-portal-2025

Response:
{
  "has_reviewed": true
}
```

**HTTP Status Codes:**
- `200 OK` - Success (GET requests)
- `201 Created` - Success (POST requests)
- `400 Bad Request` - Invalid data/missing fields
- `401 Unauthorized` - Invalid/missing API key
- `409 Conflict` - Duplicate review
- `500 Internal Server Error` - Server error

**Sample Usage (JavaScript):**
```javascript
// Get all reviews
fetch('http://localhost/LavaLust-Final/api/reviews', {
  headers: {
    'X-API-Key': 'lavalust-job-portal-2025'
  }
})
  .then(response => response.json())
  .then(data => {
    console.log(`Total reviews: ${data.count}`);
    data.reviews.forEach(review => {
      console.log(`${review.user_name} - ${review.rating} stars`);
    });
  });

// Submit review
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
    comment: 'Great platform!'
  })
})
  .then(response => response.json())
  .then(data => console.log(data.message));
```

**Sample Usage (cURL):**
```bash
# Health check
curl http://localhost/LavaLust-Final/api/health

# Get reviews
curl -H "X-API-Key: lavalust-job-portal-2025" \
     http://localhost/LavaLust-Final/api/reviews

# Submit review
curl -X POST http://localhost/LavaLust-Final/api/reviews/submit \
  -H "X-API-Key: lavalust-job-portal-2025" \
  -H "Content-Type: application/json" \
  -d '{"user_id":123,"user_type":"applicant","user_name":"John Doe","rating":5,"comment":"Great!"}'
```

**Security Notes:**
1. **API Key Protection** - Store API key securely, don't commit to GitHub
2. **HTTPS in Production** - Use SSL certificate for encrypted communication
3. **Rate Limiting** - Consider adding request limits (e.g., 100 requests/hour)
4. **Input Validation** - All inputs validated server-side
5. **SQL Injection Prevention** - Using parameterized queries

---

## 📋 Complete API Configuration Summary

### Google OAuth 2.0
```php
// app/config/config.php
$config['google_client_id'] = '1000422010753-ro6eirfnt491jqrg2184l6sdlr2t7bav.apps.googleusercontent.com';
$config['google_client_secret'] = 'REDACTED_GOOGLE_CLIENT_SECRET';
$config['google_redirect_uri'] = 'http://localhost/LavaLust-Final/index.php/auth/google/callback';
$config['ca_bundle_path'] = 'C:/wamp64/bin/php/php8.3.14/extras/ssl/cacert.pem';
```

**Where to Get:**
- Google Cloud Console: https://console.cloud.google.com/
- Create OAuth 2.0 Client ID
- Enable Google+ API

### Gmail SMTP (PHPMailer)
```php
// In Controllers (CompanyController.php, ApplicantController.php)
$mail->Host       = 'smtp.gmail.com';
$mail->Username   = 'robabarintos@gmail.com';
$mail->Password   = 'hbvu mfbf fnea sbak';  // App Password
$mail->Port       = 587;
```

**Where to Get:**
- Google Account → Security → 2-Step Verification → App Passwords
- Generate 16-character password
- **DO NOT use regular Gmail password!**

### OpenStreetMap/Nominatim API (Leaflet)
```javascript
// FREE - No API Key Needed
const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + address;
```

**Fair Use Policy:**
- 1 request per second
- Add User-Agent header
- No API key/billing required

### Review System REST API
```php
// API Key
$apiKey = 'lavalust-job-portal-2025';

// Base URL
$baseUrl = 'http://localhost/LavaLust-Final/api';

// Routes (app/config/routes.php)
$router->get('/api/health', 'ApiController::health');
$router->get('/api/reviews', 'ApiController::getReviews');
$router->get('/api/reviews/stats', 'ApiController::getStats');
$router->post('/api/reviews/submit', 'ApiController::submitReview');
$router->get('/api/reviews/check', 'ApiController::checkReview');
```

**How to Use:**
- Add header: `X-API-Key: lavalust-job-portal-2025`
- All responses in JSON format
- CORS enabled for cross-origin requests

---

## 🎓 Taglish Explanation ng Code Flow

### Google Sign-In Flow:

1. **User clicks "Sign in with Google"**
   ```php
   // SocialAuthController::google()
   $client = new Google_Client();
   $authUrl = $client->createAuthUrl();
   // Redirect user to Google login page
   ```
   **Explanation:** Ang system ay gumagawa ng special URL from Google, tapos redirect doon ang user para mag-login.

2. **Google validates credentials**
   - User logs in sa Google
   - Google checks kung tama ang email/password
   - Pag OK, Google sends back authentication code

3. **System receives callback**
   ```php
   // SocialAuthController::googleCallback()
   $client->fetchAccessTokenWithAuthCode($_GET['code']);
   $payload = $client->verifyIdToken();
   $email = $payload['email'];
   ```
   **Explanation:** Ang code na galing kay Google, i-convert natin to user info (email, name, etc.)

4. **Check if user exists**
   ```php
   $existing = $appModel->getApplicantByEmail($email);
   if ($existing && !empty($existing['birthdate'])) {
       // Login directly
       $_SESSION['logged_in'] = true;
       redirect('applicant/dashboard');
   } else {
       // Show completion form
       $this->call->view('applicant/google_complete');
   }
   ```
   **Explanation:** 
   - Kung may existing account na + may birthdate → diretso login
   - Kung new user o walang birthdate → papasagutan muna ng form (name, birthdate)

### Email Verification Flow:

1. **Generate code**
   ```php
   $verification_code = rand(1000, 9999); // e.g., 1234
   ```
   **Explanation:** Random 4-digit number generated.

2. **Save to database**
   ```php
   $data['verification_code'] = $verification_code;
   $data['is_verified'] = 0;
   $appModel->insertApplicant($data);
   ```
   **Explanation:** I-save ang code sa database, status = "not yet verified"

3. **Send email**
   ```php
   $mail = new PHPMailer(true);
   $mail->isSMTP();
   $mail->Host = 'smtp.gmail.com';
   $mail->Username = 'robabarintos@gmail.com';
   $mail->Password = 'hbvu mfbf fnea sbak'; // App Password!
   $mail->Body = "Your code is: $verification_code";
   $mail->send();
   ```
   **Explanation:** Gamit ang Gmail SMTP, i-send ang email with code sa user.

4. **User enters code**
   ```php
   $inputCode = $_POST['c1'] . $_POST['c2'] . $_POST['c3'] . $_POST['c4'];
   if ($companyModel->verifyCode($email, $inputCode)) {
       // Success - set is_verified = 1
   } else {
       // Error - wrong code
   }
   ```
   **Explanation:** I-compare ang na-input ng user vs code sa database. Match → verified!

### Review System Flow:

1. **User selects stars**
   ```javascript
   star.addEventListener('click', () => {
       selectedRating = index + 1; // 1 to 5
       updateStars(selectedRating);
   });
   ```
   **Explanation:** Pag click sa star, i-update ang rating (1-5 stars).

2. **Submit review**
   ```php
   // ReviewController::submit()
   $data = [
       'user_id' => $_SESSION['applicant_id'],
       'user_type' => 'applicant',
       'user_name' => $_SESSION['applicant_name'],
       'rating' => $_POST['rating'],
       'comment' => $_POST['comment']
   ];
   $this->ReviewModel->insertReview($data);
   ```
   **Explanation:** Kunin ang rating + comment from form, i-save sa database.

3. **Anonymize name**
   ```php
   private function anonymizeName($name) {
       $firstTwo = mb_substr($name, 0, 2); // "Jo"
       $remaining = mb_strlen($name) - 2;   // 6 (from "hn Doe")
       return $firstTwo . str_repeat('*', $remaining); // "Jo******"
   }
   ```
   **Explanation:** "John Doe" → "Jo***" para hindi kita ang full name.

4. **Display reviews**
   ```php
   $reviews = $this->ReviewModel->getAllReviews();
   foreach ($reviews as $review) {
       echo "<p>{$review['user_name']} - {$review['rating']} stars</p>";
   }
   ```
   **Explanation:** I-fetch lahat ng reviews from database, i-display with anonymized names.

### Leaflet Map Flow:

1. **Initialize map**
   ```javascript
   const map = L.map('companyMap').setView([14.5995, 120.9842], 13);
   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
   ```
   **Explanation:** Gumawa ng map centered sa Manila, add tiles (map images) from OpenStreetMap.

2. **Search address**
   ```javascript
   const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}`;
   fetch(url)
     .then(response => response.json())
     .then(data => {
       const lat = data[0].lat;
       const lon = data[0].lon;
       map.setView([lat, lon], 15);
     });
   ```
   **Explanation:** I-search ang address sa Nominatim API, makukuha ang latitude/longitude, i-update ang map.

3. **Save coordinates**
   ```php
   $data['latitude'] = $latitude;
   $data['longitude'] = $longitude;
   $companyModel->insertCompany($data);
   ```
   **Explanation:** I-save ang coordinates sa database para later ma-display ulit ang location.

### REST API Flow:

1. **Client sends request**
   ```javascript
   fetch('http://localhost/LavaLust-Final/api/reviews', {
     headers: { 'X-API-Key': 'lavalust-job-portal-2025' }
   })
   ```
   **Explanation:** External app (e.g., mobile app) sends HTTP request with API key.

2. **Server validates API key**
   ```php
   private function validateApiKey() {
       $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
       if ($apiKey !== 'lavalust-job-portal-2025') {
           http_response_code(401);
           exit;
       }
   }
   ```
   **Explanation:** I-check kung tama ang API key. Wrong key → 401 Unauthorized error.

3. **Process request**
   ```php
   public function getReviews() {
       $this->validateApiKey();
       $reviews = $this->ReviewModel->getAllReviews();
       echo json_encode(['count' => count($reviews), 'reviews' => $reviews]);
   }
   ```
   **Explanation:** Kunin ang reviews from database, i-convert to JSON, i-send pabalik.

4. **Client receives JSON**
   ```javascript
   .then(response => response.json())
   .then(data => {
       console.log(`Total reviews: ${data.count}`);
   });
   ```
   **Explanation:** Mobile app receives JSON data, pwede na i-display sa UI.

---

## 🔐 Security Best Practices

### 1. API Key Security
```php
// ❌ BAD: Hardcoded in client-side JavaScript
const API_KEY = 'lavalust-job-portal-2025'; // Visible sa browser!

// ✅ GOOD: Use environment variables
$apiKey = $_ENV['API_KEY'] ?? 'default-key';

// ✅ BETTER: Store in config.php (server-side only)
$config['api_key'] = 'lavalust-job-portal-2025';
```

### 2. Password Security
```php
// ❌ BAD: Plain text passwords
$password = $_POST['password'];
// Save: "mypassword123" → visible sa database!

// ✅ GOOD: Hash passwords
$hashed = password_hash($password, PASSWORD_BCRYPT);
// Save: "$2y$10$abc123..." → encrypted, hindi nababasa

// Verify on login
if (password_verify($inputPassword, $hashedFromDB)) {
    // Correct password
}
```

### 3. SQL Injection Prevention
```php
// ❌ BAD: Direct string concatenation
$sql = "SELECT * FROM users WHERE email = '$email'";
// Vulnerable to: ' OR '1'='1

// ✅ GOOD: Parameterized queries
$stmt = $this->db->raw("SELECT * FROM users WHERE email = ?", [$email]);
```

### 4. HTTPS in Production
```
❌ http://example.com/api/reviews
   → Data visible in plain text

✅ https://example.com/api/reviews
   → Data encrypted with SSL/TLS
```

---

## 📚 Summary

**Integrations Used:**
1. ✅ **Google OAuth 2.0** - Third-party authentication
2. ✅ **PHPMailer + Gmail SMTP** - Email sending service
3. ✅ **Leaflet + OpenStreetMap** - FREE mapping solution
4. ✅ **Review System** - Custom-built internal feature
5. ✅ **REST API** - External access interface

**API Keys/Credentials Needed:**
- Google Client ID & Secret (from Google Cloud Console)
- Gmail App Password (from Google Account Security)
- API Key for REST API (`lavalust-job-portal-2025`)
- ❌ NO API key needed for OpenStreetMap/Leaflet (FREE!)

**Files to Configure:**
- `app/config/config.php` - Google OAuth credentials
- `app/controllers/CompanyController.php` - Gmail SMTP settings
- `app/controllers/ApplicantController.php` - Gmail SMTP settings
- `app/controllers/ApiController.php` - REST API key validation
- `app/config/routes.php` - API routes

Yun na! Complete documentation with Taglish explanation. 🎉
