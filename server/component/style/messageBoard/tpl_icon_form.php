<form class="d-inline" action="<?php echo $url; ?>" method="post">
    <fieldset class="d-inline" <?php echo $disabled ? 'disabled="disabled"' : ""; ?>>
        <input class="form-control" type="hidden" name="reply[value]" value="<?php echo $icon; ?>">
        <input class="form-control" type="hidden" name="reply[id]" value="<?php echo $id_reply; ?>">
        <input class="form-control" type="hidden" name="link[value]" value="<?php echo $record_id; ?>">
        <input class="form-control" type="hidden" name="link[id]" value="<?php echo $id_link; ?>">
        <input class="form-control" type="hidden" name="__form_name" value="<?php echo $form_name; ?>">
        <button type="submit" class="btn btn-light">
            <?php $this->output_icon($icon); ?>
            <?php echo $count ? $count : ""; ?>
        </button>
    </fieldset>
</form>

