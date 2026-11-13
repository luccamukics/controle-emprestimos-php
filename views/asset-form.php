<?php
$a=[];$serial=is_string($_GET['serial']??null)?$_GET['serial']:'';
if($serial!==''){$a=$service->query('SELECT * FROM ativos WHERE serial_number=?',[$serial])[0]??[];if(!$a)throw new DomainException('Ativo não encontrado.');$a['original']=$serial;}
if($_SERVER['REQUEST_METHOD']==='POST')$a=$form;
?>
<section class="card form-card"><div class="section-head"><div><h2><?= empty($a['original'])?'Novo equipamento':'Editar equipamento' ?></h2><p>O status é atualizado automaticamente pelos empréstimos e devoluções.</p></div><a href="<?= e(url('assets')) ?>">Voltar</a></div>
<form method="post" action="<?= e(url('asset-form')) ?>"><?= csrf() ?><input type="hidden" name="action" value="asset-save"><input type="hidden" name="original" value="<?= e($a['original']??'') ?>"><div class="form-grid">
<?php selectField('empresa','Empresa',['Arklok'=>'Arklok','Vivo'=>'Vivo'],$a);input('serial_number','Número de série',15,$a);input('patrimonio','Patrimônio (opcional · Arklok)',20,$a,false);input('tipo','Tipo (ex.: NOTEBOOK ou CELULAR)',20,$a);input('marca','Marca',30,$a);input('modelo','Modelo',30,$a);input('itens_entregues','Itens entregues (opcional)',30,$a,false);input('id_chamado','Chamado GLPI',10,$a,true,'number'); ?>
</div><fieldset id="cell-fields"><legend>Dados de celular <small>Opcionais; salvos somente quando o tipo for CELULAR.</small></legend><div class="form-grid"><?php input('imei_1','IMEI 1 (15 dígitos)',15,$a,false);input('imei_2','IMEI 2 (15 dígitos)',15,$a,false);input('numero_celular','Número do celular',20,$a,false); ?></div></fieldset><div class="form-actions"><a class="button secondary" href="<?= e(url('assets')) ?>">Cancelar</a><button>Salvar ativo</button></div></form></section>
