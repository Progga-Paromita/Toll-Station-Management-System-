<?php

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $username = 'admin';
    $result = '';
    
    // We get the PDO connection from Laravel
    $pdo = DB::connection()->getPdo();
    $stmt = $pdo->prepare("DECLARE p_result VARCHAR2(100); BEGIN validate_login(:username, p_result); :result := p_result; END;");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':result', $result, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
    $stmt->execute();
    
    echo "Procedure Call Success!\n";
    echo "Username: $username, Result: $result\n";
    
    // Test with invalid username
    $username2 = 'nonexistent';
    $result2 = '';
    $stmt->bindParam(':username', $username2, PDO::PARAM_STR);
    $stmt->bindParam(':result', $result2, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
    $stmt->execute();
    echo "Username: $username2, Result: $result2\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
