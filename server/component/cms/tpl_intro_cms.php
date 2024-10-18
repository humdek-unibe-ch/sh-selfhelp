<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="bg-light mb-4 rounded-2 py-5 px-3">
    <h1>Content Management System (CMS)</h1>
    <p>
        The Content Management System (CMS) allows to modify the content of existing pages and create new pages or delete existing pages.
    </p>
    <p>
        Pages are organised as a collection of sections that are rendered on the page, one below the other.
        Sections have different styles wich define the apperance of the sections.
        Depending on the style of a section, the section has different fields which define the content of the section.
        The value of a field can be a simple plaintext or a collection of child sections which have their own styles and children.
    </p>
    <p>
        Navigate to the available pages with the help of the <code>Page Index</code> card.
        Once a page is selected a preview of the page is shown (if available) and some properties of the page.
        If sections are associated to the page a new card <code>Page Sections</code> will appear which allows to select individual sections of the page and access the fields of the selected section.
        The hierarchical path of the current selected element is shown at the top of the page.
    </p>
    <p>
        To edit the content of a page or section, use the button <i class="fas fa-edit"></i> in the header of the card <code>Page Properties</code> or <code>Section Properties</code> (if available). In the edit mode sections can be created, removed, or ordered and the values of fields can be changed.
        Property cards are highligted in yellow whenever the edit mode is active.
    </p>
</div>
