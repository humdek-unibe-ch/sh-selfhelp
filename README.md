# SelfHelp WebApp

The SelfHelp WebApp is a tool that allows to create a web application that serves as a platform for research experiments.
The basic concept is as follows:

Pages are organised as a collection of sections that are rendered on the page, one below the other.
Sections have different `styles` which define the apperance of the sections.
Depending on the style of a section, the section has different `fields` which define the content of the section.
The value of a field can be a simple plaintext or a collection of child sections which have their own styles and children.

Currently available styles include, but are not linited to, alert boxes, buttons, card containers, forms, media elements, tabs, lists, and support for markdown texts.
A demonstration of avaliable styles can be found [here](https://selfhelp.psy.unibe.ch/demo/styles).

The app is designed in such a way that it can be extended with new styles or custom components without having to modify existing code.
Some instructions on how to extend the code can be found [here](https://selfhelp.psy.unibe.ch/demo/extend).

A tutorial for first steps in creating a new WebApp can be found [here](https://selfhelp.psy.unibe.ch/demo/assets/tutorial.pdf).
