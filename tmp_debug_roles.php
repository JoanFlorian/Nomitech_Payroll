<?php
$content = file_get_contents('app/Http/Controllers/Admin/RoleManagementController.php');
$output = "Searching 'Auxiliares' in RoleManagementController:\n";
preg_match_all('/.*Auxiliares.*/i', $content, $matches);
foreach($matches[0] as $m) $output .= "- " . trim($m) . "\n";
file_put_contents('tmp_debug_output.txt', $output);
echo "Debug finished.";
