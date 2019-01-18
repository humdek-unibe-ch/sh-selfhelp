# v0.9.7

### Bugfix

### Changes

 - When exporting user inputs and user activities use the unique user code
   instead of a hash (#77)

### New Features

 - When manually adding a new user, provide a unique user code (#77)

# v0.9.10

### Bugfix

 - Fix a bug with the style `conditionalContainer`

### Changes

 - Update style descriptions (#138)

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

# v0.9.8

### New Features

 - Navigation pages can be added to the header menu (links to the first entry)
   (#134)

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

# v0.9.5

### Bugfix

  - Handle multiple forms on the same page

### Changes

 - A new style `formUserInput` which combines `formDoc` and `formLog` (#87)

### New Features

 - A new style `showUserInput` to display user input (#87)

# v0.9.4

### Bugfix

 - Improve the list of non-used secions in the CMS (#94)

### Changes

 - Minify JS and CSS files (#97)
 - Seperate create form for navigation sections (#84)

### New Features

 - Style descriptions and style groups (#99)
 - Load default values of style fields from the db (#98)

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
