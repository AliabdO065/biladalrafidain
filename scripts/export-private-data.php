<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app=require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(config('database.default')!=='mysql')throw new RuntimeException('This export is for MySQL/MariaDB only.');
$connection=config('database.connections.mysql');
$binary=getenv('MYSQLDUMP_BINARY') ?: (is_file('D:/xampp/mysql/bin/mysqldump.exe')?'D:/xampp/mysql/bin/mysqldump.exe':'mysqldump');
$folder=storage_path('app/private/backups/'.date('Ymd-His').'-'.Illuminate\Support\Str::random(8));
Illuminate\Support\Facades\File::ensureDirectoryExists($folder,0700);
$sql=$folder.'/database.sql';
$process=new Symfony\Component\Process\Process([
$binary,'--single-transaction','--skip-lock-tables','--skip-add-locks','--no-tablespaces','--skip-comments',
'--default-character-set=utf8mb4','--host='.$connection['host'],'--port='.$connection['port'],
'--user='.$connection['username'],'--result-file='.$sql,$connection['database'],
],null,['MYSQL_PWD'=>(string)$connection['password']]);
// Password stays out of arguments, filenames, console output and the backup contents.
$process->setTimeout(120)->mustRun();
chmod($sql,0600);
$archive=$folder.'/data-and-images.zip';$zip=new ZipArchive;
if($zip->open($archive,ZipArchive::CREATE)!==true)throw new RuntimeException('Cannot create private backup.');
$zip->addFile($sql,'database.sql');
foreach([storage_path('app/catalog')=>'storage/app/catalog',public_path('images/landing')=>'public/images/landing']as$path=>$prefix){
if(!is_dir($path))continue;
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS))as$file){
if($file->isLink())continue;
if($file->isFile())$zip->addFile($file->getPathname(),$prefix.'/'.str_replace('\\','/',substr($file->getPathname(),strlen($path)+1)));
}}
$zip->close();chmod($archive,0600);
echo "Private database and image backup created outside the public directory.\n".$archive.PHP_EOL;
