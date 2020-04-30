# v2.0.4 (latest)

### Bugfix

 - Fix problem when AJAX calls were denied for CMS preview page.

### Changes

 - Remove graph debug logs
 - Add missing style field documentation


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
