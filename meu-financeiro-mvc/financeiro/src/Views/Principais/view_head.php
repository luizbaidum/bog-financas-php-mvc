<?php
    if (!isset($_SESSION) ||
        !isset($_SESSION['logado']) ||
        $_SESSION['logado'] !== true ||
        !isset($_SESSION['user']) ||
        !isset($_SESSION['nivel'])
    ) {
        header ('location: logout?erro=true');
    }
?>

<head>
    <meta charset="utf-8">

    <title><?= $title ?? $this->empresa; ?></title>

    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/bootstrap.css.map">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/media-query.css">
</head>