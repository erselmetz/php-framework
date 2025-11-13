<?php if ($notice = flash('notice')): ?>
    <div class="alert alert-info">
        <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<h1><?= htmlspecialchars($headline ?? 'Welcome to the Simple PHP Framework', ENT_QUOTES, 'UTF-8') ?></h1>

<p>
    Explore the framework by creating controllers inside <code>app/controllers</code>
    and views inside <code>app/views</code>.
</p>