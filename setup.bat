@echo off
echo ========================================
echo    Sistema Odonto - Setup Automatizado
echo ========================================
echo.

echo [1/8] Verificando dependencias...
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ERRO: PHP nao encontrado. Instale o PHP 8.2+ primeiro.
    pause
    exit /b 1
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo ERRO: Composer nao encontrado. Instale o Composer primeiro.
    pause
    exit /b 1
)

where mysql >nul 2>nul
if %errorlevel% neq 0 (
    echo AVISO: MySQL nao encontrado. Certifique-se de que o MySQL esta instalado e rodando.
)

echo [2/8] Instalando dependencias do Laravel...
composer install
if %errorlevel% neq 0 (
    echo ERRO: Falha ao instalar dependencias do Composer.
    pause
    exit /b 1
)

echo [3/8] Configurando arquivo de ambiente...
if not exist .env (
    copy .env.example .env
    echo Arquivo .env criado. Configure as variaveis de ambiente.
)

echo [4/8] Gerando chave da aplicacao...
php artisan key:generate
if %errorlevel% neq 0 (
    echo ERRO: Falha ao gerar chave da aplicacao.
    pause
    exit /b 1
)

echo [5/8] Configurando cache...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo [6/8] Executando migracoes...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo AVISO: Falha ao executar migracoes. Verifique a configuracao do banco de dados.
)

echo [7/8] Criando diretorios de log...
if not exist storage\logs (
    mkdir storage\logs
)

echo [8/8] Configurando permissoes...
icacls storage /grant Everyone:F /T >nul 2>nul
icacls bootstrap\cache /grant Everyone:F /T >nul 2>nul

echo.
echo ========================================
echo    Setup concluido com sucesso!
echo ========================================
echo.
echo Proximos passos:
echo 1. Configure o banco de dados no arquivo .env
echo 2. Execute: php artisan migrate:mysql --force (se necessario)
echo 3. Execute: php artisan serve
echo 4. Acesse: http://localhost:8000
echo.
echo Para o frontend React:
echo 1. Navegue para: ..\FrontEnd\dashboard-odonto
echo 2. Execute: npm install
echo 3. Execute: npm start
echo.
pause
