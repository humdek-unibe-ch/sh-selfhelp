<div class="quiz card mb-3">
    <div class="card-body">
        <?php echo $title; ?>
        <button id="quizBtn-right-<?php echo $id; ?>" class="btn btn-info">
            <?php echo $right_label; ?>
        </button>
        <button id="quizBtn-wrong-<?php echo $id; ?>" class="btn btn-info">
            <?php echo $wrong_label; ?>
        </button>
        <div id="quizContent-right-<?php echo $id; ?>" class="card bg-light" style="display:none">
            <div class="card-body">
                <?php echo $right_content; ?>
            </div>
        </div>
        <div id="quizContent-wrong-<?php echo $id; ?>" class="card bg-light" style="display:none">
            <div class="card-body">
                <?php echo $wrong_content; ?>
            </div>
        </div>
    </div>
</div>
