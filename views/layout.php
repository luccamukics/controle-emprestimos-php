<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Controle de Empréstimos</title><link rel="stylesheet" href="assets/app.css"><script src="assets/app.js" defer></script></head><body>
<aside><a class="brand" href="index.php"><span class="brand-icon">CE</span><span>Controle de<br><strong>Empréstimos</strong></span></a><p class="nav-label">GESTÃO DE ATIVOS</p><nav>
<?php foreach(['dashboard'=>'Visão geral','assets'=>'Ativos','people'=>'Colaboradores','loans'=>'Empréstimos','reports'=>'Relatórios'] as $key=>$label): ?><a class="<?= str_starts_with($page,substr($key,0,-1))?'active':'' ?>" href="<?= e(url($key)) ?>"><?= e($label) ?></a><?php endforeach ?>
</nav><div class="sidebar-note">PHP + MySQL<br><small>Controle de equipamentos</small></div></aside>
<main><header><div><p class="eyebrow">CONTROLE DE EMPRÉSTIMOS</p><h1><?= e(match($page){'dashboard'=>'Visão geral','assets'=>'Ativos','asset-form'=>'Cadastro de ativo','people'=>'Colaboradores','person-form'=>'Cadastro de colaborador','loans'=>'Empréstimos','loan-form'=>'Novo empréstimo','return-form'=>'Registrar devolução','reports'=>'Relatórios',default=>'Controle de ativos'}) ?></h1></div><span class="today"><?= date('d/m/Y') ?></span></header>
<?php if(isset($_SESSION['flash'])): ?><div role="status" class="alert success"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']);endif ?>
<?php if($error): ?><div role="alert" class="alert error"><?= e($error) ?></div><?php endif ?>
<?= $content ?>
<footer>Controle de Empréstimos · Ativos, pessoas e movimentações em um só lugar.</footer></main></body></html>
