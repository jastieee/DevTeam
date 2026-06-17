<?php

echo "Generated Token:<br><br>";

$token = bin2hex(random_bytes(32));

echo $token;