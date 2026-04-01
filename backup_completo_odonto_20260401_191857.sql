-- Dump completo do banco odonto
-- Gerado em 2026-04-01 19:18:57

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- ----------------------------
-- Estrutura da tabela `acessos`
-- ----------------------------
DROP TABLE IF EXISTS `acessos`;
CREATE TABLE `acessos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Geral',
  `nivel_risco` enum('baixo','medio','alto','critico') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baixo',
  `sistema_interno` tinyint(1) NOT NULL DEFAULT '1',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `grupo_acesso_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acessos_codigo_unique` (`codigo`),
  KEY `acessos_grupo_acesso_id_foreign` (`grupo_acesso_id`),
  KEY `acessos_categoria_ativo_index` (`categoria`,`ativo`),
  KEY `acessos_nivel_risco_index` (`nivel_risco`),
  CONSTRAINT `acessos_grupo_acesso_id_foreign` FOREIGN KEY (`grupo_acesso_id`) REFERENCES `grupo_acessos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `acessos`
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (1, 'Acesso API Senior', 'integracao.api.senior', 'Acesso criado por teste', 'Integracao', 'medio', 1, 1, NULL, '2026-03-18 03:49:22', '2026-03-18 03:49:22');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (2, 'Dashboard - Visualizar', 'dashboard.view', 'Permite visualizar indicadores do dashboard.', 'Dashboard', 'baixo', 1, 1, 16, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (3, 'Pacientes - Gerenciar', 'paciente.manage', 'Permite criar, editar e excluir pacientes.', 'Pacientes', 'medio', 1, 1, 15, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (4, 'Agendamentos - Gerenciar', 'agendamento.manage', 'Permite gerenciar agenda e confirmacoes.', 'Agenda', 'medio', 1, 1, 16, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (5, 'Financeiro - Gerenciar', 'financeiro.manage', 'Permite alterar contas e movimentos financeiros.', 'Financeiro', 'alto', 1, 1, 15, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (6, 'Relatorios - Visualizar', 'relatorio.view', 'Permite visualizar relatorios operacionais e gerenciais.', 'Relatorios', 'baixo', 1, 1, 15, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (7, 'Usuarios - Gerenciar', 'usuario.manage', 'Permite gerenciar usuarios e vinculo com grupos.', 'Seguranca', 'critico', 1, 1, 15, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `acessos` (`id`, `nome`, `codigo`, `descricao`, `categoria`, `nivel_risco`, `sistema_interno`, `ativo`, `grupo_acesso_id`, `created_at`, `updated_at`) VALUES (8, 'SaaS - Administracao Geral', 'saas.admin', 'Permite administrar assinaturas, planos e clientes do SaaS.', 'SaaS', 'critico', 1, 1, 14, '2026-04-01 18:54:57', '2026-04-01 18:54:57');

-- ----------------------------
-- Estrutura da tabela `agreements`
-- ----------------------------
DROP TABLE IF EXISTS `agreements`;
CREATE TABLE `agreements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato_responsavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clinic_commission` decimal(5,2) DEFAULT NULL,
  `desconto_percentual` decimal(5,2) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_agreements_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `anamneses`
-- ----------------------------
DROP TABLE IF EXISTS `anamneses`;
CREATE TABLE `anamneses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_resposta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sim/Não',
  `obrigatorio` tinyint(1) NOT NULL DEFAULT '0',
  `opcoes_resposta` json DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `cache`
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `cache`
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_.portal.20260331190337@odonto.local|127.0.0.1', 'i:1;', 1774987210);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_.portal.20260331190337@odonto.local|127.0.0.1:timer', 'i:1774987210;', 1774987210);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_1fd8e4cfeb25c473c174cced2fcfdfb7', 'i:1;', 1774911692);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_1fd8e4cfeb25c473c174cced2fcfdfb7:timer', 'i:1774911692;', 1774911692);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_21c7ea48997eeecf541f9afb4a8bfc81', 'i:1;', 1774911130);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_21c7ea48997eeecf541f9afb4a8bfc81:timer', 'i:1774911130;', 1774911130);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_4d8b073a6b178d1072df30ab78f1cc22b6ceef9d', 'i:1;', 1774911627);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_4d8b073a6b178d1072df30ab78f1cc22b6ceef9d:timer', 'i:1774911627;', 1774911627);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_7c2150d7106073168321f39ec452420d', 'i:2;', 1774993234);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_7c2150d7106073168321f39ec452420d:timer', 'i:1774993234;', 1774993234);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_7f3072bf378b98d6bbf2f013cff3e287', 'i:2;', 1775066481);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_7f3072bf378b98d6bbf2f013cff3e287:timer', 'i:1775066481;', 1775066481);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_a75f3f172bfb296f2e10cbfc6dfc1883', 'i:1;', 1775067812);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_a75f3f172bfb296f2e10cbfc6dfc1883:timer', 'i:1775067812;', 1775067812);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_a913c90313e9e20b6fd8b985a6ec4d51719ed540', 'i:1;', 1774991097);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_a913c90313e9e20b6fd8b985a6ec4d51719ed540:timer', 'i:1774991097;', 1774991097);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_d2bfa8e8b749d2772a21edee7b70a2b3', 'i:2;', 1775067248);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_d2bfa8e8b749d2772a21edee7b70a2b3:timer', 'i:1775067248;', 1775067248);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_dashboard:local:appointments_stats', 'a:2:{s:23:\"appointments_statistics\";a:4:{s:5:\"today\";a:3:{s:5:\"total\";i:1;s:9:\"completed\";i:0;s:7:\"pending\";i:0;}s:9:\"this_week\";a:2:{s:5:\"total\";i:1;s:6:\"by_day\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:3:\"day\";s:7:\"Tuesday\";s:5:\"total\";i:1;}s:11:\"\0*\0original\";a:2:{s:3:\"day\";s:7:\"Tuesday\";s:5:\"total\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"this_month\";a:2:{s:5:\"total\";i:16;s:7:\"by_week\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:3:{i:0;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:4:\"week\";i:12;s:5:\"total\";i:14;}s:11:\"\0*\0original\";a:2:{s:4:\"week\";i:12;s:5:\"total\";i:14;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:4:\"week\";i:11;s:5:\"total\";i:1;}s:11:\"\0*\0original\";a:2:{s:4:\"week\";i:11;s:5:\"total\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:4:\"week\";i:13;s:5:\"total\";i:1;}s:11:\"\0*\0original\";a:2:{s:4:\"week\";i:13;s:5:\"total\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:19:\"status_distribution\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:8:\"canceled\";s:5:\"total\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:8:\"canceled\";s:5:\"total\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:1;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:9:\"completed\";s:5:\"total\";i:8;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:9:\"completed\";s:5:\"total\";i:8;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:2;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:9:\"confirmed\";s:5:\"total\";i:5;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:9:\"confirmed\";s:5:\"total\";i:5;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:3;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:11:\"in_progress\";s:5:\"total\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:11:\"in_progress\";s:5:\"total\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}i:4;O:21:\"App\\Models\\Scheduling\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:11:\"schedulings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:2:{s:6:\"status\";s:9:\"scheduled\";s:5:\"total\";i:1;}s:11:\"\0*\0original\";a:2:{s:6:\"status\";s:9:\"scheduled\";s:5:\"total\";i:1;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:6:{s:4:\"date\";s:4:\"date\";s:12:\"scheduled_at\";s:8:\"datetime\";s:12:\"confirmed_at\";s:8:\"datetime\";s:11:\"canceled_at\";s:8:\"datetime\";s:6:\"return\";s:7:\"boolean\";s:8:\"duration\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:13:{i:0;s:10:\"patient_id\";i:1;s:15:\"professional_id\";i:2;s:12:\"procedure_id\";i:3;s:4:\"date\";i:4;s:4:\"time\";i:5;s:6:\"return\";i:6;s:3:\"obs\";i:7;s:6:\"status\";i:8;s:12:\"scheduled_at\";i:9;s:8:\"duration\";i:10;s:12:\"confirmed_at\";i:11;s:11:\"canceled_at\";i:12;s:19:\"cancellation_reason\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:12:\"generated_at\";s:27:\"2026-03-31T21:58:19.637711Z\";}', 1774994359);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_ddb1b9ec7844ef9cfda4a92d9babf754', 'i:1;', 1775071171);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_ddb1b9ec7844ef9cfda4a92d9babf754:timer', 'i:1775071171;', 1775071171);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_de226f3f5dc0c66a464effdc07ca6b1f', 'i:15;', 1775070643);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_de226f3f5dc0c66a464effdc07ca6b1f:timer', 'i:1775070643;', 1775070643);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_e74635d2595fdd6cd38901753cf0694bcc7f6527', 'i:1;', 1775071171);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_e74635d2595fdd6cd38901753cf0694bcc7f6527:timer', 'i:1775071171;', 1775071171);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_f1f70ec40aaa556905d4a030501c0ba4', 'i:2;', 1774910322);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_f1f70ec40aaa556905d4a030501c0ba4:timer', 'i:1774910321;', 1774910322);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_portal-login:paciente.portal.20260331190337@odonto.local|127.0.0.1', 'i:1;', 1774988303);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_portal-login:paciente.portal.20260331190337@odonto.local|127.0.0.1:timer', 'i:1774988303;', 1774988303);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_portal.20260331190337@odonto.local|127.0.0.1', 'i:1;', 1774987446);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_portal.20260331190337@odonto.local|127.0.0.1:timer', 'i:1774987446;', 1774987446);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_saas_admin|127.0.0.1', 'i:3;', 1775065782);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_saas_admin|127.0.0.1:timer', 'i:1775065782;', 1775065782);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_saas.odonto|127.0.0.1', 'i:1;', 1775065756);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES ('laravel_cache_saas.odonto|127.0.0.1:timer', 'i:1775065756;', 1775065756);

-- ----------------------------
-- Estrutura da tabela `cache_locks`
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `cargos`
-- ----------------------------
DROP TABLE IF EXISTS `cargos`;
CREATE TABLE `cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `nivel_acesso` enum('baixo','medio','alto','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baixo',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cargos_nome_unique` (`nome`),
  KEY `cargos_ativo_nivel_acesso_index` (`ativo`,`nivel_acesso`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `cargos`
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (1, 'Cargo API Senior', 'Teste integracao frontend-backend', 'alto', 1, '2026-03-18 03:49:06', '2026-03-18 03:49:06');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (2, 'Dentista Clínico Geral', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (3, 'Ortodontista', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (4, 'Endodontista', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (5, 'Periodontista', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (6, 'Implantodontista', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (7, 'Cirurgião Bucomaxilofacial', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (8, 'Odontopediatra', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (9, 'Prótese Dentária', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (10, 'ASB (Auxiliar de Saúde Bucal)', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (11, 'TSB (Técnico em Saúde Bucal)', NULL, 'baixo', 1, '2026-03-24 22:53:26', '2026-03-24 22:53:26');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (12, 'Recepcionista', NULL, 'baixo', 1, '2026-03-24 22:53:27', '2026-03-24 22:53:27');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (13, 'Gerente de Clínica', NULL, 'baixo', 1, '2026-03-24 22:53:27', '2026-03-24 22:53:27');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (14, 'Administrativo/Financeiro', NULL, 'baixo', 1, '2026-03-24 22:53:27', '2026-03-24 22:53:27');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (15, 'Auxiliar de Limeza', NULL, 'baixo', 1, '2026-03-24 22:55:47', '2026-03-24 22:55:47');
INSERT INTO `cargos` (`id`, `nome`, `descricao`, `nivel_acesso`, `ativo`, `created_at`, `updated_at`) VALUES (16, 'Supervissor', NULL, 'baixo', 1, '2026-03-24 22:55:47', '2026-03-24 22:55:47');

-- ----------------------------
-- Estrutura da tabela `contas_pagars`
-- ----------------------------
DROP TABLE IF EXISTS `contas_pagars`;
CREATE TABLE `contas_pagars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_original` decimal(10,2) NOT NULL,
  `valor_pago` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_pendente` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('Pendente','Vencido','Pago','Parcial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `prioridade` enum('Baixa','Média','Alta','Crítica') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Média',
  `forma_pagamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contas_pagars_codigo_unique` (`codigo`),
  KEY `contas_pagars_supplier_id_foreign` (`supplier_id`),
  KEY `contas_pagars_status_data_vencimento_index` (`status`,`data_vencimento`),
  CONSTRAINT `contas_pagars_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `contas_recebers`
-- ----------------------------
DROP TABLE IF EXISTS `contas_recebers`;
CREATE TABLE `contas_recebers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paciente_id` bigint unsigned NOT NULL,
  `procedure_id` bigint unsigned NOT NULL,
  `scheduling_id` bigint unsigned DEFAULT NULL,
  `categoria` enum('Consulta','Limpeza','Restauração','Endodontia','Ortodontia','Cirurgia','Prótese','Implante','Clareamento','Outros') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Consulta',
  `valor_original` decimal(10,2) NOT NULL,
  `valor_recebido` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_pendente` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_recebimento` date DEFAULT NULL,
  `status` enum('Pendente','Vencido','Recebido','Parcial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendente',
  `prioridade` enum('Baixa','Média','Alta','Crítica') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Média',
  `forma_pagamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `convenio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contas_recebers_codigo_unique` (`codigo`),
  KEY `contas_recebers_paciente_id_foreign` (`paciente_id`),
  KEY `contas_recebers_procedure_id_foreign` (`procedure_id`),
  KEY `contas_recebers_scheduling_id_foreign` (`scheduling_id`),
  KEY `contas_recebers_status_data_vencimento_index` (`status`,`data_vencimento`),
  CONSTRAINT `contas_recebers_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contas_recebers_procedure_id_foreign` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contas_recebers_scheduling_id_foreign` FOREIGN KEY (`scheduling_id`) REFERENCES `schedulings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `employees`
-- ----------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `hire_date` date DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `salary` decimal(10,2) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_email_unique` (`email`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `idx_employees_name` (`name`),
  KEY `idx_employees_email` (`email`),
  KEY `idx_employees_role` (`role`),
  KEY `idx_employees_active` (`active`),
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `employees`
INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `role`, `photo`, `cro`, `specialty`, `active`, `hire_date`, `birth_date`, `address`, `salary`, `commission_rate`, `created_at`, `updated_at`, `user_id`) VALUES (1, 'Dr. João Silva', '(11) 98765-4321', 'joao.silva@odonto.com', 'Dentista', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-03-27 03:22:10', '2026-03-27 03:22:10', NULL);
INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `role`, `photo`, `cro`, `specialty`, `active`, `hire_date`, `birth_date`, `address`, `salary`, `commission_rate`, `created_at`, `updated_at`, `user_id`) VALUES (2, 'Dra. Maria Santos', '(11) 98765-4322', 'maria.santos@odonto.com', 'Ortodontista', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-03-27 03:22:10', '2026-03-27 03:22:10', NULL);
INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `role`, `photo`, `cro`, `specialty`, `active`, `hire_date`, `birth_date`, `address`, `salary`, `commission_rate`, `created_at`, `updated_at`, `user_id`) VALUES (3, 'Carlos Oliveira', '(11) 98765-4323', 'carlos.oliveira@odonto.com', 'Recepcionista', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-03-27 03:22:10', '2026-03-27 03:22:10', NULL);
INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `role`, `photo`, `cro`, `specialty`, `active`, `hire_date`, `birth_date`, `address`, `salary`, `commission_rate`, `created_at`, `updated_at`, `user_id`) VALUES (4, 'Ana Costa', '(11) 98765-4324', 'ana.costa@odonto.com', 'Auxiliar de Enfermagem', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-03-27 03:22:10', '2026-03-27 03:22:10', NULL);
INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `role`, `photo`, `cro`, `specialty`, `active`, `hire_date`, `birth_date`, `address`, `salary`, `commission_rate`, `created_at`, `updated_at`, `user_id`) VALUES (5, 'Dr. Pedro Ferreira', '(11) 98765-4325', 'pedro.ferreira@odonto.com', 'Cirurgião Dentista', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-03-27 03:22:10', '2026-03-27 03:22:10', NULL);
INSERT INTO `employees` (`id`, `name`, `phone`, `email`, `role`, `photo`, `cro`, `specialty`, `active`, `hire_date`, `birth_date`, `address`, `salary`, `commission_rate`, `created_at`, `updated_at`, `user_id`) VALUES (6, 'Lucia Mendes', '(11) 98765-4326', 'lucia.mendes@odonto.com', 'Higienista Dental', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-03-27 03:22:10', '2026-03-27 03:22:10', NULL);

-- ----------------------------
-- Estrutura da tabela `failed_jobs`
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `fluxo_caixas`
-- ----------------------------
DROP TABLE IF EXISTS `fluxo_caixas`;
CREATE TABLE `fluxo_caixas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('Entrada','Saída') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_movimento` date NOT NULL,
  `forma_pagamento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conta_receber_id` bigint unsigned DEFAULT NULL,
  `conta_pagar_id` bigint unsigned DEFAULT NULL,
  `paciente_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fluxo_caixas_conta_receber_id_foreign` (`conta_receber_id`),
  KEY `fluxo_caixas_conta_pagar_id_foreign` (`conta_pagar_id`),
  KEY `fluxo_caixas_paciente_id_foreign` (`paciente_id`),
  KEY `fluxo_caixas_supplier_id_foreign` (`supplier_id`),
  KEY `fluxo_caixas_tipo_data_movimento_index` (`tipo`,`data_movimento`),
  CONSTRAINT `fluxo_caixas_conta_pagar_id_foreign` FOREIGN KEY (`conta_pagar_id`) REFERENCES `contas_pagars` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fluxo_caixas_conta_receber_id_foreign` FOREIGN KEY (`conta_receber_id`) REFERENCES `contas_recebers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fluxo_caixas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fluxo_caixas_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `forma_pagamentos`
-- ----------------------------
DROP TABLE IF EXISTS `forma_pagamentos`;
CREATE TABLE `forma_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3498db',
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taxa` decimal(8,2) NOT NULL DEFAULT '0.00',
  `taxa_juros` decimal(5,2) NOT NULL DEFAULT '0.00',
  `parcelas_max` int NOT NULL DEFAULT '1',
  `dias_vencimento` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `aceita_parcelamento` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_forma_pagamentos_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `funcionarios`
-- ----------------------------
DROP TABLE IF EXISTS `funcionarios`;
CREATE TABLE `funcionarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_id` bigint unsigned DEFAULT NULL,
  `data_cadastro` date DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cro` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intervalo` int DEFAULT NULL COMMENT 'Intervalo entre atendimentos em minutos',
  `comissao` decimal(5,2) DEFAULT NULL COMMENT 'Percentual de comissão',
  `chave_pix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'true = ativo, false = inativo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funcionarios_email_unique` (`email`),
  KEY `funcionarios_status_name_index` (`status`,`name`),
  KEY `funcionarios_cargo_id_index` (`cargo_id`),
  CONSTRAINT `funcionarios_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `funcionarios`
INSERT INTO `funcionarios` (`id`, `name`, `telefone`, `email`, `cargo_id`, `data_cadastro`, `foto`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cro`, `intervalo`, `comissao`, `chave_pix`, `status`, `created_at`, `updated_at`) VALUES (1, 'Dr. Joao Silva', 11999998888, 'joao.silva@clinica.com', NULL, '2000-01-01', NULL, '01310100', 'Rua das Flores', 100, NULL, 'Centro', 'Sao Paulo', 'SP', 'SP-12345', 30, 15.50, 'joao.silva@clinica.com', 1, '2026-03-24 21:32:17', '2026-03-26 20:24:35');
INSERT INTO `funcionarios` (`id`, `name`, `telefone`, `email`, `cargo_id`, `data_cadastro`, `foto`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cro`, `intervalo`, `comissao`, `chave_pix`, `status`, `created_at`, `updated_at`) VALUES (2, 'Carla Martins', 7121320, 'tiago2008.1@hotmail.com', NULL, '2026-03-24', NULL, 41150120, NULL, NULL, NULL, 'Cabula', 'Salvador', 'BA', NULL, 30, 0.00, NULL, 1, '2026-03-24 22:56:25', '2026-03-24 22:56:25');
INSERT INTO `funcionarios` (`id`, `name`, `telefone`, `email`, `cargo_id`, `data_cadastro`, `foto`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cro`, `intervalo`, `comissao`, `chave_pix`, `status`, `created_at`, `updated_at`) VALUES (3, 'Bruna', 71986660020, 'teste@example.com', 7, '2026-03-24', NULL, 41000000, 'Rua 2', 2, 'Sala 2', 'Bairro 2', 'Feira de Santana', 'BA', '123-45', 30, 0.50, NULL, 1, '2026-03-24 23:11:36', '2026-03-24 23:11:36');

-- ----------------------------
-- Estrutura da tabela `group_anamneses`
-- ----------------------------
DROP TABLE IF EXISTS `group_anamneses`;
CREATE TABLE `group_anamneses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `cor` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3498db',
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `grupo_acessos`
-- ----------------------------
DROP TABLE IF EXISTS `grupo_acessos`;
CREATE TABLE `grupo_acessos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `cor` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `permissoes` json DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_acessos_nome_unique` (`nome`),
  KEY `grupo_acessos_ativo_index` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `grupo_acessos`
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (1, 'Grupo API Senior', 'Grupo criado por teste', '#1D4ED8', '[\"usuarios.view\", \"pacientes.create\"]', 1, '2026-03-18 03:49:13', '2026-03-18 03:49:13');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (2, 'Grupo Teste 20260326213306', 'Grupo para validar login', '#2563EB', '[\"DASHBOARD_VIEW\", \"USERS_MANAGE\", \"payment_access\", \"pix_access\"]', 1, '2026-03-26 21:33:07', '2026-03-30 22:37:19');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (3, 'Grupo Teste Login', 'Grupo para testes de login', '#2563EB', '[\"DASHBOARD_VIEW\", \"USERS_MANAGE\"]', 1, '2026-03-26 21:42:08', '2026-03-26 21:42:08');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (4, 'Administrador', 'Grupo criado automaticamente pelo modulo Usuarios', '#2563EB', '[\"*\"]', 1, '2026-03-26 22:00:52', '2026-03-26 22:01:13');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (5, 'Dentista', 'Grupo criado automaticamente pelo modulo Usuarios', '#2563EB', '[\"DASHBOARD_VIEW\", \"PATIENTS_VIEW\", \"PATIENTS_MANAGE\", \"SCHEDULINGS_VIEW\", \"PROCEDURES_MANAGE\", \"ODONTOGRAM_VIEW\", \"ODONTOGRAM_MANAGE\", \"TREATMENTS_MANAGE\"]', 1, '2026-03-26 22:00:53', '2026-03-26 22:01:13');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (6, 'Secretária', 'Grupo criado automaticamente pelo modulo Usuarios', '#2563EB', '[\"DASHBOARD_VIEW\", \"PATIENTS_VIEW\", \"PATIENTS_MANAGE\", \"SCHEDULINGS_VIEW\", \"SCHEDULINGS_MANAGE\", \"FINANCE_RECEIVABLE_VIEW\"]', 1, '2026-03-26 22:00:53', '2026-03-26 22:01:13');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (7, 'Auxiliar Dentista', 'Grupo criado automaticamente pelo modulo Usuarios', '#2563EB', '[\"DASHBOARD_VIEW\", \"PATIENTS_VIEW\", \"SCHEDULINGS_VIEW\", \"ODONTOGRAM_VIEW\", \"TREATMENTS_ASSIST\"]', 1, '2026-03-26 22:00:53', '2026-03-26 22:01:13');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (8, 'Faxineiro', 'Grupo criado automaticamente pelo modulo Usuarios', '#2563EB', '[\"DASHBOARD_VIEW\", \"TASKS_VIEW\"]', 1, '2026-03-26 22:00:54', '2026-03-26 22:01:13');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (9, 'Financeiro', 'Grupo financeiro criado pelo modulo Usuarios', '#0EA5E9', '[\"DASHBOARD_VIEW\", \"FINANCE_DASHBOARD_VIEW\", \"FINANCE_PAYABLE_VIEW\", \"FINANCE_PAYABLE_MANAGE\", \"FINANCE_RECEIVABLE_VIEW\", \"FINANCE_RECEIVABLE_MANAGE\", \"FINANCE_CASHFLOW_VIEW\", \"FINANCE_REPORTS_VIEW\"]', 1, '2026-03-26 22:24:59', '2026-03-26 22:24:59');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (14, 'SaaS Admin', 'Perfil com administracao completa do portal SaaS.', '#1D4ED8', '[\"*\"]', 1, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (15, 'Admin Clinica', 'Gestao completa da clinica e equipe.', '#0F766E', '[\"dashboard.view\", \"paciente.manage\", \"agendamento.manage\", \"financeiro.manage\", \"relatorio.view\", \"usuario.manage\"]', 1, '2026-04-01 18:54:57', '2026-04-01 18:54:57');
INSERT INTO `grupo_acessos` (`id`, `nome`, `descricao`, `cor`, `permissoes`, `ativo`, `created_at`, `updated_at`) VALUES (16, 'Operacional', 'Acesso operacional para agenda e atendimento.', '#7C3AED', '[\"dashboard.view\", \"paciente.view\", \"agendamento.manage\", \"relatorio.view\"]', 1, '2026-04-01 18:54:57', '2026-04-01 18:54:57');

-- ----------------------------
-- Estrutura da tabela `job_batches`
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `jobs`
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `migrations`
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4, '2025_03_31_185206_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5, '2025_04_15_184013_create_pacientes_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6, '2025_05_10_204152_create_employees_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7, '2025_05_23_050700_create_schedulings_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8, '2025_05_23_190453_create_procedures_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9, '2025_05_23_213817_create_agreements_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10, '2025_05_27_200850_create_anamneses_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11, '2025_05_28_010638_create_group_anamneses_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12, '2025_05_28_013218_create_forma_pagamentos_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13, '2025_10_16_202655_add_enhanced_fields_to_existing_tables', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14, '2025_10_16_204007_create_odontogramas_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15, '2025_10_16_204213_create_treatment_plans_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16, '2025_11_10_000002_add_corrected_performance_indexes', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17, '2025_11_11_221810_add_cidade_to_pacientes_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18, '2025_11_12_020840_create_suppliers_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19, '2025_11_12_023106_create_reports_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20, '2025_11_12_023932_create_contas_pagars_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21, '2025_11_12_023953_create_contas_recebers_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22, '2025_11_12_024016_create_fluxo_caixas_table', 1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23, '2026_03_18_003500_create_cargos_table', 2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24, '2026_03_18_003510_create_grupo_acessos_table', 2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25, '2026_03_18_003520_create_acessos_table', 2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26, '2026_03_24_000001_create_funcionarios_table', 3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27, '2026_03_26_120000_add_fornecedor_fields_to_suppliers_table', 4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28, '2026_03_26_220000_add_access_fields_to_users_table', 5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29, '2026_03_26_230000_add_extra_fields_to_agreements_table', 6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30, '2026_03_26_230001_add_extra_fields_to_forma_pagamentos_table', 6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31, '2026_03_26_230002_add_extra_fields_to_group_anamneses_table', 6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32, '2026_03_26_230003_add_extra_fields_to_anamneses_table', 6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33, '2026_03_27_000001_add_status_columns_to_schedulings_table', 7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34, '2026_03_27_000002_add_business_unique_index_to_schedulings_table', 8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35, '2026_03_31_100000_add_portal_support_to_users_and_pacientes', 9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36, '2026_03_31_210000_create_saas_mensalidades_table', 10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37, '2026_03_31_220000_create_saas_solicitacoes_table', 11);

-- ----------------------------
-- Estrutura da tabela `odontogramas`
-- ----------------------------
DROP TABLE IF EXISTS `odontogramas`;
CREATE TABLE `odontogramas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `dentes` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacoes_gerais` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `odontogramas_paciente_id_unique` (`paciente_id`),
  CONSTRAINT `odontogramas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `pacientes`
-- ----------------------------
DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE `pacientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `convenio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idade` int DEFAULT NULL,
  `data_nascimento` date NOT NULL,
  `responsavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf_responsavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profissao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_sanguineo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pessoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf_cnpj` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `audit_trail` json DEFAULT NULL,
  `data_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pacientes_cpf_cnpj_unique` (`cpf_cnpj`),
  UNIQUE KEY `pacientes_email_unique` (`email`),
  KEY `idx_pacientes_name` (`name`),
  KEY `idx_pacientes_email` (`email`),
  KEY `idx_pacientes_telefone` (`telefone`),
  KEY `idx_pacientes_cpf_cnpj` (`cpf_cnpj`),
  KEY `idx_pacientes_created_at` (`created_at`),
  KEY `idx_pacientes_updated_at` (`updated_at`),
  KEY `pacientes_user_id_foreign` (`user_id`),
  CONSTRAINT `pacientes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `pacientes`
INSERT INTO `pacientes` (`id`, `user_id`, `name`, `convenio`, `telefone`, `idade`, `data_nascimento`, `responsavel`, `cpf_responsavel`, `celular`, `estado`, `sexo`, `profissao`, `estado_civil`, `tipo_sanguineo`, `pessoa`, `cpf_cnpj`, `email`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `observacoes`, `audit_trail`, `data_source`, `created_at`, `updated_at`) VALUES (2, NULL, 'Paulo Tiago Magalhães', 'São Cristovão', 71990000000, 43, '1982-07-08', 'Responsavel 1', '222.222.222-22', '', 'BA', 'Masculino', 'Desenvolvedor', 'Solteiro(a)', 'O+', 'Física', '000.000.000-00', 'paulo.tiago.fake@exemplo.com', '41830-200', 'Rua das Acacias', 1234, 'Casa 02', 'Vila Nova', 'Salvador', 'Teste no Banco odonto do laravel via API REST', NULL, 'manual', '2026-03-24 20:24:36', '2026-03-29 04:28:02');
INSERT INTO `pacientes` (`id`, `user_id`, `name`, `convenio`, `telefone`, `idade`, `data_nascimento`, `responsavel`, `cpf_responsavel`, `celular`, `estado`, `sexo`, `profissao`, `estado_civil`, `tipo_sanguineo`, `pessoa`, `cpf_cnpj`, `email`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `observacoes`, `audit_trail`, `data_source`, `created_at`, `updated_at`) VALUES (4, NULL, 'Luciana', 'Bradesco Saúde', 71986660020, 81, '1944-07-08', 'Responsavel 3', 11111111107, 7198774411, 'BA', 'Feminino', 'Enfermeira', 'Solteiro(a)', 'O+', 'Física', 44444544444, 'mateus2@example.com', 41000000, 'Rua 2', 2, 'Sala 2', 'Bairro 2', NULL, 'novo paciente', NULL, 'manual', '2026-03-24 20:35:50', '2026-03-24 20:35:50');
INSERT INTO `pacientes` (`id`, `user_id`, `name`, `convenio`, `telefone`, `idade`, `data_nascimento`, `responsavel`, `cpf_responsavel`, `celular`, `estado`, `sexo`, `profissao`, `estado_civil`, `tipo_sanguineo`, `pessoa`, `cpf_cnpj`, `email`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `observacoes`, `audit_trail`, `data_source`, `created_at`, `updated_at`) VALUES (5, NULL, 'Maria Silva', 'ASSIM Saúde', 7121320269, 25, '2001-01-01', 'Responsavel 3', 33333333333, 71986660021, 'BA', 'Masculino', 'Enfermeira', 'Solteiro(a)', 'A+', 'Jurídica', 12222222000127, 'teste@example.com', 41000000, 'Rua 2', 2, 'Sala 2', 'Bairro 2', NULL, 'novo paciente', NULL, 'manual', '2026-03-27 22:55:48', '2026-03-27 22:55:48');
INSERT INTO `pacientes` (`id`, `user_id`, `name`, `convenio`, `telefone`, `idade`, `data_nascimento`, `responsavel`, `cpf_responsavel`, `celular`, `estado`, `sexo`, `profissao`, `estado_civil`, `tipo_sanguineo`, `pessoa`, `cpf_cnpj`, `email`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `observacoes`, `audit_trail`, `data_source`, `created_at`, `updated_at`) VALUES (6, NULL, 'Luciana Marques', 'Hapvida', 7121320269, 25, '2000-07-26', 'Maria', 11111111111, 71996285453, 'BA', 'Masculino', 'Designer', 'Solteiro(a)', 'AB+', 'Jurídica', 22222222000170, 'Lu@example.com', 41000000, 'Rua 2', 2, 'Sala 2', 'Bairro 2', NULL, 'paciente retornado', NULL, 'manual', '2026-03-28 03:54:25', '2026-03-28 03:54:25');
INSERT INTO `pacientes` (`id`, `user_id`, `name`, `convenio`, `telefone`, `idade`, `data_nascimento`, `responsavel`, `cpf_responsavel`, `celular`, `estado`, `sexo`, `profissao`, `estado_civil`, `tipo_sanguineo`, `pessoa`, `cpf_cnpj`, `email`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `observacoes`, `audit_trail`, `data_source`, `created_at`, `updated_at`) VALUES (7, 13, 'Paciente Portal Demo', NULL, 11999990000, NULL, '1990-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'paciente.portal.20260331190337@odonto.local', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'manual', '2026-03-31 19:03:37', '2026-03-31 19:03:37');

-- ----------------------------
-- Estrutura da tabela `password_reset_tokens`
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `patient_procedures`
-- ----------------------------
DROP TABLE IF EXISTS `patient_procedures`;
CREATE TABLE `patient_procedures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `procedure_id` bigint unsigned NOT NULL,
  `scheduling_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `value` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_procedures_procedure_id_foreign` (`procedure_id`),
  KEY `patient_procedures_scheduling_id_foreign` (`scheduling_id`),
  KEY `patient_procedures_paciente_id_procedure_id_index` (`paciente_id`,`procedure_id`),
  CONSTRAINT `patient_procedures_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_procedures_procedure_id_foreign` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_procedures_scheduling_id_foreign` FOREIGN KEY (`scheduling_id`) REFERENCES `schedulings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `personal_access_tokens`
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `personal_access_tokens`
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (3, 'App\\Models\\User', 1, 'verify-users-api', '2b26d04a3b7391b8bf4df429f6e4a898c1a469e5e11afa4056da5ed4bb6c749c', '[\"*\"]', '2026-03-26 21:39:37', NULL, '2026-03-26 21:39:36', '2026-03-26 21:39:37');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (16, 'App\\Models\\User', 2, 'dashboard-odonto-web', 'fd5e5d6cb227cc7902beda126917b2f4fca83adbc78150bfc59ee6bf9788cdde', '[\"*\"]', '2026-03-26 22:00:06', NULL, '2026-03-26 21:59:58', '2026-03-26 22:00:06');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (21, 'App\\Models\\User', 7, 'validate-level-user', 'c0e783a44138708275e53f5d193a32304b1334395a7acadd06361951e516fd37', '[\"*\"]', NULL, NULL, '2026-03-26 22:01:36', '2026-03-26 22:01:36');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (50, 'App\\Models\\User', 8, 'dashboard-odonto-web', '1f307fb0cdbef40f20888b4d9c98c5cd710f186b1a3cd3f64213a4344713865b', '[\"*\"]', '2026-03-31 21:40:13', NULL, '2026-03-31 20:06:51', '2026-03-31 21:40:13');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (61, 'App\\Models\\User', 14, 'dashboard-odonto-saas-admin', '36762593f19e329bbe7c3dcde7404622d6d6dff33ee218347d268e12e20311c8', '[\"*\"]', '2026-04-01 19:10:17', NULL, '2026-04-01 17:47:38', '2026-04-01 19:10:17');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (64, 'App\\Models\\User', 13, 'dashboard-odonto-web', '52f910095d860a963260e30c15b280bf3d01c7520e503a34c4c403fd368ed9e1', '[\"*\"]', '2026-04-01 18:00:22', NULL, '2026-04-01 17:59:21', '2026-04-01 18:00:22');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES (66, 'App\\Models\\User', 15, 'dashboard-odonto-web', '956020bdfb42f6e08db33934535f71b6e92b6dc8980ae8f7f9fef809e0c83f65', '[\"*\"]', '2026-04-01 19:18:31', NULL, '2026-04-01 18:22:32', '2026-04-01 19:18:31');

-- ----------------------------
-- Estrutura da tabela `procedures`
-- ----------------------------
DROP TABLE IF EXISTS `procedures`;
CREATE TABLE `procedures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accepts_agreement` tinyint(1) NOT NULL DEFAULT '0',
  `preparation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `requires_anesthesia` tinyint(1) NOT NULL DEFAULT '0',
  `complexity_level` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_procedures_name` (`name`),
  KEY `idx_procedures_value` (`value`),
  KEY `idx_procedures_active` (`active`),
  KEY `idx_procedures_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `procedures`
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (1, 'Limpeza Dental (Profilaxia)', 80.00, '45 minutos', 1, 'Paciente deve estar em jejum de 2h', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (2, 'Restauração com Resina', 150.00, '60 minutos', 1, 'Nenhuma preparação especial', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (3, 'Extração Dental Simples', 120.00, '30 minutos', 1, 'Jejum de 4h, medicação pré-cirúrgica', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (4, 'Canal Radicular (Endodontia)', 350.00, '120 minutos', 1, 'Radiografia prévia obrigatória', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (5, 'Implante Dentário', 1200.00, '180 minutos', 0, 'Exames médicos completos, jejum de 8h', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (6, 'Aparelho Ortodôntico', 2500.00, '90 minutos', 1, 'Moldagem prévia dos dentes', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (7, 'Clareamento Dental', 300.00, '60 minutos', 0, 'Limpeza prévia obrigatória', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');
INSERT INTO `procedures` (`id`, `name`, `value`, `time`, `accepts_agreement`, `preparation`, `description`, `category`, `active`, `requires_anesthesia`, `complexity_level`, `created_at`, `updated_at`) VALUES (8, 'Prótese Total', 800.00, '90 minutos', 1, 'Moldagem e planejamento prévios', NULL, NULL, 1, 0, 1, '2026-03-27 03:22:11', '2026-03-27 03:22:11');

-- ----------------------------
-- Estrutura da tabela `reports`
-- ----------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json NOT NULL,
  `period_start` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_end` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_scheduled` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `saas_mensalidades`
-- ----------------------------
DROP TABLE IF EXISTS `saas_mensalidades`;
CREATE TABLE `saas_mensalidades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clinica` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plano_id` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plano_nome` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_mensal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_usuarios` int unsigned NOT NULL DEFAULT '0',
  `usuarios_ativos` int unsigned NOT NULL DEFAULT '0',
  `proximo_vencimento` date NOT NULL,
  `status` enum('em_dia','atrasada','inadimplente','suspensa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'suspensa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `saas_mensalidades_clinica_unique` (`clinica`),
  KEY `saas_mensalidades_status_plano_idx` (`status`,`plano_id`),
  KEY `saas_mensalidades_vencimento_idx` (`proximo_vencimento`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `saas_mensalidades`
INSERT INTO `saas_mensalidades` (`id`, `clinica`, `plano_id`, `plano_nome`, `valor_mensal`, `total_usuarios`, `usuarios_ativos`, `proximo_vencimento`, `status`, `created_at`, `updated_at`) VALUES (1, 'Sem clinica', 'premium', 'Premium', 160.00, 11, 11, '2026-04-26', 'em_dia', '2026-03-31 21:38:57', '2026-04-01 19:10:17');

-- ----------------------------
-- Estrutura da tabela `saas_solicitacoes`
-- ----------------------------
DROP TABLE IF EXISTS `saas_solicitacoes`;
CREATE TABLE `saas_solicitacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome_clinica` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plano_id` enum('basico','profissional','premium') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'basico',
  `status` enum('pendente','aguardando_pagamento','aprovada','rejeitada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `pagamento_confirmado` tinyint(1) NOT NULL DEFAULT '0',
  `data_pagamento` timestamp NULL DEFAULT NULL,
  `comprovante_pagamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `motivo_rejeicao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aprovado_por` bigint unsigned DEFAULT NULL,
  `aprovado_em` timestamp NULL DEFAULT NULL,
  `rejeitado_por` bigint unsigned DEFAULT NULL,
  `rejeitado_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `saas_solicitacoes_email_unique` (`email`),
  KEY `saas_solicitacoes_status_created_at_index` (`status`,`created_at`),
  KEY `saas_solicitacoes_plano_id_index` (`plano_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `schedulings`
-- ----------------------------
DROP TABLE IF EXISTS `schedulings`;
CREATE TABLE `schedulings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `professional_id` bigint unsigned NOT NULL,
  `procedure_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `return` tinyint(1) NOT NULL DEFAULT '0',
  `obs` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active_duplicate_guard` tinyint GENERATED ALWAYS AS ((case when (`status` = _utf8mb4'canceled') then NULL else 1 end)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_schedulings_business_unique` (`patient_id`,`professional_id`,`procedure_id`,`date`,`time`,`active_duplicate_guard`),
  KEY `schedulings_date_professional_id_index` (`date`,`professional_id`),
  KEY `schedulings_patient_id_date_index` (`patient_id`,`date`),
  KEY `idx_schedulings_patient_id` (`patient_id`),
  KEY `idx_schedulings_professional_id` (`professional_id`),
  KEY `idx_schedulings_procedure_id` (`procedure_id`),
  KEY `idx_schedulings_date` (`date`),
  KEY `idx_schedulings_status` (`status`),
  KEY `idx_schedulings_date_status` (`date`,`status`),
  KEY `idx_schedulings_created_at` (`created_at`),
  KEY `idx_schedulings_patient_date` (`patient_id`,`date`),
  KEY `idx_schedulings_professional_date` (`professional_id`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `schedulings`
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (1, 2, 1, 1, '2026-03-28', '08:00', 0, 'Primeira consulta do paciente', 'completed', '2026-03-27 03:24:09', 60, NULL, NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 03:24:09', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (2, 4, 2, 2, '2026-03-28', '09:30', 0, 'Paciente com histórico de alergia a anestésico', 'confirmed', '2026-03-27 03:24:09', 45, '2026-03-27 05:34:56', NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 05:34:56', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (3, 2, 1, 3, '2026-03-29', '10:00', 0, 'Confirmado via telefone', 'confirmed', '2026-03-25 03:24:09', 90, '2026-03-27 03:24:09', NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 03:24:09', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (4, 4, 2, 4, '2026-03-29', '14:30', 1, 'Retorno para avaliação do procedimento anterior', 'confirmed', '2026-03-24 03:24:09', 30, '2026-03-26 03:24:09', NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 03:24:09', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (5, 2, 3, 5, '2026-03-27', '08:00', 0, 'Procedimento em andamento', 'in_progress', '2026-03-26 03:24:09', 60, '2026-03-26 03:24:09', NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 03:24:09', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (6, 2, 1, 6, '2026-03-24', '11:00', 0, 'Procedimento concluído com sucesso', 'completed', '2026-03-22 03:24:09', 60, '2026-03-23 03:24:09', NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 03:24:09', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (7, 4, 2, 1, '2026-03-22', '15:15', 0, NULL, 'completed', '2026-03-20 03:24:09', 45, '2026-03-21 03:24:09', NULL, NULL, '2026-03-27 03:24:09', '2026-03-28 02:52:58', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (8, 2, 1, 3, '2026-03-31', '16:00', 0, NULL, 'canceled', '2026-03-25 03:24:09', 60, NULL, '2026-03-26 03:24:09', 'Paciente solicitou cancelamento por motivo pessoal', '2026-03-27 03:24:09', '2026-03-27 03:24:09', NULL);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (9, 2, 4, 7, '2026-04-03', '09:00', 0, 'Procedimento complexo - atenção especial', 'confirmed', '2026-03-27 03:24:09', 120, '2026-03-27 05:03:07', NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 05:03:07', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (10, 2, 5, 8, '2026-04-06', '13:00', 1, 'Retorno pós-tratamento de canal', 'completed', '2026-03-27 03:24:09', 30, NULL, NULL, NULL, '2026-03-27 03:24:09', '2026-03-27 03:24:09', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (11, 4, 2, 1, '2026-03-27', '15:00', 0, NULL, 'completed', '2026-03-27 05:12:47', 60, '2026-03-27 05:12:47', NULL, NULL, '2026-03-27 05:12:47', '2026-03-27 05:12:47', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (12, 2, 1, 6, '2026-03-27', '11:00', 0, 'Procedimento concluido com sucesso', 'completed', '2026-03-27 05:12:47', 60, '2026-03-27 05:12:47', NULL, NULL, '2026-03-27 05:12:47', '2026-03-27 05:12:47', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (13, 2, 1, 1, '2026-03-27', '08:00', 0, 'Primeira consulta do paciente', 'completed', '2026-03-27 05:12:47', 60, NULL, NULL, NULL, '2026-03-27 05:12:47', '2026-03-27 05:12:47', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (14, 4, 2, 2, '2026-03-27', '09:30', 0, 'Paciente com historico de alergia a anestesico', 'completed', '2026-03-27 05:12:47', 60, NULL, NULL, NULL, '2026-03-27 05:12:47', '2026-03-27 05:12:47', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (15, 2, 1, 3, '2026-03-27', '10:00', 0, NULL, 'confirmed', '2026-03-27 05:12:47', 60, '2026-03-27 05:12:47', NULL, NULL, '2026-03-27 05:12:47', '2026-03-27 05:12:47', 1);
INSERT INTO `schedulings` (`id`, `patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `return`, `obs`, `status`, `scheduled_at`, `duration`, `confirmed_at`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`, `active_duplicate_guard`) VALUES (16, 7, 4, 4, '2026-04-01', '08:00', 0, NULL, 'scheduled', '2026-03-31 19:24:25', 120, NULL, NULL, NULL, '2026-03-31 19:24:25', '2026-03-31 19:24:25', 1);

-- ----------------------------
-- Estrutura da tabela `sessions`
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `sessions`
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('364gWVd5ZZkKcDVWdCzm5sbIDzzpBZOoaEq9gXm6', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRmZJOElUV0Q1dWZwYnczcEw2eWs4R1lyd2FzM0FGQmwwZTJkSjRZbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774387841);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('asoRqs0fDVNbTejGzkFGijt78VIKpptydx4cy8N4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ2p6Um04NjdWTFhDYkVkYlhNVXg1ODhDU284dzJuZGdBZTdRSm90RSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774909235);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('bdrOIpzil7HKs1fv6v7GAY3WgkreVHqYG0pEgcoA', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUxzZk5lNmFRYnNQQmowclNicng0VWlVTG9PdlhZOUJrQUlXc0RaZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562202);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('C4jWeA1ri19fgHfQKyLrDR6SKPEwZZ3kYMAkxLuQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieGFtVFBFSGRnRnFabDJjcWFNUzliNkZkYkF5WUtYZHBDZ3YyQmRlZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562300);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('cEGb7qp3FqYfMI0XW1EuYyKdKUlda0YrHBPBXXZj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYmRqdnc2bjV6U3dIVlVxMWJOV1o1bkp2QU1FbDlyWmJ4UzNKd0RReSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562205);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('cY9AWds0LwN4QpzZIEhzRWidipqaEVxs4RjvqYtt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGpXWjFXN3A1MDZvbXFFRnBpNkpVR09CSnVydUdHNFRzQzZ3akhKSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562313);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('GRMYJsFShokSiWhQmo8orq2HM9K3R9jHpKAQdUat', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT2g0VEhaemVWNWJrdVF3SkNvMjZUQ1RHc3hVakZ3Uzk1RXFMdjJNOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562303);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('hnOgckquXNkzHqAvtPLrY9KiGWA5PHIEIbQnexj4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRk54cmZTOUtKc3RlMHhwNlZUOGJqekVqTHVhNFg2SjNxRWFiYm9kYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774383050);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('JZXgMpOzdvxpSgjzUvCi6W4iNmMbYQlhMvNRiYag', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjYwUXJuMjV6a1Z1ZzNrZU1GUFdXS3loelRCQlJMWGRvTTJSUU91RiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562216);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('MPoIkTreZdSjBMDiDeLquSCjlSJpnttlRFmgifnj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoickJLaHpDQ2NSYkQwUko0dHlXNlFldVNSZnQ2SlhScnBYSE9xRlhlOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562310);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('n52gHPF68mt1wJlAA5xcJNXvz7qZ5ouKcBPTHR5D', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieUdxQXI0YVpSRWNCVjlzOUI3NjJsQWM0Q2dZUjRiMUw1WWRXMGM2UCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774565991);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('nQP5k9RqY6A7PmDWEf7adpYy3bGMfoSSLzZYTFmi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibFcydmJ1c200cGpTNnNaZzZGalRMV2lYa1BjTXN1UTMyaWdPSVZQcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562212);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('qO4gG5x84Pep9urxCYaVHLhvVo68m7GbVCMMQRGa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibmZZckV0SWk2Y3p1aWlkdVZ2aDI0TUlsMElkM2FQQ3VvOEIwMTZDYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562352);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('RhBj1v0BiCsibCgrk9l3B7GkWRS20A7rnh2gLfEE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUzNwdWFyNnhqN1RxTXREYzdWYzJFWllaVDdTMWU3YXJwMERjYkpoYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562208);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('S2OEmT9UefcqAqXJ1zorcKRvBigN9DjiXYy7mXVW', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieFVwUmRqaHlLUlF4bzdBenhsbWlVbVBtVkp6cXUyN3BpamR2M0tsUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1773804887);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('V02RK217pseJNgAYePlXFGsJTTubL35D0NTG6wUL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmhvR0kzYURLbGk4N1hOa3I1c0x1MWFHY0pOSVAzWVJTcTJKaDh2USI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774562307);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('WUV3RLhbEaUbjzZpGLnZnzSDtqBaLX97N5LARd5g', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidmJ1ZTk5QzVFc21Dcll1M0tYUUlhWWNVck9nVm80STBlSjFLeGRFVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1773805000);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('x0gACUaXAlEmCZL8M8HWuZoB29BWU6FqJEy09U9a', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRlZNNGdxZFN3UENTOFNCalJmdE9LY2ZDcHJQR1pNVk95eXZHYnZKdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774376740);
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('XqTKavEyZ6xRmrDaoUTLqrNKHjemdrJBY9y4X8sm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; pt-BR) WindowsPowerShell/5.1.26100.7920', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib2V6aHk0d1RVcmVtVVpCc1hlemZvVm1UTlBadFBsNzk5YXhiUmZqMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1774385655);

-- ----------------------------
-- Estrutura da tabela `suppliers`
-- ----------------------------
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `avaliacao` decimal(3,1) NOT NULL DEFAULT '0.0',
  `pix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `suppliers`
INSERT INTO `suppliers` (`id`, `name`, `razao_social`, `email`, `phone`, `cnpj`, `tipo`, `categoria`, `contato`, `status`, `avaliacao`, `pix`, `pix_key_type`, `street`, `number`, `complement`, `neighborhood`, `city`, `state`, `cep`, `created_at`, `updated_at`) VALUES (3, 'DentalTech', 'DentalTech Equipamentos Odontológicos Ltda', 'mateus2@example.com', '(71) 98666-0020', '44.244.940/0001-00', 'Materiais', 'Máscaras', 'Luciana', 0, 5.0, NULL, NULL, 'Rua 2', 2, NULL, 'Bairro 2', 'Feira de Santana', 'BA', '41000-000', '2026-03-26 21:19:29', '2026-03-26 21:20:03');

-- ----------------------------
-- Estrutura da tabela `treatment_plans`
-- ----------------------------
DROP TABLE IF EXISTS `treatment_plans`;
CREATE TABLE `treatment_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Estrutura da tabela `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sobrenome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `clinica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `especialidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo_acesso_id` bigint unsigned DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `ultimo_login_em` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_grupo_acesso_id_foreign` (`grupo_acesso_id`),
  CONSTRAINT `users_grupo_acesso_id_foreign` FOREIGN KEY (`grupo_acesso_id`) REFERENCES `grupo_acessos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `users`
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1, 'Usuario Teste20260326213317', 'usuarioteste20260326213317', 'Usuario', 'Teste20260326213317', 'usuarioteste.20260326213317@example.com', NULL, NULL, NULL, 2, 1, 'staff', '2026-03-26 21:39:35', NULL, '$2y$12$bGWnvLn1XFmksQyt6A/bxuolLfKK5hRVqjfEFzBdAp2XHvyrRfRCG', NULL, '2026-03-26 21:33:18', '2026-03-26 21:39:35');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (2, 'Usuario Teste Login', 'teste.login', 'Usuario', 'Teste', 'teste.login@ssait.local', NULL, NULL, NULL, 3, 1, 'staff', '2026-03-26 21:59:58', NULL, '$2y$12$/q0bPdC8BgyCnsYWHy8IvuxRTzqs43EXIJAJSPCOMr7z4o3PhpRz6', NULL, '2026-03-26 21:42:08', '2026-03-26 21:59:58');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (3, 'Admin Sistema', 'admin.odonto', 'Admin', 'Sistema', 'admin.odonto@ssait.local', NULL, NULL, NULL, 4, 1, 'staff', '2026-04-01 17:57:48', NULL, '$2y$12$kXUYW9JKJtwaHhSiCwXYyOiLdlAwzgFhcEumKE.vI0p6dxvEn4xKm', NULL, '2026-03-26 22:00:53', '2026-04-01 17:57:48');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (4, 'Dentista Padrao', 'dentista.odonto', 'Dentista', 'Padrao', 'dentista.odonto@ssait.local', NULL, NULL, NULL, 5, 1, 'staff', '2026-03-26 23:52:23', NULL, '$2y$12$Z3MEqhjhN6IvHuUjOoe9.ewi6JKMIIWFzsJgMATtyszZ2RRZPLpKe', NULL, '2026-03-26 22:00:53', '2026-03-26 23:52:23');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (5, 'Secretaria Padrao', 'secretaria.odonto', 'Secretaria', 'Padrao', 'secretaria.odonto@ssait.local', NULL, NULL, NULL, 6, 1, 'staff', '2026-03-26 23:14:33', NULL, '$2y$12$wyfHELRlB29tzxfTqQj9Ae.FpyBIh4ZHHq1SwsGmVvQDlvQtiSHO2', NULL, '2026-03-26 22:00:53', '2026-03-26 23:14:33');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (6, 'Auxiliar Padrao', 'auxiliar.odonto', 'Auxiliar', 'Padrao', 'auxiliar.odonto@ssait.local', NULL, NULL, NULL, 7, 1, 'staff', '2026-03-26 22:02:44', NULL, '$2y$12$boAFsun2zb7M8zNZdYze3etfr.lXgueqzBFt9omQ39Gv.q0IXUvKm', NULL, '2026-03-26 22:00:54', '2026-03-26 22:02:44');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (7, 'Faxineiro Padrao', 'faxineiro.odonto', 'Faxineiro', 'Padrao', 'faxineiro.odonto@ssait.local', NULL, NULL, NULL, 8, 1, 'staff', '2026-03-26 22:01:36', NULL, '$2y$12$ltUqt9JVR/2V/Nt6l4g.2O4A6yygyetzpaMAbA33KBIwFTojBqWKG', NULL, '2026-03-26 22:00:54', '2026-03-26 22:01:36');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (8, 'Financeiro Odonto', 'financeiro.odonto', 'Financeiro', 'Odonto', 'financeiro.odonto@ssait.local', NULL, NULL, NULL, 9, 1, 'staff', '2026-03-31 20:06:51', NULL, '$2y$12$7LcZ2uwid/A.deeDOMIna.kGVmNTDOi6M0tErioIIpKZ8B0VpVp3e', NULL, '2026-03-26 22:24:59', '2026-03-31 20:06:51');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (13, 'Paciente Portal Demo', NULL, NULL, NULL, 'paciente.portal.20260331190337@odonto.local', NULL, NULL, NULL, NULL, 1, 'paciente', '2026-04-01 17:59:21', NULL, '$2y$12$oZoyYBFJPM.HEKpK6xp6qutBORxGfIjEWyjVDUjYPlr.dUxQ23zyC', NULL, '2026-03-31 19:03:37', '2026-04-01 17:59:21');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (14, 'SaaS Admin', 'saasadmin', NULL, NULL, 'saasadmin@odonto.local', NULL, NULL, NULL, NULL, 1, 'saas_admin', '2026-04-01 17:47:38', NULL, '$2y$12$OzoviUE5GxmKDMq.X6cBtu9HygiLtY7Vm49R2E7rhOwubFDlXpnvC', NULL, '2026-03-31 20:51:55', '2026-04-01 17:47:38');
INSERT INTO `users` (`id`, `name`, `username`, `nome`, `sobrenome`, `email`, `telefone`, `clinica`, `especialidade`, `grupo_acesso_id`, `ativo`, `tipo`, `ultimo_login_em`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (15, 'Paulo Tiago Oliveira de   Magalhães', NULL, 'Paulo Tiago', 'Oliveira de   Magalhães', 'tiago2008.1@hotmail.com', NULL, NULL, NULL, 1, 1, 'saas_admin', '2026-04-01 18:22:32', NULL, '$2y$12$YC3LTZjn1Jz3RsOt6TALUOFdYyMRUMWXL/kP0l6GTZjD10Z/Wy3Ve', NULL, '2026-04-01 18:17:07', '2026-04-01 18:22:32');

SET FOREIGN_KEY_CHECKS=1;
