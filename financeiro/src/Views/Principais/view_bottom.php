<?php 
    use src\Diretorio; 
    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/primeiro-acesso'):
?>
    <script src="<?= Diretorio::getBaseUrl() ?>/scripts/primeiro-acesso.js?<?= round(microtime(true), 0); ?>"></script>
<?php else: ?>
    <script src="<?= Diretorio::getBaseUrl() ?>/js/jquery.js?<?= round(microtime(true), 0); ?>"></script>
    <script src="<?= Diretorio::getBaseUrl() ?>/js/bootstrap.bundle.js?<?= round(microtime(true), 0); ?>"></script>
    <script src="<?= Diretorio::getBaseUrl() ?>/scripts/geral.js?<?= round(microtime(true), 0); ?>"></script>
    <script src="<?= Diretorio::getBaseUrl() ?>/scripts/formatations.js?<?= round(microtime(true), 0); ?>"></script>
<?php endif; ?>

<script src="<?= Diretorio::getBaseUrl() ?>/scripts/functions.js?<?= round(microtime(true), 0); ?>"></script>