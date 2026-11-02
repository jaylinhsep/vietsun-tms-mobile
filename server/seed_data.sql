INSERT INTO `vendors` (`id`, `vendor_name`, `vendor_code`, `phone`, `password_hash`, `is_active`) 
VALUES 
(1, 'HOANG LONG PHAT', 'HLP', '0901234567', 'hlp@123', 1),
(2, 'TRANCO', 'TRANCO', '0902345678', 'tranco@123', 1),
(3, 'VIETSUN', 'VIETSUN', '0903456789', 'vietsun@123', 1),
(4, 'VUONG PHAT', 'VUONGPHAT', '0904567890', 'vp@123', 1)
ON DUPLICATE KEY UPDATE `vendor_name`=VALUES(`vendor_name`);

INSERT INTO `drivers` (`vendor_id`, `driver_name`, `phone`, `pin_code`, `assigned_plate`, `is_active`)
VALUES
(1, 'Tài xế Hoàng Long Phát 1', '0911000001', '123456', '50E39861', 1),
(1, 'Tài xế Hoàng Long Phát 2', '0911000002', '123456', '50E65490', 1),
(3, 'Tài xế Vietsun 1', '0911000003', '123456', '50H10106', 1),
(3, 'Tài xế Vietsun 2', '0911000004', '123456', '50H90944', 1)
ON DUPLICATE KEY UPDATE `driver_name`=VALUES(`driver_name`);
