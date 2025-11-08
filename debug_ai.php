<?php
$python = 'C:\\Users\\Macky\\AppData\\Local\\Programs\\Python\\Python312\\python.exe';
$script = __DIR__ . '\\functions\\hayah_ai\\hayahai.py';
exec("\"$python\" \"$script\" 2>&1", $output, $return_var);

echo "<pre>";
print_r($output);
echo "Return code: $return_var";
echo "</pre>";
