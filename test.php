<?php
header('Content-Type: application/json');

// Set up logging
$log_file = __DIR__ . '/debug.log';
function log_message($message) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

$root_dir = __DIR__;
$posts = [];

// Log the root directory
log_message("Root directory: $root_dir");

// Đường dẫn đến file CSS cần thêm
$css_file = '../style2.css'; // Adjust this path as needed
log_message("CSS file to inject: $css_file");

// Hàm để chèn thẻ link CSS vào nội dung HTML nếu chưa có
function inject_css($file_content, $css_file) {
    // Kiểm tra xem file CSS đã được thêm chưa
    if (strpos($file_content, $css_file) === false) {
        log_message("Injecting CSS: $css_file");
        $css_tag = '<link rel="stylesheet" href="' . $css_file . '">';
        if (preg_match('/<head>/', $file_content)) {
            // Nếu thẻ <head> tồn tại, chèn thẻ CSS vào sau <head>
            return preg_replace('/<head>/', "<head>\n\t$css_tag", $file_content, 1);
        } else {
            // Nếu không có thẻ <head>, thêm thẻ CSS vào đầu file
            return $css_tag . "\n" . $file_content;
        }
    }
    log_message("CSS already injected, skipping...");
    return $file_content; // Trả về nội dung ban đầu nếu CSS đã tồn tại
}

// Handling posts from the 'posts' directory
$posts_directories = glob($root_dir . '/posts/UpNote_*', GLOB_ONLYDIR);
log_message("Found " . count($posts_directories) . " directories in /posts.");
foreach ($posts_directories as $folder) {
    log_message("Processing directory: $folder");
    $post_files = array_merge(
        glob($folder . '/*.html'),
        glob($folder . '/*.htm'),
        glob($folder . '/*.html5') // Assuming 'html5' is a valid extension you want to include
    );
    log_message("Found " . count($post_files) . " files in $folder.");

    foreach ($post_files as $file) {
        log_message("Processing file: $file");
        $filename = basename($file);
        preg_match('/\[(.*?)\]/', $filename, $matches);
        $category = $matches[1] ?? 'Uncategorized';
        log_message("Category: $category");

        // Đọc nội dung của file HTML
        $file_content = @file_get_contents($file);
        if ($file_content === false) {
            log_message("Failed to read file: $file");
            continue; // Bỏ qua nếu không thể đọc file
        }

        // Chèn CSS vào nội dung
        $file_content_with_css = inject_css($file_content, $css_file);

        // Ghi nội dung đã chèn CSS trở lại file
        if (file_put_contents($file, $file_content_with_css) === false) {
            log_message("Failed to write to file: $file");
            continue; // Bỏ qua nếu không thể ghi lại file
        }

        log_message("Successfully processed and updated file: $file");

        $posts[] = [
            'path' => '/fishated' . str_replace($root_dir, '', $file),
            'category' => $category,
            'title' => preg_replace('/^\[.*?\]\s*/', '', str_replace(['.html', '.htm', '.html5'], '', $filename)), // Remove category tag and extension from title
            'date' => filemtime($file)
        ];
    }
}

// Handling HTML files directly inside the 'Words' directory and assigning categories based on filename
$words_files = array_merge(
    glob($root_dir . '/posts/Words/*.html'),
    glob($root_dir . '/posts/Words/*.htm'),
    glob($root_dir . '/posts/Words/*.html5') // Assuming 'html5' is a valid extension you want to include
);
log_message("Found " . count($words_files) . " files in /posts/Words.");

foreach ($words_files as $file) {
    log_message("Processing file: $file");
    $filename = basename($file);
    preg_match('/\[(.*?)\]/', $filename, $matches);
    $category = $matches[1] ?? "ACT";  // Default to "ACT" if no category is specified in the filename
    log_message("Category: $category");

    // Đọc nội dung của file HTML
    $file_content = @file_get_contents($file);
    if ($file_content === false) {
        log_message("Failed to read file: $file");
        continue; // Bỏ qua nếu không thể đọc file
    }

    // Chèn CSS vào nội dung
    $file_content_with_css = inject_css($file_content, $css_file);

    // Ghi nội dung đã chèn CSS trở lại file
    if (file_put_contents($file, $file_content_with_css) === false) {
        log_message("Failed to write to file: $file");
        continue; // Bỏ qua nếu không thể ghi lại file
    }

    log_message("Successfully processed and updated file: $file");

    $posts[] = [
        'path' => '/fishated' . str_replace($root_dir, '', $file),
        'category' => $category,
        'title' => preg_replace('/^\[.*?\]\s*/', '', str_replace(['.html', '.htm', '.html5'], '', $filename)), // Remove category tag and extension from title
        'date' => filemtime($file)
    ];
}

log_message("Sorting posts by date.");
usort($posts, function ($a, $b) {
    return $b['date'] - $a['date'];
});

log_message("Returning JSON response with " . count($posts) . " posts.");
echo json_encode($posts);
?>
