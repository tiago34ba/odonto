<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * TTL padrão para cache (1 hora)
     */
    const DEFAULT_TTL = 3600;

    /**
     * TTL para cache de longa duração (24 horas)
     */
    const LONG_TTL = 86400;

    /**
     * TTL para cache rápido (5 minutos)
     */
    const SHORT_TTL = 300;

    /**
     * Buscar ou criar cache
     */
    public static function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error("Erro no cache: {$e->getMessage()}");
            return $callback();
        }
    }

    /**
     * Cache específico para pacientes
     */
    public static function cachePatients($callback, int $page = 1, int $perPage = 10)
    {
        $key = "patients:page:{$page}:per_page:{$perPage}";
        return self::remember($key, $callback, self::SHORT_TTL);
    }

    /**
     * Cache específico para agendamentos
     */
    public static function cacheAppointments($callback, $date = null)
    {
        $dateKey = $date ? date('Y-m-d', strtotime($date)) : 'all';
        $key = "appointments:date:{$dateKey}";
        return self::remember($key, $callback, self::SHORT_TTL);
    }

    /**
     * Cache específico para relatórios
     */
    public static function cacheReports($callback, string $reportType, array $filters = [])
    {
        $filtersKey = md5(serialize($filters));
        $key = "reports:{$reportType}:filters:{$filtersKey}";
        return self::remember($key, $callback, self::LONG_TTL);
    }

    /**
     * Cache específico para procedimentos
     */
    public static function cacheProcedures($callback)
    {
        $key = "procedures:all";
        return self::remember($key, $callback, self::LONG_TTL);
    }

    /**
     * Cache específico para convênios
     */
    public static function cacheInsurances($callback)
    {
        $key = "insurances:all";
        return self::remember($key, $callback, self::LONG_TTL);
    }

    /**
     * Cache específico para funcionários
     */
    public static function cacheEmployees($callback)
    {
        $key = "employees:all";
        return self::remember($key, $callback, self::DEFAULT_TTL);
    }

    /**
     * Cache para estatísticas do dashboard
     */
    public static function cacheDashboardStats($callback)
    {
        $key = "dashboard:stats";
        return self::remember($key, $callback, self::SHORT_TTL);
    }

    /**
     * Invalidar cache específico
     */
    public static function forget(string $key): bool
    {
        try {
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar cache: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Invalidar cache por padrão
     */
    public static function forgetByPattern(string $pattern): bool
    {
        try {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                return Redis::del($keys) > 0;
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao invalidar cache por padrão: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Invalidar cache de pacientes
     */
    public static function forgetPatients(): bool
    {
        return self::forgetByPattern('patients:*');
    }

    /**
     * Invalidar cache de agendamentos
     */
    public static function forgetAppointments(): bool
    {
        return self::forgetByPattern('appointments:*');
    }

    /**
     * Invalidar cache de relatórios
     */
    public static function forgetReports(): bool
    {
        return self::forgetByPattern('reports:*');
    }

    /**
     * Invalidar cache de procedimentos
     */
    public static function forgetProcedures(): bool
    {
        return self::forget('procedures:all');
    }

    /**
     * Invalidar cache de convênios
     */
    public static function forgetInsurances(): bool
    {
        return self::forget('insurances:all');
    }

    /**
     * Invalidar cache de funcionários
     */
    public static function forgetEmployees(): bool
    {
        return self::forget('employees:all');
    }

    /**
     * Invalidar cache do dashboard
     */
    public static function forgetDashboard(): bool
    {
        return self::forget('dashboard:stats');
    }

    /**
     * Limpar todo o cache
     */
    public static function flush(): bool
    {
        try {
            return Cache::flush();
        } catch (\Exception $e) {
            Log::error("Erro ao limpar todo o cache: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Verificar se o Redis está conectado
     */
    public static function isRedisConnected(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            Log::error("Redis não conectado: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Obter informações do cache
     */
    public static function getCacheInfo(): array
    {
        try {
            return [
                'redis_connected' => self::isRedisConnected(),
                'cache_driver' => config('cache.default'),
                'redis_info' => Redis::info()
            ];
        } catch (\Exception $e) {
            return [
                'redis_connected' => false,
                'cache_driver' => config('cache.default'),
                'error' => $e->getMessage()
            ];
        }
    }
}