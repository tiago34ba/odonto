$folders = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "tests"
)

$files = @(
    "artisan",
    "composer.json"
)

$projectPath = Read-Host "Digite o caminho do projeto Laravel para verificar"

Write-Host "\nVerificando pastas padrão..."
foreach ($folder in $folders) {
    if (Test-Path "$projectPath\$folder") {
        Write-Host "[OK] Pasta encontrada: $folder" -ForegroundColor Green
    } else {
        Write-Host "[FALTA] Pasta não encontrada: $folder" -ForegroundColor Red
    }
}

Write-Host "\nVerificando arquivos essenciais..."
foreach ($file in $files) {
    if (Test-Path "$projectPath\$file") {
        Write-Host "[OK] Arquivo encontrado: $file" -ForegroundColor Green
    } else {
        Write-Host "[FALTA] Arquivo não encontrado: $file" -ForegroundColor Red
    }
}
