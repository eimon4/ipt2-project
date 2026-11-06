// ==============================================================================
// SwiftTrack - Authentication & Shared Helpers
// ==============================================================================

/**
 * Returns the currently logged in user and their profile, or null if logged out.
 */
async function getCurrentUser() {
    if (!supabaseClient) return null;

    const { data: { session }, error } = await supabaseClient.auth.getSession();
    if (error || !session || !session.user) {
        return null;
    }

    const authUser = session.user;

    // Fetch the role and full name from the 'profiles' table
    const { data: profile } = await supabaseClient
        .from('profiles')
        .select('*')
        .eq('id', authUser.id)
        .maybeSingle();

    return {
        id: authUser.id,
        email: authUser.email,
        name: profile?.name || authUser.user_metadata?.name || 'Student',
        role: profile?.role || authUser.user_metadata?.role || 'user'
    };
}

/**
 * Protects a page: redirects to index.html if the user is not logged in.
 * If requiredRole is 'admin', also blocks non-admin users.
 */
async function requireAuth(requiredRole = null) {
    checkConfigNotice();

    const user = await getCurrentUser();

    if (!user) {
        window.location.href = 'index.html';
        return null;
    }

    if (requiredRole === 'admin' && user.role !== 'admin') {
        alert('Access denied: School Administrator privileges required.');
        window.location.href = 'feed.html';
        return null;
    }

    // Render the standard top navbar
    renderNavbar(user);
    return user;
}

/**
 * Signs the user out from Supabase and redirects to the login page.
 */
async function logout() {
    if (supabaseClient) {
        await supabaseClient.auth.signOut();
    }
    window.location.href = 'index.html';
}

/**
 * Renders the top navigation bar with user details and role-based links.
 */
function renderNavbar(user) {
    const navContainer = document.getElementById('navbar-container');
    if (!navContainer) return;

    const currentPage = window.location.pathname.split('/').pop() || 'feed.html';
    const isAdmin = user.role === 'admin';
    const greetingText = isAdmin ? `Admin: ${escapeHtml(user.name)}` : `Hello, ${escapeHtml(user.name)}`;

    navContainer.innerHTML = `
      <header class="bg-blue-900 text-white shadow relative">
        <div class="hidden lg:block absolute left-6 top-1/2 -translate-y-1/2 font-bold text-xl tracking-tight">
          <a href="feed.html" class="hover:text-blue-200 transition">SwiftTrack.</a>
        </div>
        <div class="w-full max-w-6xl mx-auto px-4 py-3 flex flex-col sm:flex-row items-center justify-between">
          <div class="lg:hidden font-bold text-xl mb-2 sm:mb-0 sm:mr-4">
            <a href="feed.html" class="hover:text-blue-200">SwiftTrack.</a>
          </div>
          <div class="w-full flex items-center justify-between flex-1">
            <div class="font-semibold text-sm sm:text-base flex items-center gap-2">
              <span>${greetingText}</span>
              ${isAdmin ? '<span class="bg-blue-800 text-xs px-2 py-0.5 rounded font-normal uppercase tracking-wider text-blue-200">Admin</span>' : ''}
            </div>
            <nav class="flex items-center space-x-3 sm:space-x-5 text-sm">
              <a href="feed.html" class="${currentPage === 'feed.html' ? 'text-white font-bold underline underline-offset-4' : 'text-blue-200 hover:text-white'}">Feed</a>
              <a href="report.html" class="${currentPage === 'report.html' ? 'text-white font-bold underline underline-offset-4' : 'text-blue-200 hover:text-white'}">Report</a>
              ${isAdmin ? `<a href="admin.html" class="${currentPage === 'admin.html' ? 'text-white font-bold underline underline-offset-4' : 'text-blue-200 hover:text-white'}">Admin</a>` : ''}
              <button onclick="logout()" class="text-blue-200 hover:text-red-300 transition">Logout</button>
            </nav>
          </div>
        </div>
      </header>
    `;
}

/**
 * Escapes HTML characters to prevent XSS.
 */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Checks if the user hasn't put their Supabase credentials yet and shows a helpful notice.
 */
function checkConfigNotice() {
    if (!isConfigured()) {
        const existing = document.getElementById('config-warning-banner');
        if (existing) return;

        const banner = document.createElement('div');
        banner.id = 'config-warning-banner';
        banner.className = 'bg-amber-500 text-white text-xs sm:text-sm px-4 py-2 text-center font-medium shadow-md sticky top-0 z-50';
        banner.innerHTML = `
          ⚠️ <strong>Supabase credentials needed:</strong> 
          Please open <code>supabase-config.js</code> and enter your <strong>SUPABASE_URL</strong> and <strong>SUPABASE_ANON_KEY</strong>. 
          See <code>supabase_schema.sql</code> to create your tables!
        `;
        document.body.prepend(banner);
    }
}

/**
 * Normalizes item or resolution status for any report.
 * Lost & Found: 'Still Missing' or 'Found'
 * Other: 'In Progress' or 'Resolved'
 */
function getItemStatus(report) {
    if (!report) return 'Unknown';
    if (report.status === 'pending') return 'Pending Review';
    if (report.status === 'rejected') return 'Rejected';

    const isLostFound = (report.category === 'Lost & Found');

    if (report.item_status && report.item_status.trim() !== '') {
        const lower = report.item_status.toLowerCase();
        if (lower.includes('found') || lower.includes('claimed') || lower.includes('returned')) {
            return isLostFound ? 'Found' : 'Resolved';
        }
        if (lower.includes('missing') || lower.includes('lost')) {
            return 'Still Missing';
        }
        return report.item_status;
    }

    // Fallback based on admin status
    if (isLostFound) {
        return report.status === 'resolved' ? 'Found' : 'Still Missing';
    } else {
        return report.status === 'resolved' ? 'Resolved' : 'In Progress';
    }
}

/**
 * Returns HTML badge representing the report's current status.
 */
function renderStatusBadge(report) {
    if (!report) return '';

    if (report.status === 'pending') {
        return '<span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-800 border border-yellow-200 text-xs px-2.5 py-0.5 rounded-full font-medium">⏳ Pending Review</span>';
    }
    if (report.status === 'rejected') {
        return '<span class="inline-flex items-center gap-1 bg-red-50 text-red-700 border border-red-200 text-xs px-2.5 py-0.5 rounded-full font-medium">✕ Rejected</span>';
    }

    const isLostFound = (report.category === 'Lost & Found');
    const itemStatus = getItemStatus(report);
    const isFoundOrResolved = (itemStatus === 'Found' || itemStatus === 'Resolved' || report.status === 'resolved');

    if (isLostFound) {
        if (isFoundOrResolved) {
            return '<span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-300 text-xs px-2.5 py-0.5 rounded-full font-semibold"><svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Already Found</span>';
        } else {
            return '<span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-800 border border-rose-300 text-xs px-2.5 py-0.5 rounded-full font-semibold"><svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Still Missing</span>';
        }
    } else {
        if (isFoundOrResolved) {
            return '<span class="inline-flex items-center gap-1 bg-blue-50 text-blue-800 border border-blue-200 text-xs px-2.5 py-0.5 rounded-full font-medium"><svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Resolved</span>';
        } else {
            return '<span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs px-2.5 py-0.5 rounded-full font-medium"><span class="w-2 h-2 rounded-full bg-amber-500"></span> In Progress</span>';
        }
    }
}

/**
 * Resilient updater for report item_status and status.
 * Automatically handles schema variations gracefully.
 */
async function updateReportItemStatus(reportId, newItemStatus, newStatus) {
    if (!supabaseClient) throw new Error('Supabase client not initialized');

    const updateData = {
        status: newStatus,
        updated_at: new Date().toISOString()
    };

    try {
        // Try updating both item_status and status
        const { data, error } = await supabaseClient
            .from('reports')
            .update({ ...updateData, item_status: newItemStatus })
            .eq('id', reportId)
            .select();

        if (error) {
            // If item_status column doesn't exist yet in Supabase (PGRST204), fallback to status
            if (error.code === 'PGRST204' || (error.message && error.message.includes('item_status'))) {
                const { data: fbData, error: fbError } = await supabaseClient
                    .from('reports')
                    .update(updateData)
                    .eq('id', reportId)
                    .select();
                if (fbError) throw fbError;
                return fbData?.[0] || true;
            }
            throw error;
        }
        return data?.[0] || true;
    } catch (err) {
        console.error('Error updating report status:', err);
        throw err;
    }
}

/**
 * Permanently deletes a report from Supabase by its ID.
 * Uses .select() to verify that PostgreSQL actually deleted the row.
 */
async function deleteReportById(reportId) {
    if (!supabaseClient) throw new Error('Supabase client not initialized');

    const { data, error } = await supabaseClient
        .from('reports')
        .delete()
        .eq('id', reportId)
        .select();

    if (error) {
        throw error;
    }

    if (!data || data.length === 0) {
        throw new Error(
            'Database permission denied: Supabase Row Level Security blocked deleting this report. Please run "fix_permissions.sql" in your Supabase SQL Editor.'
        );
    }

    return data[0];
}

/**
 * Updates a report in Supabase by its ID.
 * Uses .select() to verify that PostgreSQL actually updated the row.
 */
async function updateReportById(reportId, fields) {
    if (!supabaseClient) throw new Error('Supabase client not initialized');

    const updatePayload = {
        ...fields,
        updated_at: new Date().toISOString()
    };

    const { data, error } = await supabaseClient
        .from('reports')
        .update(updatePayload)
        .eq('id', reportId)
        .select();

    if (error) {
        throw error;
    }

    if (!data || data.length === 0) {
        throw new Error(
            'Database permission denied: Supabase Row Level Security blocked updating this report. Please run "fix_permissions.sql" in your Supabase SQL Editor.'
        );
    }

    return data[0];
}



