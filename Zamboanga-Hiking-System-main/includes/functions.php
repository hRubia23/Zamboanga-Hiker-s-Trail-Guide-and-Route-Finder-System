<?php
function sanitize($data){ return htmlspecialchars(strip_tags($data)); }
function redirect($url){ header("Location: $url"); exit(); }

// Auto-computed trail stats (saved from the admin map picker) formatted for display
function format_trail_distance($km){
    return ($km === null || $km === '') ? null : rtrim(rtrim(number_format((float)$km, 1), '0'), '.') . ' km';
}
function format_trail_duration($min){
    if ($min === null || $min === '') return null;
    $min = (int)$min;
    if ($min < 60) return $min . ' min';
    $h = intdiv($min, 60);
    $m = $min % 60;
    return $h . ' hr' . ($m ? ' ' . $m . ' min' : '');
}
function format_trail_elevation($m){
    return ($m === null || $m === '') ? null : (int)$m . ' m';
}
function trail_feature_map(){
    return [
        'forest' => ['🌲', 'Forest Path'],
        'water' => ['🌊', 'Water Features'],
        'wildlife' => ['🦅', 'Wildlife Viewing'],
        'photo' => ['📸', 'Photo Opportunities'],
    ];
}

function parse_trail_features($features){
    if (!$features) return [];

    $decodedFeatures = json_decode($features, true);
    $featureValues = is_array($decodedFeatures) ? $decodedFeatures : explode(',', $features);

    return array_values(array_filter(array_map('trim', $featureValues), 'strlen'));
}

function save_trail_features($selectedFeatures, $customFeatures){
    $featureMap = trail_feature_map();
    $selectedFeatures = array_filter((array)$selectedFeatures, 'is_string');
    $features = array_values(array_intersect($selectedFeatures, array_keys($featureMap)));
    $customFeatureLines = preg_split('/\R/', (string)$customFeatures);

    foreach ($customFeatureLines as $feature) {
        $feature = trim($feature);
        if ($feature === '' || isset($featureMap[$feature])) continue;

        $isDuplicate = false;
        foreach ($features as $existingFeature) {
            if (strcasecmp($existingFeature, $feature) === 0) {
                $isDuplicate = true;
                break;
            }
        }

        if (!$isDuplicate) $features[] = $feature;
    }

    return $features ? json_encode($features, JSON_UNESCAPED_UNICODE) : null;
}
?>