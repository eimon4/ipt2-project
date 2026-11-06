-- ==============================================================================
-- SwiftTrack Migration: Add item_status column
-- ==============================================================================
-- Run this in your Supabase Dashboard -> SQL Editor -> New Query -> Run:
-- ==============================================================================

ALTER TABLE public.reports 
ADD COLUMN IF NOT EXISTS item_status TEXT DEFAULT 'Still Missing';

