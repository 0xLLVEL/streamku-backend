<?php
$url = 'https://vidking.net/embed/tv/99966/1/1';
$headers = get_headers($url, 1);
print_r($headers);
