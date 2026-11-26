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
?>