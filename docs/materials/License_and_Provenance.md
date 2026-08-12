# License, provenance and naming

**Status:** 23 July 2026 · risk items 3 and 4 from
`Lastenheft_Pflichtenheft_Blueprint.md`, ch. 21 (English translation/rename pending).

> This document describes the technical and organisational situation and the
> working rules derived from it. It is **not legal advice**. Before publication, a
> legally responsible body at your own institution should confirm items 1 and 3.

---

## 1. Two licenses

| | Version 1 | Version 2 |
| --- | --- | --- |
| Author | Université de La Rochelle and others | Ralf Erlebach |
| License | CeCILL-B | GNU GPL v3 or later |
| Period | 2013–2018 | from 2026 |

Moodle plugins must be licensed compatibly with GPL v3; the new code base is
therefore GPL v3+. CeCILL-B is a permissive license under French law with a strong
**attribution and citation obligation**.

**Practical consequence:** the main risk is not license compatibility but
traceability — who took which line from where.

---

## 2. Working rules for development

1. **Default path: re-implementation from the specification.** The functional
   behaviour of V1 (parser rules, normalisation, tolerance measure, hint levels) is
   captured as a *behavioural description with reference cases* and re-implemented
   from that. Reference cases are test data, not code.
2. **No copy-paste without labelling.** If, exceptionally, a passage is taken over,
   the file receives an additional provenance note with the source file, source
   license and original copyright, and the event is logged in this document
   (section 5).
3. **No adoption of third-party libraries from V1.** Enyo, Bootstrap 3, the bundled
   jQuery version and the Bower configuration are dropped entirely.
   `thirdpartylibs.xml` is only created if third-party code is actually shipped —
   which is currently not the case.
4. **Language files and icons** are created anew, not taken over.
5. **Documentation and screenshots** from V1 are not taken over.

---

## 3. The component name `mod_elang`

Keeping the name is technically correct — it is the precondition for an upgrade
path instead of a parallel installation. It does, however, raise two questions that
must be resolved **before** publication:

1. **Entry in the Moodle plugins directory.** The `mod_elang` entry belongs to the
   original authors.

   **As of 23/07/2026:** contact has already been made with **Christophe Demko**,
   the maintainer of `mod_elang` 1.x, about continuing the maintainership. That is
   the intended and likely route.

   Until written confirmation is available, the fallback path from section 3.3
   formally remains. To be clarified during the handover: takeover or
   co-maintainership of the directory entry, handling of the existing GitHub
   repository (continuation, fork or new repository), and attribution of the
   original authorship in the release notes and `README.md`.
2. **Expectations of existing users.** A release under the same component with a
   completely new data model and interface must be communicated unmistakably as a
   major version change: release notes, an upgrade notice, a migration guide, and an
   explicit reminder of the recommendation to back up before upgrading.

### 3.3 Fallback variant

Should the handover, against expectations, not come about, the fallback variant is a
new component name with a migration tool from `mod_elang` — technically more
involved, but without a naming conflict. The decision should be made **before**
phase 2, because it affects the migration concept.

As long as the question is open, the migration concept is built so that it supports
both routes: the migration reads the legacy tables by their names and does not
depend on the source and target being the same component.

---

## 4. Attributions

Regardless of the legal outcome: the original work is named in `README.md` and in
the release notes. Version 2.0 is a new development, but it visibly stands in the
tradition of the original — that deserves to be stated.

---

## 5. Log of adopted passages

| Date | Target file | Origin | Extent | Note set |
| --- | --- | --- | --- | --- |
| — | — | — | — | — |

*(Empty. Release 2.0.0-alpha.1 contains no line taken over from version 1.)*
