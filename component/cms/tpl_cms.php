<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_page_list(); ?>
        </div>
        <div class="col d-block">
            <?php $this->output_page_content(); ?>
        </div>
        <div class="col-auto">
            <?php $this->output_global_section_list(); ?>
            <?php $this->output_page_section_list(); ?>
            <?php $this->output_section_hierarchy_list(); ?>
        </div>
    </div>
</div>
