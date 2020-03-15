<?php
declare(strict_types=1);

use App\App;
use Dotenv\Dotenv;

(Dotenv::create(APP_DIR))->load();
$config = require __DIR__ . '/../config/main.php';

return new App($config);
