/**
 * Trail location picker built on Google Maps + Places Autocomplete.
 * Call initTrailPicker(...) from the Maps API "callback" function.
 * Everything is limited to Zamboanga City.
 */
const ZC_BOUNDS = { south: 6.80, west: 121.75, north: 7.45, east: 122.35 };
const ZC_CENTER = { lat: 6.9214, lng: 122.0790 };
// Reference point for trail stats: Zamboanga City Hall (city center)
const ZC_REFERENCE = { lat: 6.9068, lng: 122.0760 };

function insideZamboangaCity(lat, lng) {
    return lat >= ZC_BOUNDS.south && lat <= ZC_BOUNDS.north && lng >= ZC_BOUNDS.west && lng <= ZC_BOUNDS.east;
}

function initTrailPicker(opts) {
    const latEl = document.getElementById(opts.latId);
    const lngEl = document.getElementById(opts.lngId);
    const locationEl = document.getElementById(opts.locationId);
    const displayEl = document.getElementById(opts.displayId);

    const savedLat = parseFloat(latEl.value);
    const savedLng = parseFloat(lngEl.value);
    const hasSaved = !isNaN(savedLat) && !isNaN(savedLng);

    const map = new google.maps.Map(document.getElementById(opts.mapId), {
        center: hasSaved ? { lat: savedLat, lng: savedLng } : ZC_CENTER,
        zoom: hasSaved ? 15 : 12,
        minZoom: 10,
        mapTypeId: 'terrain',
        restriction: { latLngBounds: ZC_BOUNDS, strictBounds: false },
        streetViewControl: false
    });

    const geocoder = new google.maps.Geocoder();
    let marker = null;
    let lastValid = hasSaved ? { lat: savedLat, lng: savedLng } : ZC_CENTER;

    function updateCoordinates(lat, lng) {
        latEl.value = lat.toFixed(6);
        lngEl.value = lng.toFixed(6);
        displayEl.innerHTML = '<strong>📍 Coordinates:</strong> Latitude: ' + lat.toFixed(6) + ', Longitude: ' + lng.toFixed(6);
        computeStats(lat, lng);
    }

    // Stats are computed automatically from the pinned location:
    // walking distance/time from the city center (Google Directions) and elevation (Open-Meteo)
    const distEl = document.getElementById(opts.distanceId);
    const durEl = document.getElementById(opts.durationId);
    const elevEl = document.getElementById(opts.elevationId);
    const statsEl = document.getElementById(opts.statsDisplayId);
    const directionsService = new google.maps.DirectionsService();
    let statsToken = 0;

    function formatDuration(min) {
        if (min < 60) return min + ' min';
        const h = Math.floor(min / 60), m = min % 60;
        return h + ' hr' + (m ? ' ' + m + ' min' : '');
    }

    function showStats() {
        if (!statsEl) return;
        const parts = [];
        if (distEl.value) parts.push('📏 ' + parseFloat(distEl.value).toFixed(1) + ' km');
        if (durEl.value) parts.push('⏱️ ' + formatDuration(parseInt(durEl.value, 10)) + ' walk');
        if (elevEl.value) parts.push('⛰️ ' + elevEl.value + ' m elevation');
        statsEl.innerHTML = parts.length
            ? '<strong>📊 Auto stats (from city center):</strong> ' + parts.join(' • ')
            : '<strong>📊 Auto stats:</strong> not available for this spot';
    }

    function computeStats(lat, lng) {
        if (!distEl) return;
        const token = ++statsToken;
        distEl.value = durEl.value = elevEl.value = '';
        if (statsEl) statsEl.innerHTML = '<strong>📊 Auto stats:</strong> calculating...';

        directionsService.route({
            origin: ZC_REFERENCE,
            destination: { lat: lat, lng: lng },
            travelMode: google.maps.TravelMode.WALKING
        }, function (result, status) {
            if (token !== statsToken) return;
            if (status === 'OK') {
                const leg = result.routes[0].legs[0];
                distEl.value = (leg.distance.value / 1000).toFixed(2);
                durEl.value = Math.round(leg.duration.value / 60);
            }
            showStats();
        });

        fetch('https://api.open-meteo.com/v1/elevation?latitude=' + lat + '&longitude=' + lng)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (token !== statsToken) return;
                if (data.elevation && data.elevation.length) elevEl.value = Math.round(data.elevation[0]);
                showStats();
            })
            .catch(function () {});
    }

    function reverseGeocode(lat, lng) {
        geocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
            if (status === 'OK' && results[0]) locationEl.value = results[0].formatted_address;
        });
    }

    function warnOutside() {
        displayEl.innerHTML = '<strong>⚠️ Trails must be inside Zamboanga City.</strong> Please choose a spot within the city.';
    }

    function placeMarker(lat, lng) {
        const pos = { lat: lat, lng: lng };
        if (marker) {
            marker.setPosition(pos);
        } else {
            marker = new google.maps.Marker({
                position: pos,
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Trail Location'
            });
            marker.addListener('dragend', function (e) {
                const la = e.latLng.lat(), ln = e.latLng.lng();
                if (!insideZamboangaCity(la, ln)) {
                    marker.setPosition(lastValid);
                    warnOutside();
                    return;
                }
                lastValid = { lat: la, lng: ln };
                updateCoordinates(la, ln);
                reverseGeocode(la, ln);
            });
        }
        lastValid = pos;
    }

    if (hasSaved) {
        placeMarker(savedLat, savedLng);
        updateCoordinates(savedLat, savedLng);
    } else if (navigator.geolocation) {
        // Start near the admin's own location when they are in Zamboanga City
        navigator.geolocation.getCurrentPosition(function (p) {
            if (!marker && insideZamboangaCity(p.coords.latitude, p.coords.longitude)) {
                map.setCenter({ lat: p.coords.latitude, lng: p.coords.longitude });
                map.setZoom(14);
            }
        }, function () {});
    }

    map.addListener('click', function (e) {
        const lat = e.latLng.lat(), lng = e.latLng.lng();
        if (!insideZamboangaCity(lat, lng)) {
            warnOutside();
            return;
        }
        placeMarker(lat, lng);
        updateCoordinates(lat, lng);
        reverseGeocode(lat, lng);
    });

    // Suggestions appear while typing and are limited to Zamboanga City
    const autocomplete = new google.maps.places.Autocomplete(locationEl, {
        bounds: ZC_BOUNDS,
        strictBounds: true,
        componentRestrictions: { country: 'ph' },
        fields: ['geometry', 'formatted_address', 'name']
    });

    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;
        const lat = place.geometry.location.lat(), lng = place.geometry.location.lng();
        if (!insideZamboangaCity(lat, lng)) {
            warnOutside();
            return;
        }
        map.setCenter(place.geometry.location);
        map.setZoom(16);
        placeMarker(lat, lng);
        updateCoordinates(lat, lng);
        locationEl.value = place.formatted_address || place.name;
    });

    // Enter picks a suggestion; it must not submit the form
    locationEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') e.preventDefault();
    });
}
