<div class="mb-3">
    <table id="user-activity" class="table table-sm table-hover">
        <thead>
            <tr>
                <th title="Id of the user" scope="col">#</th>
                <th title="Email of the user" scope="col">Email</th>
                <th title="Status of the user" scope="col">Status</th>
                <th title="Validation code associated to the user" scope="col">Code</th>
                <th title="Date of the last login" scope="col">Last Login</th>
                <th title="Access count to experimentor pages" scope="col">Activity</th>
                <th title="Percentage of vistited experimenter pages and navigation sections" scope="col">Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php $this->output_user_activity_rows(); ?>
        </tbody>
    </table>
</div>
