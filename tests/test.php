<?php

use App\Domain\Processor\Ticket;
use WecarSwoole\Util\File;

include './base.php';

for ($i = 0; $i < 30; $i++) {
    Ticket::get("test");
    echo "ticket get {$i}\n";
}
echo "over\n";