## Improvements
### User Facing
#### Functionality
- way to edit SLO, PLO, ILO, GEO data from site
- use Bootstrap validation features
    - on non-numeric, show error when changing back
- Need easy ability to update groups of SLOs based on mappings
    - Mappings are not always known when SLO is submitted
    
#### Usability
- loading icon
- maybe apply a bootstrap theme? (i.e. http://startbootstrap.com/template-overviews/sb-admin-2/)

### Security
- Rate limiter to select onChange calls?
- remove use of inline css and js for tighter CSP (jquery, i'm looking (mostly) at you!)
- pre-compile handlebars templates for tighter CSP

## Musings
- What about changes to mappings/statements for historical? How do we archive?

## Uncategorized
- Maybe use react.js instead of handlebars and jquery?
- Move course/section/ILO/PLO/GEO/etc data to database and make editing interface