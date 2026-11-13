<?php
declare(strict_types=1);
require __DIR__.'/../src/bootstrap.php';
$count=0;
function check(bool $ok,string $message): void {global $count;if(!$ok)throw new RuntimeException('FALHOU: '.$message);$count++;}
function rejects(callable $f,string $message): void {try{$f();}catch(DomainException){check(true,$message);return;}check(false,$message);}
$a=['serial_number'=>'teste1','tipo'=>'celular','marca'=>'samsung','modelo'=>'a55','itens_entregues'=>'cabo','id_chamado'=>'123','empresa'=>'Arklok','patrimonio'=>'p1','imei_1'=>'123456789012345','imei_2'=>'','numero_celular'=>'(11) 99999-0000'];
$v=LoanService::assetData($a);
check($v['serial_number']==='TESTE1'&&$v['tipo']==='CELULAR','Maiúsculas no ativo');
check($v['imei_1']==='123456789012345'&&$v['imei_2']===null,'Dados opcionais de celular');
check(LoanService::assetData(array_replace($a,['tipo'=>'notebook']))['imei_1']===null,'Limpeza IMEI de notebook');
rejects(fn()=>LoanService::assetData(array_replace($a,['imei_1'=>'123'])),'IMEI curto rejeitado');
rejects(fn()=>LoanService::assetData(array_replace($a,['imei_2'=>'ABCDEFGHIJKLMNO'])),'IMEI não numérico rejeitado');
rejects(fn()=>LoanService::assetData(array_replace($a,['numero_celular'=>'abc'])),'Telefone inválido rejeitado');
rejects(fn()=>LoanService::assetData(array_replace($a,['empresa'=>'Vivo'])),'Patrimônio Vivo rejeitado');
rejects(fn()=>LoanService::assetData(array_replace($a,['serial_number'=>str_repeat('A',16)])),'Limite serial respeitado');
rejects(fn()=>LoanService::assetData(array_replace($a,['marca'=>''])),'Marca obrigatória');
rejects(fn()=>LoanService::positive('0'),'Chamado zero rejeitado');
rejects(fn()=>LoanService::positive('2.5'),'Chamado decimal rejeitado');
rejects(fn()=>LoanService::positive('2147483648'),'Chamado maior que INT rejeitado');
rejects(fn()=>LoanService::positive(['1']),'Array rejeitado');
check(e('<script>')==='&lt;script&gt;','Escape HTML');
echo "OK: $count verificações de validação.\n";
