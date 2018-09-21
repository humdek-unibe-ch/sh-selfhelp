<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Upload Asset File</h1>
        <p>Browse for the file you want to upload and provide a name under which the file will be stored on the server.</p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Upload</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post" enctype='multipart/form-data'>
                <div class="row">
                    <div class="form-group col">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter a file name (no extension)" required>
                    </div>
                    <div class="form-group col">
                        <label>File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="file" required>
                            <label class="custom-file-label text-muted">Choose file</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
            </form>
        </div>
    </div>
</div>
