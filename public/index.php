<?php

declare(strict_types=1);

use app\App;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

require "../vendor/autoload.php";

(new App())->run(ServerRequestFactory::fromGlobals());
