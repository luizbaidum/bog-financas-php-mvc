<?php use src\Diretorio; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? $this->view->settings['title'] ?? 'Bog Finanças'; ?></title>
    <link rel="stylesheet" href="<?= Diretorio::getBaseUrl() ?>/css/bootstrap.css">
    <link rel="stylesheet" href="<?= Diretorio::getBaseUrl() ?>/css/custom.css">
    <link rel="stylesheet" href="<?= Diretorio::getBaseUrl() ?>/css/media-query.css">
    <link rel="stylesheet" href="<?= Diretorio::getBaseUrl() ?>/css/navbar-style.css">
</head>