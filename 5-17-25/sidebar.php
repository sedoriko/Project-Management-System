<style>
.sidebar {
    width: 80px;
    position: fixed;
    height: 100%;
    transition: all 0.3s;
    z-index: 1031;
    left: 0;
    top: 0;
    overflow-x: hidden;
}
.sidebar:hover {
    width: 250px;
}
.sidebar-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.sidebar-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}
.sidebar-text {
    display: none;
    margin-left: 10px;
    white-space: nowrap;
}
.sidebar:hover .sidebar-text {
    display: inline;
}
.sidebar-icon {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
}
.sidebar:hover .sidebar-icon {
    display: none;
}
</style>

<div class="sidebar bg-dark text-white" id="sidebar">
    <div class="sidebar-brand">
        <img src="/project_management/image/logo.png" class="sidebar-logo" alt="Logo">
        <h4 class="sidebar-text">Infinity Co.</h4>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="home.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                    <i class="fas fa-fw fa-tachometer-alt mx-1"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            
            <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="project_list.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Projects">
                        <i class="fas fa-fw fa-project-diagram mx-1"></i>
                        <span class="sidebar-text">Projects</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link text-white" href="task_list.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Tasks">
                        <i class="fas fa-fw fa-tasks mx-1"></i>
                        <span class="sidebar-text">Tasks</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link text-white" href="manage_progress.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Productivity">
                    <i class="fas fa-fw fa-clock mx-1"></i>
                    <span class="sidebar-text">Productivity</span>
                </a>
            </li>
            
            <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="reports.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Reports">
                        <i class="fas fa-fw fa-chart-bar mx-1"></i>
                        <span class="sidebar-text">Reports</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['type'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="user_list.php" data-bs-toggle="tooltip" data-bs-placement="right" title="User Management">
                        <i class="fas fa-fw fa-users mx-1"></i>
                        <span class="sidebar-text">User Management</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Mobile Toggle Button -->
<button id="sidebarToggle" class="btn btn-dark d-md-none" style="z-index: 1032; position: fixed; top: 10px; left: 10px;">
    <i class="fa fa-bars"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    let isExpanded = false;
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Toggle for mobile
    sidebarToggle.addEventListener('click', function() {
        isExpanded = !isExpanded;
        if (isExpanded) {
            sidebar.style.width = '250px';
        } else {
            sidebar.style.width = '80px';
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar.style.width = isExpanded ? '250px' : '80px';
            sidebar.style.pointerEvents = 'auto';
        } else {
            sidebar.style.width = '80px';
            sidebar.style.pointerEvents = 'auto';
        }
    });
});
</script>