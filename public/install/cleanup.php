<?php

/**
 * Cleanup installer files after successful installation
 */
function deleteDirectory($dir)
{
    if (! is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = $dir.DIRECTORY_SEPARATOR.$file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

// Check if installation is completed
if (! file_exists(__DIR__.'/../../storage/installer.lock')) {
    http_response_code(403);
    exit('Installation not completed');
}

// Delete installer directory
$installerDir = __DIR__;
if (deleteDirectory($installerDir)) {
    echo json_encode(['success' => true, 'message' => 'Installer cleaned up successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cleanup installer']);
}
