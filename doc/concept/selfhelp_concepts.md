Conceptually, a SelfHelp app consists of the following elements:

- `section`: A section is a piece of content that will be rendered to a page. A page can consist of a single section or can be composed of multiple sections. A section is always associated with a `style`.
- `style`: A style defines the apperance of a section and specifies the type of content that can be assigned to a section. The content is specified through `fields`.
- `field`: A field defines a specific bit of content that will be rendered within a section. Each style has a set of fields and the style template defines where these fields will be rendered. However, given that multiple sections can have the same styles, while the type of a field remains always the same, the content is specific for each section.
- `component`: To seperate concerns the [Model-View-Controller (MVC)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) pattern is used. In order to simplify the handling of this pattern components are introduced: A component combines a model, a view, and a controller into one class which allows to encapsulate the MVC pattern without losing the aspect of seperation. Every style is a component but a component must not necessarily be a style.
- `page`: A page is the root element that is requested by the browser when connecting to a specific url. This means that a page is always associated to a unique `url` and has a unique name (called `keyword`).
Several types of pages exist:
  - **Section Page**: A section page is the most common type page. It is a collection of sections that are rendered one below the other.
The content of section pages can be modified in the CMS.
  - **Navigation Page**: A navigation page is a special type of section page where only one section is rendered at a time.
A set of root sections, or navigation sections, is assigned to a navigation page.
An id that is postfixed to the url indicates which section is rendered.
  - **Component Page**: A component page renders one single custom component.
The name of the component to render is matched by the keyword of the page.
  - **Custom Page**: A custom page renders whatever is defined by a function with the name `create_'page_keyword'_page()` where `'page_keyword'` is the keyword of the page. This function must be defined in `index.php`.
  - **Ajax Page**: An ajax page is used to handle ajax calls. This allows to include ajax calls to the ACL sysetem and thus, set up access rights with custom granularity.
- `service`: A service is a collection of functionalities that allows to manipulate certain aspects of the application. This includes a handler for database access, user permissions, link generation, user input, content parsing, etc.
- `action`: Actions are templates from which `jobs` are created and scheduled based on certain conditions. Different types of actions exist, e.g. *FormActions*, *QualtricsActions*, or *ManualActions*.
- `job`: A job is an instace of an `action`. A job is placed in a job queue and will execute operations based on certain conditions.
- `task`: A task describes group operations within SelfHelp. A task is triggered by a `job`.
- `notification`: A notification is an output message which is triggered by a `job`. Messages are either sent as an email, a push notification, or possibly in the future as an SMS.
- `actionScript`: A specific piece of code which is executed right befor a `job` is created.
