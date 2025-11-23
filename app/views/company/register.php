<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Your Company</title>

<!-- ✅ Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
  body {
    font-family: "Poppins", sans-serif;
    background-color: #f3f4f6;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 880px;
    margin: 60px auto;
    background: #ffffff;
    padding: 45px 55px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
  }

  h2 {
    text-align: center;
    color: #1e293b;
    margin-bottom: 35px;
    font-weight: 600;
  }

  form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px 35px;
  }

  label {
    font-weight: 500;
    color: #334155;
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
  }

  input[type="text"],
  input[type="email"],
  input[type="file"],
  textarea,
  select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 14px;
    background-color: #f9fafb;
    transition: 0.3s ease;
  }

  input:focus,
  textarea:focus,
  select:focus {
    border-color: #2563eb;
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  }

  textarea {
    resize: vertical;
    min-height: 85px;
  }

  .full {
    grid-column: span 1;
  }

  button {
    grid-column: span 2;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border: none;
    padding: 13px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s ease;
  }

  button:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-2px);
  }

  .phone-group {
    display: flex;
    gap: 10px;
  }

  .phone-group select {
    width: 38%;
  }

  .phone-group input {
    width: 62%;
  }

  /* ✅ Password icon style */
  .password-wrapper {
    position: relative;
    width: 100%; /* allow full-width within the grid column */
  }

  .password-wrapper input {
    width: 100%;
    padding: 10px 14px;
    padding-right: 44px; /* space for the toggle */
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background-color: #f9fafb;
    transition: 0.3s;
    box-sizing: border-box;
  }

  .password-wrapper input:focus {
    border-color: #2563eb;
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  }

  .toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    cursor: pointer;
    user-select: none;
    padding: 4px;
    line-height: 1;
    color: #374151;
    background: transparent;
    border-radius: 6px;
  }

  @media (max-width: 768px) {
    form {
      grid-template-columns: 1fr;
    }
    .full {
      grid-column: span 1;
    }
    .phone-group {
      flex-direction: column;
    }
    .phone-group select,
    .phone-group input {
      width: 100%;
    }
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Register Your Company</h2>
    <form id="companyForm" action="<?= site_url('/company/save') ?>" method="POST" enctype="multipart/form-data">

      <div>
        <label>Company Name:</label>
        <input type="text" name="company_name" required>
      </div>

      <div>
        <label>Address:</label>
        <input id="companyAddress" type="text" name="address" placeholder="Start typing address..." required>
        <input type="hidden" id="address_lat" name="latitude">
        <input type="hidden" id="address_lng" name="longitude">
        <div id="companyMap" style="height:240px;border-radius:8px;margin-top:10px;border:1px solid #e6edf6;display:none;"></div>
        <div id="companyMapNote" style="margin-top:8px;color:#6b7280;font-size:13px;display:none;">Selected place: <span id="companyPlaceName"></span></div>
      </div>

      <div>
        <label>Phone:</label>
        <input class="phone-input" inputmode="numeric" maxlength="11" pattern="\d{10,11}" type="text" name="phone" required>
      </div>

      <div>
        <label>Email:</label>
        <input type="email" name="email" required>
      </div>

      <div>
        <label>Password:</label>
        <div class="password-wrapper">
          <input id="companyPassword" type="password" name="password" required minlength="8" pattern=".{8,}" title="Minimum 8 characters" autocomplete="new-password">
          <span class="toggle-password" id="toggleCompanyPassword" style="display:none;">👁️</span>
        </div>
      </div>

      <div>
        <label>Confirm Password:</label>
        <div class="password-wrapper">
          <input id="confirmPassword" type="password" name="confirm_password" required minlength="8" pattern=".{8,}" title="Minimum 8 characters" onpaste="return false;" oncopy="return false;" autocomplete="new-password">
          <span class="toggle-password" id="toggleConfirmPassword" style="display:none;">👁️</span>
        </div>
      </div>

      <div class="full">
        <label>Business Permit (PDF, JPG, PNG - Max 5MB):</label>
        <input type="file" name="business_permit" accept=".pdf,.jpg,.jpeg,.png" required>
        <div style="font-size:12px;color:#6b7280;margin-top:4px;">Upload a clear copy of your business permit for verification by admin</div>
      </div>

      <div id="companyPwdHint" style="grid-column:span 2;color:#dc2626;font-size:13px;margin-top:-8px;display:none;">Password must be at least 8 characters.</div>
      <button type="submit">Register Company</button>
    </form>
  </div>

  <script>
// ✅ Password toggle using emojis 👁️ / 🚫
function setupPasswordToggle(inputId, toggleId) {
  const input = document.getElementById(inputId);
  const toggle = document.getElementById(toggleId);

  toggle.addEventListener("click", () => {
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    toggle.textContent = show ? "🚫" : "👁️";
  });
}

setupPasswordToggle("companyPassword", "toggleCompanyPassword");
setupPasswordToggle("confirmPassword", "toggleConfirmPassword");

// Only show toggle icon when user types something
function attachToggleVisibility(inputId, toggleId){
  var input = document.getElementById(inputId);
  var toggle = document.getElementById(toggleId);
  input.addEventListener('input', function(){
    if(input.value && input.value.length>0) toggle.style.display = 'inline';
    else toggle.style.display = 'none';
  });
}
attachToggleVisibility('companyPassword','toggleCompanyPassword');
attachToggleVisibility('confirmPassword','toggleConfirmPassword');

// Enforce 8+ length client-side before submit
document.getElementById('companyForm').addEventListener('submit', function(e){
  var p = document.getElementById('companyPassword').value;
  var c = document.getElementById('confirmPassword').value;
  if(p.length < 8 || c.length < 8){
    e.preventDefault();
    alert('Password must be at least 8 characters long.');
    return;
  }
  if(p !== c){
    e.preventDefault();
    alert('Passwords do not match.');
    return;
  }
});

// Dynamic password length hint (same behavior as applicant form)
const companyPwd = document.getElementById('companyPassword');
const companyConfirm = document.getElementById('confirmPassword');
const companyHint = document.getElementById('companyPwdHint');
function updateCompanyHint(){
  const len = companyPwd.value.length;
  if(len === 0 || len >= 8){
    companyHint.style.display = 'none';
    return;
  }
  companyHint.style.display = 'block';
  companyHint.style.color = '#dc2626';
  companyHint.textContent = 'Password must be at least 8 characters.';
}
companyPwd.addEventListener('input', updateCompanyHint);
companyConfirm.addEventListener('input', updateCompanyHint);

// ✅ Password match check
document.getElementById("companyForm").addEventListener("submit", function(e) {
  const pwd = document.getElementById("companyPassword").value;
  const confirm = document.getElementById("confirmPassword").value;
  if (pwd !== confirm) {
    e.preventDefault();
    alert("Passwords do not match. Please confirm your password.");
    return;
  }
});

// ✅ Initialize Leaflet Map (FREE)
const mapElem = document.getElementById('companyMap');
const noteElem = document.getElementById('companyMapNote');
const placeNameElem = document.getElementById('companyPlaceName');
const addressInput = document.getElementById('companyAddress');
const latInput = document.getElementById('address_lat');
const lngInput = document.getElementById('address_lng');

// Default center: Manila
const defaultCenter = [14.5995, 120.9842];

const map = L.map('companyMap').setView(defaultCenter, 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let marker = L.marker(defaultCenter).addTo(map);

// Show map
mapElem.style.display = 'block';
noteElem.style.display = 'block';
placeNameElem.textContent = 'Manila, Philippines';

// ✅ When user types address and presses Enter
// Trigger on Enter OR when input loses focus
addressInput.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    searchAddress(addressInput.value);
  }
});

addressInput.addEventListener('blur', function() {
  searchAddress(addressInput.value);
});


// ✅ Search address using OpenStreetMap Nominatim (free)
async function searchAddress(query) {
  if (!query.trim()) return;

  const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;
  try {
    const response = await fetch(url);
    const data = await response.json();

    if (data && data.length > 0) {
      const { lat, lon, display_name } = data[0];

      // Update map + marker
      map.setView([lat, lon], 15);
      marker.setLatLng([lat, lon]);

      // Update hidden inputs
      latInput.value = lat;
      lngInput.value = lon;

      // Update note
      placeNameElem.textContent = display_name;
      noteElem.style.display = 'block';
    } else {
      alert('No location found. Try another address.');
    }
  } catch (error) {
    console.error('Error fetching location:', error);
  }
}
</script>
  <script>
// Client-side phone input enforcement: strip non-digits and limit length
document.querySelectorAll('.phone-input').forEach(function(el){
  // prevent letters while typing
  el.addEventListener('input', function(e){
    var cleaned = this.value.replace(/\D+/g,'');
    if(cleaned.length > 11) cleaned = cleaned.slice(0,11);
    if(this.value !== cleaned) this.value = cleaned;
  });

  // block non-digit key presses for better UX
  el.addEventListener('keypress', function(e){
    var ch = String.fromCharCode(e.which || e.keyCode);
    if(/\D/.test(ch)) e.preventDefault();
  });
});
</script>
</body>
</html>
