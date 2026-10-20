<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="formUserInputLogList-card-cell" data-header="<?php echo htmlspecialchars((string)$header); ?>">
<?php
if (!empty($is_html)) {
    // values_map template: CMS HTML trusted; row interpolations already escaped.
    echo $value;
} else {
    echo htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
</div>
