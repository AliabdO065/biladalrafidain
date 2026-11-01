$ErrorActionPreference = 'Stop'
$projectPath = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$stagingPath = Join-Path ([IO.Path]::GetTempPath()) ('Biladalrafidain-release-' + [guid]::NewGuid().ToString('N'))
$appPath = Join-Path $stagingPath 'bilad'
$webPath = Join-Path $stagingPath 'public_html'
New-Item -ItemType Directory -Path $appPath,$webPath -Force | Out-Null
foreach ($directory in @('app','config','database','lang','resources','routes')) {
    Copy-Item -LiteralPath (Join-Path $projectPath $directory) -Destination (Join-Path $appPath $directory) -Recurse
}
New-Item -ItemType Directory -Path (Join-Path $appPath 'bootstrap/cache') -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $projectPath 'bootstrap/app.php') -Destination (Join-Path $appPath 'bootstrap/app.php')
foreach ($file in @('artisan','composer.json','composer.lock','.env.production.example')) {
    Copy-Item -LiteralPath (Join-Path $projectPath $file) -Destination (Join-Path $appPath $file)
}
Get-ChildItem -LiteralPath (Join-Path $projectPath 'public') -Force |
    Where-Object { $_.Name -notin @('storage','hot') } |
    ForEach-Object { Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $webPath $_.Name) -Recurse }
Copy-Item -LiteralPath (Join-Path $projectPath 'deploy/public-index.php') -Destination (Join-Path $webPath 'index.php') -Force
Copy-Item -LiteralPath (Join-Path $projectPath 'docs/namecheap-deployment.md') -Destination (Join-Path $stagingPath 'DEPLOYMENT.md')
Copy-Item -LiteralPath (Join-Path $projectPath 'docs/whatsapp-setup-and-cost.md') -Destination (Join-Path $stagingPath 'whatsapp-setup-and-cost.md')
foreach ($directory in @('app/public','app/catalog','app/private/documents','framework/cache/data','framework/sessions','framework/views','logs')) {
    New-Item -ItemType Directory -Path (Join-Path $appPath ('storage/'+$directory)) -Force | Out-Null
}
$env:COMPOSER_CACHE_DIR = Join-Path ([IO.Path]::GetTempPath()) 'Biladalrafidain-composer-cache'
Push-Location $appPath
try {
    & composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress
    if ($LASTEXITCODE -ne 0) { throw 'Production dependency install failed.' }
    & composer check-platform-reqs --no-dev
    if ($LASTEXITCODE -ne 0) { throw 'Production platform check failed.' }
    & composer audit --locked --no-dev
    if ($LASTEXITCODE -ne 0) { throw 'Production dependency audit failed.' }
} finally { Pop-Location }
$archivePath = Join-Path $projectPath 'dist/namecheap-release.zip'
New-Item -ItemType Directory -Path (Split-Path $archivePath -Parent) -Force | Out-Null
& php (Join-Path $projectPath 'scripts/zip-release.php') $stagingPath $archivePath
if ($LASTEXITCODE -ne 0) { throw 'Release archive creation failed.' }
Write-Output ('Release archive: ' + $archivePath)
Write-Output ('Staging directory: ' + $stagingPath)
