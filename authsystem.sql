-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: localhost:8889
-- Χρόνος δημιουργίας: 03 Μάη 2026 στις 15:52:41
-- Έκδοση διακομιστή: 8.0.44
-- Έκδοση PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `authsystem`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `email_verified_tokens`
--

CREATE TABLE `email_verified_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `email_verified_tokens`
--

INSERT INTO `email_verified_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(3, 4, '33f36985bfbeb9df1bccf7c914683277e404b0b764013257d175091c2e9a0d15', '2026-05-03 19:33:45', '2026-05-03 18:37:20', '2026-05-03 15:33:45');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `successful` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `attempted_at`, `successful`) VALUES
(1, 'makridhs.xaralampos@gmail.com', '::1', '2026-05-03 15:38:29', 1),
(2, 'makridhs.xaralampos@gmail.com', '::1', '2026-05-03 15:40:08', 1);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 4, 'ac6db5658b28f53a1a1e672dbb75d35e03b20f5160d877ccbe01877188dffb4a', '2026-05-03 19:09:40', '2026-05-03 18:40:00', '2026-05-03 15:39:40');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Άδειασμα δεδομένων του πίνακα `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(4, 'Xaralampos Makridhs', 'makridhs.xaralampos@gmail.com', '$2y$10$B5ONTUE87JVeBscVgH21d.ScClQ5VE.Rr9YVTJlbXfapu693Lgi1q', '2026-05-03 18:37:20', '2026-05-03 15:33:45', '2026-05-03 15:40:00');

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `email_verified_tokens`
--
ALTER TABLE `email_verified_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_token_hash` (`token_hash`),
  ADD KEY `idx_email_user_id` (`user_id`),
  ADD KEY `idx_email_token_expires_at` (`expires_at`);

--
-- Ευρετήρια για πίνακα `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_email` (`email`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`),
  ADD KEY `idx_login_attemptes_attempted_at` (`attempted_at`);

--
-- Ευρετήρια για πίνακα `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_token_hash` (`token_hash`),
  ADD KEY `idx_email_user_id` (`user_id`),
  ADD KEY `idx_email_token_expires_at` (`expires_at`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `email_verified_tokens`
--
ALTER TABLE `email_verified_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT για πίνακα `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT για πίνακα `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `email_verified_tokens`
--
ALTER TABLE `email_verified_tokens`
  ADD CONSTRAINT `fk_email_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_password_token_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
