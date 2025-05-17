<style>
.topbar-user-info {
    font-weight: bold !important;
    margin-right: 1rem;
}
.navbar {
    z-index: 1030; /* Lower than sidebar */
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    padding-left: 80px; /* Space for collapsed sidebar */
    transition: padding-left 0.3s;
}
.main-content {
    margin-top: 60px; /* Height of topbar */
    margin-left: 80px; /* Width of collapsed sidebar */
    transition: margin-left 0.3s;
}
</style>

<nav class="navbar navbar-expand navbar-light bg-dark topbar static-top shadow">
    <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-white small topbar-user-info">
                    <?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?>
                </span>
                <span class="badge bg-<?php 
                    echo $_SESSION['type'] == 1 ? 'danger' : 
                         ($_SESSION['type'] == 2 ? 'primary' : 'secondary'); 
                ?>">
                    <?php 
                        echo $_SESSION['type'] == 1 ? 'Admin' : 
                             ($_SESSION['type'] == 2 ? 'Manager' : 'Employee'); 
                    ?>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="view_user.php?id=<?php echo $_SESSION['user_id']; ?>">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Logout Modal remains the same -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>