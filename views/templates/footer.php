<?php
// views/templates/footer.php
?>
<footer class="py-4 mt-auto" style="background-color: var(--dark-text);">
    <div class="container">
        <div class="row justify-content-between">

            <!-- About -->
            <div class="col-md-6 mb-3 text-light">
                <h5 class="font-weight-bold mb-2">JeevanSetu</h5>
                <p class="text-muted small mb-0">
                    A simplified national platform for managing blood and organ donations, 
                    built to support government health services with transparency and efficiency.
                </p>
            </div>

            <!-- Login / Register -->
            <div class="col-md-3 mb-3 text-light text-md-right">
                <h6 class="font-weight-bold mb-2">Access</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="login.php" class="text-light small">Login</a></li>
                    <li><a href="register.php" class="text-light small">Register</a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom -->
        <div class="row mt-3 pt-3 border-top border-secondary">
            <div class="col-12 text-center text-muted small">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> JeevanSetu. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="../../assets/js/main.js"></script>

</body>
</html>
