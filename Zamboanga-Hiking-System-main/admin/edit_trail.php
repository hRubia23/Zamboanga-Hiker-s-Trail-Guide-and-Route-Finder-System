<?php
require_once "../includes/auth.php";
check_login();
require_once "../includes/db.php";
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM trails WHERE id=?");
$stmt->execute([$id]);
$trail = $stmt->fetch();
if (!$trail) die("Trail not found!");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $difficulty = $_POST['difficulty'];
    $image = $trail['image'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../public/assets/uploads/";
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $image);
    }
    $stmt = $pdo->prepare("UPDATE trails SET name=?, description=?, location=?, difficulty=?, image=? WHERE id=?");
    $stmt->execute([$name, $description, $location, $difficulty, $image, $id]);
    header("Location: manage_trails.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trail - Zamboanga Hiking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(34, 197, 94, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.15) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        .floating-leaves {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .leaf {
            position: absolute;
            font-size: 24px;
            opacity: 0.2;
            animation: fall linear infinite;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.2;
            }
            90% {
                opacity: 0.2;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .header-section {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.9));
            padding: 40px;
            border-radius: 25px 25px 0 0;
            text-align: center;
            color: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 30% 50%, rgba(255,255,255,0.1) 0%, transparent 25%),
                radial-gradient(circle at 70% 70%, rgba(255,255,255,0.08) 0%, transparent 25%);
            animation: float 15s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(20px, -20px) rotate(3deg); }
            66% { transform: translate(-15px, 15px) rotate(-3deg); }
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .header-section h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 2px 2px 15px rgba(0,0,0,0.2);
        }

        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .form-container {
            background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
            padding: 50px;
            border-radius: 0 0 25px 25px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-top: none;
        }

        .form-grid {
            display: grid;
            gap: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group label {
            font-weight: 700;
            color: #1f2937;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .label-icon {
            font-size: 1.3rem;
        }

        .required {
            color: #ef4444;
            font-size: 0.9rem;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            transform: translateY(-2px);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
            font-family: inherit;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url('data:image/svg+xml,<svg xmlns="http:
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 24px;
            padding-right: 50px;
        }

        .difficulty-preview {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .difficulty-badge {
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .difficulty-badge.easy {
            background: rgba(34, 197, 94, 0.2);
            color: #065f46;
        }

        .difficulty-badge.moderate {
            background: rgba(251, 191, 36, 0.2);
            color: #92400e;
        }

        .difficulty-badge.hard {
            background: rgba(239, 68, 68, 0.2);
            color: #991b1b;
        }

        .difficulty-badge.active {
            border-color: currentColor;
            transform: scale(1.05);
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-upload-input {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: rgba(16, 185, 129, 0.08);
            border: 2px dashed #10b981;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #059669;
            font-weight: 600;
        }

        .file-upload-label:hover {
            background: rgba(16, 185, 129, 0.15);
            border-color: #059669;
            transform: translateY(-2px);
        }

        .file-upload-icon {
            font-size: 2rem;
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .file-hint {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .current-image {
            margin-top: 15px;
            padding: 20px;
            background: rgba(16, 185, 129, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .current-image-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #059669;
            margin-bottom: 12px;
            display: block;
        }

        .image-preview {
            width: 100%;
            max-width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
        }

        .btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px 32px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn span {
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
        }

        .btn-secondary {
            background: rgba(107, 114, 128, 0.1);
            color: #4b5563;
            border: 2px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: rgba(107, 114, 128, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .helper-text {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .helper-icon {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 30px 25px;
            }

            .header-section {
                padding: 30px 25px;
            }

            .header-section h2 {
                font-size: 2rem;
            }

            .button-group {
                flex-direction: column;
            }

            .difficulty-preview {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 15px;
            }

            .header-icon {
                font-size: 2.5rem;
            }

            .header-section h2 {
                font-size: 1.75rem;
            }

            .form-container {
                padding: 25px 20px;
            }

            .btn {
                padding: 16px 24px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <script src="https:
    <div class="floating-leaves">
        <div class="leaf" style="left: 10%; animation-duration: 15s; animation-delay: 0s;">🍃</div>
        <div class="leaf" style="left: 30%; animation-duration: 18s; animation-delay: 3s;">🌿</div>
        <div class="leaf" style="left: 50%; animation-duration: 20s; animation-delay: 6s;">🍃</div>
        <div class="leaf" style="left: 70%; animation-duration: 17s; animation-delay: 2s;">🌿</div>
        <div class="leaf" style="left: 85%; animation-duration: 19s; animation-delay: 5s;">🍃</div>
    </div>

    <div class="container">
        <div class="header-section">
            <div class="header-content">
                <div class="header-icon">✏️</div>
                <h2>Edit Trail</h2>
                <p>Update trail information and details</p>
            </div>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="editTrailForm">
                <div class="form-grid">
                    <!-- Trail Name -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">🏔️</span>
                            Trail Name
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="name" value="<?= htmlspecialchars($trail['name']) ?>" required placeholder="Enter trail name">
                        <div class="helper-text">
                            <span class="helper-icon">💡</span>
                            Choose a descriptive and memorable name
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">📝</span>
                            Description
                            <span class="required">*</span>
                        </label>
                        <textarea name="description" required placeholder="Describe the trail, its features, and what makes it special..."><?= htmlspecialchars($trail['description']) ?></textarea>
                        <div class="helper-text">
                            <span class="helper-icon">💡</span>
                            Include highlights, scenery, and what hikers can expect
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">📍</span>
                            Location
                        </label>
                        <input type="text" name="location" id="locationInput" value="<?= htmlspecialchars($trail['location']) ?>" placeholder="e.g., Mt. Pulog, Benguet">
                        <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($trail['latitude'] ?? '') ?>">
                        <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($trail['longitude'] ?? '') ?>">
                        <div class="helper-text">
                            <span class="helper-icon">💡</span>
                            Search and click on the map to set the trail location
                        </div>
                    </div>

                    <!-- Google Maps -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">🗺️</span>
                            Trail Location on Map
                        </label>
                        <div id="map" style="width: 100%; height: 400px; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1);"></div>
                        <div class="helper-text">
                            <span class="helper-icon">💡</span>
                            <span id="coordinates-display">Click on the map to set the exact trail location</span>
                        </div>
                    </div>

                    <!-- Difficulty -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">⚡</span>
                            Difficulty Level
                        </label>
                        <select name="difficulty" id="difficultySelect">
                            <option value="Easy" <?= $trail['difficulty']=='Easy'?'selected':'' ?>>🟢 Easy - Perfect for beginners</option>
                            <option value="Moderate" <?= $trail['difficulty']=='Moderate'?'selected':'' ?>>🟡 Moderate - Some experience needed</option>
                            <option value="Hard" <?= $trail['difficulty']=='Hard'?'selected':'' ?>>🔴 Hard - Challenging terrain</option>
                        </select>
                        <div class="difficulty-preview">
                            <div class="difficulty-badge easy <?= $trail['difficulty']=='Easy'?'active':'' ?>">🟢 Easy</div>
                            <div class="difficulty-badge moderate <?= $trail['difficulty']=='Moderate'?'active':'' ?>">🟡 Moderate</div>
                            <div class="difficulty-badge hard <?= $trail['difficulty']=='Hard'?'active':'' ?>">🔴 Hard</div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group">
                        <label>
                            <span class="label-icon">📷</span>
                            Trail Image
                        </label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="image" id="imageInput" class="file-upload-input" accept="image/*">
                            <label for="imageInput" class="file-upload-label">
                                <span class="file-upload-icon">📁</span>
                                <div class="file-info">
                                    <div class="file-name" id="fileName">Choose a new image (optional)</div>
                                    <div class="file-hint">PNG, JPG, JPEG up to 5MB</div>
                                </div>
                            </label>
                        </div>
                        
                        <?php if($trail['image']): ?>
                        <div class="current-image">
                            <span class="current-image-label">📸 Current Image:</span>
                            <img src="../public/assets/uploads/<?= htmlspecialchars($trail['image']) ?>" alt="Current trail image" class="image-preview" onerror="this.parentElement.style.display='none'">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <span>💾 Update Trail</span>
                    </button>
                    <a href="manage_trails.php" class="btn btn-secondary">
                        <span>← Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let map;
        let marker;
        let autocomplete;

        
        function initMap() {
            
            const defaultCenter = { lat: 6.9214, lng: 122.0790 };
            
            
            const savedLat = parseFloat(document.getElementById('latitude').value);
            const savedLng = parseFloat(document.getElementById('longitude').value);
            const initialCenter = (savedLat && savedLng) ? { lat: savedLat, lng: savedLng } : defaultCenter;

            
            map = new google.maps.Map(document.getElementById('map'), {
                center: initialCenter,
                zoom: savedLat && savedLng ? 15 : 12,
                mapTypeId: 'terrain',
                styles: [
                    {
                        featureType: 'poi.park',
                        elementType: 'geometry.fill',
                        stylers: [{ color: '#a7f3d0' }]
                    },
                    {
                        featureType: 'landscape.natural',
                        elementType: 'geometry.fill',
                        stylers: [{ color: '#d1fae5' }]
                    }
                ]
            });

            
            if (savedLat && savedLng) {
                marker = new google.maps.Marker({
                    position: initialCenter,
                    map: map,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                    title: 'Trail Location'
                });

                updateCoordinatesDisplay(savedLat, savedLng);

                
                marker.addListener('dragend', function(event) {
                    const lat = event.latLng.lat();
                    const lng = event.latLng.lng();
                    updateCoordinates(lat, lng);
                    reverseGeocode(lat, lng);
                });
            }

            
            map.addListener('click', function(event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                
                if (marker) {
                    marker.setPosition(event.latLng);
                } else {
                    marker = new google.maps.Marker({
                        position: event.latLng,
                        map: map,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        title: 'Trail Location'
                    });

                    marker.addListener('dragend', function(event) {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();
                        updateCoordinates(lat, lng);
                        reverseGeocode(lat, lng);
                    });
                }
                
                updateCoordinates(lat, lng);
                reverseGeocode(lat, lng);
            });

            
            const locationInput = document.getElementById('locationInput');
            autocomplete = new google.maps.places.Autocomplete(locationInput, {
                types: ['geocode'],
                componentRestrictions: { country: 'ph' }
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                
                if (!place.geometry) {
                    return;
                }

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                map.setCenter(place.geometry.location);
                map.setZoom(15);

                if (marker) {
                    marker.setPosition(place.geometry.location);
                } else {
                    marker = new google.maps.Marker({
                        position: place.geometry.location,
                        map: map,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        title: 'Trail Location'
                    });

                    marker.addListener('dragend', function(event) {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();
                        updateCoordinates(lat, lng);
                        reverseGeocode(lat, lng);
                    });
                }

                updateCoordinates(lat, lng);
            });
        }

        function updateCoordinates(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            updateCoordinatesDisplay(lat, lng);
        }

        function updateCoordinatesDisplay(lat, lng) {
            document.getElementById('coordinates-display').innerHTML = 
                `<strong>📍 Coordinates:</strong> Latitude: ${lat.toFixed(6)}, Longitude: ${lng.toFixed(6)}`;
        }

        function reverseGeocode(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat: lat, lng: lng };

            geocoder.geocode({ location: latlng }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('locationInput').value = results[0].formatted_address;
                }
            });
        }

        
        const imageInput = document.getElementById('imageInput');
        const fileName = document.getElementById('fileName');
        
        imageInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileName.textContent = e.target.files[0].name;
            } else {
                fileName.textContent = 'Choose a new image (optional)';
            }
        });

        
        const difficultySelect = document.getElementById('difficultySelect');
        const difficultyBadges = document.querySelectorAll('.difficulty-badge');
        
        difficultySelect.addEventListener('change', (e) => {
            difficultyBadges.forEach(badge => {
                badge.classList.remove('active');
                if (badge.classList.contains(e.target.value.toLowerCase())) {
                    badge.classList.add('active');
                }
            });
        });

        
        const form = document.getElementById('editTrailForm');
        form.addEventListener('submit', (e) => {
            const name = form.querySelector('[name="name"]').value.trim();
            const description = form.querySelector('[name="description"]').value.trim();
            
            if (name.length < 3) {
                e.preventDefault();
                alert('Trail name must be at least 3 characters long');
                return;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                alert('Please provide a more detailed description (at least 20 characters)');
                return;
            }

            
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            if (!lat || !lng) {
                const confirmSubmit = confirm('⚠️ No location coordinates set. Do you want to continue without setting a map location?');
                if (!confirmSubmit) {
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>