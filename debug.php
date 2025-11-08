<?php
exec("python -c \"print('Hello from PHP')\" 2>&1", $output, $return_var);
echo "<pre>";
print_r($output);
echo "Return code: $return_var";
echo "</pre>";