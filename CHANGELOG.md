# v1.1.7.1 (latest)
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
