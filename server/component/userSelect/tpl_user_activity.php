<div class="mb-3">
    <table id="user-activity" class="table table-sm table-hover">
        <thead>
            <tr>
                <th scope="col"><?php $this->output_title("id"); ?></th>
                <th scope="col"><?php $this->output_title("email"); ?></th>
                <th scope="col"><?php $this->output_title("status"); ?></th>
                <th scope="col"><?php $this->output_title("code"); ?></th>
                <th scope="col"><?php $this->output_title("login"); ?></th>
                <th scope="col"><?php $this->output_title("activity"); ?></th>
                <th scope="col"><?php $this->output_title("progress"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $this->output_user_activity_rows(); ?>
        </tbody>
    </table>
</div>
