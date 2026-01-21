<?php
    use src\System\MonthAndYear;

    $years = MonthAndYear::getYears();
    $months = MonthAndYear::getMonths();

    $ano_selecionado = $_GET['anoFiltro'] ?? date('Y');
    $mes_selecionado = $_GET['mesFiltro'] ?? date('m');
?>

<select class="form-select form-control" id="idAnoFiltro" name="anoFiltro">
    <?php foreach ($years as $v): ?>
        <option value="<?= $v; ?>"<?= ($v == $ano_selecionado ? ' selected ' : ''); ?>><?= $v; ?></option>
    <?php endforeach;?>
</select>

<select class="form-select form-control" id="idMesFiltro" name="mesFiltro">
    <?php foreach ($months as $k => $v): ?>
        <option value="<?= $k; ?>"<?= ($k == $mes_selecionado ? ' selected ' : ''); ?>><?= $v; ?></option>
    <?php endforeach;?>
</select>