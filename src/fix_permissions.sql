-- ==============================================================================
-- SwiftTrack: Fix Permissions for Editing & Deleting Reports (Run in Supabase)
-- ==============================================================================
-- Instructions:
-- 1. Open your Supabase Dashboard: https://supabase.com/dashboard
-- 2. Select your project -> Click "SQL Editor" on the left menu.
-- 3. Click "New query", paste all the code below, and click "Run".
-- ==============================================================================

-- 1. Ensure item_status column exists
ALTER TABLE public.reports 
ADD COLUMN IF NOT EXISTS item_status TEXT DEFAULT 'Still Missing';

-- 2. Ensure Row Level Security is active
ALTER TABLE public.reports ENABLE ROW LEVEL SECURITY;

-- 3. Clear any existing conflicting policies on reports
DROP POLICY IF EXISTS "Anyone logged in can read approved reports" ON public.reports;
DROP POLICY IF EXISTS "Students can view their own reports" ON public.reports;
DROP POLICY IF EXISTS "Authenticated users can submit reports" ON public.reports;
DROP POLICY IF EXISTS "Admins can view all reports" ON public.reports;
DROP POLICY IF EXISTS "Admins and owners can update reports" ON public.reports;
DROP POLICY IF EXISTS "Admins and owners can delete reports" ON public.reports;

-- 4. SELECT: Anyone logged in can view approved/resolved reports on the feed.
-- Authors can view their own reports. Admins can view all reports.
CREATE POLICY "Anyone logged in can read approved reports"
  ON public.reports FOR SELECT
  TO authenticated
  USING (
    status IN ('approved', 'resolved') OR 
    auth.uid() = user_id OR
    (auth.jwt() ->> 'email') = user_email OR
    EXISTS (
      SELECT 1 FROM public.profiles 
      WHERE profiles.id = auth.uid() AND profiles.role = 'admin'
    )
  );

-- 5. INSERT: Authenticated users can submit reports
CREATE POLICY "Authenticated users can submit reports"
  ON public.reports FOR INSERT
  TO authenticated
  WITH CHECK (true);

-- 6. UPDATE: Report authors and admins can edit and update reports
CREATE POLICY "Admins and owners can update reports"
  ON public.reports FOR UPDATE
  TO authenticated
  USING (
    auth.uid() = user_id OR 
    (auth.jwt() ->> 'email') = user_email OR
    EXISTS (
      SELECT 1 FROM public.profiles 
      WHERE profiles.id = auth.uid() AND profiles.role = 'admin'
    )
  )
  WITH CHECK (
    auth.uid() = user_id OR 
    (auth.jwt() ->> 'email') = user_email OR
    EXISTS (
      SELECT 1 FROM public.profiles 
      WHERE profiles.id = auth.uid() AND profiles.role = 'admin'
    )
  );

-- 7. DELETE: Report authors and admins can permanently delete reports
CREATE POLICY "Admins and owners can delete reports"
  ON public.reports FOR DELETE
  TO authenticated
  USING (
    auth.uid() = user_id OR 
    (auth.jwt() ->> 'email') = user_email OR
    EXISTS (
      SELECT 1 FROM public.profiles 
      WHERE profiles.id = auth.uid() AND profiles.role = 'admin'
    )
  );

