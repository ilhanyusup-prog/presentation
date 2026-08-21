# presentation

Almuhib solar proposal deck, rebuilt as an editable PowerPoint file with
python-pptx. The layout follows the reference document: the reference page is
810 x 1440 pt, so the deck keeps its portrait 9:16 proportions at 7.5 x 13.333
in, which is the reference at two thirds scale.

## Build

```bash
pip install -r requirements.txt
python build_presentation.py     # writes presentation.pptx
```

All 13 slides live as `deck/slide_NN.py` against the shared helpers in
`deck/kit.py`. Positions, sizes, type sizes and letter spacing are written in
reference points and converted once through `u()` and `size()`.

```bash
python tools/reference.py 7      # dump a reference page
python tools/check.py 7          # compare one built slide to the reference
python tools/check.py --all
```

Type is set in Arial and Arial Black, matching the source. Install those fonts
(or let PowerPoint substitute them) to see the intended metrics.

`assets/` holds artwork lifted from the source: the cover villa render, both
logos, and the line-art icons. Empty picture frames stay as named slots with
live labels so a real photo can drop in later. Everything else — background,
text, rules, cards, the progress rail and the page number — is a native
editable PowerPoint shape.
