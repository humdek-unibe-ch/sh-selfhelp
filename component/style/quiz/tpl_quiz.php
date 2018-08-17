<div class="quiz card mb-3">
    <div class="card-body">
        <?php echo $this->title; ?>
        <button id="quizBtn-right-<?php echo $this->id; ?>" class="btn btn-info">
            <?php echo $this->right_label; ?>
        </button>
        <button id="quizBtn-wrong-<?php echo $this->id; ?>" class="btn btn-info">
            <?php echo $this->wrong_label; ?>
        </button>
        <div id="quizContent-right-<?php echo $this->id; ?>" class="card bg-light" style="display:none">
            <div class="card-body">
                <?php echo $this->right_content; ?>
            </div>
        </div>
        <div id="quizContent-wrong-<?php echo $this->id; ?>" class="card bg-light" style="display:none">
            <div class="card-body">
                <?php echo $this->wrong_content; ?>
            </div>
        </div>
    </div>
</div>
