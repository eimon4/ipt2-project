-- ==============================================================================
-- SwiftTrack: Assign School Administrator Role
-- ==============================================================================
-- Run this in your Supabase Dashboard -> SQL Editor
-- 
-- 1. Replace 'your_admin_email@school.edu' with the email address of the account
-- 2. Click "Run"
-- ==============================================================================

UPDATE public.profiles
SET role = 'admin'
WHERE email = 'your_admin_email@school.edu';

-- Verify that the role was changed to admin:
SELECT id, name, email, role, created_at 
FROM public.profiles 
WHERE email = 'your_admin_email@school.edu';

