<?php
function h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function old(string $key, string $default = ''): string
{
    return h($_POST[$key] ?? $_GET[$key] ?? $default);
}
function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}

function DispErrorMsg(): ?int
{
    echo $_SESSION['flash_message'];
    if (!empty($_SESSION['flash_message'])) {

        echo "<div class='error-message'>" . h($_SESSION['flash_message']  ) . "</div>";

       // $_SESSION['flash_message'] = null;
        return 1;
    }
    return null;
   
}
?>
