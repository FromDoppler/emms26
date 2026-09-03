USE `EMMS26`;

ALTER TABLE `speakers`
  ADD COLUMN IF NOT EXISTS `image_modal` varchar(255) DEFAULT NULL AFTER `image`;
