<?php
declare(strict_types=1);
final class Database {
    public static function connect(bool $serverOnly = false): PDO {
        $file = __DIR__ . '/../config/database.php';
        if (!is_file($file)) throw new RuntimeException('Copie config/database.example.php para config/database.php e preencha o acesso ao MySQL.');
        $c = require $file;
        if (($c['database'] ?? '') !== 'controle_emprestimos_php') throw new RuntimeException('Use o banco exclusivo controle_emprestimos_php na configuração.');
        $dsn = 'mysql:host='.$c['host'].';port='.(int)$c['port'].';charset=utf8mb4'.($serverOnly?'':';dbname='.$c['database']);
        return new PDO($dsn,$c['username'],$c['password'],[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
    }
}
