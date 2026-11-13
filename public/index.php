<?php
declare(strict_types=1);
ini_set('session.use_strict_mode','1');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Strict','secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off']);
session_start();
header('X-Content-Type-Options: nosniff');header('X-Frame-Options: DENY');header('Referrer-Policy: same-origin');header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
require __DIR__.'/../src/bootstrap.php';
$_SESSION['csrf']??=bin2hex(random_bytes(32));
$page=is_string($_GET['page']??null)?$_GET['page']:'dashboard';
$allowed=['dashboard','assets','asset-form','people','person-form','loans','loan-form','return-form','reports','term','export'];
if(!in_array($page,$allowed,true)){http_response_code(404);$page='not-found';}
$error=null;$service=null;$form=$_POST;
$q=is_string($_GET['q']??null)?mb_substr(trim($_GET['q']),0,100):'';
$status=is_string($_GET['status']??null)?$_GET['status']:'';
$filter=is_string($_GET['filter']??null)?$_GET['filter']:'abertos';
try {
    $service=new LoanService(Database::connect());
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!is_string($_POST['csrf']??null)||!hash_equals($_SESSION['csrf'],$_POST['csrf']))throw new DomainException('Sessão expirada. Atualize a página e tente novamente.');
        $action=$_POST['action']??'';
        switch($action){
            case 'asset-save':$page='asset-form';$service->saveAsset($_POST);$_SESSION['flash']='Ativo salvo com sucesso.';redirect('assets');
            case 'person-save':$page='person-form';$service->savePerson($_POST);$_SESSION['flash']='Colaborador salvo com sucesso.';redirect('people');
            case 'lend':$page='loan-form';$id=$service->lend($_POST);$_SESSION['flash']='Empréstimo #'.$id.' registrado. O termo está disponível na lista.';redirect('loans');
            case 'return':$page='return-form';$id=$service->giveBack($_POST);$_SESSION['flash']='Devolução #'.$id.' registrada. O ativo está disponível novamente.';redirect('loans',['filter'=>'devolvidos']);
            case 'asset-delete':$page='assets';$service->deleteAsset($_POST);$_SESSION['flash']='Ativo excluído.';redirect('assets');
            default:throw new DomainException('Operação inválida.');
        }
    }
    if($page==='term')Documents::word($service->term(LoanService::positive($_GET['id']??'')),($_GET['kind']??'')==='return');
    if($page==='export'){
        $kind=$_GET['kind']??'assets';
        if($kind==='assets'){
            $rows=$service->assets($q,$status);$cols=['empresa','serial_number','patrimonio','tipo','marca','modelo','at_status','colaborador','setor','itens_entregues','id_chamado','imei_1','imei_2','numero_celular'];
            $headers=['Empresa','Serial','Patrimônio','Tipo','Marca','Modelo','Status','Colaborador','Setor','Itens entregues','Chamado GLPI','IMEI 1','IMEI 2','Número celular'];
        }elseif($kind==='departments'){
            $rows=$service->departments();$cols=['departamento','total'];$headers=['Departamento','Ativos emprestados'];
        }elseif($kind==='loans'){
            $rows=$service->loans($filter,$q);$cols=['id','nome','id_colaborador','id_ativo','tipo','marca','modelo','departamento','data_saida','data_devolucao','dias','condicao_saida','condicao_retorno','observacoes','id_chamado','chamado_devolucao'];
            $headers=['ID','Colaborador','Login','Serial','Tipo','Marca','Modelo','Departamento','Saída','Devolução','Dias','Condição saída','Condição retorno','Observações','Chamado saída','Chamado devolução'];
        }else throw new DomainException('Relatório inválido.');
        Documents::spreadsheet($headers,array_map(fn($r)=>array_map(fn($c)=>$r[$c],$cols),$rows),'relatorio-'.$kind);
    }
    ob_start();
    if($page==='not-found')echo '<section class="card"><h2>Página não encontrada</h2><a href="index.php">Voltar ao painel</a></section>';
    else require __DIR__.'/../views/'.$page.'.php';
    $content=ob_get_clean();
}catch(Throwable $ex){
    if(ob_get_level())ob_end_clean();
    if($ex instanceof DomainException)$error=$ex->getMessage();
    elseif($ex instanceof PDOException){
        $code=(int)($ex->errorInfo[1]??0);
        $error=match($code){1062=>'Já existe um registro com esse serial, patrimônio ou login.',1451=>'Este registro possui histórico vinculado e não pode ser excluído.',1452=>'Colaborador ou ativo não encontrado. Atualize a página.',1213,1205=>'Outra operação está atualizando o registro. Tente novamente.',default=>'Não foi possível acessar o banco. Confira MySQL, config/database.php e a instalação das tabelas.'};
        error_log('Controle PHP: erro MySQL '.$code);
    }else {$error=$ex instanceof RuntimeException?$ex->getMessage():'Não foi possível concluir a operação.';error_log('Controle PHP: '.get_class($ex));}
    $content='';
    if($service && in_array($page,['asset-form','person-form','loan-form','return-form'],true)){
        try{ob_start();require __DIR__.'/../views/'.$page.'.php';$content=ob_get_clean();}catch(Throwable $ignored){if(ob_get_level())ob_end_clean();}
    }
    if($content==='')$content=$ex instanceof DomainException ? '<section class="card"><div class="section-head"><h2>Operação não concluída</h2><a href="index.php">Voltar ao painel</a></div></section>' : '<section class="card"><div class="section-head"><div><h2>Verifique a configuração</h2><p>Consulte o arquivo LEIA_PRIMEIRO.md incluído no projeto.</p><a href="index.php">Tentar novamente</a></div></div></section>';
    http_response_code($ex instanceof DomainException?422:500);
}
require __DIR__.'/../views/layout.php';
