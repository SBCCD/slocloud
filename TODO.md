## Demo for Open Source?
- remove sensitive information (i.e. passwords) from git
- remove sbccd, chc, and sbvc configs from git (history too)
- change dev password and use it for demos (remove password from history)

## Improvements
### Technical
- deploy process
- precompile templates
- reduce ajax requests

### User Facing
#### Functionality
- way to edit SLO, PLO, ILO, GEO data from site
- way to edit submitted SLOs when needed
    - Maybe a way to start from an already submitted one?
- use Bootstrap validation features
    - on non-numeric, show error when changing back
- Need easy ability to update groups of SLOs based on mappings
    - Mappings are not always known when SLO is submitted
    
#### Usability
- loading icon
- apply a bootstrap theme (http://startbootstrap.com/template-overviews/sb-admin-2/)

### Security
- Rate limiter to select onChange calls?
- remove use of inline css and js for tighter CSP (jquery, i'm looking (mostly) at you!)
- pre-compile handlebars templates for tighter CSP

## Musings
- What about changes to mappings/statements for historical? How do we archive?

## Uncategorized
- Move data functions to Model Code. Have to divide into institution specific and general code.
- Maybe use react.js instead of handlebars and jquery?
- Move course/section/ILO/PLO/GEO/etc data to database and make editing interface