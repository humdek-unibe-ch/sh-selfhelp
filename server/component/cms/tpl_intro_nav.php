<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="alert alert-info py-5 px-4">
    <h1>This is a Navigation Page</h1>
    <p>
        Like a normal page, a <em>navigation page</em> consist of a set of sections.
        However, unlike a normal page, a navigation pages does not render sections one below the other but renders only one root section, called <em>navigation section</em>, at a time.
        The purpose of a navigation page is to navigate from one section to another without changing the base url.
        The section to render is indicated by a trailing id in the url.
    </p>
    <p>
        The card <code>Navigation Hierarchy</code> on the left shows how the navigation sections are ordered.
        Note that this list can be hierarchical which allows to create navigation section children.
        The order and the hierarchy of the naviagtion sections can be changed in each individual navigation section with the field <code>navigation</code>.
        Only navigation sections have this field.
    </p>
    <p>
        The same as with normal pages or sections, the field <code>sections</code> allows to add child sections that are rendered inside the page or section one below the other.
        Note, however, that sections added to the <code>sections</code> field on the <em>navigation page</em>, will appear on every <em>navigation section</em> before the content of the navigation section is renderd.
    </p>
    <p>
        Each navigation section includes a navigation pannel that allows to select a specific section to render. By default this is an <em>accordion list</em>.
        Properties for the specific style appear in the card <code>Page Properties</code> of a navigation page.
    </p>
</div>
