<?php 
    $mes_selecionado = $_POST['mesFiltro'] ?? date('M');
    $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Todos'); 
?>

<select class="form-select form-control" id="idMesFiltro" name="mesFiltro">
    <?php foreach ($months as $v): ?>
        <option value="<?= $v; ?>"<?= ($v == $mes_selecionado ? ' selected ' : ''); ?>><?= $v; ?></option>
    <?php endforeach;?>
</select>