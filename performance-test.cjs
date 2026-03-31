const { performance } = require('perf_hooks');
const https = require('https');
const http = require('http');

class PerformanceTest {
    constructor(baseUrl = 'http://localhost:8000') {
        this.baseUrl = baseUrl;
        this.results = {
            totalTests: 0,
            passedTests: 0,
            failedTests: 0,
            averageResponseTime: 0,
            totalResponseTime: 0,
            endpoints: {}
        };
    }

    async makeRequest(endpoint, method = 'GET', data = null) {
        const startTime = performance.now();
        
        return new Promise((resolve, reject) => {
            const url = `${this.baseUrl}${endpoint}`;
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };

            const requestModule = url.startsWith('https') ? https : http;
            
            const req = requestModule.request(url, options, (res) => {
                let responseData = '';
                
                res.on('data', (chunk) => {
                    responseData += chunk;
                });
                
                res.on('end', () => {
                    const endTime = performance.now();
                    const responseTime = endTime - startTime;
                    
                    resolve({
                        statusCode: res.statusCode,
                        responseTime: responseTime,
                        data: responseData,
                        headers: res.headers
                    });
                });
            });

            req.on('error', (error) => {
                const endTime = performance.now();
                const responseTime = endTime - startTime;
                
                reject({
                    error: error.message,
                    responseTime: responseTime
                });
            });

            if (data && method !== 'GET') {
                req.write(JSON.stringify(data));
            }
            
            req.end();
        });
    }

    async testEndpoint(endpoint, expectedStatus = 200, concurrentRequests = 10) {
        console.log(`\nTestando endpoint: ${endpoint}`);
        console.log(`Requisições simultâneas: ${concurrentRequests}`);
        
        const promises = [];
        const results = [];
        
        // Teste de carga
        for (let i = 0; i < concurrentRequests; i++) {
            promises.push(this.makeRequest(endpoint));
        }
        
        try {
            const responses = await Promise.allSettled(promises);
            
            let successCount = 0;
            let errorCount = 0;
            let totalTime = 0;
            let minTime = Infinity;
            let maxTime = 0;
            
            responses.forEach((response, index) => {
                this.results.totalTests++;
                
                if (response.status === 'fulfilled') {
                    const result = response.value;
                    const responseTime = result.responseTime;
                    
                    totalTime += responseTime;
                    minTime = Math.min(minTime, responseTime);
                    maxTime = Math.max(maxTime, responseTime);
                    
                    if (result.statusCode === expectedStatus) {
                        successCount++;
                        this.results.passedTests++;
                    } else {
                        errorCount++;
                        this.results.failedTests++;
                        console.log(`  ❌ Req ${index + 1}: Status ${result.statusCode} (esperado ${expectedStatus})`);
                    }
                    
                    results.push({
                        success: result.statusCode === expectedStatus,
                        responseTime: responseTime,
                        statusCode: result.statusCode,
                        dataSize: result.data ? result.data.length : 0
                    });
                } else {
                    errorCount++;
                    this.results.failedTests++;
                    console.log(`  ❌ Req ${index + 1}: Erro - ${response.reason.error}`);
                    
                    results.push({
                        success: false,
                        responseTime: response.reason.responseTime || 0,
                        error: response.reason.error
                    });
                }
            });
            
            const avgTime = totalTime / concurrentRequests;
            this.results.totalResponseTime += totalTime;
            
            // Armazena resultados do endpoint
            this.results.endpoints[endpoint] = {
                successCount,
                errorCount,
                avgResponseTime: avgTime,
                minResponseTime: minTime === Infinity ? 0 : minTime,
                maxResponseTime: maxTime,
                throughput: concurrentRequests / (totalTime / 1000), // req/s
                results: results
            };
            
            console.log(`  ✅ Sucessos: ${successCount}/${concurrentRequests}`);
            console.log(`  ⏱️  Tempo médio: ${avgTime.toFixed(2)}ms`);
            console.log(`  ⚡ Min/Max: ${minTime === Infinity ? 0 : minTime.toFixed(2)}ms / ${maxTime.toFixed(2)}ms`);
            console.log(`  📊 Throughput: ${(concurrentRequests / (totalTime / 1000)).toFixed(2)} req/s`);
            
        } catch (error) {
            console.error(`Erro no teste do endpoint ${endpoint}:`, error);
        }
        
        return results;
    }

    async runFullApiTest() {
        console.log('🚀 INICIANDO TESTE DE PERFORMANCE - BACKEND LARAVEL');
        console.log('='.repeat(60));
        
        const endpoints = [
            '/api/financeiro/dashboard',
            '/api/financeiro/contas-pagar',
            '/api/financeiro/contas-receber', 
            '/api/financeiro/fluxo-caixa',
            '/api/financeiro/contas-pagar/pendentes',
            '/api/financeiro/contas-receber/pendentes',
            '/api/financeiro/contas-pagar/vencidas',
            '/api/financeiro/contas-receber/vencidas',
            '/api/financeiro/contas-receber/vencendo',
            '/api/pacientes',
            '/api/procedures'
        ];
        
        // Teste com diferentes cargas
        const loadTests = [
            { concurrent: 5, label: 'Carga Baixa' },
            { concurrent: 15, label: 'Carga Média' },
            { concurrent: 30, label: 'Carga Alta' }
        ];
        
        for (const load of loadTests) {
            console.log(`\n📈 ${load.label.toUpperCase()} (${load.concurrent} requisições simultâneas)`);
            console.log('-'.repeat(50));
            
            for (const endpoint of endpoints) {
                await this.testEndpoint(endpoint, 200, load.concurrent);
            }
        }
        
        this.generateReport();
    }

    generateReport() {
        console.log('\n📋 RELATÓRIO DE PERFORMANCE - BACKEND');
        console.log('='.repeat(60));
        
        console.log(`📊 Resumo Geral:`);
        console.log(`   Total de testes: ${this.results.totalTests}`);
        console.log(`   Sucessos: ${this.results.passedTests}`);
        console.log(`   Falhas: ${this.results.failedTests}`);
        console.log(`   Taxa de sucesso: ${((this.results.passedTests / this.results.totalTests) * 100).toFixed(2)}%`);
        console.log(`   Tempo médio geral: ${(this.results.totalResponseTime / this.results.totalTests).toFixed(2)}ms`);
        
        console.log(`\n📈 Performance por Endpoint:`);
        Object.entries(this.results.endpoints).forEach(([endpoint, data]) => {
            console.log(`\n🔗 ${endpoint}`);
            console.log(`   ✅ Sucessos: ${data.successCount}/${data.successCount + data.errorCount}`);
            console.log(`   ⏱️  Tempo médio: ${data.avgResponseTime.toFixed(2)}ms`);
            console.log(`   ⚡ Range: ${data.minResponseTime.toFixed(2)}ms - ${data.maxResponseTime.toFixed(2)}ms`);
            console.log(`   📊 Throughput: ${data.throughput.toFixed(2)} req/s`);
        });
        
        // Análise de performance
        console.log(`\n🎯 Análise de Performance:`);
        const avgResponseTime = this.results.totalResponseTime / this.results.totalTests;
        
        if (avgResponseTime < 100) {
            console.log(`   🟢 EXCELENTE - Tempo de resposta médio muito bom (${avgResponseTime.toFixed(2)}ms)`);
        } else if (avgResponseTime < 300) {
            console.log(`   🟡 BOM - Tempo de resposta médio aceitável (${avgResponseTime.toFixed(2)}ms)`);
        } else if (avgResponseTime < 1000) {
            console.log(`   🟠 ATENÇÃO - Tempo de resposta médio alto (${avgResponseTime.toFixed(2)}ms)`);
        } else {
            console.log(`   🔴 CRÍTICO - Tempo de resposta médio muito alto (${avgResponseTime.toFixed(2)}ms)`);
        }
        
        const successRate = (this.results.passedTests / this.results.totalTests) * 100;
        if (successRate >= 99) {
            console.log(`   🟢 ESTABILIDADE EXCELENTE - ${successRate.toFixed(2)}% de sucesso`);
        } else if (successRate >= 95) {
            console.log(`   🟡 ESTABILIDADE BOA - ${successRate.toFixed(2)}% de sucesso`);
        } else {
            console.log(`   🔴 ESTABILIDADE CRÍTICA - ${successRate.toFixed(2)}% de sucesso`);
        }
    }
}

// Executar teste
async function runTest() {
    const tester = new PerformanceTest('http://localhost:8000');
    await tester.runFullApiTest();
}

if (require.main === module) {
    runTest().catch(console.error);
}

module.exports = PerformanceTest;