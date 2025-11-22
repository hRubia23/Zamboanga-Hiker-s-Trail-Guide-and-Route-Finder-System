<?php
function sanitize($data){ return htmlspecialchars(strip_tags($data)); }
function redirect($url){ header("Location: $url"); exit(); }
?>