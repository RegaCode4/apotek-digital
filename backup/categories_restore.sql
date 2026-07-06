-- Restore tabel categories untuk database apotek_digital
-- 15 kategori, id 1-15 cocok dengan category_id pada seed medicines.

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Sistem Pencernaan & Metabolisme', 'Obat saluran cerna, asam lambung, dan metabolisme.', NOW(), NOW()),
(2, 'Sistem Kardiovaskular', 'Obat jantung, hipertensi, dan pembuluh darah.', NOW(), NOW()),
(3, 'Sistem Saraf Pusat', 'Analgesik, antikonvulsan, antidepresan, dan psikotropika.', NOW(), NOW()),
(4, 'Anti-Infeksi Sistemik', 'Antibiotik dan antimikroba sistemik.', NOW(), NOW()),
(5, 'Sistem Pernapasan', 'Bronkodilator, mukolitik, antihistamin, dan dekongestan.', NOW(), NOW()),
(6, 'Sistem Endokrin', 'Antidiabetik, hormon, dan kortikosteroid.', NOW(), NOW()),
(7, 'Antineoplastik', 'Obat kemoterapi dan terapi kanker.', NOW(), NOW()),
(8, 'Dermatologikal', 'Obat topikal untuk kulit (krim, salep, losion).', NOW(), NOW()),
(9, 'Sistem Muskuloskeletal', 'NSAID, antirematik, dan obat asam urat.', NOW(), NOW()),
(10, 'Organ Sensorik', 'Obat mata, telinga, dan hidung.', NOW(), NOW()),
(11, 'Sistem Genitourinari & Hormon Seks', 'Obat saluran kemih, prostat, dan kontrasepsi/hormon seks.', NOW(), NOW()),
(12, 'Darah & Organ Pembentuk Darah', 'Suplemen darah, antikoagulan, dan antifibrinolitik.', NOW(), NOW()),
(13, 'Antiparasit, Insektisida & Repelen', 'Obat cacing, skabisida, dan antiparasit.', NOW(), NOW()),
(14, 'Berbagai Macam (Various)', 'Antiseptik, rehidrasi, dan produk lain-lain.', NOW(), NOW()),
(15, 'Vitamin dan Suplemen', 'Vitamin, mineral, dan suplemen kesehatan.', NOW(), NOW());
