# v6.6.0 (Not published)
### New features
 - `data_config` - `all_fields` is enabled by default and it is set to `true`
 - add `live_search` to `tables` and `fields` in the `data config builder`

# v6.5.2
### Bugfix
 - properly set default language on a new user 
 - `REDIRECT_ON_LOGIN` do not redirect if the last keyword was starting with `ajax_`

# v6.5.1
### New features
 - #368 - when `debug` is enabled in `console.log` is printed the field object and what data is retrieved. 

# v6.5.0
### New features
 - move data config fetch in `BaseModel` from `StyleModel` 
 - #362 - add option for `Last Login`, where we can use `never` or we can use date as `__current_date__` where it is replaced by the current date or `__current_date__@-5 days` where it takes the date for 5 days ago. It only can be used with days + and - days after `@`
 - #331 - add fields `min` and `max` for characters size in style `textarea`
 - #331 - add fields `min` and `max` for characters size in style `input`
 - #330 - check the user locale and if the locale exists it is loaded as default for request
 - #330 - check the user locale and if the locale exists it is loaded as default for request (for mobile)
 - add get all fields function in the basic `StyleView`
 - add global variable `SELECTED_RECORD_ID`
 - !16 - add `impersonate` functionality (only for `TPF` user)

### Bugfix
 - #364 - fix settings for notifications in SelfHelp mobile app

# v6.4.9
### Bugfix
 - `showUserInput` - properly show the label of the field
 - `select` - properly display items for mobile
 - massively improve loading large amount of data in `admin/data` view and remove the limit to `10000` rows

# v6.4.8
### Bugfix 
  - fix in `formActions` proper selection for `Schedule type`

### New features
  - merge `db version` and `git version` in one row for `Impressum`

# v6.4.7
 - add slashes to json strings when loaded from form actions

# v6.4.6
 - enable `log_bin_trust_function_creators` during installation

# v6.4.5
### Bugfix
 - fix giving permissions to a new user on SelfHelp installation for MySQL8
 - adjust old SQL scripts containing groups to be `groups` in order to work with MySQL8

# v6.4.4
### Bugfix
 - updating external row data is for the current user only
 - insert external row or update external row return the row id on success
 - fix notification body when the action is sent


# v6.4.3
### Bugfix
 - #363 - fix callback requests and data export requests

# v6.4.2
### Bugfix
 - fix upload static data file handling and error handling
 - fix default user for automated tasks' executions

# v6.4.1
### Bugfix
 - properly load edit and delete mode for entry in mobile app

# v6.4.0
### New features
 - add user types to the user. All existing users will be set of type `user`
 - update the Group management description
 - create class `Selfhelp` that initialize the application.
 - add column `id_users` in table `uploadRows`. Adjust all upload table functionality to use the user from this column
 - add extra filtering on `EXTERNAL_FORMS` via keywords: `LAST_HOUR, LAST_DAY, LAST_WEEK, LAST_MONTH, LAST_YEAR`
 - add option to wrap internal `output_alerts` in container
 - add transaction log when a user is deleted
 - style `form` add internal confirmation data 

### Bugfix
 -  adjust `entryRecord` to load children like `entryList` and now it properly load values of records in edit mode
 - #358 - improve the time needed to check if multiple people are editing the same page
 - bring back secured cookies with same site: Lax

# v6.3.1
### Bugfix
 - fix an error when checking how many users are working on the same page

# v6.3.0
### New features
 - dynamically load the schema for the `jobConfig`
 - adjust scheduling `jobs` to be expandable and used by `plugins`
 - modify column `config` to be `longtext` in table `tasks`
 - catch `checkbox_value` if sent for a `input` from type `checkbox`
 - add `fieldType` `password`
 - move the alert for saving CMS fields on the properties column
 - improve the drag and drop of elements in the CMS page
 - add new field type `panel`. In this field we can add extra elements in customization mode
 - #353 - adding transactions info to scheduled job id page
 - #352 - filtering actions on calendar view
 - #350 - add table `libraries`. The data in the table is loaded in the style `version` to visualize which libraries are used.
 - move all `globals` from the main CMS menu to the pages on the left side on a separate holder
 - add global page with `security questions`
 - add `anonymous users` option
 - add `anonymous users` option to reset password if the security questions' answers match
 - add `mobile_redirect_url` in the session that is sent to apps if needed

### Bugfix
- bugfix the live search in the dropdown for selecting groups for job actions from type `add_group` or `remove_group`;
- now `condition_on_execute` is properly used for jobs form type `tasks`
- add margin to the home button on missing page
- #357 - is_multiple in select does not save data properly.

# v6.2.0
### New features
 - make function `calc_dynamic_values` protected in `StyleModel`
 - add class `selfHelp-locale-language_code` example: `selfHelp-locale-de-CH` in the style
 - in `admin/data` hide all columns starting with "_"
 - track activity for admin pages and show notifications when multiple people work on the same page

###  Bugfix
 - save external data on update properly add new columns if they do not exists
 - fix the size of the `markdown` fields in a style in the CMS
 - #356 - fix condition check from cronjob

# v6.1.6
###  Bugfix
 - adding input fields as children in style `validate` are saved in the DB as user input data

# v6.1.5
###  New features
 - add `__keyword__`, `__platform__`, `__language__` to the global variables and they can be loaded with `{{}}`, ex `{{__keyword__}}` for the `data_config`

# v6.1.4
###  New features
 - add `__keyword__`, `__platform__`, `__language__` to the global variables and they can be loaded with `{{}}`, ex `{{__keyword__}}`

# v6.1.3
###  New features
 - `reminders` in `Jobs` inherit `on_execute_condition` from the `notification`;

# v6.1.2
### Bugfix
 - fix the bug when dynamic data overwrite the variable with the retrieved value in the form action and later the variable is gone for further caught actions.

# v6.1.1
###  New features
 - add extra logging when time is calculated for scheduling actions 

# v6.1.0
###  New features
 - add user_id to the jobs when an email is sent from the system
 - add `View calendar` to `scheduled_jobs` where all scheduled jobs can be shown in a calendar for a user
 - add button `View Action` on scheduled job view, if the job was scheduled from an action. The button leads to the action that scheduled the job
 - in style `register` - now for group we can select multiple groups which will be assigned to the user
 - separate `groups` in `groups` and `db_roles`. The groups can give access to `experimental` pages and can be used in conditions. DB roles are used to give privileges and are used for management tool.
 - `conditions` are based now only on `groups` from type `group`. The DB roles groups cannot be used in conditions and they cannot be automatically add or removed from an user.
 - #348 - In user profile there are 2 new fields `Do not send emails` and `Do not send push notifications`. When they are enabled the user will not receive the selected type of messaging

### Bugfix
 - #347 - fix the tabs loading
 - #346 - fix that the condition is not refreshed when the form is submitted and the condition is in the form

# v6.0.0
###  New features
 - rework module `Form Actions`. If you scheduled form actions in the past please contact support
  - old actions should be manually checked after the update. They will be in table `old_formActions`. The related tables should be checked `formActions_groups` and `formActionsReminders` and once the migration is done these tables can be deleted
 - update `jsoneditor.min.js` from `2.6.1` to `2.9.1`
 - better labeling and colors for `data_config` and `condition` buttons based on if a configuration is set or not
 - add new internal style `jobConfig`
 - refactor user input data to be with types: `internal`, `external` instead of `dynamic`, `static`

###  Bugfix
 - properly load form reminder in `action` with `reminders`

### Plugins
 - <code>`fitrockr`</code> plugin should be updated to v1.0.4

# v5.11.3
### Security Update
 - make cookies secure and samesite = strict

# v5.11.2
###  Bugfix
 - add `cache` for loading user_input forms

# v5.11.1
###  New features
 - add `_index` in `entryList` which keep the index of the array elements

# v5.11.0
### Security Update
 - adjust csp_rules to include `unsafe-inline` instead of `unsafe-eval`. Update jQueryBuilder with this [version](https://github.com/lol768/jQuery-QueryBuilder)

###  New features
 - add db function `drop_table_column`
 - add db function `add_foreign_key`
 - add db function `drop_foreign_key`
 - add field `internal` to styles: `formUserInput`, `formUserInputLog` and `formUserInputRecord`. If internal is enabled the form data is not shown and can be used only internally.
 - #342 add an option to set a default value for `gender` and `name`(user_name) in style `validation`. If the default value is set the fields will be hidden.
 - #343 add a field `email_activate_email_address` in `Email Templates`. If the field is set, it will be used as an email from where is sent the message and where it can be replied.
 - #344 add fields in `Email Templates` for sending emails when a profile is deleted. If the fields are filled the email will be sent to the user. If the field `email_delete_profile_email_address_notification_copy` is set, another email will be sent to this address. This functionality is used to notify admin users that user in the system deleted their profile.

### Bugfix
 - `select` when `required` now properly check if it is filled
 - style `showUserInput` show `timestamp` instead of `record_id`
 - properly assign the value for `field_not_found` for custom fields in `data_config`;

# v5.10.1
###  New features
 - add a method for executing parent methods in `hooks`
 - add `help` tooltip loading for style `descriptionTime` 

### Bugfix
 - show correctly when one user is selected in `user data`
 - fix `i` tooltip placement on top and its loading

# v5.10.0
### Bugfix
 - fix errors when sending validation email (php8)
 - fix add children to style `validate` using new UI;
 - do not load `globals` and `modules` pages in the mobile app
 - fix `css` upload files in the assets when there are no `css` files

###  New features
 - add backend changes for mobile preview in WEB
 - add `custom_css` page where a raw `css` can be added
 - for `backend` pages hide the middle-ui

# v5.9.0
### Bugfix
 - properly check if form for use_preferences exists on a new setup

###  New features
 - add field `redirect` to style `validate`. If a page is selected in the validate style, the user will be logged in and redirected to that page after a successful validation of the user account
 - add option to clear selected value in `select` input

# v5.8.0
### Bugfix
 - Properly order a static table data

# v5.7.0
### Bugfix
 - Show field `valid` for notifications scheduled from action for form. The filed is used for notifications that have reminders attached to them.

###  New features
 - Add an option to group assets in folders

# v5.6.1
###  New features
 - add return option `all_as_array` in the `data_config_builder`. This will be used for easy graph data loading

# v5.6.0

- change the type of field `filter` from `text` to `code`
- adjust dynamic store procedures to work better with `filters`

# v5.5.0
###  Bugfix
 - fix `source` field for images to be copied form the display values (after changing the field type to display none)

# v5.4.1
###  Bugfix
 - proper loading New UI buttons for navigation section children

# v5.4.0
###  Bugfix
 - set `admin-link` to be a `backend` page
 - fix remove `entry_list` from page or another section

 ###  New features
 - add `confirmation_title`, `label_cancel`, `label_continue`, `label_message` fields for style `button`
 - add style `loop` to core Selfhelp
 - add styles: `table`, `tableRow`, `tableCell`;

# v5.3.0 - PHP 8 Support
###  New features
 - Check if a JSON value is dynamically set with {{}} and if it is accept it
 - All dynamic variables can be accessed with {{var_name}} where the var_name is the name of the variable
 - Fix the `entry_list` style for the new UI
 - add field `all_fields` in `data_config`. Now if this is true it loads all fields as variables which can be accessed with {{var_name}} where var_name is the name of the field/column
 - add page `translations` where the users can define global translation keys and use the key to load the proper translation based on the selected language. A key is accessed by {{key_name}}, and this will be replaced with the value for the selected language
 - separate CMS from website, now the CMS can be accessed at `/admin/cms` when manually added
 - move page `email` to page `globals` and convert the page to `backend` page
 - clear user data now clear the static data for the user too
 - **BREAKING** - now when we retrieve all data from data config it return a coma separated string instead of "['ex1','ex2','ex3']"
 - add an option to show php_info. If the global `SHOW_PHP_INFO` is set to `true` in `globals_untracked.php`
 
 ###  Bugfix
 - update flatpickr to v4.6.13, now time values can be cleared

# v5.2.4
###  Bugfix
 - bring back extendedPDO for nested transactions

# v5.2.3
###  Bugfix
 - Load properly groups in `actions`

# v5.2.2
###  Bugfix
 - In `conditionBuilder` a field can have empty string (`''`) as value in comparison

# v5.2.1
###  New features
 - #333 - show `avatar` image in profile id a form `avatar` exists and the user selected an avatar picture

# v5.2.0
###  New features
 - delete reminders for form actions when the correct form is filled
 - add `email_activate_subject` and `email_reminder_subject` fields for configuration when sending emails
 - add an option to attach files when schedule a mail action for forms
 - add language check `condition`
 - add an option for multiple selection of jobs in `scheduledJobs` and then `execute` them or `delete` them
 - for `formActions` list on right click open edit or view action in a new tab
 - add `searchBuilder` for `formActions`;
 - add `searchBuilder` for `scheduledJobs`;
 - add `searchBuilder` for `users`;
 - add header position for page edit in the old UI. Fix page editing in old UI to remove pages from menu
 - saving form with `ajax` enabled will refresh the inputs based on the values 
 - #332 - add language class based on the locale to all `styles` with `css` property

# v5.1.5
### Bugfix
 - Fix form children loading when is in CMS and when is on normal page
 - `cronjob` - the cronjob should be changed to enable php cli to use apc module: example (`* * * * * php --define apc.enable_cli=1  /home/project/server/cronjobs/ScheduledJobsQueue.php`)

# v5.1.4
### Bugfix
 - Overwrite time when scheduling action from form input

# v5.1.3
### Bugfix
 - Fix clearing cache by type

# v5.1.2
### Bugfix
 - Fix style `conditionFailed` not showing in new UI edit mode
 - Fix mobile language load

 ###  New features
  - send the default language id to the mobile app
  - reset `cache` on CMS preferences update
  - cache style_name when searched by id and cache if the style can have children

# v5.1.1
 ### Bugfix
 - Fix login service

# v5.1.0
 ###  New features
  - `hooks` - now it is possible to have multiple hooks for the same `method`

 ### Bugfix
  - Fix `tabs` loading. Now correctly load the first tab if none is selected. 
  - Fix `simpleMDE` text-area line selection on click.
  - Fix `help` MDE parse
  - #326 - Fix markdown parse for fields `markdown-inline`.

# v5.0.0

### BREAKING CHANGES
 - after updating an older version to 5.0.0 please check the impressum and if needed install the required plugins
 - in `globals_untracked.php` change `define('LANGUAGE', 'de-CH'); // German` to `define('LANGUAGE', 2); // German`
 - add in the apache config: 

 `<Directory "${PROJECT_PATH}/server/plugins">    
    <Files ~ "\.json$">
        Require all granted
    </Files>    
</Directory>`

### New Features
 - #321 - improve installation script
 - #317 - add field `condition` for all styles. Add also `debug`.
 - move field `platform` to be checked in the conditions
 - gulp files are refreshed every time when a new version is uploaded
 - add option for `section_name` change
 - add option for `keyword` change
 - add option for `page_access_type` change
 - add option for page `is_headless` change
 - add markdown editor for fields from type `markdown`
 - add `cache` - require module `php-apcu` (`sudo apt install php7.2-apcu`)
 - add `hooks` - require module `php-uopz` (`sudo apt install php7.2-uopz`)
    - add `hook_on_function_execute` which can be added on function enter.  
    - add `hook_overwrite_return` which can overwrite the return value. 

### Bugfix 
 - fix json condition builder for `in_one_of` type used with other condition
 - autocomplete is not read only anymore and can be deleted
 - improve the loading of UserInput by reducing the queries number
 - a style `link` can have empty `label`
 - `git tag` fix - set ownership to www-data (example `sudo chown -R www-data:www-data /home/www/selfhelp_v5_0_0/`)

### Internal
 - rework the `entryList` and `entryRecord` styles and how they work
 - add new internal style `conditionBuilder`
 - add new internal style `dataConfigBuilder`
 - add new internal style `actionConfigBuilder`
 - add new internal style `qualtricsSurveyConfigBuilder`

### Changes
 - deprecate style `trigger`. From now on, the new plugins autoload based on their name
 - deprecate style `conditionFailed`. Now all styles have conditions.
 - move style `mermaidForm`to a separate plugin
 - move style `book`to a separate plugin
 - move styles `graph`, `graphBar`, `graphLegend`, `graphPie`, `graphsSankey` to a separate plugin `plotly-graphs`

# v4.9.6
### New Features
 - style `qualtricsSurvey` can accept url parameters in the same way a normal Qualtrics survey can do. Ex: `url?code=test&session=1`;

# v4.9.5
### Bugfix
 - BMZ calculations add default trace name for the user

# v4.9.4
### Bugfix
 - fix cronjob `ScheduledJobsQueue` after the condition change

# v4.9.3
### Bugfix
 - load all children of the `conditionalContainer` in the CMS

### New Features
 - add page keyword comparison in conditional container

# v4.9.2
### Bugfix
 - fix `conditionalContainer` computation for entry records

# v4.9.1
### Bugfix
 - style `conditionalContainer` compute information about its children only if the condition is fulfilled. Otherwise only load children if any of them are `conditonFailed` style.

# v4.9.0
- improve the performance of the functions and procedures which are used to show the user data (dynamic and static);

# v4.8.4 

### Bugfix
 - #307 - When the language is changed it is saved for the user in DB and when the user login somewhere else it will automatically load the selected language

# v4.8.3

### New Features
 - An activation email can be send from CMS/admin. Go to users then select the user that you should receive an activation link and click button `Send Activation Email`;

# v4.8.2

### Bugfix
 - when scheduling `task` based on `QualtricsAction` or `FormAction` for target groups, it does not schedule the task for the person who triggered the action unless the person is in the targeted group
 - add scroll to `showUserInput` table

# v4.8.1

### New Features
 - add variable `custom_time` to the list with variables that can be overwritten in `QaltricsAction` and `FormAction`;

# v4.8.0
 - style `book` check required fields only for the current page and already passed pages when save the values
 - add `ajax_set_user_language` page permissions to admin, subject and therapist group, otherwise these groups has less rights than any newly created group 

# v4.7.2
 - add `user_name` to the user list and selected user view

# v4.7.1
 - Add variable pairs configuration for Qualtrics to overwrite data information
 - Add variable pairs configuration for Forms to overwrite data information

# v4.7.0
 - Bugfix: add ajax call as open page for changing the language

# v4.6.6
 - Bugfix: properly change `@user_name` to the real user name when we send notifications

# v4.6.5
 - Bugfix: Lobby chat group can be renamed
 - Bugfix: Show user info on selected user

# v4.6.4
 - Added embedded data `code` for qualtrics surveys from type `follow-up`. The variable is needed fot anonymous types of surveys.

# v4.6.3
 - get router params for styles `FormUserInputLog` and `FormUserInputLog` even if they are in conditional container.
 - fix mobile loading formField values for entry records

# v4.6.2
 - `@user_code` is replaced with the real user code in the body of the scheduled notifications or emails by Qualtrics Callback

# v4.6.1
 - keep user_name or email in the session. Execute the query only once.
 - bug fix: when input is used in `refContainer` and then in `formUserInputRecord` now properly load if there are entered values

# v4.6.0
 - fix `etryRecord` load. Form id now is successfully extracted from the parameter and remove the dynamic, static suffix
 - add field `filter` to styles `entryRecord` and `entryList`;
 - add field `css_mobile` to all styles that have fields `css`
 - add field `children` to style `link`. Now it can be used to define custom clickable objects.
 - improve performance for selfhelp mobile calls for guest users

# v4.5.0
 - #305 - Add all ajax requests to the page ACL. If you need an ajax request for style `graph` a page with ajax call url should be created.

# v4.4.4
 - disable graphs Ajax calls 

# v4.4.3
 - bug fix wrong reminder scheduling for surveys and forms

# v4.4.2
 - bug fix search style url loading when there is no BASE_PATH

# v4.4.1
 - bugfixing slow user_code loading
 - bugfix message board error
 - bugfix cronjob sending mails 

# v4.4.0
 - Fix style `conditionFailed` loading for mobile apps
 - Adjust `entry` styles to use static data for visualizing


# v4.3.0

### New Features
 - #302 - add a sub styles `formUserInputLog` where `is_log` is `true` and add a sub style `formUserInputRecord` where `is_log` is always `false`
 - #299 - add JSON Editor.
    - add shcema for `dataConfig`
    - add schema for `json-logic`
 - #295 - add `moduleFormsActions`. It is similar to Qualtrics actions. Now we can attach notifications, reminders and tasks to form when is loaded(started) or when is finished(submitted).
 - #301 - add for fields `data_config` a UI builder for easier JSON generation 
 - #300 - add UI builder for `conditionalContainer`. Add fields `jquery_builder_json` and `data_config` in style `conditionalContainer`.
 - `conditionContainer` can compare dates to the current time
 - static data (Qualtrics data) can be seen in the data panel (Admin->Data)
 - #303 - add new style `conditionFailed`. It should be used as a child in style `conditionalContainer`. It is a holder for other styles and it is loaded only if the condition in the `conditionalContainer` is not met.
 - now the user code could be accessed by `@user_code`. This string is automatically replaced with the user code. It is used in the same way that `@user` replace the user name.
 - add Qualtric Survey config JSON builder
 - add action (Qualtrics and Form) config builder
 - add condition check to scheduled mails from Qualtrics and Form
 - add condition check to scheduled taks from Qualtrics and Form
 - add field `exec_time` in table `user_activity` and record the time needed from the server to execute the request

### Bugfix
 - in style `book`, `next` button is hidden when there are no more pages and `back` is hidden when it is the first page

# v4.2.0

### New Features
 - add a new style `book`. It holds children that are displayed as pages based on [turn.js](http://www.turnjs.com)
 - add a new style `refContainer`. It wraps styles and make them available on multiple places in selfhelp.
 - add field `locked_after_submit` in style `input`. If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.
 - add field `locked_after_submit` in style `radio`. If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.
 - add field `locked_after_submit` in style `select`. If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.
 - add field `locked_after_submit` in style `slider`. If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.
 - add field `locked_after_submit` in style `textarea`. If selected and if the field is used in a form that is not `is_log`, once the value is set, the field will not be able to be edited anymore.
 - add `groupTarget` for Qualtrics actions from type `task`. If a target group/s is selected all the users in that group will receive the task.

### Bugfix
 - in style `radio` the field `is_required` is proeprly checked

# v4.1.2

### New Features
 - add an option to set default date and time for the `input` style. If in value is typed `now`, it will load the current datetime.
 - rename the default label for the leading record for `showUserInput` from `Date` to `Record Id`.

# v4.1.1

### Bugfix
 - fix insert script for `update_v3.10.0_v4.0.0.sql`

# v4.1.0

### New Features
 - add an option for a menu icon for the website
 - add `pageAccessTypes`; It separates pages to mobile, web or mobile_and_web
 - add style `entryList`; The style visualize entries from a formUserInput used as admin tool for inserting entries.
 - add style `entryRecord`; The style visualize selected entry. It needs a `page` with advanced settings and path containing `record_id`. Example: `/courseViewAdvanced/[i:record_id]?`
 - add style `calendar`; The style is only for mobile and it shows the calendar in the app.
 - add new method `output_content_entry` and `output_content_mobile_entry` with param the entry value. Then the style can be used to visualize the value as an entry. The value of these styles can be dynamically loaded as entry if the form name is set in the value field with the `$` sign (ex. if we have a form with inpiut name `firstName`, in order to visualize that input, we should use `$firstName`). The styles which have this new method are:
    - `markdown`
    - `heading`
    - `image`
    - `div`
    - `button`
    - `conditionalContainer`
    - `form`
    - `formUserInput`
    - `input`
    - `container`
    - `markdownInline`
    - `card`
    - `plaintext`
    - `alert`
    - `link`
    - `rawtext`
    - `textarea`
 - load all custom css files in the mobile app
 - set locale from mobile calls
 - add field `own_entries_only` in style `formUserInput`. 
 - add field `platform` in style `conditionalContainer`. The style can filter calls from mobile, web or both.

### Changes
 - `formUserInput` can be added to a page with path containing `record_id`. Example: `/courseViewAdvanced/[i:record_id]?`. When the id is loaded the entry will be in edit mode. Also an option for delete will apear.
 - add `icon` to style `tab`.
 - `form` without label does not load the button anymore
 - add field `email_address` to style `formUserInput`. Use `@email_user` to retrieve automatically the user email. Emails are separated by the MAIL_SEPARATOR. It is `;`. 

# v4.0.3

### Bugfix
 - show the number of the messages over the users in the chat and order the users by the number of the new messages

# v4.0.2

### Bugfix
 - fix new line in field used in JSON logic in `conditionalContainer`

# v4.0.1

### Bugfix
 - fix sending attachments for emails

# v4.0.0

### Bugfix
 - Resend a verification email on already invited users
 - Fix show datetime calendar when click on button for style input type date
 - Add basePath to mobile request; if there is no last_user_page in router it gave an error -  now i check if is set before used

### Changes

 - Refactor `mailModule` into `scheduledJobsModule`
    - add `Tasks` to the `scheduledJobs`
        - `add_group` task
        - `remove_group` task
    - add `Notifications` to the `scheduledJobs`        
    - add `Emails` to the `scheduledJobs`
 - Add conditions to the jobs, which are checked before execution and only if it is fulfilled it is executed
 - Add `timestamp` filed to table `uploadRows`
 - Add field `use_as_container` to style `qualtricsSurvey`
 - Add field `children` to style `qualtricsSurvey`
 - Add field `restart_on_refresh` to style `qualtricsSurvey`
 - Add field `reedirect_at_end` to style `qualtricsSurvey`
 - Add an option to schedule notifications after time period on specific time

### New Features

 - Add an option for mobile calls. If the `POST` request contains parameter `mobile` which is `true`, then the request return JSON object
 - Adjust all styles to be able to return JSON structure when a mobile call is made 
 - Add `device_id` and `device_token` to table `users`. It is used for sending notifications to the user
 - Add PHP-FCM library
 - Expand style qualtricsSurvey; add schedule times and once per user or once per schedule options
 - Add style `MessageBoard`
 - Add image select to select style
 - Add taggedElement to qualtrics_templates for the iframe_resizer; improve checking actions for  qualtrics survey_repsonse and now make maximum one request to the survey data and only if it is needed

# v3.10.2

### Changes
 - Style `input` type `date` and `datetime`, the calendar now is without week numbers and the week starts on Monday instead of Sunday.

# v3.10.1

### New Features
 - Add property `filter` in data_config. It improves the customization of all styles that has field `data_config`; Issue: #293;

# v3.10.0.1
### Bugfix
 - fix new line in field used in JSON logic in `conditionalContainer` (this version was created for workwell project, the bug was fixed in v4.0.2)

# v3.10.0

### New Features
 - Synchronize single action/survey in Qualtrics; Issue: #292;


# v3.9.2

### Changes
 - Add new global `REDIRECT_ON_LOGIN` In `globals_untracked.php`. When it is set to `false` the user is always redirected to home otherwise it is redirected to the last page.

# v3.9.1

### Bugfix
 - In style `conditionalContainer` get the last input value instead of the
   first if multiple values are available for one form field (e.g. with
   `is_log` enabled).

---------
# v3.9.0

### Changes
 - For now, disable the field `ajax` from style `formUserInput` as it does not
   work with anchors.

### New Features
 - Allow to jump to tabs by using the location hash (!46). Such an anchored tab
   will be activated.
 - Allow to disable style fields in the DB. This will only disable the display
   and should be used with care because it will be difficult to change a style
   field once it is disabled.

---------
# v3.8.0

### Changes
 - In order reduce the ACL DB connections, the ACL of the current user is cached in the ACL service (!41).
 - Handle the style field css like any other field (!45, #290).
 - Reduce DB requests by caching page information fetched from the DB.

### New Features
 - Added a utility script to generate the boilerplate code for a new style.

---------
# v3.7.0

### Changes
 - Add field format to style `input`. It is used for all date, time and datetime formats. [Info](https://selfhelp.psy.unibe.ch/demo/style/471)

---------
# v3.6.1

### Bugfix
 - Bad Scaling of UserInput Service (#284, !37).
 - Catch Argument Count Error in conditionalContainer (!36).

### Changes
 - Redesign of the Navigation bar to reduce DB requests (!38).

---------
# v3.6.0

### Bugfix
 - Export section now corectly exports CSS values.

### Changes
 - Inputs with types: date, datetime and time now are based on [flatpickr](https://flatpickr.js.org)

---------
# v3.5.0

### Bugfix
 - Issue:#288; Move time check in the global sanitize; in the future it should be adjusted to the new gulp version and array structure
 - Issue: #286; Export sections containg fields without content.

### New Features
 - Cretae Plugin functionality for Selfhelp. All installed plugins can be seen in `impressum` 
 - Add `triggerStyle`. It is used in order to call a `plugin` when a criteria is fulfilled.

---------
# v3.4.1

### Bugfix
 - Conditional container fix for null values (This fix was not applied in v3.4.0).

---------
# v3.4.0

### New Features
 - Issue: #240; Add export section functionality. A JSON file is generated for the sections and its children.
 - Issue: #240; Add import section functionality. A JSON file can be imported and then a section is generated. It can be found in unused sections. 

---------
# v3.3.0

### New Features
 - Issue: #203; Delete all unassigned sections and their children
 - Issue: #238; Improve security server configuration
 - Issue: #277; Add option to send data to the user in style `formUserInput`

### Changes
 - Change ACL from view to procedure; add chatTherapist and chatSubject pages to moduleChat
 - Issue: #283; Redesign of the data export page. Now the ueser and the forms are in adropdown list and they should be selected.
 - Redesign chat, now it works with groups and not with chat rooms. All existing chat rooms were converted to groups
    - A therapist should have the chatTherapist permision
    - A subject should habe chatSubject permision
    - All groups which has chats and the user is in them are loaded as tabs
    - All messages are sent to the group, if there are multiple therapist in the group all of them will recieve the message
    - All old chat rooms are created as groups and users are assigned to them
    - The therapist should be in the same groups with the subject in order to send message to him/her
    - All groups that had access to contact now have access to `chatSubject`
    - Remove pages: 'contact', 'chatAdminDelete', 'chatAdminInsert', 'chatAdminSelect', 'chatAdminUpdate'
    - All notification mails are send with the new mail module and they can be seen in the list
    - Subject group can be renamed in the chat style.
 - Issue: #201; Add style version that visualize the database version and the application version [Link](https://selfhelp.psy.unibe.ch/demo/style/806#section-806)
 - Issue: #196; add new link `#last_user_page` for buttons and links; It links to the last unique visited page `#last_user_page`
 - Issue: #199 ; remove `user_name` form the MySQL proceudure;

### Bugfix
 - Issue: #285 When an users is deleted all scheduled emails for that users are deleted too.
 - Issue: #282 Faster loading on page users
 - Issue: #281 Add config for rendering singleLineBreaks for SimpleMDE. Noe the preview in the markdown editor will show correctly new lines

---------
# v3.2.0

### New Features
 - In `QualtricsModule` - Add on option for annonymous survey.
 - `For group(s)` in `Qualtrics Action` is not mandatory anymore.
 - Add new field `data_config` in style `graph`. [More information](https://selfhelp.psy.unibe.ch/demo/style/631)
 - Add new field `data_config` in style `markdown`. [More information](https://selfhelp.psy.unibe.ch/demo/style/454)
 - Add `PDF Export` checkbox in style `container`. [More information](https://selfhelp.psy.unibe.ch/demo/style/447)
 - Add new style `search`. It gets the typed paramter and append it to the url
 - Add field `config` in `Qualtrics Surveys`. [More information](https://selfhelp.psy.unibe.ch/demo/style/802)
 - Add function `[BMZ] Evaluate motive` which can be assigned in `Qualtrics Action`. [More information](https://selfhelp.psy.unibe.ch/demo/cms_feature/799)

### Changes
 - When a user create new CMS page he/she recieves a full access to that page.

### Bugfix
 - fix the case where `ACL` does not show all possbile pages but just shows what the group has access.
 - `ACL` chekcs if the page is open access and if it is open access, we give select mode for the user even if there is no special ACL rule, issue #274.
 - Fix #239; The chat room's name can contain numbers, letters, - and _ characters.

---------
# v3.1.0

### New Features

 - Generate PDF files as output for spesific callback functions
    - It is used for `Workwell` project. A folder `workwell_cg_ap_4` should be created in folder `assets`. The new folder should have 777 rights
    - It is used for `Workwell` project. A folder `workwell_cg_ap_5` should be created in folder `assets`. The new folder should have 777 rights
    - It is used for `Workwell` project. A folder `workwell_eg_ap_4` should be created in folder `assets`. The new folder should have 777 rights
    - It is used for `Workwell` project. A folder `workwell_eg_ap_5` should be created in folder `assets`. The new folder should have 777 rights

---------
# v3.0.1

### Bugfix

 - Fix slow loading of `Groups` module

---------
# v3.0.0

### Changes

 - In style `register` add default `group` for new users
 - In style `register` add `open_registration` property. If it isselected, a new user can register without a code. A new one will be generarted for him/her automatically
 - Add export for datatables
 - In style `select` add a field `live_search`. It eneable text filtering. The style is based now on bootstrap-select.js

### New Features

 - Add page CMS preferences
 - Add user language selection
 - Create/edit/delete language
 - Add Module Mail
    - Mail queue that show all shceduled emails
    - Schedule email manually
    - Cronjob that check and send mails
- Add Module Qualtrics
    - Add projects
    - Add surveys
    - Add actions
    - Qualtrics synchronization
- Add transcations
- Add jQuery confirm dialog
- Add flatpicker - now time can be selected too
- Add new style `qualtricsSurvey`. It displays a Qualtroics Survey in iFrame. It uses iframeResizer.js
- Add `lookups` table
- Generate PDF files as output for spesific callback functions
    - It is used for `Workwell` project. A folder `workwell_evaluate_personal_strenghts` should be created in folder `assets`. The new folder should have 777 rights
    - It uses php-pdftk library
    - It requires pdftk installed on the server <https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/> Ubuntu 18.04 installation command: `sudo snap install pdftk` (if there some issues a symbolic link maybe needed `sudo ln -s /snap/pdftk/current/usr/bin/pdftk /usr/bin/pdftk` )
- For Qualtrics synchronizations:
    - `php-dom` is needed: `sudo apt-get install php7.2-xml`
    - `php-curl` is needed: `sudo apt-get install php7.2-curl`

---------
# v2.0.6

### Bugfix

 - Fix style `filterToggle` to persist filter state after page reload (view
   only).
 - In filter styles, ignore invalid filter names instead of always returning
   false.


### Changes

 - In style `autocomplete` disable browser input autocomplete.
 - Change style `filterToggle` to an inline-block element.

### New Features

 - In style `input` provide an internal field to allow disabling browser input
   autocomplete.


---------
# v2.0.5

### Bugfix

 - Add support for igraphs and filters in IE11 and Edge.

### Changes

 - Use gulp-babel to transpile ES6 to ES5 (and thus enable support for IE11)


---------
# v2.0.4

### Bugfix

 - Fix problem when AJAX calls were denied for CMS preview page.
 - For graphs, only use dynamic data which is not marked as removed.

### Changes

 - Remove graph debug logs.
 - Add missing style field documentation.
 - Open style field description on hover and focus.

### New Features

 - Add field `single_user` to style `graphBar`.
 - Add field `single_user` to style `graphPie`.


---------
# v2.0.3

### Bugfix

 - Fix a problem with form names and the style `showUserInput` where deleting
   entries was not possible.

### New Features

 - Allow to order graphs by providing an array of value types
 - Allow to define a custom anchor in style `formUserInput` (#197)
 - Allow to define a custom anchor in style `showUserInput`


---------
# v2.0.2

### Bugfix

 - Fix a security issue

### Changes

 - Don't show login button if login Title name is empty
 - Order dynamic data fetched with AJAX call by user ID first


---------
# v2.0.1

### Bugfix

 - Fix resizing problem with style `graphSankey`


---------

# v2.0.0

### Bugfix

 - Fix ACL-checks on AJAX requests. It is now possible to use AJAX request in
   any style. The AJAX requests inherit the ACL of the page from which they are
   called.
 - Fix bug when multiple tab styles are used on the same page.

### Changes

 - Alter the table `user_input` to allow fields to be grouped by row with a
   record id instead of the timestamp.
 - Open AJAX requests for everyone by default.
 - Use new style `autocomplete` in `ChatAdminUpdate` component.


### New Features

 - New style `graph` (#254). This style allows to render all kind of graphs
   using the library [Plotly.js](https://plotly.com/javascript/). The data used
   to draw the graphs can either be
    - manually entered numbers
    - from an uploaded CSV file
    - from dynamic user input data
 - New style `graphBar` (#259). This style is based on style `graph` but
   provides a more convenient interface to render bar graphs than the rather
   complex style `graph`. However, it is lacking in terms of flexibility.
 - New style `graphPie` (#258). This style is based on style `graph` but
   provides a more convenient interface to render pie graphs than the rather
   complex style `graph`. However, it is lacking in terms of flexibility.
 - New style `graphSankey` (#253). This style is based on style `graph` but
   is specialised to render Sankey diagrams. This style offers more flexibility
   with respect to Sankey diagrams than the style `graph` is capable of
   offering.
 - New style `graphLegend` (#260). This style allows to render a static legend
   which can be used as a global legend for multiple graphs.
 - New styles `filterToggle` and `filterToggleGroup` (#257). These styles
   allow to filter graph data and store the active filters in the session.
 - New style `autocomple` (#256). This style allows to use AJAX calls in a
   convenient way to search for items in the database.
 - Extension of the `AssetComponent` to allow uploading CSV files which can
   then be used to render graphs (#255).
 - Add a symbol to the header of a collapsible card to indicate that the card
   is collapsible.


---------

# v1.3.5

### Bugfix

 - catch exception in `conditionalContainer` (#233)

### Changes

 - update to Bootstrap 4.4.1 to fix issues wit Internet Explorer (#230)


---------
# v1.3.4

### Bugfix

 - fix export data page (#228)

### Changes

 - add timestamp in front of the file name for the exported files


---------
# v1.3.3

### Changes

 - add `validation_code` to the user name in all chats


---------
# v1.3.2


### New Features

 - add conditional container based on user groups (#229)
    - `$` sign is used to define the group name - returns true/false
    - example: `{ "==" : [true,"$subject"] }`


---------
# v1.3.1

### Bugfix

 - interested user now can register (#223)

### New Features

 - any data view request is logged into user activity


---------
# v1.3.0

### Bugfix

 - delete content and delete title load properly in the modal form when you
   delete `showUserInput`'s entry

### Changes

 - `showUserInput` is a datatable now.

### New Features

 - `showUserInput` is a datatable now. By default all functionality is
   stripped. If you want to add configuration you have to use css classes in
   the `showUserInput` form
    - `dt-sortable` - make all column sortable
    - `dt-searching` - add the search field
    - `dt-bPaginate` - add the page grouping
    - `dt-bInfo` - add the info footer
    - `dt-order-0-asc`, `dt-order-1-desc` - order the desired column asc or desc
    - `dt-hide-0` - hide the desired column
 - data page added under admin tab. It visualize all user input data


---------
# v1.2.0

### Bugfix

 - Chat add user to room with multiple groups - duplicate view (#214)
 - fix new lines in chat messages

### Changes

 - Visualize all additional groups (different from admin, subject and
   therapist) which has access to the chat in the chat - similar to how chat
   rooms are visualized

### New Features

 - therapist can initialize communications with user in chat rooms and group tabs
 - add groups and chat group to the user list table for visualization
 - Add callback `set_group_for_user` which assign a group to existing user
   using the registration code (#224).
 - add chatRoom title
 - visualize chatGroup Name where the description was


---------
# v1.1.10

### Changes

 - create page - the page keyword can contain numbers, letters, - and _ characters (#215)
 - added update script which remove all spaces and dots from already existing page names 


---------
# v1.1.9

### New Features

 - Improved group ACL:
   - A group can now be customized with detailed rules per page.
   - When creating a new group a more coarse ACL selection can be used.
 - adding callback functionality
 - allow to assign a group to a verification code via a callback. This will
   then be used to assign the specified group to the user consuming the
   verification code


---------
# v1.1.8.3

### Changes

 - added new install.ch script
 - database initial do not contain views, functions and procedure. There is another sql script.
 - removed the privileges sql file. Not needed anymore. The new user has grant access all.


---------
# v1.1.7.2

### Changes

 - include interested users to the user overview table (#207)
 - update GULP to version 4.0.2

### Bugfix

 - do not redirect login when coming from an open page (#206)
 - fix a php error (pass the email address as param) (#208)
 - only render the page overview on actual pages
 - also allow child pages for open pages (#204)


---------
# v1.1.7

### Bugfix

 - In style `mermaidForm` adjust visible node size and size of the final `svg`
   to correct overlapping text (#189).
 - Fix the style `userProgerss` (#192).
 - Fix redirect after Login (#187).
 - Fix style `mermaidForm` id handling (#190).
 - Allow all types of characters in select and radio buttons (#193).
 - Fix styles `formUserInput` and `mermaidForm` to handle special characters as
   form name (#194).

### New Features

 - Add a back to the top button (#191).


----------
# v1.1.6

### Bugfix

 - Fix typos and misinformation in DB with respect to the fields on page `home`.

### Changes

 - Only store the last URL in the DB if the URL points to an experimenter page
   (#187).

### New Features

 - Allow to customize navigation buttons (#186).


------------
# v1.1.5

### Bugfix

 - Several fixes with the style `mermaidForm`:
   - Support all types of input styles as child elements (#185)
   - Allow all kind of characters as node content. One exception: `"` is
     automatically replaced with `'` (#182).
   - Fix a bug where multiple input fields used the same title (#183).

### New Features

 - The style `mermaidForm` uses the label of a child input field as a title of
   a mermaid node (#184).


------------
# v1.1.4

### Bugfix

 - Improve asset deletion handling and fixing a bug where filenames with umlaut
   could not be deleted (#180).

### Changes

 - Add an option `none` for all bootstrap style selections. This allows for
   more bootstrap classes which would be used instead of the default styles
   (#181).


------------
# v1.1.3

### Bugfix

 - Separate mermaid js file from the minified style js file to avoid breaking
   scripts on IE (#179).
 - Make users clickable on all pages of the user table (#178).
 - Fix a problem with the minified js file of the style `nestedList` (#176).
 - Fix a bug in the style `mermaidForm` where always the last label was shown as
   modal title instead of the label of the node that was clicked.


------------
# v1.1.2

### Bugfix

 - Fix umlaut problem with SMS service (#164).
 - Workaround for Safari bug with audio and video controls (#166).
 - Fix a problem when trying to add a user to a chatroom without having a user
   selected.
 - Only show contact icon when logged in.
 - Fix chat subject dropdown on small screens.

### Changes

 - Change CSP rule to allow images from `https://via.placeholder.com/`.
 - Update Bootstrap to version v4.3.1.
 - Distinguish between export and experiment activity.
 - Change the image syntax in markdown to allow more linking options.
 - Use PHPMailer for sending mails. A new service `mail` was created for this
   purpose.
 - Only grant select rights to experimenter group when creating a new page.
 - New user state and slightly changed user state handling. Note that this
   **requires updated DB privileges**.
 - Move the `email_reset` form from the email CMS to the style `resetPassword`
   which allows to provide more options on how the email is sent (html,
   subject).
 - Move the `email_notification` form from the email CMS to the style `chat`
   which allows to provide more options on how the email is sent (html,
   subject).
 - Changed the way how services are passed along the styles and components.
 - Create a common css and js collection for styles. These files are loaded
   before the specific ones from a style.

### New Features

 - Remember the target url when not logged in (#150).
 - Remember the last url of a user and redirect to it after login (#147).
 - Add a `json` style which allows to define base styles with JSON (covers #168).
 - Add user overview table with user activity information to the user admin
   menu (#163).
 - Add a `userProgress` style which allows to display the current user progress
   (#80).
 - Allow to customize the `meta:description` tag (#164).
 - Add a `mermaidForm` style which allows to describe graphs and allows a
   subject to change node labels (#162).
 - Improve the CMS:
   - add a schematic page overview.
   - allow to jump to the real page.
   - if acl allows it, a user sees a small edit icon on to bottom right of each
     section page which allows an experimenter to switch back to the CMS.
 - Improve Markdown
   - Allow more linking options (same syntax as with linking styles).
   - Allow to fetch user form input fields (covers #154).
 - Allow to mark user inputs as deleted (when `is_log` is checked) (#118).
 - Allow to create a open access page (related to #174).
 - New style `emailForm` which allows a user to enter an email address and
   receive emails which are customizable in the CMS (#174).
 - Allow to store emails of interested users (#174). This introduced a new
   user state.
 - Improved users overview and user overview.
 - Improved the validation code handling (#157).
 - Allow to clean user data (remove activity and user input of a user) (#172).
 - Use a language-specific CSV separator (#161).


------------
# v1.0.4

### Bugfix

 - Slider lables need to recompute their position on slide size change (#153).
 - Gender specific information was not displayed correctly (#159).

### New Features

 - Add field placeholder to the style textarea (#160).


------------
# v1.0.3

### Bugfix

 - Allow the reminder script to update the `is_reminded` flag and reset the
   flag on user login, depending on the user settings.


------------
# v1.0.2

### Bugfix

 - Fix validation behaviour when no custom input field is present.
 - Fix bad url in chat notification email.
 - Fix bug where CMS menu was overlapping with navbar.


------------

# v1.0.1

### Bugfix

 - Reuse ununsed navigation container (#139)
 - Make the CMS content dependant on the language and gender selection (#130)

### Changes

 - Add a style description for each style and generate the style list for each
   Style group.
 - Make CMS page and section navigation sticky.
 - Set the default of the `text_md` field of style `navigationContainer` to
   `# @title`.

### New Features

 - Allow to enable/disable the navigation menu on navigation pages (#75)


------------

# v1.0.0

### Bugfix

 - Add missing email description.
 - Change the colour of the `progressBar` label when the `type` is set to
   *light* or *warning*.
 - Add custom css classes in style `nestedList` also if collapsed on small
   screens.
 - For females, the input label for the style `showUserInput` was not shown if
   no female version was available. Now it defaults back to the male label.
 - User inputs are now properly checked in the style `fromUserInput`.

### New Features

 - Improve the style `progressBar` (enable/disable label and stripes)
 - Improve the style `carousel` (enable/disable crossfade, allow captions)
 - Allow custom input fields in the style `validate` (#124).


------------

# v0.9.13

### Bugfix

 - Validation style is now displayed correctly in CMS

### Changes

 - In the `input` style add the type `password` to the list of possibilities
 - Add the user name next to the profile label
 - Use styles to design the profile page (instead of a hardcoded template)
 - Remove page `user_input_success`
 - Section names must be unique

### New Features

 - Allow to change notification settings on the profile page
 - Allow to change the user name on the profile page
 - Notify a user by email or SMS on chat message reception (#144)
 - Allow to upload CSS files on the assests page (#107)
 - Allow to overwrite existing asset and css files
 - Allow to remove asset and css files from the server
 - A new style `carousel` to display images in a slide show (#125)


------------

# v0.9.12

### Bugfix

 - In style `conditionalContainer` also fetch db fields with special caracters
   in their name

### Changes

 - Move the contact menu link to the right and use a envelope symbol
 - Activate POST on `home` and all experimenter pages
 - Activate GET on `request` page and change the request url
 - Rename the group `experimenter` to `therapist`
 - Change the ACL of group `therapist`
 - Make the chat administration ACL seperate from content pages

### New Features

 - Indicate whether new chat messages are here (#65)
 - When submitting with `formUserInput` jump back to the form (#140)
 - In style `conditionalContainer` add a field `debug` which displays the
   result of the condition and the values of the involved db fields
 - Allow to create chat rooms (#78). This includes
   - An adimn section to create and delete rooms, add users to a room, remove
     users from a room
   - A changed chat interface where rooms can be selected if available.


------------

# v0.9.11

### Bugfix

 - Fix typo

### Changes

 - When exporting user inputs and user activities use the unique user code
   instead of a hash (#77)

### New Features

 - When manually adding a new user, provide a unique user code (#77)
 - Allow to modify email content in the admin section (#141)
 - Implement automatic email reminder (#79)


------------

# v0.9.10

### Bugfix

 - Fix a bug with the style `conditionalContainer`

### Changes

 - Update style descriptions (#138)


------------

# v0.9.9

### Bugfix

 - Fix an issue with the validation process (#132)
 - Fix `showUserInput` language and gender problem (#131)

### New Features

 - Create HTML anchors for wrapper and form sections (#109)
 - Add linking support for assets, anchors, and nav pages (#114)
 - New style `conditionalContainer` which allows to attach a condition to
   content such that the content is only displayed if the condition resolves to
   true (#128)
 - New style audio (#116)


------------

# v0.9.8

### New Features

 - Navigation pages can be added to the header menu (links to the first entry)
   (#134)


------------

# v0.9.7

### Bugfix

 - Fix the textarea size problem (#119)

### Changes

 - Style `login` is now a normal style that can be assigned to a section (#122)
 - UserInput checkbox when creating a new page is now obsolete (#129)

### New Features

 - New style `div` (#112)
 - New style `register` which allows a user to register (#121)
 - The login page can now be customized fully (#122)
 - A headless page can be created (#123)
 - A list of validation codes can be generated which can be used to register
   (#121)


------------

# v0.9.6

### Bugfix

 - Fix some typos (#89)
 - Fix CSS field in style `formUserInput` (#127) and the `markdown` styles
   (#126)
 - Fix `quiz` and `tabs` styles (#115)

### Changes

 - Allow gender and language variations for the fields source and sources (#111)
 - Improve the `showUserInput` style

### New Features

 - Allow to add css classes to the navigation menu of a nav page with the field
   `nav\_css` (#105)
 - Allow to generate links to nav sections (#104)


------------

# v0.9.5

### Bugfix

  - Handle multiple forms on the same page

### Changes

 - A new style `formUserInput` which combines `formDoc` and `formLog` (#87)

### New Features

 - A new style `showUserInput` to display user input (#87)


------------

# v0.9.4

### Bugfix

 - Improve the list of non-used secions in the CMS (#94)

### Changes

 - Minify JS and CSS files (#97)
 - Seperate create form for navigation sections (#84)

### New Features

 - Style descriptions and style groups (#99)
 - Load default values of style fields from the db (#98)


------------

# v0.9.3

### Bugfix

 - Default value of checkbox and radio buttons (#100)
 - Unique ids for sortable lists (#86)
 - HTML-escape markdown in CMS (#83)
 - JS error in collapsed nested list (#81)

### Changes

 - Card titles are now of type `markdownInline` (#93)
 - Automatically load style include files (#92)
 - User input is only stored to the database if the style `formDoc` or
   `formLog` is used
 - Allow HTML in `markdown` (#82)
 - Restrict rights for group assignements (#102)

### New Features

 - Allow to open links in a new tab (#91)
 - Show progress when uploading files (#90)
 - Two types of user input styles `formDoc` and `formLog` to handle user input
   data (#87)
 - Allow experimenter to make group assignments (#101)
