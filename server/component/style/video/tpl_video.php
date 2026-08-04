<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<video class="<?php echo $fluid; ?> <?php echo $this->css; ?>" controls<?php
if ($this->tracking_enabled) {
    echo ' data-track-interval="' . intval($this->track_interval_seconds) . '"';
    echo ' data-section-id="' . intval($this->id_section) . '"';
    echo ' data-page-keyword="' . htmlspecialchars($this->track_page_keyword, ENT_QUOTES, 'UTF-8') . '"';
    echo ' data-source="' . htmlspecialchars($this->track_source, ENT_QUOTES, 'UTF-8') . '"';
}
?>>
    <?php $this->output_video_sources(); ?>
    <?php echo $this->alt; ?>
</video>
