# SelfHelp on the TPF portfolio (October CMS)

Everything needed to show SelfHelp at <https://www.tpf.philhum.unibe.ch/portfolio> in the same style as Streamix,
Sessions Wrapper and the other projects. No theme, plugin or PHP file has to be changed - it is **22 images, one block
of CSS and two pieces of markup**, all pasted in the October back end. Plan about 15 minutes.

| Step | What | File in this folder | Where in the October back end |
|---|---|---|---|
| 1 | Upload the images | `projectImgs/selfhelp/` (22 files, 5.3 MB) | **Media** > `projectImgs` > new folder `selfhelp` |
| 2 | Add the CSS | `paste/2-css-append-to-tpf.css` | **CMS > Assets > css > `tpf.css`** (end of the file) |
| 3 | Create the page | `paste/3-selfhelp-page-markup.htm` | **CMS > Pages > Add** (Markup) |
| 4 | Check, then publish | - | open `/portfolio/selfhelp`, untick **Hidden** |
| 5 | Add the card to the overview | `paste/5-portfolio-page-markup.htm` | **CMS > Pages > `portfolio`** (Markup) |

The number in each `paste` file name is the step number. Open the file in any text editor (Notepad, VS Code), press
**Ctrl+A**, **Ctrl+C**, switch to the October editor and press **Ctrl+V**.

---

## Step 1 - Upload the images (Media)

1. Log in to <https://www.tpf.philhum.unibe.ch/backend> and open **Media**.
2. Open the folder `projectImgs` (the one that contains `streamix`, ...). Click **Add folder**, name it exactly
   `selfhelp` and open it.
3. Click **Upload** (or drag the files into the window) and select **all 22 files** from `projectImgs/selfhelp/`
   of this folder.
4. Test one picture in the browser. This address must show the dark-blue banner:
   <https://www.tpf.philhum.unibe.ch/storage/app/media/projectImgs/selfhelp/selfhelp_banner.jpg>

| Files | Used for |
|---|---|
| `selfhelp_banner.jpg` (2560 x 512) | banner background of the page |
| `selfhelp.png` | white SelfHelp logo in the banner |
| `selfhelp_eval.png` | picture in the grey header of the card on `/portfolio` |
| `01_overview.jpg` ... `18_architecture.jpg` (1600 x 900) | the 18 pictures of the six chapters |
| `19_publications.jpg` (1600 x 900) | picture of the research chapter |

Keep the file names and the folder exactly as they are: the markup links to
`/storage/app/media/projectImgs/selfhelp/<file name>` (the same pattern as `projectImgs/streamix/streamix.png`).

## Step 2 - Add the CSS (Assets)

The page re-uses the theme's own classes (`portfolioTitle`, `maskDim`, `projectTitle`, `linkObject`, ...). Only the
banner picture, a mask for it and the small chapter dividers ("01 - Build", ...) are new: 67 lines.

1. Back end > **CMS** > **Assets** (left column) > `css` > `tpf.css`.
2. Click into the editor, press **Ctrl+End** (jump to the end of the file) and add an empty line.
3. Paste the complete content of `paste/2-css-append-to-tpf.css`.
4. **Save** (Ctrl+S).

Every rule starts with `.selfhelpTitle`, `.selfhelpPage` or `.selfhelpSection`, so the other portfolio pages are not
affected. The theme loads `tpf.css` through the October asset combiner, which refreshes itself when the file is saved.
If you still see the old look, press **Ctrl+F5**.

## Step 3 - Create the page (CMS > Pages)

1. Back end > **CMS** > **Pages** > **Add**.
2. Fill in the page settings (the same fields as on the other project pages):

   | Field | Value |
   |---|---|
   | Title | `SelfHelp` |
   | URL | `/portfolio/selfhelp` |
   | File name | `selfhelp` |
   | Layout | `backButton` |
   | Description | `SelfHelp Portfolio` |
   | Hidden | **tick it** - until you have checked the page only back-end users can open it |

3. Open the **Markup** tab, click into the editor and paste the complete content of
   `paste/3-selfhelp-page-markup.htm`. The first line is
   `{% partial 'site/backButton' link = '/portfolio' label = 'Portfolio' %}` - it is the "Portfolio" back button above
   the banner, exactly as on the Rigged Body Animation and Moog pages.
4. Leave the **Code** tab empty - this page does not need any PHP.
5. **Save**.

## Step 4 - Check the page, then publish it

1. Stay logged in to the back end and open <https://www.tpf.philhum.unibe.ch/portfolio/selfhelp>.
2. Check:
   - [ ] Banner: dark-blue picture on the right, the white SelfHelp logo (bug) on the left, title **SelfHelp** and the
     sub-title in white.
   - [ ] **SelfHelp Summary** card with a red header (11 bullet points) next to *The story behind SelfHelp*.
   - [ ] Seven chapters, *01 - Build* to *07 - Research*, each with a short red bar and a thin line.
   - [ ] A click on a picture opens it large (lightbox) with its title.
   - [ ] **Publications** card: three groups (7 / 4 / 4), every entry opens its DOI page.
   - [ ] **Links** card and **Plugins on GitHub** card (15 plugins).
   - [ ] The **Portfolio** back button leads to `/portfolio`.
   - [ ] On a phone (or with a narrow browser window) text and pictures stack and nothing runs out of the screen.
3. All fine? Open **CMS > Pages > selfhelp**, **untick Hidden**, **Save**.

## Step 5 - Add the SelfHelp card to the portfolio overview

The card goes right after the Streamix card. Everything else stays as it is, including the
`{% for record in records ... %}` loop that renders the other projects.

1. Back end > **CMS** > **Pages** > `portfolio`.
2. Open the **Markup** tab, click into the editor, press **Ctrl+A** (select everything) and paste the complete content
   of `paste/5-portfolio-page-markup.htm`. It is the current markup of the page, unchanged, plus one block between the
   two Twig comments `{# ===== SelfHelp card - start ... #}` and `{# ===== SelfHelp card - end ===== #}`.
3. Do not touch the settings or the **Code** tab of the page.
4. **Save** and open <https://www.tpf.philhum.unibe.ch/portfolio>. The SelfHelp card is the second one;
   **Read more** opens `/portfolio/selfhelp`.

---

## If something looks wrong

| What you see | Reason | Fix |
|---|---|---|
| Plain grey banner, chapter titles without red bar | CSS from step 2 missing or not saved | check the end of `tpf.css`, save, **Ctrl+F5** |
| Broken-picture icons | folder or file names differ | Media must contain `projectImgs/selfhelp/` with the 22 original file names |
| "Page not found" on `/portfolio/selfhelp` | **Hidden** is still ticked and you are not logged in to the back end, or the URL differs | untick **Hidden**; URL must be `/portfolio/selfhelp` |
| Text like `{% partial ... %}` visible on the page | markup pasted into the wrong tab, or the whole `theme-files/selfhelp.htm` was pasted | paste only `paste/3-...htm`, only into **Markup** |
| Page ends in the middle of the publications | the paste was cut off | select the whole file again (Ctrl+A) and paste again |
| Card on `/portfolio` without picture | `selfhelp_eval.png` is missing in Media | upload it (step 1) |

## Take it off again

1. **Pages > portfolio > Markup**: delete everything from `{# ===== SelfHelp card - start` to
   `{# ===== SelfHelp card - end ===== #}` (both lines included), save.
2. **Pages > selfhelp**: delete the page.
3. **Assets > css > tpf.css**: delete the block that starts with `/* SelfHelp portfolio page` down to the end of the file.
4. **Media**: delete the folder `projectImgs/selfhelp`.

## Maintaining the page later

- **New publication:** copy one `<a href="https://doi.org/..." class="linkObject rounded border mt-1" ...> ... </a>`
  block inside the **Publications** card, paste it into the right group and change DOI and text. Then update the
  numbers: the red badge of the group, the sentence "They cover ... (7), ... (4) and ... (4)" in chapter 07, the
  summary bullet "Studies built with SelfHelp are reported in 15 journal articles", the second paragraph of the card in step 5 and the picture
  `19_publications.jpg`.
- **New plugin:** copy one entry of the **Plugins on GitHub** card; "15 public plugins" appears in the summary,
  the card in step 5, the chapter *Fifteen plugins - and your own*, `16_plugins.jpg` and `selfhelp_banner.jpg`.
- **Plugins that are deliberately not listed:** the ones you excluded (filters, unipark, shepard, jsPlumb, calendar,
  sleep efficiency, forest chat), the private `studybuddy` plugin, `sh-shp-search` (its README is a copy of the
  calendar plugin) and `sh2-shp-survey-js` (belongs to SelfHelp 2, the Symfony version).

## Optional: theme files (only if the theme is deployed from Git / FTP)

`theme-files/selfhelp.htm` is the page of step 3 as one file (settings + markup). Copy it to
`themes/zanor-zanor-mdb-loaded/pages/selfhelp.htm`. Steps 1, 2 and 5 are still needed. Do **not** paste this file into
the October editor - it contains the settings block (`title = ...`, `==`) that the editor has as separate fields.
