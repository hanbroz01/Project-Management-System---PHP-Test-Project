<footer class="site-footer">
        <p>&copy; <?php echo date('Y'); ?> Employee Dashboard Management System. All Rights Reserved.</p>
    </footer>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div id="flash-data" data-message="<?php echo htmlspecialchars($_SESSION['flash_message']); ?>"></div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            const flashElement = $('#flash-data');
            if (flashElement.length) {
                const message = flashElement.data('message');
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "5000"
                };
                toastr.success(message, 'System Notification');
            }
        });
    </script>
</body>
</html>