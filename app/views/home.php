<?php if ($notice = flash('notice')): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if ($success = flash('success')): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<h1><?= htmlspecialchars($headline ?? 'Welcome to the Simple PHP Framework', ENT_QUOTES, 'UTF-8') ?></h1>

<p>
    Explore the framework by creating controllers inside <code>app/controllers</code>
    and views inside <code>app/views</code>.
</p>

<div class="features">
    <h2>Available Features</h2>
    <ul>
        <li><a href="<?= route('form.show') ?? '/form' ?>">Form Example</a> - Form validation with CSRF protection</li>
        <li><a href="<?= route('api.users') ?? '/api/users' ?>">API Users</a> - JSON API endpoint using User model</li>
        <li><a href="<?= route('api.cached') ?? '/api/cached' ?>">Cached API</a> - Example of caching functionality</li>
    </ul>
</div>