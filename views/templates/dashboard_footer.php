<?php
// views/templates/dashboard_footer.php (Dedicated Dashboard Footer)

// Since dashboard_header.php defined $user_type_display, we ensure it's available here
if (!isset($user_type_display)) {
    $user_type_display = "User"; // Fallback
}
?>
    <footer class="py-3 mt-auto" style="background-color: #f1f1f1;">
        <div class="container text-center">
            <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> JeevanSetu | <?php echo $user_type_display; ?> System Interface</p>
        </div>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script src="../../assets/js/main.js"></script> 

</body>
</html>