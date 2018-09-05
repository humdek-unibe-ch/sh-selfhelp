<div class="mb-2 <?php echo $border; ?>">
    <div class="d-flex"><strong><?php echo $this->title; ?></strong> <?php $this->output_type(); ?> <small class="ml-auto"><?php echo $this->locale; ?></small></div>
    <div><?php $this->output_field_content(); ?></div>
</div>
