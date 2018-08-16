<div class="container mt-3">
    <div class="jumbotron">
        <h1>Create a new Section</h1>
        <?php $this->output_title(); ?>
    </div>
    <div class="card card-body mb-3">
        <form action="<?php echo $url; ?>" method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="section-name" class="form-control" placeholder="Enter Section Name" required>
            </div>
            <div class="form-group">
                <label>Style</label>
                <select class="form-control" name="section-style">
                    <?php $this->output_style_list(); ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Section</button>
            <a href="<?php echo $url; ?>" class="btn btn-secondary float-right">Cancel</a>
        </form>
    </div>
</div>
