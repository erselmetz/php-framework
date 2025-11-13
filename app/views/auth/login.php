<h1>Login</h1>

<?php if ($error = flash('error')): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<p>This is a placeholder login page.</p>

