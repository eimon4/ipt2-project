-- ==============================================================================
-- SwiftTrack Migration: Allow Users and Admins to Delete Reports
-- ==============================================================================
-- Run this in your Supabase Dashboard -> SQL Editor
-- ==============================================================================

DROP POLICY IF EXISTS "Admins and owners can delete reports" ON public.reports;

CREATE POLICY "Admins and owners can delete reports"
  ON public.reports FOR DELETE
  TO authenticated
  USING (
    auth.uid() = user_id OR 
    EXISTS (
      SELECT 1 FROM public.profiles 
      WHERE profiles.id = auth.uid() AND profiles.role = 'admin'
    )
  );

