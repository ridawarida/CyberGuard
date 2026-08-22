<style>
    .admin-top-menu { background: #ffffff; border: 1px solid #e8e0db; box-shadow: 0 4px 16px #30231b0b; padding: 12px 16px; margin-bottom: 22px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
    .admin-menu-brand { color: #20242b; font-weight: 800; text-decoration: none; }
    .admin-menu-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .admin-menu-link { background: transparent; border: 0; color: #5e6268; text-decoration: none; padding: 7px 9px; font-size: 13px; }
    .admin-menu-link:hover { color: #b21e3b; }
    .admin-menu-logout { cursor: pointer; }
</style>

<nav class="admin-top-menu" aria-label="Admin navigation">
    <a href="{{ route('admin.dashboard') }}" class="admin-menu-brand">
        <i class="fas fa-shield-halved me-2"></i>Admin workspace
    </a>
    <div class="admin-menu-actions">
        <a href="{{ route('admin.dashboard') }}" class="admin-menu-link">
            <i class="fas fa-chart-line me-1"></i>Dashboard
        </a>
        <a href="{{ route('admin.help-directory.index') }}" class="admin-menu-link">
            <i class="fas fa-location-dot me-1"></i>Help directory
        </a>
        <form method="POST" action="{{ route('staff.logout') }}" class="m-0">
            @csrf
            <button type="submit" class="admin-menu-link admin-menu-logout">
                <i class="fas fa-arrow-right-from-bracket me-1"></i>Log out
            </button>
        </form>
    </div>
</nav>
