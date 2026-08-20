<?php
function checkUrl($url) {
    echo "Checking $url...\n";
    $headers = @get_headers($url, 1);
    if ($headers) {
        echo $headers[0] . "\n";
        if (isset($headers['Location'])) {
            echo "Redirects to: " . (is_array($headers['Location']) ? implode(", ", $headers['Location']) : $headers['Location']) . "\n";
        }
    } else {
        echo "Failed to get headers.\n";
    }
    echo "--------------------------\n";
}

checkUrl('https://vidking.net/embed/tv?tmdb=99966&season=1&episode=1');
checkUrl('https://vidking.net/embed/tv?id=99966&s=1&e=1');
