-- Banco exclusivo da aplicação PHP. Não usa nem altera controle_emprestimos.
CREATE DATABASE IF NOT EXISTS controle_emprestimos_php CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE controle_emprestimos_php;
CREATE TABLE IF NOT EXISTS colaboradores (
 login VARCHAR(10) NOT NULL PRIMARY KEY,
 nome VARCHAR(40) NOT NULL,
 CPF VARCHAR(15) NOT NULL,
 departamento VARCHAR(30) NOT NULL,
 cargo VARCHAR(30) NOT NULL,
 campus VARCHAR(30) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS ativos (
 empresa VARCHAR(20) NULL,
 serial_number VARCHAR(15) NOT NULL PRIMARY KEY,
 patrimonio VARCHAR(20) NULL,
 tipo VARCHAR(20) NOT NULL,
 modelo VARCHAR(30) NOT NULL,
 marca VARCHAR(30) NOT NULL,
 at_status ENUM('disponivel','emprestado') NOT NULL DEFAULT 'disponivel',
 itens_entregues VARCHAR(30) NULL,
 id_chamado INT NOT NULL,
 imei_1 VARCHAR(15) NULL,
 imei_2 VARCHAR(15) NULL,
 numero_celular VARCHAR(20) NULL,
 UNIQUE KEY uq_ativos_patrimonio (patrimonio)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS emprestimos (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_colaborador VARCHAR(10) NOT NULL,
 id_ativo VARCHAR(15) NOT NULL,
 data_saida DATE NOT NULL,
 data_devolucao DATE NULL,
 condicao_saida VARCHAR(100) NOT NULL,
 condicao_retorno VARCHAR(100) NULL,
 observacoes VARCHAR(255) NULL,
 chamado_devolucao INT NULL,
 KEY idx_abertos (data_devolucao, data_saida),
 KEY idx_ativo_aberto (id_ativo, data_devolucao),
 CONSTRAINT fk_emprestimos_colaborador FOREIGN KEY (id_colaborador) REFERENCES colaboradores(login) ON UPDATE CASCADE ON DELETE RESTRICT,
 CONSTRAINT fk_emprestimos_ativo FOREIGN KEY (id_ativo) REFERENCES ativos(serial_number) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
