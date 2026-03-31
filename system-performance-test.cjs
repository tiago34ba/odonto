const { performance } = require('perf_hooks');
const fs = require('fs');
const path = require('path');

class SystemPerformanceReport {
    constructor() {
        this.backendResults = null;
        this.frontendResults = null;
        this.systemsStatus = {
            backend: false,
            frontend: false
        };
    }

    async checkBackendStatus() {
        console.log('🔍 Verificando status do backend Laravel...');
        
        try {
            const http = require('http');
            const result = await this.makeRequest('http://localhost:8000/api/financeiro/dashboard');
            this.systemsStatus.backend = true;
            console.log('✅ Backend Laravel está rodando na porta 8000');
            return true;
        } catch (error) {
            console.log('❌ Backend Laravel não está respondendo na porta 8000');
            console.log('   Execute "php artisan serve" na pasta do projeto odonto');
            this.systemsStatus.backend = false;
            return false;
        }
    }

    async checkFrontendStatus() {
        console.log('🔍 Verificando status do frontend React...');
        
        try {
            const result = await this.makeRequest('http://localhost:3000');
            this.systemsStatus.frontend = true;
            console.log('✅ Frontend React está rodando na porta 3000');
            return true;
        } catch (error) {
            console.log('❌ Frontend React não está respondendo na porta 3000');
            console.log('   Execute "npm start" na pasta do dashboard-odonto');
            this.systemsStatus.frontend = false;
            return false;
        }
    }

    async makeRequest(url, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const http = require('http');
            const req = http.request(url, { method: 'GET' }, (res) => {
                res.on('data', () => {});
                res.on('end', () => resolve({ statusCode: res.statusCode }));
            });
            
            req.on('error', reject);
            req.setTimeout(timeout, () => {
                req.destroy();
                reject(new Error('Timeout'));
            });
            
            req.end();
        });
    }

    async runBackendTest() {
        if (!this.systemsStatus.backend) {
            console.log('\n⚠️  Pulando teste do backend - servidor não está rodando');
            return null;
        }

        console.log('\n🚀 EXECUTANDO TESTE DO BACKEND');
        console.log('='.repeat(50));
        
        const PerformanceTest = require('./performance-test.cjs');
        const tester = new PerformanceTest('http://localhost:8000');
        
        await tester.runFullApiTest();
        return tester.results;
    }

    async runFrontendTest() {
        if (!this.systemsStatus.frontend) {
            console.log('\n⚠️  Pulando teste do frontend - servidor não está rodando');
            return null;
        }

        console.log('\n🚀 EXECUTANDO TESTE DO FRONTEND');
        console.log('='.repeat(50));
        
        const FrontendPerformanceTest = require('../dashboard-odonto/frontend-performance-test.cjs');
        const tester = new FrontendPerformanceTest('http://localhost:3000');
        
        await tester.runFullFrontendTest();
        return tester.results;
    }

    generateComparativeReport() {
        console.log('\n📊 RELATÓRIO COMPARATIVO DE PERFORMANCE DOS SISTEMAS');
        console.log('='.repeat(70));
        
        const timestamp = new Date().toLocaleString('pt-BR');
        console.log(`📅 Data do teste: ${timestamp}`);
        
        // Status dos sistemas
        console.log('\n🔧 STATUS DOS SISTEMAS:');
        console.log(`   Backend Laravel (porta 8000): ${this.systemsStatus.backend ? '🟢 Online' : '🔴 Offline'}`);
        console.log(`   Frontend React (porta 3000): ${this.systemsStatus.frontend ? '🟢 Online' : '🔴 Offline'}`);
        
        // Comparação de performance
        if (this.backendResults && this.frontendResults) {
            console.log('\n📈 COMPARAÇÃO DE PERFORMANCE:');
            
            const backendAvg = this.backendResults.totalResponseTime / this.backendResults.totalTests;
            const frontendAvg = this.frontendResults.totalResponseTime / this.frontendResults.totalTests;
            
            console.log(`\n⏱️  TEMPO DE RESPOSTA MÉDIO:`);
            console.log(`   Backend API: ${backendAvg.toFixed(2)}ms`);
            console.log(`   Frontend: ${frontendAvg.toFixed(2)}ms`);
            
            if (backendAvg < frontendAvg) {
                console.log(`   🏆 Backend é ${((frontendAvg / backendAvg - 1) * 100).toFixed(1)}% mais rápido`);
            } else {
                console.log(`   🏆 Frontend é ${((backendAvg / frontendAvg - 1) * 100).toFixed(1)}% mais rápido`);
            }
            
            console.log(`\n✅ TAXA DE SUCESSO:`);
            const backendSuccess = (this.backendResults.passedTests / this.backendResults.totalTests) * 100;
            const frontendSuccess = (this.frontendResults.passedTests / this.frontendResults.totalTests) * 100;
            
            console.log(`   Backend API: ${backendSuccess.toFixed(2)}%`);
            console.log(`   Frontend: ${frontendSuccess.toFixed(2)}%`);
            
            console.log(`\n📊 THROUGHPUT (requisições/segundo):`);
            // Calcular throughput médio para cada sistema
            let backendThroughput = 0;
            let frontendThroughput = 0;
            
            if (this.backendResults.endpoints) {
                const throughputs = Object.values(this.backendResults.endpoints).map(e => e.throughput || 0);
                backendThroughput = throughputs.reduce((a, b) => a + b, 0) / throughputs.length;
            }
            
            if (this.frontendResults.pages) {
                const throughputs = Object.values(this.frontendResults.pages).map(p => p.throughput || 0);
                frontendThroughput = throughputs.reduce((a, b) => a + b, 0) / throughputs.length;
            }
            
            console.log(`   Backend API: ${backendThroughput.toFixed(2)} req/s`);
            console.log(`   Frontend: ${frontendThroughput.toFixed(2)} req/s`);
        }
        
        // Recomendações
        console.log('\n💡 RECOMENDAÇÕES DE OTIMIZAÇÃO:');
        
        if (this.backendResults) {
            const avgBackend = this.backendResults.totalResponseTime / this.backendResults.totalTests;
            if (avgBackend > 500) {
                console.log('   🔧 Backend: Considere implementar cache e otimização de queries');
            } else if (avgBackend > 200) {
                console.log('   🔧 Backend: Performance aceitável, monitore sob alta carga');
            } else {
                console.log('   ✅ Backend: Performance excelente');
            }
        }
        
        if (this.frontendResults) {
            const avgFrontend = this.frontendResults.totalResponseTime / this.frontendResults.totalTests;
            if (avgFrontend > 2000) {
                console.log('   🔧 Frontend: Implemente code splitting e lazy loading');
            } else if (avgFrontend > 1000) {
                console.log('   🔧 Frontend: Otimize assets e considere CDN');
            } else {
                console.log('   ✅ Frontend: Performance boa para aplicação React');
            }
        }
        
        // Arquitetura geral
        console.log('\n🏗️  AVALIAÇÃO DA ARQUITETURA:');
        
        if (this.systemsStatus.backend && this.systemsStatus.frontend) {
            console.log('   ✅ Arquitetura separada backend/frontend funcionando corretamente');
            console.log('   ✅ API REST operacional com endpoints funcionais');
            console.log('   ✅ Frontend React servindo interface do usuário');
            
            console.log('\n📋 PONTOS DE ATENÇÃO:');
            console.log('   🔍 Monitorar performance sob carga real de usuários');
            console.log('   🔍 Implementar monitoramento de logs e métricas');
            console.log('   🔍 Considerar implementação de cache Redis para API');
            console.log('   🔍 Avaliar necessidade de CDN para assets estáticos');
        } else {
            console.log('   ⚠️  Arquitetura incompleta - nem todos os serviços estão rodando');
        }
        
        this.saveReportToFile();
    }

    saveReportToFile() {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const reportData = {
            timestamp: new Date().toISOString(),
            systemsStatus: this.systemsStatus,
            backendResults: this.backendResults,
            frontendResults: this.frontendResults,
            summary: {
                backendAvgResponseTime: this.backendResults ? 
                    this.backendResults.totalResponseTime / this.backendResults.totalTests : null,
                frontendAvgResponseTime: this.frontendResults ? 
                    this.frontendResults.totalResponseTime / this.frontendResults.totalTests : null,
                backendSuccessRate: this.backendResults ? 
                    (this.backendResults.passedTests / this.backendResults.totalTests) * 100 : null,
                frontendSuccessRate: this.frontendResults ? 
                    (this.frontendResults.passedTests / this.frontendResults.totalTests) * 100 : null
            }
        };
        
        const fileName = `performance-report-${timestamp}.json`;
        const filePath = path.join(__dirname, fileName);
        
        try {
            fs.writeFileSync(filePath, JSON.stringify(reportData, null, 2), 'utf8');
            console.log(`\n💾 Relatório salvo em: ${filePath}`);
        } catch (error) {
            console.error('❌ Erro ao salvar relatório:', error.message);
        }
    }

    async runFullSystemTest() {
        console.log('🏥 SISTEMA DE GESTÃO ODONTOLÓGICA - TESTE DE PERFORMANCE');
        console.log('='.repeat(70));
        console.log('🎯 Testando Backend Laravel + Frontend React');
        console.log('📊 Análise de performance, estabilidade e throughput\n');
        
        // Verificar status dos sistemas
        await this.checkBackendStatus();
        await this.checkFrontendStatus();
        
        if (!this.systemsStatus.backend && !this.systemsStatus.frontend) {
            console.log('\n❌ ERRO: Nenhum sistema está rodando!');
            console.log('   Inicie os serviços antes de executar os testes.');
            return;
        }
        
        // Executar testes
        this.backendResults = await this.runBackendTest();
        this.frontendResults = await this.runFrontendTest();
        
        // Gerar relatório comparativo
        this.generateComparativeReport();
        
        console.log('\n🎉 TESTE DE PERFORMANCE CONCLUÍDO!');
    }
}

// Executar teste completo
async function runFullTest() {
    const reporter = new SystemPerformanceReport();
    await reporter.runFullSystemTest();
}

if (require.main === module) {
    runFullTest().catch(console.error);
}

module.exports = SystemPerformanceReport;