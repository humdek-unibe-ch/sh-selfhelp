<div class="jumbotron">
    <h1>Manage Emails</h1>
    <p>Change the content of emails that are sent to users.</p>
    <p>The following keywords can be used to create dynamic content:</p>
    <ul class="">
        <li><code>@project</code> will be replaced by the project name (the title of the page <code>home</code>).</li>
        <li><code>@link</code> will be replaced by a link that is specific to the email it is used in:</li>
        <ul>
            <li>In <code>email_activate</code> the activation link is generated.</li>
            <li>In <code>email_reset</code> the link to reset the password is generated.</li>
            <li>In <code>email_reminder</code> the link to the project home is generated.</li>
        </ul>
    </ul>
</div>
