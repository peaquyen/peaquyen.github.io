<?php
header('Content-Type: application/json');

$root_dir = __DIR__;
$posts = [];


// Handling posts from the 'posts' directory
$posts_directories = glob($root_dir . '/posts/UpNote_*', GLOB_ONLYDIR);
foreach ($posts_directories as $folder) {
    $post_files = array_merge(
        glob($folder . '/*.html'),
        glob($folder . '/*.htm'),
        glob($folder . '/*.html5') // Assuming 'html5' is a valid extension you want to include
    );
    foreach ($post_files as $file) {
        $filename = basename($file);
        preg_match('/\[(.*?)\]/', $filename, $matches);
        $category = $matches[1] ?? 'Uncategorized';

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
foreach ($words_files as $file) {
    $filename = basename($file);
    preg_match('/\[(.*?)\]/', $filename, $matches);
    $category = $matches[1] ?? "ACT";  // Default to "ACT" if no category is specified in the filename

    $posts[] = [
        'path' => '/fishated' . str_replace($root_dir, '', $file),
        'category' => $category,
        'title' => preg_replace('/^\[.*?\]\s*/', '', str_replace(['.html', '.htm', '.html5'], '', $filename)), // Remove category tag and extension from title
        'date' => filemtime($file)
    ];
}

usort($posts, function ($a, $b) {
    return $b['date'] - $a['date'];
});

echo json_encode($posts);
?>
