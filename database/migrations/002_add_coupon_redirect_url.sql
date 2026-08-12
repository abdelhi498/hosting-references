-- Migration: add a per-coupon redirect URL.
-- Run this once on any database created BEFORE this feature existed
-- (via phpMyAdmin -> SQL tab, or mysql CLI). Safe to run on a fresh
-- install too — it does nothing if the column already exists.

ALTER TABLE coupons
  ADD COLUMN IF NOT EXISTS redirect_url VARCHAR(500) DEFAULT NULL AFTER discount_text;
