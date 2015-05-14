DROP INDEX idx_value ON #__userxtd_profiles;
ALTER TABLE #__userxtd_profiles CHANGE `value` `value` TEXT;
