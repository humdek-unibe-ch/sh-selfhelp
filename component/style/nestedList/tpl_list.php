<form class="mb-1">
    <input class="form-control list-search" placeholder="<?php echo $search_text; ?>">
    <button type="button" class="close clear-search" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</form>
<div class="list-group nested-list">
    <?php $this->output_list_items($items); ?>
</div>
