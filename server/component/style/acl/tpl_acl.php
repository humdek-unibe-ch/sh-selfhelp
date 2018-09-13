<table class="table table-hover table-sm table-responsive-sm">
    <thead class="thead-dark">
        <tr>
        <th scope="col"><?php echo $this->title; ?></th>
            <th scope="col">Select</th>
            <th scope="col">Insert</th>
            <th scope="col">Update</th>
            <th scope="col">Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php $this->output_items(); ?>
    </tbody>
</table>
