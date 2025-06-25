<?php
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

function sanitizeForSQL($conn, $data) {
    if (is_array($data)) {
        return array_map(function($item) use ($conn) {
            return $conn->quote($item);
        }, $data);
    }
    
    return $conn->quote($data);
}
?>