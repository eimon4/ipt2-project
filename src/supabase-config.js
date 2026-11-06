// ==============================================================================
// SwiftTrack - Supabase Configuration
// ==============================================================================
// 1. Go to your Supabase project dashboard: https://supabase.com/dashboard
// 2. Click "Project Settings" (gear icon) -> "API"
// 3. Copy your "Project URL" and paste it in SUPABASE_URL below
// 4. Copy your "anon / public" API key and paste it in SUPABASE_ANON_KEY below
// ==============================================================================

const SUPABASE_URL = 'https://blbvvpxaasuzxjwcyhsn.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_HNmv5WXlglcoHGPmyiX9lw_mESwFDmb';

// Initialize the Supabase client using the official JS SDK from CDN
const supabaseClient = window.supabase ? window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY) : null;

// Helper to check if credentials have been filled in
function isConfigured() {
    return SUPABASE_URL.trim() !== '' &&
        SUPABASE_ANON_KEY.trim() !== '' &&
        !SUPABASE_URL.includes('YOUR_PROJECT_ID') &&
        !SUPABASE_ANON_KEY.includes('YOUR_ANON_PUBLIC_KEY');
}
