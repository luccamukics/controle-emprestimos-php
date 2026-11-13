<?php
declare(strict_types=1);
final class LoanService {
    public function __construct(private PDO $db) {}
    public function query(string $sql, array $params=[]): array {
        $s=$this->db->prepare($sql); $s->execute($params); return $s->fetchAll();
    }
    private function execute(string $sql,array $params=[]): int {
        $s=$this->db->prepare($sql);$s->execute($params);return $s->rowCount();
    }
    private function transaction(callable $fn): mixed {
        $this->db->beginTransaction();
        try {$result=$fn();$this->db->commit();return $result;}
        catch(Throwable $e) {if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public static function text(array $data,string $key,int $max,bool $required=true): string {
        if(isset($data[$key]) && !is_scalar($data[$key])) throw new DomainException('Campo inválido: '.$key);
        $value=trim((string)($data[$key]??''));
        if($required && $value==='') throw new DomainException('Preencha o campo '.$key.'.');
        if(mb_strlen($value)>$max) throw new DomainException('O campo '.$key.' aceita até '.$max.' caracteres.');
        return $value;
    }
    public static function positive(mixed $value): int {
        if(!is_scalar($value) || !preg_match('/^[1-9][0-9]*$/',(string)$value) || (float)$value>2147483647) throw new DomainException('Informe um chamado ou identificador inteiro positivo (até 2147483647).');
        return (int)$value;
    }
    public static function assetData(array $d): array {
        $v=[];
        foreach(['serial_number'=>15,'tipo'=>20,'marca'=>30,'modelo'=>30,'itens_entregues'=>30,'patrimonio'=>20] as $k=>$max)
            $v[$k]=self::text([$k=>mb_strtoupper(self::text($d,$k,$max,!in_array($k,['itens_entregues','patrimonio'])),'UTF-8')],$k,$max,!in_array($k,['itens_entregues','patrimonio']));
        $v['empresa']=self::text($d,'empresa',20);
        if(!in_array($v['empresa'],['Arklok','Vivo'],true)) throw new DomainException('Selecione a empresa.');
        if($v['empresa']==='Vivo' && $v['patrimonio']!=='') throw new DomainException('Patrimônio é permitido somente para Arklok.');
        $v['patrimonio']=$v['patrimonio']?:null;
        $v['id_chamado']=self::positive($d['id_chamado']??'');
        foreach(['imei_1'=>15,'imei_2'=>15,'numero_celular'=>20] as $k=>$max) $v[$k]=$v['tipo']==='CELULAR'?(self::text($d,$k,$max,false)?:null):null;
        foreach(['imei_1','imei_2'] as $k) if($v[$k]!==null && !preg_match('/^[0-9]{15}$/',$v[$k])) throw new DomainException('Cada IMEI deve conter exatamente 15 dígitos.');
        $n=$v['numero_celular'];
        if($n!==null && (!preg_match('/^[+ ()0-9-]+$/',$n) || strlen(preg_replace('/\D/','',$n))<8 || strlen(preg_replace('/\D/','',$n))>15)) throw new DomainException('Número de celular inválido. Use de 8 a 15 dígitos.');
        return $v;
    }
    public function saveAsset(array $d): void {
        $v=self::assetData($d);$original=self::text($d,'original',15,false);
        $this->transaction(function()use($v,$original){
            if($original!=='') {
                if(!$this->query('SELECT serial_number FROM ativos WHERE serial_number=? FOR UPDATE',[$original])) throw new DomainException('Ativo não encontrado.');
                // O status pertence ao fluxo de movimentação; não é editado pelo formulário.
                $sets=implode(',',array_map(fn($k)=>"$k = ?",array_keys($v)));
                $this->execute("UPDATE ativos SET $sets WHERE serial_number=?",[...array_values($v),$original]);
            } else {
                $cols=implode(',',array_keys($v));$qs=implode(',',array_fill(0,count($v),'?'));
                $this->execute("INSERT INTO ativos ($cols) VALUES ($qs)",array_values($v));
            }
        });
    }
    public function savePerson(array $d): void {
        $v=[];
        foreach(['login'=>10,'nome'=>40,'CPF'=>15,'departamento'=>30,'cargo'=>30,'campus'=>30] as $k=>$max) {
            $value=self::text($d,$k,$max);
            if(in_array($k,['nome','departamento','cargo'])) $value=mb_convert_case($value,MB_CASE_TITLE,'UTF-8');
            $v[$k]=self::text([$k=>$value],$k,$max);
        }
        $original=self::text($d,'original',10,false);
        if($original!=='') {
            if(!$this->query('SELECT login FROM colaboradores WHERE login=?',[$original])) throw new DomainException('Colaborador não encontrado.');
            $sets=implode(',',array_map(fn($k)=>"$k = ?",array_keys($v)));
            $this->execute("UPDATE colaboradores SET $sets WHERE login=?",[...array_values($v),$original]);
        } else $this->execute('INSERT INTO colaboradores (login,nome,CPF,departamento,cargo,campus) VALUES (?,?,?,?,?,?)',array_values($v));
    }
    public function lend(array $d): int {
        $login=self::text($d,'id_colaborador',10);$serial=self::text($d,'id_ativo',15);
        $condition=self::text($d,'condicao_saida',100);$notes=self::text($d,'observacoes',255,false);
        return $this->transaction(function()use($login,$serial,$condition,$notes){
            if(!$this->query('SELECT login FROM colaboradores WHERE login=? FOR UPDATE',[$login])) throw new DomainException('Cadastre ou selecione o colaborador.');
            $a=$this->query('SELECT at_status FROM ativos WHERE serial_number=? FOR UPDATE',[$serial]);
            if(!$a || $a[0]['at_status']!=='disponivel') throw new DomainException('O ativo não está disponível.');
            if($this->query('SELECT id FROM emprestimos WHERE id_ativo=? AND data_devolucao IS NULL',[$serial])) throw new DomainException('O ativo já possui empréstimo em aberto.');
            $this->execute('INSERT INTO emprestimos (id_colaborador,id_ativo,data_saida,condicao_saida,observacoes) VALUES (?,?,?,?,?)',[$login,$serial,date('Y-m-d'),$condition,$notes]);
            $id=(int)$this->db->lastInsertId();
            $this->execute("UPDATE ativos SET at_status='emprestado' WHERE serial_number=?",[$serial]);
            return $id;
        });
    }
    public function giveBack(array $d): int {
        $id=self::positive($d['id']??'');$condition=self::text($d,'condicao_retorno',100);$ticket=self::positive($d['chamado_devolucao']??'');
        return $this->transaction(function()use($id,$condition,$ticket){
            // Ordem de bloqueio igual à do empréstimo: ativo antes de movimentação.
            $rows=$this->query('SELECT id_ativo FROM emprestimos WHERE id=?',[$id]);
            if(!$rows) throw new DomainException('Empréstimo não encontrado.');
            $serial=$rows[0]['id_ativo'];
            if(!$this->query('SELECT serial_number FROM ativos WHERE serial_number=? FOR UPDATE',[$serial])) throw new DomainException('O ativo foi alterado. Atualize a página e tente novamente.');
            $rows=$this->query('SELECT data_devolucao,id_ativo FROM emprestimos WHERE id=? FOR UPDATE',[$id]);
            if($rows && $rows[0]['id_ativo']!==$serial) throw new DomainException('O ativo foi alterado. Atualize a página e tente novamente.');
            if(!$rows || $rows[0]['data_devolucao']!==null) throw new DomainException('Empréstimo já devolvido.');
            $this->execute('UPDATE emprestimos SET data_devolucao=?,condicao_retorno=?,chamado_devolucao=? WHERE id=?',[date('Y-m-d'),$condition,$ticket,$id]);
            $this->execute("UPDATE ativos SET at_status='disponivel' WHERE serial_number=?",[$serial]);
            return $id;
        });
    }
    public function deleteAsset(array $d): void {
        $serial=self::text($d,'serial_number',15);
        $this->transaction(function()use($serial){
            if(!$this->query('SELECT serial_number FROM ativos WHERE serial_number=? FOR UPDATE',[$serial])) throw new DomainException('Ativo não encontrado.');
            if($this->query('SELECT id FROM emprestimos WHERE id_ativo=? LIMIT 1',[$serial])) throw new DomainException('Este ativo possui histórico de empréstimos e não pode ser excluído.');
            $this->execute('DELETE FROM ativos WHERE serial_number=?',[$serial]);
        });
    }
    public function assets(string $search='',string $status=''): array {
        $sql="SELECT a.*,c.nome AS colaborador,c.departamento AS setor FROM ativos a LEFT JOIN emprestimos e ON e.id_ativo=a.serial_number AND e.data_devolucao IS NULL LEFT JOIN colaboradores c ON c.login=e.id_colaborador WHERE (a.serial_number LIKE ? OR a.modelo LIKE ? OR a.patrimonio LIKE ? OR c.nome LIKE ?)";
        $p=array_fill(0,4,'%'.$search.'%');
        if(in_array($status,['disponivel','emprestado'],true)){$sql.=' AND a.at_status=?';$p[]=$status;}
        return $this->query($sql.' ORDER BY a.tipo,a.marca,a.modelo',$p);
    }
    public function people(string $q=''): array { return $this->query('SELECT * FROM colaboradores WHERE login LIKE ? OR nome LIKE ? ORDER BY nome',['%'.$q.'%','%'.$q.'%']); }
    public function loans(string $filter='abertos',string $q=''): array {
        $sql="SELECT e.*,c.nome,c.departamento,a.tipo,a.marca,a.modelo,a.id_chamado,DATEDIFF(COALESCE(e.data_devolucao,CURDATE()),e.data_saida) AS dias FROM emprestimos e JOIN colaboradores c ON c.login=e.id_colaborador JOIN ativos a ON a.serial_number=e.id_ativo WHERE (c.nome LIKE ? OR e.id_ativo LIKE ? OR c.login LIKE ?)";
        if($filter==='abertos')$sql.=' AND e.data_devolucao IS NULL';
        if($filter==='devolvidos')$sql.=' AND e.data_devolucao IS NOT NULL';
        return $this->query($sql.' ORDER BY e.data_saida ASC,e.id DESC',array_fill(0,3,'%'.$q.'%'));
    }
    public function departments(): array { return $this->query('SELECT c.departamento,COUNT(*) AS total FROM emprestimos e JOIN colaboradores c ON c.login=e.id_colaborador WHERE e.data_devolucao IS NULL GROUP BY c.departamento ORDER BY total DESC'); }
    public function term(int $id): array {
        $r=$this->query('SELECT e.*,c.*,a.* FROM emprestimos e JOIN colaboradores c ON c.login=e.id_colaborador JOIN ativos a ON a.serial_number=e.id_ativo WHERE e.id=?',[$id]);
        if(!$r) throw new DomainException('Empréstimo não encontrado.'); return $r[0];
    }
}
