<?php
declare(strict_types=1);
require __DIR__.'/../src/bootstrap.php';
$db=Database::connect();$s=new LoanService($db);$token=strtoupper(bin2hex(random_bytes(4)));
$login='T'.$token;$serial='TEST'.$token;$renamed='NEW'.$token;$createdPerson=false;$createdAsset=false;$id=null;$checks=0;
function expect(bool $ok,string $msg): void {global $checks;if(!$ok)throw new RuntimeException('FALHOU: '.$msg);$checks++;}
function expectRejected(callable $fn,string $msg): void {try{$fn();}catch(DomainException){expect(true,$msg);return;}expect(false,$msg);}
try {
 $s->savePerson(['login'=>$login,'nome'=>'Pessoa de Teste','CPF'=>'000.000.000-00','departamento'=>'TI Teste','cargo'=>'Analista','campus'=>'Teste']);$createdPerson=true;
 $a=['serial_number'=>$serial,'tipo'=>'notebook','marca'=>'dell','modelo'=>'teste','itens_entregues'=>'cabo','id_chamado'=>'123','empresa'=>'Arklok','patrimonio'=>''];
 $s->saveAsset($a);$createdAsset=true;
 expect($s->query('SELECT at_status FROM ativos WHERE serial_number=?',[$serial])[0]['at_status']==='disponivel','Novo ativo disponível');
 $id=$s->lend(['id_colaborador'=>$login,'id_ativo'=>$serial,'condicao_saida'=>'Bom estado','observacoes'=>'Teste automatizado']);
 expect($s->query('SELECT at_status FROM ativos WHERE serial_number=?',[$serial])[0]['at_status']==='emprestado','Status após empréstimo');
 expectRejected(fn()=>$s->lend(['id_colaborador'=>$login,'id_ativo'=>$serial,'condicao_saida'=>'Bom estado']),'Bloqueio de segundo empréstimo');
 expectRejected(fn()=>$s->deleteAsset(['serial_number'=>$serial]),'Bloqueio de exclusão em aberto');
 $s->saveAsset(array_replace($a,['original'=>$serial,'serial_number'=>$renamed]));
 expect($s->query('SELECT id_ativo FROM emprestimos WHERE id=?',[$id])[0]['id_ativo']===$renamed,'FK acompanha edição de serial');
 $s->giveBack(['id'=>$id,'condicao_retorno'=>'Bom estado','chamado_devolucao'=>'456']);
 $r=$s->query('SELECT * FROM emprestimos WHERE id=?',[$id])[0];
 expect($r['data_devolucao']!==null&&(int)$r['chamado_devolucao']===456,'Devolução persistida');
 expect($s->query('SELECT at_status FROM ativos WHERE serial_number=?',[$renamed])[0]['at_status']==='disponivel','Ativo disponível novamente');
 expectRejected(fn()=>$s->giveBack(['id'=>$id,'condicao_retorno'=>'Bom','chamado_devolucao'=>'456']),'Bloqueio de devolução repetida');
 expectRejected(fn()=>$s->deleteAsset(['serial_number'=>$renamed]),'Histórico encerrado preservado');
 echo "OK: $checks verificações de integração.\n";
} finally {
 if($db->inTransaction())$db->rollBack();
 if($createdPerson){$q=$db->prepare('DELETE FROM emprestimos WHERE id_colaborador=?');$q->execute([$login]);}
 if($createdAsset){$q=$db->prepare('DELETE FROM ativos WHERE serial_number IN (?,?)');$q->execute([$serial,$renamed]);}
 if($createdPerson){$q=$db->prepare('DELETE FROM colaboradores WHERE login=?');$q->execute([$login]);}
}
