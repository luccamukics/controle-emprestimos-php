<?php
declare(strict_types=1);
final class Documents {
    private static function xml(mixed $v): string { return htmlspecialchars(preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','',(string)($v??'')) ?? '',ENT_XML1|ENT_QUOTES,'UTF-8'); }
    private static function send(string $path,string $name,string $type): never {
        header('Content-Type: '.$type);header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Length: '.filesize($path));header('Cache-Control: no-store');
        readfile($path);unlink($path);exit;
    }
    public static function word(array $r,bool $return): never {
        if(!class_exists(ZipArchive::class))throw new RuntimeException('Ative a extensão zip no php.ini para gerar documentos.');
        if($return && !$r['data_devolucao'])throw new DomainException('Registre a devolução antes de gerar este termo.');
        $cell=$r['tipo']==='CELULAR';
        $template='TERMO_'.($return?'DEVOLUCAO':'RESPONSABILIDADE').($cell?'_CELULAR':'').'_MODELO.docx';
        $path=tempnam(sys_get_temp_dir(),'termo_');
        try {
            if(!copy(__DIR__.'/../templates/'.$template,$path))throw new RuntimeException('Não foi possível copiar o modelo do termo.');
            $z=new ZipArchive();if($z->open($path)!==true)throw new RuntimeException('Não foi possível abrir o modelo Word.');
            $months=['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
            $d=new DateTimeImmutable($r['data_devolucao']??$r['data_saida']);
            $v=$r+['cpf'=>$r['CPF'],'serial'=>$r['serial_number']];
            $v['modelo']=$return?$r['modelo']:$r['tipo'].' '.$r['marca'].' '.$r['modelo'];
            $v['observacoes']=$r['condicao_retorno']??'';
            $v['data_devolucao']=$d->format('d/m/Y');
            $v['data_extenso']=$d->format('j').' de '.$months[(int)$d->format('n')-1].' de '.$d->format('Y');
            foreach(['imei_1','imei_2','numero_celular'] as $k)$v[$k]=$r[$k]?:'Não informado';
            $replacements=[];foreach($v as $k=>$value)$replacements['{{'.$k.'}}']=self::xml($value);
            $xml=$z->getFromName('word/document.xml');
            if($xml===false)throw new RuntimeException('Modelo Word inválido.');
            $xml=strtr($xml,$replacements);
            if(preg_match('/{{[^}]+}}/',$xml))throw new RuntimeException('O modelo contém campos não preenchidos.');
            $z->addFromString('word/document.xml',$xml);$z->close();
            self::send($path,($return?'devolucao':'responsabilidade').'-'.$r['id'].'.docx','application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        } finally {if(is_file($path))unlink($path);}
    }
    public static function spreadsheet(array $headers,array $rows,string $name): never {
        if(!class_exists(ZipArchive::class))throw new RuntimeException('Ative a extensão zip no php.ini para exportar XLSX.');
        $path=tempnam(sys_get_temp_dir(),'relatorio_');
        try {
            $z=new ZipArchive();if($z->open($path,ZipArchive::OVERWRITE)!==true)throw new RuntimeException('Não foi possível gerar a planilha.');
            $z->addFromString('[Content_Types].xml','<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $z->addFromString('_rels/.rels','<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $z->addFromString('xl/workbook.xml','<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Relatório" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $z->addFromString('xl/_rels/workbook.xml.rels','<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            $xml='<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" state="frozen"/></sheetView></sheetViews><sheetData>';
            foreach([$headers,...$rows] as $i=>$row){$xml.='<row r="'.($i+1).'">';foreach(array_values($row) as $v)$xml.='<c t="inlineStr"><is><t xml:space="preserve">'.self::xml($v).'</t></is></c>'; $xml.='</row>';}
            $z->addFromString('xl/worksheets/sheet1.xml',$xml.'</sheetData></worksheet>');$z->close();
            self::send($path,$name.'.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } finally {if(is_file($path))unlink($path);}
    }
}
