<?php $this->output_controller_alerts_fail(); ?>
<?php $this->output_controller_alerts_success(); ?>
<div id="section-<?php echo $this->id_section; ?>" class="mermaidForm <?php echo $this->css; ?>">
    <div class="mermaidCode"><?php echo $code; ?></div>
    <div class="mermaidFormEditableFields mermaidFormHidden"><?php echo $fields; ?></div>
    <div class="mermaidFormName mermaidFormHidden"><?php echo $formName; ?></div>
    <?php $this->output_modal(); ?>
</div>
