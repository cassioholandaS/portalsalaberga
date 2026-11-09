<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Não disponível.');
}

fwrite(
    STDERR,
    "Migrador legado desativado. Aplique os scripts numerados em config/migrations após validar um backup.\n"
);
exit(1);
