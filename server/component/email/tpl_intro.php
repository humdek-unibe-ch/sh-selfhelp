<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="jumbotron">
    <h1>Manage Emails</h1>
    <p>Change the content of emails that are sent to users.</p>
    <p>Only use plaintext. However, the following keywords can be used to create dynamic content:</p>
    <ul class="">
        <li><code>@project</code> will be replaced by the project name (the title of the page <code>home</code>).</li>
        <li><code>@link</code> will be replaced by a link that is specific to the email it is used in:</li>
        <ul>
            <li>In <code>email_activate</code> the activation link is generated.</li>
            <li>In <code>email_reminder</code> the link to the project home is generated.</li>
        </ul>
    </ul>
</div>
<div class="card card-body">
    <p>The following styles allow to define the content of automatically sent emails. Use the normal <a href="<?php echo $cms_url; ?>">CMS</a> to edit them:</p>
    <ul>
        <li><code>chat</code> allows to define the notification email sent upon receiving a new chat message.</li>
        <li><code>reset_password</code> allows to define the email sent when resetting a password.</li>
        <li><code>emailForm</code> allows to define two emails, one to be sent to a new email address entered in a form and one to be sent to a list of adimintrators, notifying them about the new email.</li>
    </ul>
</div>
