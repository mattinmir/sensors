<?php
var_dump($_SERVER);

foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
