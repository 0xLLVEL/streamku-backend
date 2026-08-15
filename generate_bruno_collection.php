<?php

// A simple helper function to convert dashes/snake case to CamelCase.
function toCamelCase($string) {
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
}

// Run route:list --json directly to avoid PowerShell encoding issues
$jsonOutput = shell_exec('php artisan route:list --json');
$routes = json_decode($jsonOutput, true);

if (!is_array($routes)) {
    die("Failed to parse routes. Raw output: \n" . substr($jsonOutput, 0, 500));
}

$baseDir = __DIR__ . '/bruno/Streamku API';

if (!is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

// Write bruno.json
file_put_contents("$baseDir/bruno.json", json_encode([
    "version" => "1",
    "name" => "Streamku API",
    "type" => "collection",
    "ignore" => ["node_modules", ".git"]
], JSON_PRETTY_PRINT));

// Write environments
$envDir = "$baseDir/environments";
if (!is_dir($envDir)) {
    mkdir($envDir, 0777, true);
}
$envContent = <<<EOT
vars {
  base_url: http://127.0.0.1:8000
  auth_token: 
}
EOT;
file_put_contents("$envDir/Local.bru", $envContent);

function determineFolder($uri) {
    $parts = explode('/', $uri);
    // Remove api/v1
    if ($parts[0] === 'api' && isset($parts[1]) && $parts[1] === 'v1') {
        array_shift($parts);
        array_shift($parts);
    }
    
    if (empty($parts)) return 'Misc';
    
    if ($parts[0] === 'admin') {
        $sub = isset($parts[1]) ? toCamelCase($parts[1]) : 'Misc';
        return "Admin/$sub";
    }
    
    return toCamelCase($parts[0]);
}

function determineName($route) {
    if (!empty($route['name'])) {
        return ucwords(str_replace(['.', '-'], ' ', $route['name']));
    }
    $method = explode('|', $route['method'])[0];
    return $method . ' ' . $route['uri'];
}

foreach ($routes as $route) {
    $uri = $route['uri'];
    
    // Only API routes
    if (!str_starts_with($uri, 'api/')) {
        continue;
    }
    
    $method = explode('|', $route['method'])[0];
    if ($method === 'HEAD' || $method === 'OPTIONS') continue;
    $methodLower = strtolower($method);
    
    $folder = determineFolder($uri);
    $folderPath = "$baseDir/$folder";
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    
    $name = determineName($route);
    $safeName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $name);
    $filePath = "$folderPath/$safeName.bru";
    
    // Check middleware string manually for sanctum
    $middlewareStr = json_encode($route['middleware'] ?? []);
    $requiresAuth = str_contains($middlewareStr, 'sanctum');
                 
    $authBlock = $requiresAuth ? "auth: bearer" : "auth: none";
    $tokenBlock = $requiresAuth ? "\nauth:bearer {\n  token: {{auth_token}}\n}\n" : "";

    $bodyBlock = "body: none";
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $bodyBlock = "body: json";
    }

    $bruContent = <<<EOT
meta {
  name: $name
  type: http
  seq: 1
}

$methodLower {
  url: {{base_url}}/$uri
  $bodyBlock
  $authBlock
}
$tokenBlock
EOT;

    file_put_contents($filePath, $bruContent);
}

echo "Bruno collection generated at $baseDir\n";
