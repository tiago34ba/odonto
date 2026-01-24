const { performance } = require('perf_hooks');
const http = require('http');

class QuickPerformanceTest {
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

    async makeRequest(endpoint, method = 'GET') {
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

            const req = http.request(url, options, (res) => {
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
                        dataSize: responseData.length
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

            req.setTimeout(10000, () => {
                req.destroy();
                reject(new Error('Timeout'));
            });
            
            req.end();
        });
    }

    async sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async testEndpoint(endpoint, expectedStatus = 200, iterations = 3) {
        console.log(`\n🔗 Testando: ${endpoint}`);
        
        let successCount = 0;
        let errorCount = 0;
        let totalTime = 0;
        let minTime = Infinity;
        let maxTime = 0;
        let totalDataSize = 0;
        
        for (let i = 0; i < iterations; i++) {
            try {
                this.results.totalTests++;
                const result = await this.makeRequest(endpoint);
                const responseTime = result.responseTime;
                
                totalTime += responseTime;
                minTime = Math.min(minTime, responseTime);
                maxTime = Math.max(maxTime, responseTime);
                totalDataSize += result.dataSize;
                
                if (result.statusCode === expectedStatus) {
                    successCount++;
                    this.results.passedTests++;
                    console.log(`  ✅ Req ${i + 1}: ${responseTime.toFixed(0)}ms (${(result.dataSize / 1024).toFixed(1)}KB)`);
                } else {
                    errorCount++;
                    this.results.failedTests++;
                    console.log(`  ❌ Req ${i + 1}: Status ${result.statusCode} - ${responseTime.toFixed(0)}ms`);
                }
                
                // Delay entre requisições para evitar rate limit
                if (i < iterations - 1) {
                    await this.sleep(100);
                }
                
            } catch (error) {
                errorCount++;
                this.results.failedTests++;
                console.log(`  ❌ Req ${i + 1}: Erro - ${error.message || error.error}`);
            }
        }
        
        const avgTime = totalTime / iterations;
        const avgDataSize = totalDataSize / successCount || 0;
        this.results.totalResponseTime += totalTime;
        
        this.results.endpoints[endpoint] = {
            successCount,
            errorCount,
            avgResponseTime: avgTime,
            minResponseTime: minTime === Infinity ? 0 : minTime,
            maxResponseTime: maxTime,
            avgDataSize: avgDataSize,
            successRate: (successCount / iterations) * 100
        };
        
        console.log(`  📊 Resumo: ${successCount}/${iterations} sucessos, ${avgTime.toFixed(0)}ms médio`);
    }

    async runQuickTest() {
        console.log('🚀 TESTE RÁPIDO DE PERFORMANCE - BACKEND LARAVEL');
        console.log('='.repeat(55));
        
        const endpoints = [
            '/api/financeiro/dashboard',
            '/api/financeiro/contas-pagar',
            '/api/financeiro/contas-receber',
            '/api/financeiro/fluxo-caixa',
            '/api/financeiro/contas-pagar/pendentes',
            '/api/financeiro/contas-receber/pendentes',
            '/api/financeiro/contas-pagar/vencidas',
            '/api/financeiro/contas-receber/vencidas'
        ];
        
        for (const endpoint of endpoints) {
            await this.testEndpoint(endpoint, 200, 3);
        }
        
        this.generateQuickReport();
    }

    generateQuickReport() {
        console.log('\n📋 RELATÓRIO RÁPIDO DE PERFORMANCE');
        console.log('='.repeat(45));
        
        const avgResponseTime = this.results.totalResponseTime / this.results.totalTests;
        const successRate = (this.results.passedTests / this.results.totalTests) * 100;
        
        console.log(`📊 Resumo Geral:`);
        console.log(`   Testes executados: ${this.results.totalTests}`);
        console.log(`   Sucessos: ${this.results.passedTests} (${successRate.toFixed(1)}%)`);
        console.log(`   Falhas: ${this.results.failedTests}`);
        console.log(`   Tempo médio: ${avgResponseTime.toFixed(0)}ms`);
        
        console.log(`\n📈 Performance por Endpoint:`);
        
        // Ordenar endpoints por tempo de resposta
        const sortedEndpoints = Object.entries(this.results.endpoints)
            .sort(([,a], [,b]) => a.avgResponseTime - b.avgResponseTime);
        
        sortedEndpoints.forEach(([endpoint, data]) => {
            const status = data.avgResponseTime < 300 ? '🟢' : 
                         data.avgResponseTime < 1000 ? '🟡' : '🔴';
            
            console.log(`   ${status} ${endpoint}`);
            console.log(`      ⏱️  ${data.avgResponseTime.toFixed(0)}ms (${data.minResponseTime.toFixed(0)}-${data.maxResponseTime.toFixed(0)}ms)`);
            console.log(`      ✅ ${data.successRate.toFixed(0)}% sucesso`);
            console.log(`      📦 ${(data.avgDataSize / 1024).toFixed(1)}KB médio`);
        });
        
        // Análise geral
        console.log(`\n🎯 Análise Geral:`);
        if (successRate >= 95 && avgResponseTime < 500) {
            console.log(`   🟢 PERFORMANCE EXCELENTE - Sistema estável e rápido`);
        } else if (successRate >= 90 && avgResponseTime < 1000) {
            console.log(`   🟡 PERFORMANCE BOA - Sistema funcionando adequadamente`);
        } else {
            console.log(`   🔴 ATENÇÃO - Performance ou estabilidade comprometida`);
        }
        
        console.log(`\n💡 Recomendações:`);
        if (avgResponseTime > 1000) {
            console.log(`   🔧 Otimizar queries do banco de dados`);
            console.log(`   🔧 Implementar cache para endpoints lentos`);
        }
        if (successRate < 95) {
            console.log(`   🔧 Investigar erros de rate limiting`);
            console.log(`   🔧 Verificar configuração do servidor`);
        }
        if (avgResponseTime < 500 && successRate >= 95) {
            console.log(`   ✅ Sistema bem otimizado - manter monitoramento`);
        }
    }
}

// Executar teste
async function runQuickTest() {
    const tester = new QuickPerformanceTest('http://localhost:8000');
    await tester.runQuickTest();
}

if (require.main === module) {
    runQuickTest().catch(console.error);
}

module.exports = QuickPerformanceTest;