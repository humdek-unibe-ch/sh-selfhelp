<?php $this->output_controller_alerts_fail(); ?>
<?php $this->output_controller_alerts_success(); ?>
<div class="<?php echo $this->css; ?>">   
   <div class="mermaidDiagram">
      <?php echo $code; ?>
   </div>
   <div id='mermaidFormEditableFields' class="mermaidFormHidden"><?php echo $fields; ?></div>
   <div id='mermaidFormName' class="mermaidFormHidden"><?php echo $formName; ?></div>
   <div id="<?php echo $formName; ?>" class="modal fade">
      <div class="modal-dialog">    
         <!-- Modal content-->
         <div class="modal-content"><?php $this->output_modal(); ?></div>
      </div>
   </div>
</div>
