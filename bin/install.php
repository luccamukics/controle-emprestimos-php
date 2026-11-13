<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../src/bootstrap.php';
try {
    $db=Database::connect(true);
    $sql=file_get_contents(__DIR__.'/../database/schema.sql');
    foreach (explode(';',$sql) as $statement) if(trim($statement)!=='') $db->exec($statement);
    echo "Banco controle_emprestimos_php e tabelas preparados com sucesso.\n";
} catch(Throwable $e) { fwrite(STDERR,"Instalação não concluída: ".$e->getMessage()."\n"); exit(1); }
