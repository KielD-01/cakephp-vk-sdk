<!DOCTYPE HTML>
<html>
<head>
    <title>VK SDK Engine</title>
    <?= $this->Html->css('../node_modules/materialize-css/dist/css/materialize.min') ?>
    <?= $this->Html->css('dev') ?>
    <?= $this->Html->script('../node_modules/jquery/dist/jquery') ?>
    <?= $this->Html->script('../node_modules/materialize-css/dist/js/materialize.min') ?>
    <?= $this->Html->script('../node_modules/angular/angular.min') ?>
    <?= $this->Html->script('../node_modules/angular-route/angular-route.min') ?>
    <?= $this->Html->script('../node_modules/angular-sanitize/angular-sanitize.min') ?>
    <?= $this->Html->script('sdk_app') ?>

    <?= $this->fetch('css') ?>
    <?= $this->fetch('js') ?>
    <?= $this->fetch('node_modules') ?>
</head>
<body ng-app="sdkMain">
<?= $this->element('ui/navbar') ?>
<div class="container vert-margins">
    <?= $this->fetch('content') ?>
</div>
<?= $this->element('ui/footer') ?>
</body>
</html>