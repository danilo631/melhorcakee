<?php
function uploadImage($file, $type = 'products') {
    $result = ['success' => false, 'file_name' => '', 'error' => ''];
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $result['error'] = 'Only JPG, PNG, GIF, and WEBP files are allowed.';
        return $result;
    }
    
    if ($file['size'] > $max_size) {
        $result['error'] = 'File size exceeds maximum limit of 5MB.';
        return $result;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = uniqid($type . '_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '/../../assets/images/uploads/' . $type . '/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $target_path = $upload_dir . $new_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $result['success'] = true;
        $result['file_name'] = $new_name;
    } else {
        $result['error'] = 'There was an error uploading your file.';
    }
    
    return $result;
}
?>