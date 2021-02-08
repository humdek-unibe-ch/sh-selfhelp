<div class="card-footer d-flex align-items-center">
    <div>
        <?php $this->output_message_footer_icons($icons) ?>
    </div>
    <div class="ml-auto">
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Comment
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <?php $this->output_message_footer_comments($comments) ?>
            </div>
        </div>
    </div>
</div>
