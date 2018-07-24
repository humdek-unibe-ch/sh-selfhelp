<div class="container-fluid my-3">
    <div class="row">
        <div class="col-3 d-none d-lg-flex">
            <?php $this->output_nav(); ?>
        </div>
        <div class="col-12 col-lg-9">
            <h1><?php echo $title; ?></h1>
            <?php $this->output_children(); ?>
            <div>
                <?php $this->output_button($label_back, $url_back); ?>
                <?php $this->output_button($label_next, $url_next); ?>
            </div>
        </div>
    </div>
</div>
