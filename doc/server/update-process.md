# Steps to Create and Deploy an Update

This small document aims at providing a step-by-step guide on how to create and deploy e new version of SelfHelp.
It is assumed that
 - all the necessary changes of the source code have been completed
 - (if required) a database update script was created with all the necessary update steps to be performed in the database.

In the following the expression `__experiment_name__` will be used as a placeholder for the experiment name and the expression `__new_version__` as a placeholder for the new version.
The version number should be of the form `v__major__.__minor__.__revision__` (e.g `v1.2.3`).

### 1. Checkout the Latest Revision of the Master Branch

Navigate to the root directory of your local copy of SelfHelp and type

```
git checkout master
git pull
```

**Make sure that all merge conflicts are resolved.**

### 2. Produce the new Documentation

Increment the version number in the file `.doxygen` by changing the value of key `PROJECT_NUMBER` to the new version and produce the new doxygen documentation with
```
doxygen .doxygen
```

Fix all warnings that are produced and recreated the document until no warnings appear.
Commit the file changes with an appropriate commit message.

### 3. Update the file `CHANGELOG.md`

Document all the incremental changes with respect to the last version.
Use GitLab syntax to link to issues (issue number prefixed with `#`) and merge requests (merge request number prefixed with `!`).

Separate the changes into the categories **Bugfixe**, **Changes**, and **New Features**.

Add move the suffix `(latest)` to the latest version heading.

### 4. Generate the Minified Styles JS and CSS Files

In order to limit the number of requests, all `*.js` and `*.css` files from styles are combined into a minified file.
This can be done by following commands

```
cd gulp
gulp
```

### 5. Merge the DB Update Script with the Initial DB File

If no DB update script was necessary for this release, skip this step.

In order to keep the initial DB file up to date it must be merged with the DB update script that was created for this release.
To do this perform the following steps:
 1. Create a new clean Database by importing the file `server/db/selfhelp_initial.sql`.
 2. Apply the DB update script
 3. Verify that the updates were performed correctly
 4. Export the updated content of the database and save it as `server/db/selfhelp_initial.sql`
 5. (If required) adapt the file `server/db/privileges_default.sql`

### 6. Add and Commit the Changed Files

Add the changed files to git
```
git add .
git ct -m "new version __new_version__"
git push
```

### 7. Create a new Tag

To create and push a new tag

```
git tag __new_version__
git push --tags
```

### 8. Deploy the Source Code

In order to deploy the new version, `ssh` to the SelfHelp server where the project to update is hosted and perform the following operations:

```
sudo su www
cd
./check_versions.sh
```

Note the current version of `__experiment_name` (this is important for applying the DB update scripts in step 9).

```
cd __experiment_name__
git checkout __new_version__
```

### 9. Deploy the DB Update

If no DB update script was necessary for this release, skip this step.

To update the DB perform the following steps:
 1. Backup the current version of the DB
 2. Apply the necessary update scripts (apply the update scripts incrementally if multiple updates are required required)
