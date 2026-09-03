-- Add speaker audit timestamps without inventing dates for existing rows.
-- Existing records remain NULL; new records receive timestamps automatically.

ALTER TABLE `speakers`
  ADD COLUMN `created_at` timestamp NULL DEFAULT NULL AFTER `meta_twitter`,
  ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `speakers`
  MODIFY COLUMN `created_at` timestamp NULL DEFAULT current_timestamp(),
  MODIFY COLUMN `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();
