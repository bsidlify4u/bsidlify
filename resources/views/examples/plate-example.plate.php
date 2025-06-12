<!DOCTYPE html>
<html lang="<?= app()->getLocale() ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Plates Example - <?= $this->e($title) ?></title>
        
        <?php if (app()->environment('local')): ?>
            <script src="http://localhost:3000/<?= $asset ?>"></script>
        <?php else: ?>
            <link rel="stylesheet" href="<?= $asset ?>">
        <?php endif; ?>
    </head>
    <body>
        <div class="container">
            <h1><?= $this->e($title) ?></h1>
            
            <p>This is an example Plates template in Bsidlify.</p>
            
            <?php if (isset($items) && count($items) > 0): ?>
                <ul>
                <?php foreach ($items as $item): ?>
                    <li><?= $this->e($item) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No items to display.</p>
            <?php endif; ?>
            
            <p>Current time: <?= date('Y-m-d H:i:s') ?></p>
            
            <?php $this->insert('examples/partials/footer.plate') ?>
        </div>
    </body>
</html> 