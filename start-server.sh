#!/bin/bash

# Start PHP server in the background
php -S localhost:2222 &

# Start browser-sync and watch for changes in PHP files
browser-sync start --proxy "localhost:2222" --files "*.php"

# Wait for the PHP server process to finish
wait
