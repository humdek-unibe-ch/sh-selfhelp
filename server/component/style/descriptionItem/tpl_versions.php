<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a href="<?php echo htmlspecialchars($this->versions['url']); ?>" class="field-versions-link ml-auto" title="Show the version history of this field">
    <i class="fas fa-history"></i> History<?php if ($this->versions['version_no']) { ?> <span class="field-versions-badge">v<?php echo intval($this->versions['version_no']); ?></span><?php } ?>
</a>
