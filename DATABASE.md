# Database Design & Schema Specification — DocuVerify

**Engine:** MariaDB / MySQL (`10.4.28-MariaDB`)  
**Port:** `3307`  
**Database Name:** `docuverify_db`  
**Collation:** `utf8mb4_unicode_ci`

---

## 1. Schema Overview

```
                      +-------------------+
                      |       users       |
                      +-------------------+
                                | 1
                                |
                                | hasMany
                                v N
+-------------------+ 1       N +-------------------+
|   transactions    |<----------|     documents     |
+-------------------+           +-------------------+
                                        | 1
                                        |
                                        | hasMany
                                        v N
                                +-------------------+
                                | verification_logs |
                                +-------------------+
```

---

## 2. Table Specifications

### 2.1 `users`
Stores authorized administrative accounts.
```sql
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);
```

### 2.2 `documents`
Stores document administrative metadata, hashes, and blockchain registration status.
```sql
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `document_code` varchar(64) NOT NULL UNIQUE,
  `document_name` varchar(255) NOT NULL,
  `document_hash` varchar(64) NOT NULL,
  `document_id` varchar(66) NOT NULL,
  `registered_by` bigint unsigned NOT NULL,
  `transaction_hash` varchar(66) DEFAULT NULL,
  `block_number` bigint unsigned DEFAULT NULL,
  `gas_used` bigint unsigned DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT NULL,
  `registration_status` enum('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
  `file_size` bigint unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `documents_document_code_index` (`document_code`),
  INDEX `documents_document_hash_index` (`document_hash`),
  INDEX `documents_document_id_index` (`document_id`),
  INDEX `documents_registration_status_index` (`registration_status`),
  CONSTRAINT `documents_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
);
```

### 2.3 `transactions`
Tracks blockchain transaction lifecycle, gas metrics, and confirmations.
```sql
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `document_id` bigint unsigned NOT NULL,
  `transaction_hash` varchar(66) NOT NULL UNIQUE,
  `transaction_type` varchar(32) NOT NULL DEFAULT 'register',
  `network` varchar(32) NOT NULL DEFAULT 'hardhat_local',
  `gas_used` bigint unsigned DEFAULT NULL,
  `gas_price` varchar(64) DEFAULT NULL,
  `block_number` bigint unsigned DEFAULT NULL,
  `transaction_status` enum('pending','confirmed','failed') NOT NULL DEFAULT 'pending',
  `raw_receipt` json DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `transactions_transaction_hash_index` (`transaction_hash`),
  INDEX `transactions_block_number_index` (`block_number`),
  INDEX `transactions_transaction_status_index` (`transaction_status`),
  CONSTRAINT `transactions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
);
```

### 2.4 `verification_logs`
Comprehensive audit trail of public verification requests.
```sql
CREATE TABLE `verification_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `document_id` bigint unsigned DEFAULT NULL,
  `document_code` varchar(64) NOT NULL,
  `uploaded_hash` varchar(64) NOT NULL,
  `blockchain_hash` varchar(64) DEFAULT NULL,
  `verification_status` enum('VALID','MODIFIED','UNVERIFIABLE','TECHNICAL_ERROR') NOT NULL,
  `processing_time_ms` int unsigned NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `verification_logs_document_code_index` (`document_code`),
  INDEX `verification_logs_uploaded_hash_index` (`uploaded_hash`),
  INDEX `verification_logs_verification_status_index` (`verification_status`),
  CONSTRAINT `verification_logs_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL
);
```

### 2.5 `activity_logs`
Audit log recording administrator logins, registrations, and system events.
```sql
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint unsigned DEFAULT NULL,
  `activity` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  INDEX `activity_logs_activity_index` (`activity`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
);
```

