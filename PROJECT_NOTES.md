# Zamboanga Hiking System – Project Notes

Handover notes so work can continue in a new chat/session. Code lives in `Zamboanga-Hiking-System-main/`.

## Stack and local setup
- PHP 8.2 + MySQL (XAMPP), PDO, no framework. Composer: PHPMailer.
- Repo folder name differs from the URL the code expects. `includes/config.php` hardcodes `SITE_URL`/`BASE_URL` as `http://localhost/Zamboanga-Hiking-System-main/...`.
  A directory junction `C:\xamppp\htdocs\Zamboanga-Hiking-System-main` -> the project folder makes that URL work (junction is local, not in git).
- Database: `zamboanga_hiking`, user `root`, no password (`includes/db.php`).
- Import `database/updated_data.sql`, then apply the migrations below in order.
- Site: `http://localhost/Zamboanga-Hiking-System-main/public/index.php`
- Admin: `.../admin/login.php` (admin / admin123 stored plain text in `admins`; admin login compares plain text).
- User login: `.../public/login_user.php` (`lynn23` / `lynn12345`, bcrypt). Users `admin` row in `users` is not usable.

## Database migrations (run after updated_data.sql)
1. `database/add_trail_coordinates.sql` – `trails.latitude`, `trails.longitude`
2. `database/add_trail_content_fields.sql` – `trails.highlights`, `tips`, `features`
3. `database/add_trail_stats.sql` – `trails.distance_km`, `duration_min`, `elevation_m`
4. `database/add_password_resets.sql` – user/admin password reset codes
5. `database/update_trail_features_to_text.sql` – allows arbitrary admin-added trail features
6. `database/add_trail_views.sql` – analytics and trail-view tracking

Reviews live in `reviews` (`status`: pending/approved/rejected). `trail_reviews` is unused.

## What was done in this session
- **Admin UI broken**: the handover commit truncated every line containing `https://` (unclosed `<link href="https:` swallowed `<style>`). Restored 41 lines across admin/public pages from the original commit.
- **Maps**: now **Google Maps** again (OpenStreetMap/Leaflet was used temporarily, removed).
  - Key: `GOOGLE_MAPS_API_KEY` in `includes/config.php`. HTTP-referrer restricted to `http://localhost/*`, `https://test.zambohike.com/*`, `https://zambohike.com/*`.
  - Admin add/edit trail: `public/assets/js/google-picker.js` (Places autocomplete limited to Zamboanga City bounds, click/drag pin, reverse geocode). Starts near the admin's location if inside the city.
  - Trail page "Get Directions": modal map, up to 3 alternative walking routes (selectable, gray vs blue), live GPS tracking that moves the marker, follows the user and re-routes after ~40 m (max once per 10 s). Dragging the map pauses follow; "Center on Me" resumes.
- **Auto trail stats**: when admin pins a location, distance + walking time from city center (Directions) and elevation (Open-Meteo) are computed and saved. Shown on trail page and homepage cards; hidden if missing. Removed hardcoded 5.2 km / 2-3 hrs / 450 m, "Loop", "Hiking Trail".
- **Admin-editable content**: Description (Trail Overview), Trail Features (checkboxes), Highlights, Essential Tips (one per line). Sections hide when empty.
- **Reviews**:
  - Trail Rating = average of **approved** reviews; "No ratings yet" if none. Rating card and review list auto-refresh every 15 s and after submit.
  - A user sees their own pending review (with "Pending approval" label); other users/guests only see approved. Rejected are hidden.
  - Stars: outline by default, fill up to clicked value, hover preview, selected value is what is submitted; reset after submit. Comment text is HTML-escaped.
- **Weather** on trail page is live (Open-Meteo) for the trail coordinates.
- **Trail page layout**: reviews + review form moved under Photo Gallery (left). Sidebar order: Stats, Rating, Essential Tips, Weather, Get Directions / Back to Trails.
- **Homepage cards** (`public/index.php`): whole card clickable (also keyboard), uses the uploaded image (`assets/uploads/<image>`), real stats/rating/features. Removed fake "Popular", fake features and 4.8 (234).

## Known issues / TODO
- Homepage hero counters "500+ Km Explored" and "1K+ Happy Hikers" are still static.
- Trail "Abong-Abong Park Trail" references `abong.jpg`, which is not in `public/assets/uploads/`; it falls back to a stock image. Re-upload via admin edit.
- Trail #3 ("anaconda trail") has a very short description; the admin edit form requires >= 20 chars.
- Google Maps JS still uses deprecated-but-working APIs (`Autocomplete`, `DirectionsService`, `Marker`); Google gives 12 months notice before removal.
- Admin password is plain text and `admin` session times out quickly (re-login required during testing).
- Geolocation (Get Directions / live tracking) needs HTTPS outside localhost.
- Search on the admin map is biased to Zamboanga City only; if a place is missing, click the map to pin it.

## Deployment plan (Hostinger)
1. Deploy first to `https://test.zambohike.com`, then `https://zambohike.com`.
2. Before deploying change: `SITE_URL`/`BASE_URL` in `includes/config.php`; DB credentials in `includes/db.php` (create a separate DB/user for test and live); set `display_errors` off; SMTP in `includes/emailconfig.php` for email verification/reset.
3. Import `updated_data.sql` + the 3 migrations on the new database.
4. Idea: move URLs/DB/API key into a git-ignored config file so switching test -> live is one file.
5. The Google key is committed in `includes/config.php` (referrer-restricted). Do not make the repo public with it; consider rotating/moving it out of git.
