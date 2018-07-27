<div class="card card-body mb-3">
    <form class="mb-1">
        <input class="form-control list-search" placeholder="<?php echo $search_text; ?>">
        <button type="button" class="close clear-search" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </form>
    <div class="list-group list-group-root">
        <?php $this->output_list_items($items); ?>
    </div>
</div>
