# presentation

Almuhib solar proposal deck, rebuilt as an editable PowerPoint file with
python-pptx. The layout follows the reference document exactly: the reference
page is 810 x 1440 pt, so the deck keeps its portrait 9:16 proportions at
7.5 x 13.333 in, which is the reference at two thirds scale.

## Build

```bash
pip install -r requirements.txt
python build_presentation.py     # writes presentation.pptx
```

Slide 1 (cover) is done. Remaining slides are added one at a time as
`build_slide_N` functions in `build_presentation.py`.

Every position, size, type size and letter spacing in the script is written in
reference points and converted once through `u()` and `size()`, so the code can
be read straight against the source page.

Type is set in Arial and Arial Black, matching the source. Install those fonts
(or let PowerPoint substitute them) to see the intended metrics.

`assets/` holds the artwork lifted from the source document: the villa render
and the Almuhib logo. Everything else on the slide — background, text, rules,
the progress rail and the page number — is a native, editable PowerPoint shape.
