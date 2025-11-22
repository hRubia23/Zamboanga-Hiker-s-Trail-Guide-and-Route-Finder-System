<?php
require_once "../includes/auth.php";
check_login();
require_once "../includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $difficulty = $_POST['difficulty'];
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $image = null;
    
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../public/assets/uploads/";
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $image);
    }
    
    $stmt = $pdo->prepare("INSERT INTO trails (name, description, location, difficulty, latitude, longitude, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $location, $difficulty, $latitude, $longitude, $image]);
    
    header("Location: manage_trails.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trail Admin - Add New Trail</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
            padding: 0;
        }

        .header {
            background: linear-gradient(135deg, #1e3a2c, #2d5a3f);
            color: white;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4CAF50, #8BC34A);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .logo-icon svg {
            width: 28px;
            height: 28px;
            fill: white;
        }

        .logo-text h1 {
            font-family: 'Merriweather', serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .logo-text p {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 400;
        }

        .header-nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #5a7a5f;
        }

        .breadcrumb a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #2e7d32;
        }

        .breadcrumb svg {
            width: 16px;
            height: 16px;
            fill: #7a9d7e;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #1e3a2c, #2d5a3f);
            padding: 35px 40px;
            color: white;
        }

        .form-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .form-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-icon svg {
            width: 38px;
            height: 38px;
            fill: white;
        }

        .form-header h2 {
            font-family: 'Merriweather', serif;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .form-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2d5a3f;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group label .required {
            color: #e53935;
            margin-left: 4px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            background: white;
            color: #1e3a2c;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='%234CAF50'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 45px;
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 50px 20px;
            border: 2px dashed #c8e6c9;
            border-radius: 12px;
            background: #f1f8f4;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #5a7a5f;
            font-weight: 500;
        }

        .file-upload-label:hover {
            border-color: #4CAF50;
            background: #e8f5e9;
        }

        .file-upload-label svg {
            width: 32px;
            height: 32px;
            fill: #4CAF50;
        }

        .file-name {
            margin-top: 12px;
            padding: 12px;
            background: #e8f5e9;
            border-radius: 8px;
            color: #2e7d32;
            font-size: 14px;
            display: none;
        }

        .file-name.active {
            display: block;
        }

        .difficulty-preview {
            margin-top: 12px;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: none;
        }

        .difficulty-preview.active {
            display: block;
        }

        .difficulty-preview.easy {
            background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
            color: #2e7d32;
        }

        .difficulty-preview.moderate {
            background: linear-gradient(135deg, #fff9c4, #fff59d);
            color: #f57f17;
        }

        .difficulty-preview.hard {
            background: linear-gradient(135deg, #ffccbc, #ffab91);
            color: #d84315;
        }

        .difficulty-preview.extreme {
            background: linear-gradient(135deg, #f8bbd0, #f48fb1);
            color: #c2185b;
        }

        #map {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .coordinates-display {
            margin-top: 10px;
            padding: 12px 16px;
            background: #e8f5e9;
            border-radius: 8px;
            color: #2e7d32;
            font-size: 13px;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }

        .btn {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn svg {
            width: 20px;
            height: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #5a7a5f;
            border: 2px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .helper-text {
            margin-top: 8px;
            font-size: 13px;
            color: #7a9d7e;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            .form-content {
                padding: 30px 25px;
            }

            .form-header {
                padding: 25px 25px;
            }

            .form-actions {
                flex-direction: column;
            }

            .breadcrumb {
                font-size: 12px;
            }

            #map {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <h1>Trail Admin</h1>
                    <p>Management Portal</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="dashboard.php" class="nav-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="manage_trails.php" class="nav-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                    </svg>
                    Manage Trails
                </a>
            </nav>
        </div>
    </header>


    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
            </svg>
            <a href="manage_trails.php">Manage Trails</a>
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
            </svg>
            <span>Add New Trail</span>
        </div>

        <div class="form-container">
            <div class="form-header">
                <div class="form-header-content">
                    <div class="form-icon">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2>Add New Trail</h2>
                        <p>Create a new hiking trail entry in the system</p>
                    </div>
                </div>
            </div>

            <div class="form-content">
                <form method="POST" enctype="multipart/form-data" id="trailForm">
                    <div class="form-group">
                        <label for="name">
                            Trail Name
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="name" name="name" placeholder="e.g., Mt. Pulag Summit Trail" required>
                        <div class="helper-text">Enter the official name of the hiking trail</div>
                    </div>


                    <div class="form-group">
                        <label for="description">
                            Description
                            <span class="required">*</span>
                        </label>
                        <textarea id="description" name="description" placeholder="Provide a detailed description of the trail, including key features, scenic views, and notable landmarks..." required></textarea>
                        <div class="helper-text">Describe the trail experience, terrain, and what hikers can expect</div>
                    </div>


                    <div class="form-group">
                        <label for="location">
                            Location
                        </label>
                        <input type="text" id="location" name="location" placeholder="Search for a location or click on the map...">
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <div class="helper-text">Search and click on the map to set the trail location</div>
                    </div>


                    <div class="form-group">
                        <label>
                            📍 Trail Location on Map
                        </label>
                        <div id="map"></div>
                        <div class="coordinates-display" id="coordinates-display">
                            Click on the map to set the exact trail location
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="difficulty">
                            Difficulty Level
                            <span class="required">*</span>
                        </label>
                        <select id="difficulty" name="difficulty" required>
                            <option value="">Select difficulty level</option>
                            <option value="Easy">Easy - Suitable for beginners</option>
                            <option value="Moderate">Moderate - Some experience required</option>
                            <option value="Hard">Hard - Experienced hikers only</option>
                            <option value="Extreme">Extreme - Expert level</option>
                        </select>
                        <div class="difficulty-preview" id="difficultyPreview"></div>
                    </div>


                    <div class="form-group">
                        <label for="image">
                            Trail Image
                        </label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="image" name="image" accept="image/*" class="file-upload-input">
                            <label for="image" class="file-upload-label">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 7v2.99s-1.99.01-2 0V7h-3s.01-1.99 0-2h3V2h2v3h3v2h-3zm-3 4V8h-3V5H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-8h-3zM5 19l3-4 2 3 3-4 4 5H5z"/>
                                </svg>
                                <span>Click to upload trail image or drag and drop</span>
                            </label>
                        </div>
                        <div class="file-name" id="fileName"></div>
                        <div class="helper-text">Upload a representative image of the trail (JPG, PNG, max 5MB)</div>
                    </div>


                    <div class="form-actions">
                        <a href="manage_trails.php" class="btn btn-secondary">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            Add Trail
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA0_SCP530X1_feONlUZ1xHIiIk_lxodE8&libraries=places&callback=initMap"async defer></script>
    <script>
        let map;
        let marker;
        let autocomplete;


        function initMap() {
            const defaultCenter = { lat: 6.9214, lng: 122.0790 };

            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultCenter,
                zoom: 12,
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

            const locationInput = document.getElementById('location');
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
            document.getElementById('coordinates-display').innerHTML = 
                '<strong>📍 Coordinates:</strong> Latitude: ' + lat.toFixed(6) + ', Longitude: ' + lng.toFixed(6);
        }

        function reverseGeocode(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat: lat, lng: lng };

            geocoder.geocode({ location: latlng }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('location').value = results[0].formatted_address;
                }
            });
        }

        const fileInput = document.getElementById('image');
        const fileName = document.getElementById('fileName');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileName.textContent = '📁 Selected: ' + this.files[0].name;
                fileName.classList.add('active');
            } else {
                fileName.classList.remove('active');
            }
        });

        const difficultySelect = document.getElementById('difficulty');
        const difficultyPreview = document.getElementById('difficultyPreview');

        difficultySelect.addEventListener('change', function() {
            const value = this.value.toLowerCase();
            difficultyPreview.className = 'difficulty-preview';
            
            if (value) {
                difficultyPreview.classList.add('active', value);
                difficultyPreview.textContent = '🏔️ ' + this.options[this.selectedIndex].text;
            } else {
                difficultyPreview.classList.remove('active');
            }
        });
    </script>
</body>
</html>