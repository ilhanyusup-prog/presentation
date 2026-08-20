# presentation

Almuhib solar proposal deck, rebuilt as an editable 16:9 PowerPoint file with
python-pptx. The source proposal is a 9:16 portrait document, so every slide is
re-composed for a 13.333 x 7.5 in canvas while keeping the original type scale,
palette and block order.

## Build

```bash
pip install -r requirements.txt
python build_presentation.py     # writes presentation.pptx
```

Slide 1 (cover) is done. Remaining slides are added one at a time as
`build_slide_N` functions in `build_presentation.py`.

Type is set in Arial and Arial Black, matching the source. Install those fonts
(or let PowerPoint substitute them) to see the intended metrics.

`assets/` holds the artwork lifted from the source document: the villa render
and the Almuhib logo. Everything else on the slide — background, text, rules,
the progress rail and the page number — is a native, editable PowerPoint shape.
