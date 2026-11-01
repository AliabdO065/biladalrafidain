<?php
// Build from a fresh, explicit staging directory; never archive the working tree or its .env.
[$script,$staging,$destination]=$argv;
$base=realpath($staging);
if(!$base || !is_file($base.'/bilad/vendor/autoload.php') || is_file($base.'/bilad/.env')) throw new RuntimeException('Unsafe or incomplete release staging.');
$zip=new ZipArchive;
if($zip->open($destination,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new RuntimeException('Cannot create release archive.');
$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
$count=0;
foreach($files as$file){
    if($file->isLink())throw new RuntimeException('Symlinks must not be bundled.');
    $relative=str_replace('\\','/',substr($file->getPathname(),strlen($base)+1));
    // The application uses glyph shaping only. Omit upstream samples, docs,
    // duplicate PHAR and the 19 MB spellchecking dictionary; retain runtime PHP,
    // shaping JSON data and all licenses. The Composer installation stays intact.
    if(preg_match('~^bilad/vendor/khaled\.alshamaa/ar-php/(?:docs(?:/|$)|examples(?:/|$)|tests(?:/|$)|\.github(?:/|$)|ArPHP\.phar$|src/data/SarahSpell/sarahspell\.db\.bz2$)~',$relative))continue;
    if(basename($relative)==='.env' || str_contains($relative,'ui-review/'))throw new RuntimeException('Private workspace content in staging.');
    $file->isDir()?$zip->addEmptyDir($relative):$zip->addFile($file->getPathname(),$relative);
    $count++;
}
$zip->close();
echo $count.' entries; '.round(filesize($destination)/1048576,2).' MiB'.PHP_EOL;
