<?php 
    $ano_selecionado = $_GET['anoFiltro'] ?? date('Y');
    $mes_selecionado = $_GET['mesFiltro'] ?? date('M');
    $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Todos');
    $years = array('2024', '2025', '2026', '2027', '2028', '2029', '2030');
?>

<select class="form-select form-control" id="idAnoFiltro" name="anoFiltro">
    <?php foreach ($years as $v): ?>
        <option value="<?= $v; ?>"<?= ($v == $ano_selecionado ? ' selected ' : ''); ?>><?= $v; ?></option>
    <?php endforeach;?>
</select>

<select class="form-select form-control" id="idMesFiltro" name="mesFiltro">
    <?php foreach ($months as $v): ?>
        <option value="<?= $v; ?>"<?= ($v == $mes_selecionado ? ' selected ' : ''); ?>><?= $v; ?></option>
    <?php endforeach;?>
</select>