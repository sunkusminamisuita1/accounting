<?php

function h(?string $s): string
{
//	echo "h->{$string}";
//	exit;
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


function old(string $key, string $default = ''): string
{
    return h($_POST[$key] ?? $GET[$key] ?? $default);
}

function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}
?>
